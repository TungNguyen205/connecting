<?php
namespace App\Social;
class Social
{
    private $twitter;

    public function __construct(Twitter $twitter)
    {
        $this->twitter = $twitter;
    }

    public function generateUrl($socialType)
    {
        return $this->{$socialType}->generateUrl();
    }

}