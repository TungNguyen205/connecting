<?php
namespace App\Social;
use App\Repository\SocialRepository;
use Firebase\JWT\JWT;
class Facebook
{
    private $fb;
    private $socialRepository;

    public function __construct(SocialRepository $socialRepository)
    {
        $this->socialRepository = $socialRepository;
        $this->fb = new \Facebook\Facebook([
            'app_id' => config('facebook.app_id'),
            'app_secret' => config('facebook.app_secret'),
            'graph_api_version' => config('facebook.api_version'),
        ]);
    }

    public function generateUrl($token, $socialId = null)
    {
        $permissions = config('facebook.permission');
        $state = ['token' => $token, 'socialType' => config('facebook.social_name'), 'action' => config('social.action.auth'), 'socialId' => $socialId];

        $helper = $this->fb->getRedirectLoginHelper();
        $pdata = $helper->getPersistentDataHandler();
        $pdata->set('state', json_encode($state));
        $loginUrl = $helper->getLoginUrl(route('social.callback'), $permissions);

        return $loginUrl;
    }

    public function auth($request)
    {
        $token = $this->getVerifyToken();
        $profileData =  $this->requestFacebookData('/me?fields=id,name,picture', 'get', [], $token);
        if(!$profileData['status']) {
            return [
                'status' => false,
                'message' =>  "a"
            ];
        }
        $profile = ['name' => @$profileData['data']['name'], 'avatar' => @$profileData['data']['picture']['data']['url']];

        $response = $this->requestFacebookData('/me/accounts', 'get', [], $token);
        if(!$response['status']) {
            return [
                'status' => false,
                'message' => "b",
                'data' => []
            ];
        }

        foreach ($response['data'] as $page) {
            $item = [
                'social_id' => $page['id'],
                'social_url' => config('facebook.url.base').$page['id'],
                'name' => $page['name'],
                'username' => @$profileData['data']['name'],
                'avatar' => $this->getPageAvatar($page['id']),
                'social_type' => config('facebook.social_name'),
                'access_token'  => [
                    'page' => $page['access_token'],
                    'user' => $token
                ],
                'shop_id'   => $request['userInfo']['id']
            ];
            $data = $this->socialRepository->createOrUpdate($item);
            return response()->json(['status' => true, 'data' => $data]);
        }
    }

    protected function requestFacebookData($endpoint, $method, $param, $token) {

        $data = [];
        $success = true;
        $code = '';
        switch ($method) {
            case 'get' :
                $response =  $this->fb->get(
                    $endpoint,
                    $token
                );
                $data = $response->getDecodedBody();
                $code = $response->getHttpStatusCode();
                break;
            case 'post' :
                $response =  $this->fb->post(
                    $endpoint,
                    $param,
                    $token
                );
                $data = $response->getDecodedBody();
                $code = $response->getHttpStatusCode();
                break;
            case 'put' :
                $response =  $this->fb->put(
                    $endpoint,

                    $param,
                    $token
                );
                $data = $response->getDecodedBody();
                $code = $response->getHttpStatusCode();
                break;
            case 'delete' :
                $response =  $this->fb->delete(
                    $endpoint,
                    $param,
                    $token
                );
                $data = $response->getDecodedBody();
                $code = $response->getHttpStatusCode();
                break;
            default :
                //request is invalid
                $success = false;
                break;
        }


        return [
            'status'=> $success,
            'message' => '',
            'data' => isset($data['data']) ? $data['data'] : $data,
            'code' => $code
        ];


    }

