<?php
namespace App\Social;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Repository\SocialRepository;
use App\Repository\PinterestBoardRepository;
use App\Helpers\PinterestHelper;
class Pinterest
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $socialRepository;
    private $pinterestBoardRepository;

    public function __construct(SocialRepository $socialRepository, PinterestBoardRepository $pinterestBoardRepository)
    {
        $this->baseUrl = config('pinterest.url.base');
        $this->clientId = config('pinterest.client_id');
        $this->clientSecret = config('pinterest.client_secret');
        $this->socialRepository = $socialRepository;
        $this->pinterestBoardRepository = $pinterestBoardRepository;
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
            $this->setParameter($accessTokenResponse['data']->access_token, false);
            $userInfo = $this->userInfo();
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
                $this->socialRepository->createOrUpdate($params);

                $fields = ['created_at', 'id', 'image', 'name', 'url'];
                $boards = $this->syncBoards($fields);
                if($boards['status'] && !empty($boards['data'])) {
                    foreach($boards['data'] as $board) {
                        $params = PinterestHelper::convert($board);
                        $params['social_id'] = $data['id'];
                        $params['shop_id'] = $request['userInfo']['id'];
                        $this->pinterestBoardRepository->createOrUpdate($params);
                    }
                }
            }

            return response()->json(['status' => true]);
        }
    }

    public function userInfo()
    {
        $fields = [
            'account_type', 'bio', 'counts', 'created_at', 'first_name', 'id', 'image', 'last_name', 'url', 'username'
        ];
        return $this->getRequest('me', [
            'access_token'  => $this->accessToken,
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

    public function postRequest2($url, $field, $data = [])
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
                    'query' => $field,
                    'body' => json_encode($data)
                ]);
            return ['status' => true, 'data' => json_decode($response->getBody()->getContents())];

        } catch (ClientException $exception)
        {
            dd($exception);
            $response = json_decode($exception->getResponse()->getBody()->getContents());
            $message = isset($response->errors->base[0]) ? $response->errors->base[0] : 'Error request';
            return ['status' => false, 'message' => $message, 'response' => $response];
        }
    }

    public function postSocial($data)
    {
        $this->setParameter($data['social']['access_token']);
        $board = trim(str_replace("https://www.pinterest.com","",$data['board']['url']),"/"); ;
        $params = [
            'board'     => $board,
            'note'      => $data['message'],
            'link'      => $data['meta_link'],
            'image_url' => $data['medias'][0]['url']
        ];

        $pin = $this->postRequest2('pins/', ['access_token' => $this->accessToken],$params);
        if(!$pin['status']) {

        }
        return [
          'status' => true,
          'data'   => [
              'post_social_id' => $pin['data']->data->id
          ]
        ];

    }

    public function syncBoards($fields)
    {
        $params = [
            'access_token' => $this->accessToken,
            'fields' => implode(",", $fields)
        ];
        $data = $this->getRequest('me/boards', $params);
        if(!$data['status']) {
            return ['status' => false, 'message' => $data['message']];
        }
        return ['status' => true, 'data' => $data['data']['data']];
    }

    public function createPinterestBoard($data)
    {
        $checkBoardNameExist = $this->pinterestBoardRepository->getBy(['name' => $data['name'], 'social_id' => $data['social']['social_id']]);

        if($checkBoardNameExist) {
            return [
                'status'    => false,
                'message'   => 'Board name exist'
            ];
        }
        $this->setParameter($data['social']['access_token']);
        $params = [
            'name' => $data['name']
        ];
        if(!empty($data['description'])) {
            $params['description'] = $data['description'];
        }
        $board = $this->postRequest2('boards/', ['access_token' => $this->accessToken],$params);
        if(!$board['status']) {

        }
        $boardParams = [
            'id' => $board['data']->data->id,
            'social_id' => $data['social']['social_id'],
            'url' => $board['data']->data->url,
            'name' => $board['data']->data->name,
            'date_create' => date('Y-m-d H:i:s'),
        ];
        $boardCreate = $this->pinterestBoardRepository->createOrUpdate($boardParams);
        if(!$boardCreate) {
            return ['status' => false, 'message' => 'Create board fail'];
        }
        return ['status' => true, 'message' => 'Create board success'];
    }
}