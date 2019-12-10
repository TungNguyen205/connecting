<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\ShopModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ShopRepository
 * @package App\Repository
 */
class ShopRepository
{

    /**
     * @param string $shopId
     * @return mixed
     */
    public function detail(string $shopId)
    {
        if($shopInfo = ShopModel::find($shopId))
            return $shopInfo;

        return false;
    }

    public function createOrUpdate(float $id, array $args)
    {
        $shop = ShopModel::find($id);
        if($shop) {
            return $shop->update($args);
        }
        return ShopModel::create($args);
    }

    public function getShopAttributes( array $data = [])
    {
        $shopInfo = ShopModel::where( $data )->first();
        if($shopInfo)
            return $shopInfo->toArray();
        return false;
    }
}