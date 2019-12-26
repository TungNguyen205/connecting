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
    ]
];
