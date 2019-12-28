<?php
namespace App\Social;
class Social
{
    private $twitter;
    private $pinterest;

    public function __construct(Twitter $twitter, Pinterest $pinterest)
    {
        $this->twitter = $twitter;
        $this->pinterest = $pinterest;
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