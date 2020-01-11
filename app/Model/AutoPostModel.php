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
       'id', 'shop_id', 'user_id', 'social_ids', 'template_id', 'post_type'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'social_ids' => 'array'
    ];


    public function getIdAttribute($value)
    {
        return (float)($value);
    }

    public function template()
    {
        return $this->belongsTo('App\Model\TemplateModel', 'template_id', 'id');
    }

}