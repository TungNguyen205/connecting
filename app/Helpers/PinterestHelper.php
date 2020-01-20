<?php

namespace App\Helpers;


class PinterestHelper
{
    static function convert($payload)
    {
        return [
            'id'        => $payload['id'],
            'image'     => $payload['image'],
            'name'      => $payload['name'],
            'url'       => $payload['url'],
            'date_create' => date('Y-m-d H:i:s', strtotime($payload['created_at']))
        ];
    }
}