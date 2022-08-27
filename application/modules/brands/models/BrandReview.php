<?php

namespace application\modules\brands\models;

use application\core\Database\EloquentModel;
use application\modules\brands\models\BrandReviewLink;

class BrandReview extends EloquentModel
{
    protected $table = 'brand_review';

    protected $primaryKey = 'br_id';

    protected $fillable = [
        'brand_id',
        'br_header',
        'br_dislike_message',
        'br_like_message'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'brand_id' => 'integer',
        'br_header' => 'string'
    ];

    public function reviews(){
        return $this->hasMany(BrandReviewLink::class, 'br_id');
    }

}
