<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\PostModel;
use App\Model\SocialModel;
class PostRepository
{
    public function create(array $arg)
    {
        return PostModel::create($arg);
    }

    public function detail($id, $shopId, $userId)
    {
        $post = PostModel::where('id', $id)->where('shop_id', $shopId)->where('user_id', $userId)->first();
        if($post) {
            $post = $post->toArray();
            $socials = SocialModel::where('shop_id', $shopId)->whereIn('id', $post['social_ids'])
                ->get();
            if($socials) {
                $socials = $socials->toArray();
            } else {
                $socials = [];
            }
            $post['socials'] = $socials;
            return $post;
        }
    }
}