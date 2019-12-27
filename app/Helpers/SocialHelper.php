<?php

namespace App\Helpers;


use Firebase\JWT\JWT;
use Pusher\Pusher;

class SocialHelper
{
    static function pushSocket($channel, $event, $payload)
    {
        $pusher = new Pusher(
            config('services.pusher.key'),
            config('services.pusher.secret'),
            config('services.pusher.app_id'),
            config('services.pusher.options')
        );

        $data['message'] = 'hello world';
        $pusher->trigger($channel, $event, $payload);
    }

    static function toArray($obj)
    {
        return json_decode(json_encode($obj), true);
    }


    static function generalToken($payload)
    {
        //encode token
        return JWT::encode($payload, config('common.jwt_token'));
    }

    static function decodeToken($token)
    {
        return JWT::decode($token, env('JWT_KEY'), ['HS256']);
    }

    public static function createResponse($status, $data = [], $message = ''){
        return [
            'status' => $status,
            'data' => $data,
            'message' =>$message
        ];
    }
}
