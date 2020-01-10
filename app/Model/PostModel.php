<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductModel
 *
 * @package App\Models
 */
class PostModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'post';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id', 'post_type', 'product_id', 'meta_link', 'sub_type', 'message', 'time_on', 'social_ids', 'social_id',
        'shop_id', 'user_id', 'social_type', 'status', 'is_repeat', 'repeat_value', 'repeat_unit',
        'post_social_id', 'publish_is_error', 'error_message',
        'media_index', 'social_insight'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'social_ids' => 'array',
    ];

    public function getIdAttribute($value)
    {
        return (float)($value);
    }

    public function medias()
    {
        return $this->belongsToMany('App\Model\MediaModel', 'App\Model\PostMediaModel', 'post_id', 'media_id');
    }

}