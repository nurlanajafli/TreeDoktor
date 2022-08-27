<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;

/**
 * application\modules\equipment\models\EquipmentRepair
 *
 * @property int $repair_id
 * @property int $user_id
 * @property int $eq_id
 * @property int|null $assigned_id
 * @property int $repair_status_id
 * @property int $repair_type_id
 * @property int $repair_priority
 * @property string|null $repair_description
 * @property \Illuminate\Support\Carbon|null $repair_end_at
 * @property string|null $repair_end_note
 * @property string $repair_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereAssignedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairEndAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairEndNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairPriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereUserId($value)
 * @mixin \Eloquent
 * @property-read \application\modules\user\models\User|null $assigned
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentEmployee[] $employees
 * @property-read int|null $employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $repair_priority_str
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentNote[] $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentPart[] $parts
 * @property-read int|null $parts_count
 * @property-read \application\modules\equipment\models\EquipmentRepairStatus $repair_status
 * @property-read \application\modules\equipment\models\EquipmentRepairType $repair_type
 * @property-read \application\modules\user\models\User $user
 * @property float|null $repair_est_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentCounter[] $counters
 * @property-read int|null $counters_count
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentRepair whereRepairEstHours($value)
 * @property-read \application\modules\equipment\models\EquipmentCounter|null $counter
 * @property-read mixed $repair_created_date
 */
class EquipmentRepair extends EloquentModel
{
    use PostNoteTrait;

    const PRIORITY_GENERAL = 1;
    const PRIORITY_EMERGENCY = 2;
    const PRIORITIES = [
        self::PRIORITY_GENERAL => 'General',
        self::PRIORITY_EMERGENCY => 'Emergency',
    ];
    protected $table = 'equipment_repairs';
    protected $primaryKey = 'repair_id';
    protected $fillable = [
        'user_id',
        'eq_id',
        'assigned_id',
        'repair_status_id',
        'repair_type_id',
        'repair_priority',
        'repair_est_hours',
        'repair_description',
        'repair_end_at',
        'repair_end_note',
        'repair_created_at'
    ];

    const COLUMNS = [
        'user_id' => 'User',
        'eq_id' => 'Equipment',
        'assigned_id' => 'Assigned',
        'repair_status_id' => 'Status',
        'repair_type_id' => 'Type',
        'repair_priority' => 'Priority',
        'repair_est_hours' => 'Est. Hours',
        'repair_description' => 'Description',
        'repair_end_at' => 'Completed on',
        'repair_end_note' => 'End Note',
        'repair_created_at' => 'Created at',
        'repair_created_date' => 'Created on'
    ];
    const ENT_NAME = 'Repair';
    const NAME_COL = false;

    protected $appends = [
        'repair_priority_str',
        'repair_created_date'
    ];

    const CREATED_AT = 'repair_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'repair_priority' => 'integer',
        'repair_end_at' => AppDate::class,
        'repair_created_at' => AppDateTime::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
        });

        static::deleting(function ($query) {
            /** @var self $query */
            $query->files()->delete();
            $query->notes()->delete();
            $query->parts()->delete();
            $query->employees()->delete();
        });
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'eq_id')->withTrashed();
    }

    public function employees()
    {
        return $this->hasMany(EquipmentEmployee::class, 'repair_id');
    }

    public function files()
    {
        return $this->hasMany(EquipmentFile::class, 'repair_id');
    }

    public function notes()
    {
        return $this->hasMany(EquipmentNote::class, 'repair_id');
    }

    public function parts()
    {
        return $this->hasMany(EquipmentPart::class, 'repair_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assigned()
    {
        return $this->belongsTo(User::class, 'assigned_id');
    }

    public function repair_type()
    {
        return $this->belongsTo(EquipmentRepairType::class, 'repair_type_id');
    }

    public function repair_status()
    {
        return $this->belongsTo(EquipmentRepairStatus::class, 'repair_status_id');
    }

//    public function counters()
//    {
//        return $this->hasMany(EquipmentCounter::class, 'repair_id');
//    }

    public function counter()
    {
        return $this->hasOne(EquipmentCounter::class, 'repair_id')->orderBy('counter_created_at', 'desc');
    }

    public function getRepairPriorityStrAttribute()
    {
        return self::PRIORITIES[$this->attributes['repair_priority']];
    }

    public function getRepairCreatedDateAttribute()
    {
        if (!empty($this->attributes['repair_created_at'])) {
            return $this->toDate($this->attributes['repair_created_at'])
                ->format(config_item('dateFormat'));
        }
        return null;
    }

}
