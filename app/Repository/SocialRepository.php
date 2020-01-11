<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\SocialModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * Class SocialRepository
 * @package App\Repository
 */
class SocialRepository
{

    public function createOrUpdate(array $args)
    {
        $social = SocialModel::where(function ($query) use ($args){
            $query->where('social_id', $args['social_id'])
            ->orWhere(function ($query2) use($args) {
                $query2->where('shop_id', $args['shop_id'])->where('social_type', $args['social_type']);
            });
        })->first();
        if($social) {
            return $social->update($args);
        }
        return SocialModel::create($args);
    }

    public function getBy(array $condition)
    {
        $social = SocialModel::where($condition)->first();
        if($social) {
            return $social->toArray();
        }
        return null;
    }

    public function checkSocial($shopId, array $socialIds)
    {
        $socials = SocialModel::where('shop_id', $shopId)->whereIn('id', $socialIds)->get();
        if($socials) {
            return $socials->toArray();
        }
        return false;
    }
}