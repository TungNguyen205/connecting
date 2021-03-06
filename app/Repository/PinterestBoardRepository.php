<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use App\Model\PinterestBoardModel;
class PinterestBoardRepository
{
    public function createOrUpdate(array $args)
    {
        $pinterest = PinterestBoardModel::find($args['id']);
        if($pinterest) {
            return $pinterest->update($args);
        }
        return PinterestBoardModel::create($args);
    }

    public function getBy(array $condition)
    {
        $board = PinterestBoardModel::where($condition)->first();
        if($board) {
            return $board->toArray();
        }
        return null;
    }
}
