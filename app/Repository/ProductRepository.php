<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\ProductModel;
class ProductRepository
{
    function createOrUpdate(array $arg)
    {
        if($product = ProductModel::where('platform_id', $arg['platform_id'])->first())
            return $product->update($arg);

        return ProductModel::create($arg);
    }

    public function list($shopId, array $filters = [])
    {
        $paginate = config('common.per_page');
        $page = isset($filters['current_page'])? $filters['current_page']: 1;
        $products = ProductModel::where('shop_id', $shopId);

        if(! empty($filters['keyword'])) {
            $products->where(function ($query) use ($filters){
                $query->where('title', 'like', '%'.$filters['keyword'].'%')
                    ->orWhere('id', 'like', '%'.$filters['keyword'].'%');
            });
        }

        if($page >= 1) {
            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        }
        // tao them cot ngay public product de sort
        $products =  $products->paginate($paginate)->toArray();

        return ['status' => true, 'data' => $products];
    }

    public function detail($productId, $shopId)
    {
        $product = ProductModel::where('id', $productId)->where('shop_id', $shopId)->first();
        if(!empty($product)) {
            return $product->toArray();
        }
        return null;
    }

    public function checkProduct($platformProductId, $shopId)
    {
        $product = ProductModel::where('platform_id', $platformProductId)->where('shop_id', $shopId)->first();
        if(!empty($product)) {
            return true;
        }
        return false;
    }
}
