<?php
namespace App\Social;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use App\Repository\SocialRepository;
class Twitter
{
    private $baseUrl;
    private $cusumerKey;
    private $secretKey;
    private $accessToken;
    private $accessTokenSecret;
    private $socialRepository;

    public function __construct(SocialRepository $socialRepository)
    {
        $this->baseUrl = config('twitter.url.base');
        $this->cusumerKey = config('twitter.consumer_key');
        $this->secretKey = config('twitter.consumer_secret');
        $this->accessToken = config('twitter.access_token');
        $this->accessTokenSecret = config('twitter.access_token_secret');
        $this->socialRepository = $socialRepository;
    }

    public function generateUrl($token)
    {
        return $this->generateAuthUrl($token);
    }

    public function auth($request)
    {
        return $this->authorize($request);
    }

    private function authorize($request)
    {
        if (isset($request['denied'])) {
            return [
                'status' => false,
                'message' => 'It looks like you haven\'t authenticated your Twitter account yet.',
            ];
        }
        $oauth_token = $request['oauth_token'];
        $oauth_verifier = $request['oauth_verifier'];
        $userInfo = $request['userInfo'];
        $accessToken = $this->accessToken($oauth_token, $oauth_verifier);
        if (!$accessToken['status']) {
            return [
                'status' => false,
                'message' => 'Can not connect Twitter',
            ];
        }
        $this->setParameter($accessToken['data']['oauth_token'], $accessToken['data']['oauth_token_secret']);
        $options = array(
            'query' => array(
                'include_email' => 'true',
            ),
        );
        $verifyCredentials = $this->getOauth1('account/verify_credentials.json', $options);
        if($verifyCredentials['status']) {
            $data = $verifyCredentials['data'];
            $socialParams = [
                'social_id' => $data->id_str,
                'social_url' => 'https://twitter.com/' . $data->screen_name,
                'email' => isset($data->email)? $data: null,
                'name' => $verifyCredentials['data']->name,
                'slug' => $verifyCredentials['data']->screen_name,
                'avatar' => $verifyCredentials['data']->profile_image_url_https,
                'social_type' => config('twitter.social_name'),
                'access_token' => array(
                    'oauth_token' => $accessToken['data']['oauth_token'],
                    'oauth_token_secret' => $accessToken['data']['oauth_token_secret'],
                ),
                'shop_id' => $userInfo['id'],
            ];

            return $this->socialRepository->createOrUpdate($socialParams);
        }

        return ['status' => true, 'data' => 'a'];
    }

    private function setParameter(
        string $twitter_access_token = null,
        string $twitter_access_token_secret = null,
        string $base_url_api = null,
        bool $version = true
    ) {
        if (!is_null($base_url_api)) {
            $this->baseUrl = "https://{$base_url_api}/";
        } else {
            $this->baseUrl = config('twitter.url.base');
        }
        if ($version) {
            $this->baseUrl .= config('twitter.api_version') . '/';
        }
        $this->accessToken = $twitter_access_token;
        $this->accessTokenSecret = $twitter_access_token_secret;

        return $this;
    }

