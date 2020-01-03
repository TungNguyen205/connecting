<?php
namespace App\Social;
use App\Repository\SocialRepository;
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
}