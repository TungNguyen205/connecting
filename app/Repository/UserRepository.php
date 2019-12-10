<?php
declare(strict_types=1);
namespace App\Repository;

use App\Model\UserModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class UserRepository
 * @package App\Repository
 */
class UserRepository
{
    public function createOrUpdate(array $args)
    {
        if(!empty($args['email'])) {
            $user = UserModel::where('email', $args['email']);
            if($user)
            {
                $update = $user->update($args);
                if($update) {
                    return UserModel::where('email', $args['email'])->first();
                }
            }
        }

        return UserModel::create($args);
    }
}