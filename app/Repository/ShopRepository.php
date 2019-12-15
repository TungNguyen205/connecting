<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\ShopModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
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

    public function login($shopDomain)
    {
        $shop = ShopModel::select(['id', 'name', 'email', 'domain', 'myshopify_domain', 'shop_owner', 'iana_timezone', 'currency'])
            ->where('myshopify_domain', $shopDomain)
            ->first();
        if(!empty($shop))
        {
            $shop = $shop->toArray();
            return [
                'status'    => true,
                'data'      => ['token' => $this->generateToken($shop)]
            ];
        } else
        {
            return [
                'status'    => false,
                'message'   => 'Shop is not exists'
            ];
        }
    }

    private function generateToken($data)
    {
        $data['expiresin'] = time() + env('JWT_EXPIRE');
        return  JWT::encode($data, env('JWT_KEY'));
    }

}