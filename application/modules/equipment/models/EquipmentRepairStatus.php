<?php

namespace application\modules\equipment\models;

use application\core\Database\EloquentModel;

/**
 * application\modules\equipment\models\EquipmentRepairStatus
 *
 * @property int $repair_status_id
 * @property string $repair_status_name
 * @property bool $repair_status_flag_default
 * @property bool $repair_status_flag_in_progress
 * @property bool $repair_status_flag_completed
 * @property string|null $repair_status_color
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusFlagCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusFlagDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusFlagInProgress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusName($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentRepair[] $repairs
 * @property-read int|null $repairs_count
 * @property bool $repair_status_locked
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairStatus whereRepairStatusLocked($value)
 */
class EquipmentRepairStatus extends EloquentModel
{
    protected $table = 'equipment_repair_statuses';
    protected $primaryKey = 'repair_status_id';
    protected $fillable = [
        'repair_status_name',
        'repair_status_flag_default',
        'repair_status_flag_in_progress',
        'repair_status_flag_completed',
        'repair_status_color'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'repair_status_flag_default' => 'boolean',
        'repair_status_flag_in_progress' => 'boolean',
        'repair_status_flag_completed' => 'boolean',
    ];

    public function repairs()
    {
        return $this->hasMany(EquipmentRepair::class, 'repair_status_id');
    }
}
