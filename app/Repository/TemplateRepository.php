<?php

namespace App\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use App\Model\TemplateModel;
class TemplateRepository
{
    public function create(array $arg)
    {
        return TemplateModel::create($arg);
    }
}
