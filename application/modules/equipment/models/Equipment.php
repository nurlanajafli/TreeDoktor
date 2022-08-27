<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\EquipmentMethodsTrait;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

use application\modules\common\models\traits\Select2Trait;
use DB;
/**
 * application\modules\equipment\models\Equipment
 *
 * @property int $eq_id
 * @property int $group_id
 * @property int $user_id
 * @property string $eq_name
 * @property string $eq_code
 * @property string|null $eq_serial
 * @property string|null $eq_description
 * @property string|null $eq_photo
 * @property bool $eq_schedule
 * @property bool $eq_repair
 * @property mixed|null $eq_cost
 * @property Carbon|null $eq_purchased_date
 * @property int|null $eq_counter_type
 * @property string|null $eq_license_plate
 * @property string|null $eq_year
 * @property string|null $eq_make
 * @property string|null $eq_model
 * @property string|null $eq_color
 * @property string|null $eq_drive_license_req
 * @property string|null $eq_gps_id
 * @property int|null $eq_gps_start_counter
 * @property Carbon|null $eq_gps_start_date
 * @property Carbon|null $eq_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqCounterType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqDriveLicenseReq($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqGpsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqGpsStartCounter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqGpsStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqLicensePlate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqMake($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqPhoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqPurchasedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqRepair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqSerial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereUserId($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentCounter[] $counters
 * @property-read int|null $counters_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $eq_counter_type_str
 * @property-read mixed $eq_photo_url
 * @property-read mixed $eq_url
 * @property-read int $last_counter
 * @property-read \application\modules\equipment\models\EquipmentGroup $group
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentNote[] $notes
 * @property-read int|null $notes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentPart[] $parts
 * @property-read int|null $parts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentRepair[] $repairs
 * @property-read int|null $repairs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentService[] $services
 * @property-read int|null $services_count
 * @property-read \application\modules\user\models\User $user
 * @property bool $eq_schedule_tool
 * @property Carbon|null $deleted_at
 * @property-read mixed $eq_code_num
 * @property-read mixed $eq_prefix
 * @property-read mixed $trashed
 * @method static \Illuminate\Database\Query\Builder|\application\modules\equipment\models\Equipment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqScheduleTool($value)
 * @method static \Illuminate\Database\Query\Builder|\application\modules\equipment\models\Equipment withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\application\modules\equipment\models\Equipment withoutTrashed()
 * @mixin EquipmentMethodsTrait;
 * @property string|null $eq_sold_at
 * @property mixed|null $eq_sold_cost
 * @property int|null $seller_id
 * @property string|null $eq_sold_code
 * @property-read mixed $eq_created_date
 * @property-read mixed $eq_sold_code_num
 * @property-read mixed $eq_sold_date
 * @property-read mixed $eq_sold_prefix
 * @property-read \application\modules\user\models\User|null $seller
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqSoldAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqSoldCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereEqSoldCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\Equipment whereSellerId($value)
 */
class Equipment extends EloquentModel
{
    use PostNoteTrait;
    use SoftDeletes;
    use EquipmentMethodsTrait;
    use Select2Trait;

    const COUNTER_TYPE_DISTANCE = 1;
    const COUNTER_TYPE_HOURS = 2;
    const COUNTER_TYPES = [
        self::COUNTER_TYPE_DISTANCE => DISTANCE_MEASUREMENT,
        self::COUNTER_TYPE_HOURS => 'hrs'
    ];

    protected $table = 'equipment';
    protected $primaryKey = 'eq_id';
    protected $fillable = [
        'group_id',
        'user_id',
        'eq_name',
        'eq_code',
        'eq_serial',
        'eq_description',
        'eq_photo',
        'eq_schedule',
        'eq_schedule_tool',
        'eq_repair',
        'eq_cost',
        'eq_purchased_date',
        'eq_counter_type',
        'eq_license_plate',
        'eq_year',
        'eq_make',
        'eq_model',
        'eq_color',
        'eq_drive_license_req',
        'eq_gps_id',
        'eq_gps_start_counter',
        'eq_gps_start_date',
        'eq_sold_at',
        'eq_sold_cost',
        'seller_id',
        'eq_sold_code',
        'eq_created_at',
        'last_counter'
    ];

    protected $appends = [
        'eq_url',
        'eq_photo_url',
        //'last_counter',
        'eq_counter_type_str',
        'eq_prefix',
        'eq_sold_prefix',
        'eq_code_num',
        'eq_sold_code_num',
        'trashed',
        'eq_created_date',
        'eq_sold_date'
    ];

    const COLUMNS = [
        'user_id' => 'User',
        'eq_id' => 'Equipment',
        'group_id' => 'Group',
        'eq_name' => 'Name',
        'eq_code' => 'Code',
        'eq_serial' => 'Serial/VIN',
        'eq_description' => 'Description',
        'eq_photo' => 'Photo',
        'eq_schedule' => 'At Schedule',
        'eq_schedule_tool' => 'Is Tool on Schedule',
        'eq_repair' => 'On Repair',
        'eq_cost' => 'Cost',
        'eq_purchased_date' => 'Purchased Date',
        'eq_counter_type' => 'Counter Type',
        'eq_license_plate' => 'License Plate',
        'eq_year' => 'Year',
        'eq_make' => 'Make',
        'eq_model' => 'Model',
        'eq_color' => 'Color',
        'eq_drive_license_req' => 'Drive License Required',
        'eq_gps_id' => 'GPS',
        'eq_gps_start_counter' => 'GPS Start',
        'eq_gps_start_date' => 'GPS Start Date',
        'eq_sold_at' => 'Sold at',
        'eq_sold_cost' => 'Sold Cost',
        'seller_id' => 'Seller',
        'eq_sold_code' => 'Old Code',
        'eq_created_at' => 'Created at',
        'eq_created_date' => 'Created on',
        'eq_sold_date' => 'Sold on'
    ];

