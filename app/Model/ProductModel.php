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
    public $incrementing = false;
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
        'title',
        'handle',
        'image',
        'shop_id',
        'price'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'price' => 'array',
    ];

    public function getIdAttribute($value)
    {
        return (float)($value);
    }

}