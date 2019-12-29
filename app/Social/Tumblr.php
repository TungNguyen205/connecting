<?php
namespace App\Social;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Repository\SocialRepository;
use Tumblr\API;
use Firebase\JWT\JWT;
class Tumblr
{
    private $comsumerKey;
    private $comsumerSecret;
    private $baseUrl;
    private $socialRepository;
    private $client;

    public function __construct(SocialRepository $socialRepository)
    {
        $this->comsumerKey = config('tumblr.consumer_key');
        $this->comsumerSecret = config('tumblr.consumer_secret');
        $this->baseUrl = config('tumblr.url.base');
        $this->socialRepository = $socialRepository;
        $this->client = new API\Client($this->comsumerKey, $this->comsumerSecret);
    }

    public function generateUrl($token, $socialId = null)
    {
        $requestHandler = $this->client->getRequestHandler();
        $requestHandler->setBaseUrl("https://www.tumblr.com/");
        $state = [
            'token' => $token,
            'socialType' => config('tumblr.name'),
            'action' => empty($socialId) ? config('social.action.auth') : config('social.action.re_auth'),
            'socialId' => $socialId,
        ];
        $resp = $requestHandler->request('POST', 'oauth/request_token', [
            'oauth_callback' => route('social.callback') . "?state=" . urlencode(json_encode($state)),
        ]);
        if($resp->status == 200) {
            // get the oauth_token
            $out = $result = $resp->body;
            $data = array();
            parse_str($out, $data);
            $payload = JWT::decode($token, env('JWT_KEY'), ['HS256']);
            $params = [
                'social_type'   => config('tumblr.name'),
                'shop_id'   => $payload->id,
                'social_id' => null,
                'access_token'  => [
                    'oauth_token'   => $data['oauth_token'],
                    'oauth_token_secret'   => $data['oauth_token_secret']
                ]
            ];
            $this->socialRepository->createOrUpdate($params);
            return response()->json([
                'status' => true,
                'url'   => 'https://www.tumblr.com/oauth/authorize?oauth_token=' . $data['oauth_token']
            ]);
        }
    }

    public function auth($request)
    {
        $oauth_token = $request['oauth_token'];
        $oauth_verifier = $request['oauth_verifier'];
        $userInfo = $request['userInfo'];
        $tumblr = $this->socialRepository->getBy(['shop_id' => $userInfo['id'], 'social_type' => config('tumblr.name')]);
        if($tumblr) {
            $this->client->setToken($tumblr['access_token']['oauth_token'], $tumblr['access_token']['oauth_token_secret']);
            $requestHandler = $this->client->getRequestHandler();
            $requestHandler->setBaseUrl("https://www.tumblr.com/");
            $resp = $requestHandler->request('POST', 'oauth/access_token', array('oauth_verifier' => $oauth_verifier));
            if($resp->status == 200) {
                $out = $result = $resp->body;
                $data = array();
                parse_str($out, $data);
                $this->client->setToken($data['oauth_token'], $data['oauth_token_secret']);
                $requestHandler->setBaseUrl(config('tumblr.url.base'));
                $info = $this->client->getUserInfo();
                $params = [
                    'social_id' => null,
                    'social_url' => $info->user->blogs[0]->url,
                    'name' => $info->user->name,
                    'username' => $info->user->blogs[0]->name,
                    'social_type'   => config('tumblr.name'),
                    'shop_id'   => $userInfo['id'],
                    'access_token'  => [
                        'oauth_token'   => $data['oauth_token'],
                        'oauth_token_secret'   => $data['oauth_token_secret']
                    ]
                ];
                $this->socialRepository->createOrUpdate($params);
                return 'ok';
            }
        }

    }
}