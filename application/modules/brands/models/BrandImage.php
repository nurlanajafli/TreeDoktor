<?php
namespace application\modules\brands\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;

use application\modules\brands\models\traits\BrandsTrait;

use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BrandImage extends EloquentModel
{
    use BrandsTrait;
    use SoftDeletes;

    protected $table = 'brand_images';
    protected $primaryKey = 'bi_id';

    protected $fillable = [
    	'bi_brand_id', 'bi_key', 'bi_value'
    ];

    protected $appends = ['file_url'];
    
    const CREATED_AT = 'bi_created_at';


    public static function boot() {
        parent::boot();
        /*
        static::deleted(function($query) {
            $filePath = 'uploads/brands/' . $query->bi_brand_id . '/';
            Storage::delete($filePath . $query->bi_value);
        });
        */
    }


	public static function prepareName($originName)
    {
        return pathinfo($originName, PATHINFO_FILENAME) . '.' . pathinfo($originName,
                PATHINFO_EXTENSION);
    }

    public function getFileUrlAttribute()
    {
        $filePath = '/uploads/brands/' . $this->attributes['bi_brand_id'] . '/';
        return $filePath.$this->attributes['bi_value'];
    }

}