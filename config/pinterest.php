<?php
return [
    'client_id' => env('PINTEREST_CLIENT_ID'),
    'client_secret' => env('PINTEREST_CLIENT_SECRET'),
    'name'  => 'pinterest',
    'api_version' => 'v1',
    'url' => [
        'base' => 'https://api.pinterest.com/'
    ],
    'permission' => [
        'read_public',
        'write_public',
        'read_relationships',
        'write_relationships'
    ]
];