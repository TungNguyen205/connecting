<?php

namespace App\Http\Middleware;
use Closure;
use App\Repository\ShopRepository;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redis;

class Permission
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

            $shop = $shopRepository->checkShop($shop['id']);
            if(empty($shop) || !$shop['status']) {
                $data['data'] = [
                    'error_code' => 101,
                    'message'    => 'Shop not found'
                ];
                return response()->json($data, 401);
            } else {
                $request->attributes->add(['shopInfo' => $shop]);
                $request->request->set('userInfo', $shop);
                return $next($request);
            }
        } catch (\Exception $ex) {
            $data['data']['message'] = $ex->getMessage();
            return response()->json($data, 401);
        }


    }
}
