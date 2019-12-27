<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\SocialModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * Class SocialRepository
 * @package App\Repository
 */
class SocialRepository
{

    public function create(array $args)
    {
        return SocialModel::create($args);
    }
}