    const APP_EQUIPMENT = [
        'eq_id', 'eq_name'
    ];

    const ENT_NAME = 'Equipment';
    const NAME_COL = 'eq_name';
    const COL_RELATIONS = [
        'group_id' => 'group',
        'user_id' => 'user',
        'seller_id' => 'seller',
    ];

    const CREATED_AT = 'eq_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'eq_schedule' => 'boolean',
        'eq_schedule_tool' => 'boolean',
        'eq_repair' => 'boolean',
        'eq_cost' => 'decimal:2',
        'eq_purchased_date' => AppDate::class,
        'eq_counter_type' => 'integer',
        'eq_gps_start_counter' => 'integer',
        'eq_gps_start_date' => 'datetime',
        'eq_created_at' => AppDateTime::class,
        'eq_sold_at' => AppDateTime::class,
        'eq_sold_cost' => 'decimal:2',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        $table = self::tableName();
        static::addGlobalScope('last_counter', function (Builder $builder) use ($table) {
            $lastRaw = \DB::raw(
                '(SELECT counter_value 
                FROM equipment_counters 
                WHERE equipment_counters.eq_id = equipment.eq_id 
                ORDER BY equipment_counters.counter_date DESC,
                    equipment_counters.counter_id DESC 
                LIMIT 1) as last_counter'
            );

            $builder->addSelect([
                $table . ".*",
                $lastRaw,
            ]);
        });
        static::addGlobalScope('sold', function (Builder $builder) use ($table) {
            $builder->whereNull('eq_sold_at');
        });
        static::deleting(function ($query) {
            /** @var self $query */
            $query->update(['eq_code' => null]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function group()
    {
        return $this->belongsTo(EquipmentGroup::class, 'group_id');
    }

    public function counters()
    {
        return $this->hasMany(EquipmentCounter::class, 'eq_id');
    }

//    public function lasCounter(){
//        return $this->belongsTo(EquipmentCounter::class, 'eq_id','eq_id')
//            ->orderByDesc('counter_created_at')
//            ->limit(1);
//    }

    public function files()
    {
        return $this->hasMany(EquipmentFile::class, 'eq_id');
    }

    public function notes()
    {
        return $this->hasMany(EquipmentNote::class, 'eq_id');
    }

    public function parts()
    {
        return $this->hasMany(EquipmentPart::class, 'eq_id');
    }

    public function services()
    {
        return $this->hasMany(EquipmentService::class, 'eq_id');
    }

    public function repairs()
    {
        return $this->hasMany(EquipmentRepair::class, 'eq_id');
    }

    public function getEqUrlAttribute()
    {
        if(!isset($this->attributes['eq_id']))
            return null;

        return base_url('equipment/' . $this->attributes['eq_id']);
    }

    public function getEqPhotoUrlAttribute()
    {
        if (empty($this->attributes['eq_photo'])) {
            return base_url('assets/img/no-equipment.png');
        } else {
            return base_url(Storage::url('uploads/equipment/' . $this->attributes['eq_id'] . '/' . $this->attributes['eq_photo']));
        }
    }

//    public function getLastCounterAttribute(): int
//    {
//        return isset($this->attributes['last_counter']) ? $this->attributes['last_counter'] : 0;
//    }

    public function getEqCounterTypeStrAttribute()
    {
        return (isset($this->attributes['eq_counter_type']))?self::COUNTER_TYPES[$this->attributes['eq_counter_type']]:null;
    }

    public function getEqPrefixAttribute()
    {
        $exp = explode('-', $this->attributes['eq_code']??'', 2);
        return $exp[0];
    }

    public function getEqCodeNumAttribute()
    {
        $exp = explode('-', $this->attributes['eq_code']??'', 2);
        return isset($exp[1]) ? trim($exp[1], "-") : "";
    }

    public function getEqSoldPrefixAttribute()
    {
        if (!isset($this->attributes['eq_sold_code'])) {
            return null;
        }
        $exp = explode('-', $this->attributes['eq_sold_code'], 2);
        return $exp[0];
    }

    public function getEqSoldCodeNumAttribute()
    {
        if (!isset($this->attributes['eq_sold_code']))
            return null;
        $exp = explode('-', $this->attributes['eq_sold_code'], 2);
        return isset($exp[1]) ? trim($exp[1], "-") : "";
    }

    public function getTrashedAttribute()
    {
        return $this->trashed();
    }

    public function getEqCreatedDateAttribute()
    {
        if (!empty($this->attributes['eq_created_at'])) {
            return $this->toDate($this->attributes['eq_created_at'])
                ->format(config_item('dateFormat'));
        }
        return null;
    }

    public function getEqSoldDateAttribute()
    {
        if (!empty($this->attributes['eq_sold_at'])) {
            return $this->toDate($this->attributes['eq_sold_at'])
                ->format(config_item('dateFormat'));
        }
        return null;
    }

    function getItems($wdata = array(), $notIn = array())
    {
        $query = $this->with(['group']);
        if (!empty($wdata)) {
            $query->where($wdata);
        }
        if (!empty($notIn)) {
            $query->whereNotIn('eq_id', $notIn);
        }
        $query->groupBy('eq_id')->orderBy('group_id')->orderBy('eq_id');
        return $query->get();
    }

    public function scopeNameOnly($query){
        return $query->withoutGlobalScopes(['last_counter'])->select(DB::raw('eq_name as name'));
    }

    public function  scopeForSchedule($query){
        return $query->where(['eq_schedule' => 1, 'eq_repair' => 0]);
    }

}
