<?php
namespace App\Social;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Repository\SocialRepository;
class Pinterest
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;

    public function __construct(SocialRepository $socialRepository)
    {
        $this->baseUrl = config('pinterest.url.base');
        $this->clientId = config('pinterest.client_id');
        $this->clientSecret = config('pinterest.client_secret');
        $this->socialRepository = $socialRepository;
    }

    public function generateUrl($token, $socialId = null)
    {
        $state = [
            'token' => $token,
            'socialType' => config('pinterest.name'),
            'action' => empty($socialId) ? config('social.action.auth') : config('social.action.re_auth'),
            'socialId' => $socialId,
        ];
        $params = [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'state' => json_encode($state),
            'scope' => implode(",", config('pinterest.permission')),
            'redirect_uri' => route('social.callback'),
        ];
        $url = $this->baseUrl."oauth?".http_build_query($params);
         return response()->json($url);
    }

    public function auth($request)
    {
        $this->setParameter();
        $params = [
            'grant_type'        => 'authorization_code',
            'client_id'         => $this->clientId,
            'client_secret'     => $this->clientSecret,
            'code'              => $request['code'],
        ];
        $accessTokenResponse = $this->postRequest('oauth/token', $params);
        if($accessTokenResponse['status']) {
            $userInfo = $this->userInfo($accessTokenResponse['data']->access_token);
            if($userInfo['status']) {
                $data = $userInfo['data']['data'];
                $params = [
                    'social_id' => $data['id'],
                    'social_url' => $data['url'],
                    'name' => $data['first_name'],
                    'username' => $data['username'],
                    'avatar' => $data['image']['60x60']['url'],
                    'social_type' => config('pinterest.name'),
                    'access_token' => $accessTokenResponse['data']->access_token,
                    'shop_id' => $request['userInfo']['id'],
                ];
                $data = $this->socialRepository->createOrUpdate($params);
                return response()->json(['status' => true, 'data' => $data]);
            }
        }
    }

    public function userInfo($accessToken)
    {
        $fields = [
            'account_type', 'bio', 'counts', 'created_at', 'first_name', 'id', 'image', 'last_name', 'url', 'username'
        ];
        return $this->getRequest('me', [
            'access_token'  => $accessToken,
            'fields'        => implode(",", $fields)
        ]);
    }

    private function setParameter(
        string $accessToken = null,
        bool $version = true
    ) {
        if ($version) {
            $this->baseUrl .= config('pinterest.api_version') . '/';
        }
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRequest(string $url, array $data = []) : array
    {
        $client = new Client();
        try{
            $response = $client->request('GET', "$this->baseUrl$url",
                [
                    'query' => $data
                ]
            );

            return ['status' => true,
                'data'      => json_decode($response->getBody()->getContents(), true)
            ];
        } catch (\Exception $exception)
        {
            return ['status' => false, 'message' => $exception->getMessage()];
        }
    }

    public function postRequest($url, $data = [])
    {
        $client = new Client();
        try{
            $response = $client->request(
                'POST',
                "$this->baseUrl$url",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($data)
                ]);
            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];

        } catch (ClientException $exception)
        {
            $response = json_decode($exception->getResponse()->getBody()->getContents());
            $message = isset($response->errors->base[0]) ? $response->errors->base[0] : 'Error request';
            return ['status' => false, 'message' => $message, 'response' => $response];
        }
    }
}