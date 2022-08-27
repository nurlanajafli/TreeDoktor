<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;

/**
 * application\modules\equipment\models\EquipmentCounter
 *
 * @property int $counter_id
 * @property int $user_id
 * @property int $eq_id
 * @property int|null $repair_id
 * @property int|null $service_report_id
 * @property int $counter_value
 * @property string|null $counter_note
 * @property \Illuminate\Support\Carbon $counter_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereCounterCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereCounterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereCounterNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereCounterValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereRepairId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereServiceReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereUserId($value)
 * @mixin \Eloquent
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @property-read \application\modules\equipment\models\EquipmentRepair|null $repair
 * @property-read \application\modules\equipment\models\EquipmentServiceReport|null $service_report
 * @property-read \application\modules\user\models\User $user
 * @property string $counter_date
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentCounter whereCounterDate($value)
 */
class EquipmentCounter extends EloquentModel
{
    use PostNoteTrait;
    protected $table = 'equipment_counters';
    protected $primaryKey = 'counter_id';
    protected $fillable = [
        'user_id',
        'eq_id',
        'repair_id',
        'service_report_id',
        'counter_value',
        'counter_note',
        'counter_date',
        'counter_created_at'
    ];

    const COLUMNS = [
        'user_id' => 'User',
        'eq_id' => 'Equipment',
        'repair_id' => 'Repair Request',
        'service_report_id' => 'Service Report',
        'counter_value' => 'Value',
        'counter_note' => 'Note',
        'counter_date' => 'Counter Date',
        'counter_created_at' => 'Created At'
    ];
    const ENT_NAME = 'Counter';
    const NAME_COL = false;

    const CREATED_AT = 'counter_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'counter_value' => 'integer',
        'counter_date' => AppDate::class,
        'counter_created_at' => AppDateTime::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            if ($user = request()->user()) {
                $query->user_id = $user->id;
            }
        });
    }

    public function queryLast($eq_id)
    {
        return $this->select('MAX(counter_value)')->where('eq_id', $eq_id);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'eq_id');
    }

    public function repair()
    {
        return $this->belongsTo(EquipmentRepair::class, 'repair_id');
    }

    public function service_report()
    {
        return $this->belongsTo(EquipmentServiceReport::class, 'service_report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
