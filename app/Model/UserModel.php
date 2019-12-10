<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserModel
 *
 * @package App\Models
 */
class UserModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array
     */

    protected $hidden = [
        // 'access_token'
    ];
    public function getIdAttribute($value)
    {
        return (float)($value);
    }

}
