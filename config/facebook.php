<?php
return [
    'app_id' => env('FACEBOOK_APP_ID'),
    'app_secret' => env('FACEBOOK_APP_SECRET'),
    'social_name' => 'facebook',
    'api_version' => 'v5.0',
    'permission' => [
        'email',
        'public_profile',
        'pages_show_list',
        // only dev
        'manage_pages',
        'publish_pages'
    ],
    'url' => [
        'base'  => 'https://www.facebook.com/',
        'api'   => 'https://graph.facebook.com/'
    ],
    'post_type'=> [
        'link' => 'link',
        'image' => 'image',
        'video' => 'video',
        'product' => 'product',
        'text' => 'text'
    ],
];
