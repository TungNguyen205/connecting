<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class TemplateModel
 *
 * @package App\Models
 */
class TemplateModel extends Model
{
    public $incrementing = true;
    /**
     * @var string
     */
    protected $table = 'template';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id', 'shop_id', 'auto_post_id', 'content'
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
