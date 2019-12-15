<?php
namespace App\Social;
use Abraham\TwitterOAuth\TwitterOAuth;
class Twitter
{
    private $consumerKey;
    private $consumerSecret;
    private $oauthToken;
    private $oauthTokenSecret;
    private $connection;

    public function __construct()
    {
        $this->consumerKey = config('twitter.consumer_key');
        $this->consumerSecret = config('twitter.consumer_secret');
        $this->connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret);
    }

    public function setParameters($oauthToken, $oauthTokenSecret)
    {
        $this->oauthToken = $oauthToken;
        $this->oauthTokenSecret = $oauthTokenSecret;
        $this->connection = new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->oauthToken, $this->oauthTokenSecret);
    }

    public function generateUrl()
    {
        $request_token = $this->connection->oauth(
            'oauth/request_token', [
            'oauth_callback' => config('twitter.url_callback')
        ]);
        // generate the URL to make request to authorize our application
        $url = $this->connection->url(
            'oauth/authorize', [
                'oauth_token' => $request_token['oauth_token']
            ]
        );

        return $url;
    }

    public function auth($oauthToken, $oauth_verifier)
    {
        $this->oauthToken = $oauthToken;

        $token = $this->connection->oauth(
            'oauth/access_token', [
                'oauth_verifier' => $oauth_verifier,
                'oauth_token' => $oauthToken
            ]
        );
        return $token;
    }

}