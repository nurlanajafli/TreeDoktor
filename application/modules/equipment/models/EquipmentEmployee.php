<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\user\models\User;

/**
 * application\modules\equipment\models\EquipmentEmployee
 *
 * @property int $emp_id
 * @property int $user_id
 * @property int|null $repair_id
 * @property int|null $service_report_id
 * @property float $emp_hours
 * @property mixed $emp_hourly_rate
 * @property \Illuminate\Support\Carbon|null $emp_worked_at
 * @property \Illuminate\Support\Carbon $emp_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereEmpCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereEmpHourlyRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereEmpHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereEmpWorkedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereRepairId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereServiceReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentEmployee whereUserId($value)
 * @mixin \Eloquent
 * @property-read \application\modules\equipment\models\EquipmentRepair|null $repair
 * @property-read \application\modules\equipment\models\EquipmentServiceReport|null $service_report
 * @property-read \application\modules\user\models\User $user
 */
class EquipmentEmployee extends EloquentModel
{
    protected $table = 'equipment_employees';
    protected $primaryKey = 'emp_id';
    protected $fillable = [
        'user_id',
        'repair_id',
        'service_report_id',
        'emp_hours',
        'emp_hourly_rate',
        'emp_worked_at',
        'emp_created_at'
    ];

    const CREATED_AT = 'emp_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'emp_hours' => 'float',
        'emp_hourly_rate' => 'decimal:2',
        'emp_worked_at' => AppDate::class,
        'emp_created_at' => AppDateTime::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repair()
    {
        return $this->belongsTo(EquipmentRepair::class, 'repair_id');
    }

    public function service_report()
    {
        return $this->belongsTo(EquipmentServiceReport::class, 'service_report_id');
    }

}
