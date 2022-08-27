<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;

/**
 * application\modules\equipment\models\EquipmentServiceReport
 *
 * @property int $service_report_id
 * @property int $user_id
 * @property int $eq_id
 * @property int $service_type_id
 * @property int $service_id
 * @property int $service_report_type
 * @property \Illuminate\Support\Carbon|null $service_report_postponed_to
 * @property string $service_report_note
 * @property \Illuminate\Support\Carbon $service_report_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceReportCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceReportNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceReportPostponedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceReportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentEmployee[] $employees
 * @property-read int|null $employees_count
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $service_report_type_str
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentNote[] $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentPart[] $parts
 * @property-read int|null $parts_count
 * @property-read \application\modules\equipment\models\EquipmentService $service
 * @property-read \application\modules\equipment\models\EquipmentServiceType $service_type
 * @property-read \application\modules\user\models\User $user
 * @property string|null $service_report_end_date
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentCounter[] $counters
 * @property-read int|null $counters_count
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceReport whereServiceReportEndDate($value)
 * @property-read \application\modules\equipment\models\EquipmentCounter|null $counter
 * @property-read mixed $service_report_created_date
 */
class EquipmentServiceReport extends EloquentModel
{
    use PostNoteTrait;

    const TYPE_COMPLETED = 1;
    const TYPE_POSTPONED = 2;
    const TYPES = [
        self::TYPE_COMPLETED => 'Completed',
        self::TYPE_POSTPONED => 'Postponed',

    ];
    protected $table = 'equipment_service_reports';
    protected $primaryKey = 'service_report_id';
    protected $fillable = [
        'user_id',
        'eq_id',
        'service_type_id',
        'service_id',
        'service_report_type',
        'service_report_postponed_to',
        'service_report_note',
        'service_report_end_date',
        'service_report_created_at'
    ];

    protected $appends = [
        'service_report_type_str',
        'service_report_created_date'
    ];

    const COLUMNS = [
        'user_id' => 'User',
        'eq_id' => 'Equipment',
        'service_type_id' => 'Service Type',
        'service_report_type' => 'Type',
        'service_report_postponed_to' => 'Postponed To',
        'service_report_note' => 'Note',
        'service_report_end_date' => 'End Date',
        'service_report_created_at' => 'Created at',
        'service_report_created_date' => 'Created on'
    ];
    const ENT_NAME = 'Service Report';
    const NAME_COL = false;

    const CREATED_AT = 'service_report_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'service_report_type' => 'integer',
        'service_report_postponed_to' => AppDate::class,
        'service_report_end_date' => AppDate::class,
        'service_report_created_at' => AppDateTime::class
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
            if ($counter = $query->counter) {
                $counter->equipment()->dissociate();
            }
        });
    }

    public function employees()
    {
        return $this->hasMany(EquipmentEmployee::class, 'service_report_id');
    }

    public function files()
    {
        return $this->hasMany(EquipmentFile::class, 'service_report_id');
    }

    public function notes()
    {
        return $this->hasMany(EquipmentNote::class, 'service_report_id');
    }

    public function parts()
    {
        return $this->hasMany(EquipmentPart::class, 'service_report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'eq_id')->withTrashed();
    }

    public function service_type()
    {
        return $this->belongsTo(EquipmentServiceType::class, 'service_type_id');
    }

    public function service()
    {
        return $this->belongsTo(EquipmentService::class, 'service_id')->withTrashed();
    }

//    public function counters()
//    {
//        return $this->hasMany(EquipmentCounter::class, 'service_report_id');
//    }

    public function counter()
    {
        return $this->hasOne(EquipmentCounter::class, 'service_report_id');
    }

    public function getServiceReportTypeStrAttribute()
    {
        return self::TYPES[$this->attributes['service_report_type']];
    }

    public function getServiceReportCreatedDateAttribute()
    {
        if (!empty($this->attributes['service_report_created_at'])) {
            return $this->toDate($this->attributes['service_report_created_at'])
                ->format(config_item('dateFormat'));
        }
        return null;
    }
}
