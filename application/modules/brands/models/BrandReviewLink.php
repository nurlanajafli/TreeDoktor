<?php

namespace application\modules\brands\models;

use application\core\Database\EloquentModel;

class BrandReviewLink extends EloquentModel
{
    protected $table = 'brand_review_links';

    protected $primaryKey = 'brl_id';

    protected $fillable = [
        'br_id',
        'brl_link',
        'brl_name'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'br_id' => 'integer',
        'brl_link' => 'string'
    ];

}
