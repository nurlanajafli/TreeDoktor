<?php

namespace application\modules\user\models;

use application\core\Database\EloquentModel;

/**
 * application\modules\user\models\LoginLog
 *
 * @property int $log_id
 * @property int|null $log_user_id
 * @property int|null $log_time
 * @property string|null $log_user_ip
 * @property string $log_data
 *
 */
class LoginLog extends EloquentModel
{
    protected $table = 'login_log';

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log_user_id',
        'log_time',
        'log_user_ip',
        'log_data'
    ];

    const CREATED_AT = null;

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
    ];

}
