<?php

namespace App\Helpers;

class Common
{
    static function getTagList()
    {
        $file = storage_path('json/tag.json');
        $tags = [];
        $data = [];
        if( ! file_exists($file))
            return $tags;

        $source = file_get_contents($file);
        $tags = json_decode($source, true);

        return $tags;
    }
}