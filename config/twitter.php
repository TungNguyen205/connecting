<?php
return [
    'consumer_key' => env('TWITTER_CONSUMER_KEY'),
    'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
    'access_token' => env('TWITTER_ACCESS_TOKEN'),
    'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET'),
    'social_name' => 'twitter',
    'api_version' => '1.1',
    'url' => [
        'base' => 'https://api.twitter.com/',
        'authorize' => 'https://api.twitter.com/oauth/authorize',
        'search_hashtag' => 'https://twitter.com/i/search/typeahead.json?filters=true&result_type=topics&src=COMPOSE&count=20&q=%23',
    ],
    'post_type' => [
        'link' => 'link',
        'text' => 'text',
        'image' => 'image',
        'video' => 'video',
    ],
    'media' => [
        'media_category' => [
            'tweet' => [
                'tweet_video' => 'tweet_video',
                'tweet_image' => 'tweet_image',
                'tweet_gif' => 'tweet_gif',
            ],
            'dm' => [
                'dm_video' => 'dm_video',
                'dm_image' => 'dm_image',
                'dm_gif' => 'dm_gif',
            ],
        ],
        'type_image' => [
            'png',
            'jpeg',
            'jpg',
        ],
        'type_gif' => [
            'gif',
        ],
        'type_video' => [
            'mp4',
        ],
    ]
];
