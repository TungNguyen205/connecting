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

    public function list($shopId)
    {
        $autoPost = AutoPostModel::with('template')->where('shop_id', $shopId)
            ->where('status', '<>', 0)
            ->get();
        if(!empty($autoPost)) {
            return $autoPost->toArray();
        }
        return [];
    }
}
