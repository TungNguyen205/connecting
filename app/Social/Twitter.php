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

}
