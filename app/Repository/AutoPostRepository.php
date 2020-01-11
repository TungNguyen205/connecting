<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\AutoPostModel;
class AutoPostRepository
{
    public function create(array $arg)
    {
        return AutoPostModel::create($arg);
    }

    public function detail($shopId)
    {
        $autoPost = AutoPostModel::with('template')->where('shop_id', $shopId)->first();
        if(!empty($autoPost)) {
            return $autoPost->toArray();
        }
        return [];
    }
}
