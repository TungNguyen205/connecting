<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
/**
 * Class PinterestBoardModel
 *
 * @package App\Models
 */
class PinterestBoardModel extends Model
{
    public $incrementing = false;
    /**
     * @var string
     */
    protected $table = 'pinterest_board';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'id', 'social_id', 'name', 'url', 'date_create', 'image'
    ];
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'image' => 'array'
    ];

    public function getIdAttribute($value)
    {
        return (float)($value);
    }

}