    private function accessToken($oauth_token, $oauth_verifier)
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->cusumerKey,
            'consumer_secret' => $this->secretKey,
            'token' => $oauth_token,
            'verifier' => $oauth_verifier,
            'token_secret' => '',
        ]);

        $stack->push($middleware);
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $response = $client->post('oauth/access_token');
            $result = explode('&', (string)$response->getBody());
            $arrResult = array();
            foreach ($result as $item) {
                $tmp = explode('=', $item);
                $arrResult[$tmp[0]] = $tmp[1];
            }

            return ['status' => true, 'data' => $arrResult];
        } catch (ClientException $e) {
            return $this->handleClientException($e);
        }
    }


    private function generateAuthUrl($token, $socialId = null, $socialAccount = null)
    {
        $requestToken = $this->requestToken($token, $socialId);
        if ($requestToken['status']) {
            $autoFill =  !empty($socialAccount) ? $socialAccount['slug'] : "";
            return config('twitter.url.authorize') . "?oauth_token={$requestToken['data']['oauth_token']}&force_login=true&screen_name="
                . $autoFill;
        }

        return false;
    }
    /**
     * @param $url
     * @param array $options
     * @return array
     */
    private function getOauth1($url, $options = array())
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => $this->cusumerKey,
            'consumer_secret' => $this->secretKey,
            'token' => $this->accessToken,
            'token_secret' => $this->accessTokenSecret,
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $response = $client->get($url, $options);

            return [
                'status' => true,
                'data' => json_decode($response->getBody()->getContents()),
            ];
        } catch (ClientException $e) {
            return $this->handleClientException($e);
        }
    }


    public function requestToken($token, $socialId = null)
    {
        $stack = HandlerStack::create();
        $state = array(
            'token' => $token,
            'socialType' => config('twitter.social_name'),
            'action' => empty($socialId) ? config('social.action.auth') : config('social.action.re_auth'),
            'socialId' => $socialId,
        );

        $middleware = new Oauth1([
            'consumer_key' => $this->cusumerKey,
            'consumer_secret' => $this->secretKey,
            'token' => $this->accessToken,
            'token_secret' => $this->accessTokenSecret,
            'callback' => route('social.callback') . "?state=" . urlencode(json_encode($state)),
        ]);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
        try {
            $response = $client->post('oauth/request_token');
            $result = explode('&', (string)$response->getBody());
            $arrResult = array();
            foreach ($result as $item) {
                $tmp = explode('=', $item);
                $arrResult[$tmp[0]] = $tmp[1];
            }

            return ['status' => true, 'data' => $arrResult];
        } catch (ClientException $e) {
            return $this->handleClientException($e);
        }
    }

    private function handleClientException($exception)
    {
        $response = $exception->getResponse();
        $header = $response->getHeaderLine('content-type');
        if (strpos($header, 'application/json') !== false) {
            $errors = json_decode($response->getBody()->getContents(), true)['errors'][0];
            // Update Database when social account error
//            $this->updateSocialAccountError($errors);

            return ['status' => false, 'message' => $errors['message'], 'code' => @$errors['code']];
        }
        $xml = simplexml_load_string($response->getBody()->getContents());
        $errors = json_decode(json_encode($xml), true)['error'];

        return ['status' => false, 'message' => $errors];
    }


    public function getRequest(string $url, array $data = []) : array
    {
        $client = new Client();
        try{
            $response = $client->request('GET', $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'query' => $data
                ]
            );

            return [
                'status' => true,
                'data'      => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function postRequest(string $url, array $data = []) : array
    {
        $client = new Client();
        try{
            $response = $client->request('POST', $url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($data)
                ]
            );

            return [
                'status' => true,
                'data'      => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function postSocial($data)
    {
        $type = $data['post_type'];
        $option = [];

        switch ($type) {
            case config('twitter.post_type.link'):
                $option = $this->processForLinkType($data);
                break;
            case config('twitter.post_type.text'):
                $query['status'] = $data['message'];
                $option =[
                    'status' => true,
                    'data' => [
                        'query' => $query
                    ]
                ] ;
                break;
            case config('facebook.post_type.image'):
                $option = $this->processForImageType($data);
                break;
            case config('facebook.post_type.video'):
                break;
        }
        if(!$option['status']) {
            return false;
        }
        $authToken = $data['social']['access_token']['oauth_token'];
        $authTokenSecret = $data['social']['access_token']['oauth_token_secret'];
        $this->setParameter($authToken, $authTokenSecret);
        $option = $option['data'];

        $result = $this->postOauth1('statuses/update.json', $option);
        if (!$result['status']) {
            return [
                'status' => false,
                'message' => $result['message'],
                'code' => @$result['code'],
            ];
        }
        return [
            'status' => true,
            'data' => [
                'post_social_id' => $result['data']->id_str
            ]
        ];
    }

    public function processForImageType($data)
    {
        $query['status'] = $data['message'];
        $media_ids = [];
        foreach ($data['medias'] as $media) {
            $mediaUpload = $this->mediaUpload($data['social']['access_token']['oauth_token'], $data['social']['access_token']['oauth_token_secret'],
                $media['url']);
            if (!$mediaUpload['status']) {
                return [
                    'status' => false,
                    'message' => $mediaUpload['message'],
                    'code' => @$mediaUpload['code'],
                ];
            }
            array_push($media_ids, $mediaUpload['data']->media_id_string);
        }

        try {
            foreach ($media_ids as $media_id) {
                while (true) {
                    $mediaUploadStatus = $this->mediaUploadStatus($media_id);
                    if (isset($mediaUploadStatus['data']->processing_info->state) &&
                        $mediaUploadStatus['data']->processing_info->state === 'failed') {
                        return [
                            'status' => false,
                            'message' => $mediaUploadStatus['data']->processing_info->error->message,
                            'code' => @$mediaUploadStatus['data']->processing_info->error->code,
                        ];
                    }
                    if (isset($mediaUploadStatus['data']->processing_info->state) &&
                        $mediaUploadStatus['data']->processing_info->state !== 'succeeded') {
                        if (isset($mediaUploadStatus['data']->processing_info->check_after_secs)) {
                            sleep($mediaUploadStatus['data']->processing_info->check_after_secs);
                        } else {
                            sleep(3);
                        }
                        continue;
                    }
                    break;
                }
            }

            if (!empty($media_ids)) {
                $query['media_ids'] = implode(',', $media_ids);
            }
            return [
                'status' => true,
                'data'  => [
                    'query' => $query
                ]
            ];
        } catch (\Exception $exception) {
            return [
                'status' => false,
                'message' => __('socialpost_validation.twitter.post.error'),
                'code' => $exception->getCode(),
            ];
        }
    }

    public function processForLinkType($data)
    {
        $query['status'] = $data['message']. ' '. $data['meta_link'];
        $option = [
            'query' => $query
        ];
        return [
            'status' => true,
            'data' => $option
        ];
    }

    private function mediaUpload($twitter_access_token, $twitter_access_token_secret, $media_path)
    {
        $this->setParameter($twitter_access_token, $twitter_access_token_secret, 'upload.twitter.com');
//        try {
            $mediaUploadInit = $this->mediaUploadInit($media_path);
            if (!$mediaUploadInit['status']) {
                return $mediaUploadInit;
            }
            $mediaUploadAppend = $this->mediaUploadAppend($mediaUploadInit['data']->media_id_string, $media_path);
            if (!$mediaUploadAppend['status']) {
                return $mediaUploadAppend;
            }
            $mediaUploadFinalize = $this->mediaUploadFinalize($mediaUploadInit['data']->media_id_string);

            return $mediaUploadFinalize;
//        } catch (\Exception $exception) {
//            return ['status' => false, 'message' => $exception->getMessage()];
//        }
    }

    private function mediaUploadInit($media_path)
    {
        $path_parts = pathinfo($media_path);
        $mediaExtension = $path_parts['extension'];
        $mediaContentLength = get_headers($media_path, 1)['Content-Length'];
        $mediaContentType = get_headers($media_path, 1)['Content-Type'];
//        try {
            $query = array(
                'command' => 'INIT',
                'total_bytes' => $mediaContentLength,
                'media_type' => $mediaContentType,
            );
            if (in_array($mediaExtension, config('twitter.media.type_video'))) {
                $query['media_category'] = config('twitter.media.media_category.tweet.tweet_video');
            } elseif (in_array($mediaExtension, config('twitter.media.type_image'))) {
                $query['media_category'] = config('twitter.media.media_category.tweet.tweet_image');
            } elseif (in_array($mediaExtension, config('twitter.media.type_gif'))) {
                $query['media_category'] = config('twitter.media.media_category.tweet.tweet_gif');
            }
            $options = array(
                'query' => $query,
            );
            $result = $this->postOauth1('media/upload.json', $options);

            return $result;
//        } catch (\Exception $exception) {
//            return ['status' => false, 'message' => $exception->getMessage()];
//        }
    }

    private function mediaUploadAppend($media_id, $media_path)
    {
        $sizeChunk = 1 * (1024 * 1024);
//        try {
            $mediaContentLength = get_headers($media_path, 1)['Content-Length'];
            $fp = fopen($media_path, 'r');
            for ($i = 0; $i <= $mediaContentLength / $sizeChunk; $i++) {
                $chunk = stream_get_contents($fp, $sizeChunk, $i * $sizeChunk);
                $options = array(
                    'multipart' => [
                        [
                            'name' => 'command',
                            'contents' => 'APPEND',
                        ],
                        [
                            'name' => 'media_id',
                            'contents' => $media_id,
                        ],
                        [
                            'name' => 'segment_index',
                            'contents' => $i,
                        ],
                        [
                            'name' => 'media_data',
                            'contents' => base64_encode($chunk),
                        ],
                    ],
                );
                $result = $this->postOauth1('media/upload.json', $options);
                if (!$result['status']) {
                    return $result;
                }
            }

            return ['status' => true];
//        } catch (\Exception $exception) {
//            return ['status' => false, 'message' => $exception->getMessage()];
//        }
    }

    private function mediaUploadFinalize($media_id)
    {
//        try {
            $options = array(
                'query' => [
                    'command' => 'FINALIZE',
                    'media_id' => $media_id,
                ],
            );
            $result = $this->postOauth1('media/upload.json', $options);

            return $result;
//        } catch (\Exception $exception) {
//            return ['status' => false, 'message' => $exception->getMessage()];
//        }
    }

    private function mediaUploadStatus($media_id)
    {
//        try {
            $options = array(
                'query' => [
                    'command' => 'STATUS',
                    'media_id' => $media_id,
                ],
            );
            $result = $this->getOauth1('media/upload.json', $options);

            return $result;
//        } catch (\Exception $exception) {
//            return ['status' => false, 'message' => $exception->getMessage()];
//        }
    }

    private function postOauth1($url, $options = array())
    {
        $settingAuth = array(
            'consumer_key' => $this->cusumerKey,
            'consumer_secret' => $this->secretKey,
            'token' => $this->accessToken,
            'token_secret' => $this->accessTokenSecret,
        );
        $stack = HandlerStack::create();
        $middleware = new Oauth1($settingAuth);
        $stack->push($middleware);
        $client = new Client([
            'base_uri' => $this->baseUrl,
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
//        try {
            $response = $client->post($url, $options);

            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];
//        } catch (ClientException $e) {
//            return $this->handleClientException($e);
//        }
    }

}
