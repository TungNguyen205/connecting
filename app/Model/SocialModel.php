<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ShopModel
 *
 * @package App\Models
 */
class SocialModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'social';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'social_id', 'social_url', 'name', 'username', 'email', 'avatar', 'social_type', 'access_token', 'shop_id',
        'error'
    ];

    protected $casts = [
        'access_token' => 'array',
        'error' => 'array'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function getIdAttribute($value)
    {
        return (float)($value);
    }

}
