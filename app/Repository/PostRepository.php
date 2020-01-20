<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\PostModel;
use App\Model\SocialModel;
use App\Model\MediaModel;
use App\Repository\ProductRepository;
use App\Repository\MediaRepository;
use App\Model\PostMediaModel;
use App\Model\PinterestBoardModel;
class PostRepository
{
    public function detail($id, $shopId, $userId)
    {
        $post = PostModel::with(['medias'])->where('id', $id)->where('shop_id', $shopId)->where('user_id', $userId)->first();
        if($post) {
            $post = $post->toArray();
            $socials = SocialModel::where('shop_id', $shopId)->where('id', $post['social_id'])
                ->first();
            if($socials) {
                $socials = $socials->toArray();
            } else {
                $socials = [];
            }
            $post['social'] = $socials;
            $board = PinterestBoardModel::where('social_id', $post['social']['social_id'])->first();
            if(!empty($board)) {
                $post['board'] = $board->toArray();
            }
            return $post;
        }
    }

    public function savePost(array $arg, $shopId, $userId)
    {
        $postParams = [
            'post_type'     => $arg['post_type'],
            'meta_link'     => $arg['meta_link'],
            'product_id'    => $arg['product_id'],
            'message'       => $arg['message'],
            'social_ids'    => $arg['social_ids'],
            'social_id'     => $arg['social_id'],
            'shop_id'       => $shopId,
            'user_id'       => $userId,
            'social_type'   => $arg['social_type'],
            'status'        => $arg['status'],
        ];
        $post = PostModel::create($postParams);
        if(!$post) {
            return false;
        }
        if($arg['post_type'] == 'image') {
            $productRepo = new ProductRepository();
            $mediaRepo = new MediaRepository();
            $product = $productRepo->detail($arg['product_id'], $shopId);
            if(empty($product)) {
                return false;
            }
            for($i = 0; $i < $arg['number_images']; $i++) {
                $image = isset($product['images'][$i])? $product['images'][$i]: $product['images'][0];
                $mediaParams = [
                    'url' => $image['src'],
                    'type' => 'image',
                    'width' => $image['width'],
                    'height' => $image['height'],
                    'shop_id' => $shopId,
                    'user_id' => $userId,
                ];
                $media = $mediaRepo->create($mediaParams);
                if(!$media) {
                    return false;
                }
                $postMediaParams = [
                    'post_id' => $post->id,
                    'media_id' => $media->id,
                ];
                PostMediaModel::create($postMediaParams);
            }
        }
        return $post;
    }
}