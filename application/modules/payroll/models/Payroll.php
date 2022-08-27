<?php

namespace application\modules\payroll\models;

use application\core\Database\EloquentModel;

class Payroll extends EloquentModel
{
    protected $table = 'payroll';

    protected $primaryKey = 'payroll_id';

    protected $fillable = [
        'payroll_id',
        'payroll_start_date',
        'payroll_end_date',
        'payroll_day',
    ];

    const CREATED_AT = null;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'payroll_start_date' => 'date',
        'payroll_end_date' => 'date',
    ];
}
