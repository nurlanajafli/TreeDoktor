<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;

/**
 * application\modules\equipment\models\EquipmentGroup
 *
 * @property int $group_id
 * @property string|null $group_name
 * @property string|null $group_prefix
 * @property string $group_color
 * @property \Illuminate\Support\Carbon $group_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup whereGroupColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup whereGroupCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentGroup whereGroupPrefix($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\Equipment[] $equipment
 * @property-read int|null $equipment_count
 * @property-read mixed $group_url
 * @property-read mixed $group_created_date
 */
class EquipmentGroup extends EloquentModel
{
    protected $table = 'equipment_groups';
    protected $primaryKey = 'group_id';
    protected $fillable = [
        'group_name',
        'group_prefix',
        'group_color',
        'group_created_at',
    ];

    protected $appends = ['group_url', 'group_created_date'];

    const COLUMNS = [
        'group_id' => 'Group',
        'group_name' => 'Name',
        'group_prefix' => 'Prefix',
        'group_color' => 'Color',
        'group_created_at' => 'Created At'
    ];
    const ENT_NAME = 'Equipment Group';
    const NAME_COL = 'group_name';

    const CREATED_AT = 'group_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'group_created_at' => AppDateTime::class
    ];

    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'group_id');
    }


    public function getGroupUrlAttribute()
    {
        return base_url('equipment/?filter[group_id]=' . $this->attributes['group_id']);
    }

    public function getGroupCreatedDateAttribute()
    {
        if (!empty($this->attributes['group_created_at'])) {
            return $this->toDate($this->attributes['group_created_at'])
                ->format(config_item('dateFormat'));
        }
        return null;
    }

}
