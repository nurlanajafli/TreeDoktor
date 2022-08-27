<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * application\modules\equipment\models\EquipmentService
 *
 * @property int $service_id
 * @property int $service_type_id
 * @property int $eq_id
 * @property int $user_id
 * @property string $service_name
 * @property string|null $service_description
 * @property int $service_date_period_type
 * @property int|null $service_date_period
 * @property int|null $service_counter_period
 * @property string|null $service_next_date
 * @property int|null $service_next_counter
 * @property string $service_start_date
 * @property string $service_created_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @property-read mixed $service_date_period_type_str
 * @property-read mixed $service_types
 * @property-read \application\modules\equipment\models\EquipmentServiceReport|null $last_report
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentServiceReport[] $reports
 * @property-read int|null $reports_count
 * @property-read \application\modules\equipment\models\EquipmentServiceType $service_type
 * @property-read \application\modules\user\models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService newQuery()
 * @method static \Illuminate\Database\Query\Builder|\application\modules\equipment\models\EquipmentService onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceCounterPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceDatePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceDatePeriodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceNextCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceNextDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereServiceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentService whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\application\modules\equipment\models\EquipmentService withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\application\modules\equipment\models\EquipmentService withoutTrashed()
 * @mixin \Eloquent
 */
class EquipmentService extends EloquentModel
{
    use PostNoteTrait;
    use SoftDeletes;

    const DATE_PERIOD_TYPE_DAY = 1;
    const DATE_PERIOD_TYPE_WEEK = 2;
    const DATE_PERIOD_TYPE_MONTH = 3;
    const DATE_PERIOD_TYPE_YEAR = 4;
    const COUNTER_PERIOD_TYPE_DISTANCE = 1;
    const COUNTER_PERIOD_TYPE_HOURS = 2;
    const DATE_PERIOD_TYPES = [
        self::DATE_PERIOD_TYPE_DAY => 'Day',
        self::DATE_PERIOD_TYPE_WEEK => 'Week',
        self::DATE_PERIOD_TYPE_MONTH => 'Month',
        self::DATE_PERIOD_TYPE_YEAR => 'Year'
    ];

    protected $table = 'equipment_services';
    protected $primaryKey = 'service_id';
    protected $fillable = [
        'service_type_id',
        'eq_id',
        'user_id',
        'service_name',
        'service_description',
        'service_date_period_type',
        'service_date_period',
        'service_counter_period',
        'service_next_date',
        'service_next_counter',
        'service_start_date',
        'service_created_at'
    ];

    const COLUMNS = [
        'service_type_id' => 'Type',
        'eq_id' => 'Equipment',
        'user_id' => 'User',
        'service_name' => 'Name',
        'service_description' => 'Description',
        'service_date_period_type' => 'Date Period Type',
        'service_date_period' => 'Date Period',
        'service_counter_period' => 'Service Interval',
        'service_next_date' => 'Next Date',
        'service_next_counter' => 'Next Counter',
        'service_start_date' => 'Start Date',
        'service_created_at' => 'Created At'
    ];
    const ENT_NAME = 'Service';
    const NAME_COL = 'service_name';

    protected $appends = [
        'service_date_period_type_str',
        'service_types'
    ];
    protected $dates = [
        'service_created_at',
        'service_next_date',
        'service_start_date'
    ];
    const CREATED_AT = 'service_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'service_date_period' => 'integer',
        'service_counter_period' => 'integer',
        'service_date_period_type' => 'integer',
        'service_next_date' => AppDate::class,
        'service_start_date' => AppDate::class,
        'service_created_at' => AppDateTime::class,
    ];

    public static $serviceTypes = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
        });

        static::creating(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
        });
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        $table = self::tableName();
        static::addGlobalScope('fields', function (Builder $builder) use ($table) {
            $subSubRaw = \DB::raw(
                '(SELECT MAX(counter_created_at) 
                FROM equipment_counters AS eq_c_date 
                WHERE equipment_counters.eq_id = eq_c_date.eq_id)'
            );
            $subQuery = EquipmentCounter::select(['eq_id as counter_eq_id', 'counter_value'])
                ->where('counter_created_at', '=', $subSubRaw)
                ->toSql();
            $builder->select([
                $table . '.*',
                \DB::raw('DATEDIFF(' . $table . '.service_next_date, NOW()) as service_next_date_due'),
                \DB::raw('(CAST(' . $table . '.service_next_counter AS SIGNED) - CAST(IFNULL(cv.counter_value,0) AS SIGNED)) as service_next_counter_due'),
            ])->leftJoin(\Db::raw('(' . $subQuery . ') as cv'), $table . '.eq_id', '=', 'cv.counter_eq_id');
        });
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

    public function getServiceTypesAttribute()
    {
        if (!self::$serviceTypes) {
            self::$serviceTypes = EquipmentServiceType::all();
        }
        return self::$serviceTypes;
    }

    public function reports()
    {
        return $this->hasMany(EquipmentServiceReport::class, 'service_id');
    }

    public function last_report()
    {
        return $this->hasOne(EquipmentServiceReport::class, 'service_id')
            ->where('service_report_type', '=', EquipmentServiceReport::TYPE_COMPLETED)
            ->orderBy('service_report_created_at', 'desc');
    }
//    public function setServiceNextDateAttribute($value)
//    {
//        $this->attributes['service_next_date'] = Carbon::createFromFormat(config_item('dateFormat'), $value);
//    }
//
//    public function getServiceNextDateAttribute()
//    {
//        return $this->asDate($this->attributes['service_next_date'])
//            ->format(config_item('dateFormat'));
//    }
//
//    public function setServiceStartDateAttribute($value)
//    {
//        $this->attributes['service_start_date'] = Carbon::createFromFormat(config_item('dateFormat'), $value);
//    }
//    public function getServiceStartDateAttribute()
//    {
//        return $this->asDate($this->attributes['service_start_date'])
//            ->format(config_item('dateFormat'));
//    }
//
//    public function getServiceCreatedAtAttribute()
//    {
//        return $this->asDateTime($this->attributes['service_created_at'])
//            ->format(config_item('dateFormat') . (config_item('time') == 12 ? ' h:i:s a' : ' H:i:s'));
//    }

    public function getServiceDatePeriodTypeStrAttribute()
    {
        return self::DATE_PERIOD_TYPES[$this->attributes['service_date_period_type']];
    }

}
