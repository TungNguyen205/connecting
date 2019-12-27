<?php

namespace App\Helpers;


class SpfShopHelper
{
    public static function convert($shop, $platform, $userId=null)
    {
        $data = [
            'platform_id' => $shop['id'],
            'platform' => $platform,
            'name' => $shop['name'],
            'email' => $shop['email'],
            'domain' => $shop['domain'],
            'country' => $shop['country'],
            'province' => $shop['province'],
            'address1' => $shop['address1'],
            'zip' => $shop['zip'],
            'city' => $shop['city'],
            'phone' => $shop['phone'],
            'currency' => $shop['currency'],
            'iana_timezone' => $shop['iana_timezone'],
            'shop_owner' => $shop['shop_owner'],
            'myshopify_domain' => $shop['myshopify_domain'],
            'access_token' => $shop['access_token'],
            'status'    => config('common.status.publish')
        ];
        if(!empty($userId)) {
            $data['user_id'] = $userId;
        }
        return $data;
    }
}
