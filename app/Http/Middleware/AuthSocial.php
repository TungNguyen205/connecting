<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;
use Firebase\JWT\JWT;
use App\Helpers\SocialHelper;
class AuthSocial
{
    public function handle($request, Closure $next)
    {
        $stage = json_decode($request->input('state', ''), true);
        if(empty($stage['token']) || empty($stage['socialType']))
            return response()->json(['status' => false, 'message' => 'Payload invalid']);

        try{
            $payload = JWT::decode($stage['token'], env('JWT_KEY'), ['HS256']);
            $request->request->set('userInfo', SocialHelper::toArray($payload));
            $request->request->set('socialType', $stage['socialType']);
            $request->request->set('action', $stage['action']);
            $request->request->set('socialId', $stage['socialId']);
            return $next($request);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => 'Token invalid']);
        }
    }
}
