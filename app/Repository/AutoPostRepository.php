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
}
