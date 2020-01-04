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

}