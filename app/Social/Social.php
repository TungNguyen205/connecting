<?php
namespace App\Social;
class Social
{
    private $twitter;
    private $pinterest;
    private $facebook;

    public function __construct(Twitter $twitter, Pinterest $pinterest, Facebook $facebook)
    {
        $this->twitter = $twitter;
        $this->pinterest = $pinterest;
        $this->facebook = $facebook;
    }

    public function generateUrl($socialType, $token)
    {
        return $this->{$socialType}->generateUrl($token);
    }

    public function auth($socialType, $request)
    {
        return $this->{$socialType}->auth($request);
    }

    public function postSocial($socialType, $data)
    {
        return $this->{$socialType}->postSocial($data);
    }

    public function createPinterestBoard($data)
    {
        return $this->pinterest->createPinterestBoard($data);
    }

}