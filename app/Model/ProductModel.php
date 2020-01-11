<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductModel
 *
 * @package App\Models
 */
class ProductModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'product';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id',
        'platform_id',
        'title',
        'handle',
        'image',
        'images',
        'shop_id',
        'price',
        'link'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'price' => 'array',
        'image' => 'array',
        'images' => 'array'
    ];

    public function getIdAttribute($value)
    {
        return (float)($value);
    }
}