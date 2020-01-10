<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class AutoPostModel
 *
 * @package App\Models
 */
class AutoPostModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'auto_post';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
       'id', 'shop_id', 'user_id', 'social_id'
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