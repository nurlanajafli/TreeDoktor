<?php

namespace application\modules\equipment\models;

use application\core\Database\EloquentModel;

/**
 * application\modules\equipment\models\EquipmentRepairType
 *
 * @property int $repair_type_id
 * @property string $repair_type_name
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairType whereRepairTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepairType whereRepairTypeName($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentRepair[] $repairs
 * @property-read int|null $repairs_count
 */
class EquipmentRepairType extends EloquentModel
{
    protected $table = 'equipment_repair_types';
    protected $primaryKey = 'repair_type_id';
    protected $fillable = [
        'repair_type_name'
    ];

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
    ];

    public function repairs()
    {
        return $this->hasMany(EquipmentRepair::class, 'repair_type_id');
    }
}
