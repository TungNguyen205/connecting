<?php
return [
    'consumer_key' => env('TUMBLR_CUSTOMER_KEY'),
    'consumer_secret' => env('TUMBLR_CUSTOMER_SECRET'),
    'name' => 'tumblr',
    'api_version' => 'v2',
    'url' => [
        'base' => 'https://api.tumblr.com/'
    ]
];