    protected function getVerifyToken(){
        $helper = $this->fb->getRedirectLoginHelper();

        if (isset($_GET['state'])) {
            $helper->getPersistentDataHandler()->set('state', $_GET['state']);
        }
        try {
            $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            return '';
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            return '';
        }

        if (! isset($accessToken)) {
            return '';
        }


        $oAuth2Client = $this->fb->getOAuth2Client();

        $tokenMetadata = $oAuth2Client->debugToken($accessToken);


        $tokenMetadata->validateAppId(config('facebook.app_id'));

        $tokenMetadata->validateExpiration();

        if (! $accessToken->isLongLived()) {
            // Exchanges a short-lived access token for a long-lived one
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                return '';
            }


        }
        return $accessToken->getValue();
    }

    private function getPageAvatar($pageId) {
        return config('facebook.url.api').config('facebook.api_version').'/'.$pageId.'/picture';
    }

    public function postSocial($data){
        $account = $data['social'];

        try {
            $token = $this->getToken($account['access_token'], 'page');

            $type = $data['post_type'];
            $source = [];

            switch ($type) {
                case config('facebook.post_type.link'):
                    $source = $this->processForLinkType($token, $data);
                    break;
                case config('facebook.post_type.text'):
                    $source =[
                        'status' => true,
                        'data' => [
                            'published' => true,
                            'message' => $data['message'],
                        ]
                    ] ;
                    break;
                case config('facebook.post_type.image'):
                    $source = $this->processForImageType($token, $data);
                    break;
                case config('facebook.post_type.video'):
                    break;
            }

            if (!$source['status']) {
                $this->proccessError($account['social_id'], $source);
                return $source;
            }
            $response = $this->requestFacebookData('/me/feed', 'post', $source['data'], $token);
//            dd($response);
//            if ($response['status']) {
//                $response['data']['post_social_id'] = $response['data']['id'];
//                unset( $response['data']['id']);
//            } else {
//                $this->proccessError($account['social_id'], $response);
//            }

        } catch (\Exception $ex) {
            $response = [
                'status' => false,
                'data' => '',
                'message' => $ex->getMessage(),
                'code' => $ex->getCode()
            ];
            $this->proccessError($account['social_id'], $response);
        }

        return $response;
    }

    private function getToken($access_token, $type) {
        return $access_token[$type];
    }

    private function processForLinkType($token,$data){
        $source = [
            'published' => true,
            'message' => $data['message'],
            'link' => $data['meta_link']
        ];

        return [
            'status' => true,
            'data' => $source
        ];
    }

    private function processForProductType($token,$data){
        $subType =  $data['sub_type'];
        if($subType == config('social.post_sub_type.image')) {
            return $this->processForImageType($token,$data);
        } else {

            $source = [
                'published' => true,
                'message' => $data['message'],
                'link' => $data['meta_link']
            ];
        }
        return [
            'status' => true,
            'data' => $source
        ];
    }

    private function processForImageType($token,$data){
        $source = [
            'published' => true,
            'message' => $data['message'],
        ];

        $result = $this->upMedia($token, $data['medias']);


        if (!$result['status']) {
            return $result;
        }
        $images = $result['data'];
        foreach ($images as $image){
            $attachMedia[] = ['media_fbid' => $image];
        }
        $source['attached_media'] = $attachMedia;
        return [
            'status' => true,
            'data' => $source
        ];
    }

    protected  function upMedia($token, $sources) {
        $post_images = [];
        $photos = [];
        if (empty($sources)) {
            return [
                'status' => false,
                'data' => [],
                'message' =>  __("socialpost_validation.get_facebook_infor_error"),
            ];
        }
        try {
            foreach ($sources as $source ) {
                $data = [
                    'published' =>false,
                    'source' =>    $this->fb->fileToUpload($source['url'])
                ];
                array_push($photos, $this->fb->request('POST','/me/photos',$data));
            }


            $uploaded_photos = $this->fb->sendBatchRequest($photos,  $token);
            if($uploaded_photos->getHttpStatusCode() == 200) {
                $respone = $uploaded_photos->getDecodedBody();

                foreach ($respone as $item) {
                    $payload = (json_decode($item['body'], true));
                    $post_images[] = $payload['id'];
                }



                return [
                    'status' => true,
                    'data' => $post_images
                ];
            } else {
                return [
                    'status' => false,
                    'data' => [],
                    'code' => $uploaded_photos->getHttpStatusCode()
                ];
            }

        } catch (\Exception $ex) {
            return [
                'status' => false,
                'data' => [],
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

    }

    private function proccessError($id,$error){
        dd($error);
        if ( isset($error['code']) && in_array($error['code'],config('social.facebook.reconnect_code') )) {
            $sa = SocialAccount::where('social_id', $id)->first();
            $sa->connect_error = [
                'code' => $error['code'],
                'message' => $error['message']
            ];
            $sa->save();
        }
    }

}