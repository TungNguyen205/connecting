<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\ProductModel;
class ProductRepository
{
    function createOrUpdate(array $arg)
    {
        if($product = ProductModel::find($arg['id']))
            return $product->update($arg);

        return ProductModel::create($arg);
    }

}
