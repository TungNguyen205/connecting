<?php
namespace App\Social;
class Social
{
    private $twitter;

    public function __construct(Twitter $twitter)
    {
        $this->twitter = $twitter;
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