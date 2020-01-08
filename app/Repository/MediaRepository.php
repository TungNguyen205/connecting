<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\MediaModel;
class MediaRepository
{
    public function create(array $arg)
    {
        return MediaModel::create($arg);
    }
}
