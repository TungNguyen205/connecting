<?php

namespace App\Helpers;

class PostHelper
{
    static function convertMessage($message, array $contentReplace)
    {
        foreach($contentReplace as $k=>$content)
        {
            $message = str_replace($k,$content,$message);
        }
        return $message;
    }
}