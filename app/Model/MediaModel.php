<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductModel
 *
 * @package App\Models
 */
class MediaModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'media';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
       'id', 'name', 'url', 'type', 'size', 'width', 'height', 'ratio', 'extension', 'user_id', 'shop_id'
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