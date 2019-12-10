<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ShopModel
 *
 * @package App\Models
 */
class ShopModel extends Model
{
    public $incrementing = false;
    /**
     * @var string
     */
    protected $table = 'shop';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'domain',
        'country',
        'province',
        'address1',
        'zip',
        'city',
        'phone',
        'currency',
        'iana_timezone',
        'shop_owner',
        'app_plan',
        'myshopify_domain',
        'status',
        'on_boarding',
        'access_token',
        'platform',
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