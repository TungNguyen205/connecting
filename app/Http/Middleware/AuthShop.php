<?php

namespace App\Http\Middleware;
use Closure;
use App\Repository\ShopRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

class AuthShop
{
    public function handle($request, Closure $next)
    {
        $shopRepository = app(ShopRepository::class);

        $data  = [
            'status'    => false ,
            'data'      => [
                'error_code'    => null,
                'message'   => 'Access token not found'
            ]
        ];

        try{
            if(!$request->header('Authorization')){
                return response()->json($data, 401);
            }

            $shop   = $shopRepository->auth($request->header('Authorization'));
            if(@$shop['expiresin'] < time())
            {
                $data['message'] = 'Token expired';
                return response()->json($data, 401);
            }
            $shop = $shopRepository->checkByPlatform($shop['platform_id']);
            if(empty($shop) || !$shop['status']) {
                $data['data'] = [
                    'error_code' => 101,
                    'message'    => 'Shop not found'
                ];
                return response()->json($data, 401);
            } else {
                $shopInfo = [
                    'id' => $shop['id'],
                    'user_id' => $shop['user_id'],
                ];
                $request->attributes->add(['shopInfo' => $shopInfo]);
                $request->request->set('shopInfo', $shopInfo);
                return $next($request);
            }
        } catch (\Exception $ex) {
            $data['data']['message'] = $ex->getMessage();
            return response()->json($data, 401);
        }


    }
}
