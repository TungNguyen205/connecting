<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductModel
 *
 * @package App\Models
 */
class PostMediaModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'post_media';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id', 'post_id', 'media_id'
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