<?php
namespace App\Social;
class Social
{
    private $twitter;
    private $pinterest;
    private $tumblr;

    public function __construct(Twitter $twitter, Pinterest $pinterest, Tumblr $tumblr)
    {
        $this->twitter = $twitter;
        $this->pinterest = $pinterest;
        $this->tumblr = $tumblr;
    }

    public function generateUrl($socialType, $token)
    {
        return $this->{$socialType}->generateUrl($token);
    }

    public function auth($socialType, $request)
    {
        return $this->{$socialType}->auth($request);
    }

}