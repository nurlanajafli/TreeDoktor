<?php
namespace application\modules\brands\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\brands\models\traits\BrandsTrait;

use application\modules\user\models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class BrandContact extends EloquentModel
{
    use BrandsTrait;
    use SoftDeletes;

    protected $table = 'brand_contacts';
    protected $primaryKey = 'bc_id';
    
    protected $fillable = ['bc_brand_id', 'bc_phone', 'bc_phone_clean', 'bc_email', 'bc_site'];
    
    const CREATED_AT = 'bc_created_at';

    /*
    protected $appends = [
        'eq_url',
        'eq_photo_url',
        'last_counter',
    ];
    */
    
    const COLUMNS = [
        'bc_brand_id'       => 'Brand',
        'bc_phone'          => 'Phone',
        'bc_email'          => 'Email',
        'bc_site'           => 'Site'
    ];
    /*
    const ENT_NAME = 'Equipment';
    const NAME_COL = 'eq_name';
    const COL_RELATIONS = [
        'group_id' => 'group',
        'user_id' => 'user',
        'seller_id' => 'seller',
    ];

    const CREATED_AT = 'eq_created_at';
    */

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    /*
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
    */
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    /*
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
            
            $query->update(['eq_code' => null]);
        });
    }
    */
    /*
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group()
    {
        return $this->belongsTo(EquipmentGroup::class, 'group_id');
    }

    public function counters()
    {
        return $this->hasMany(EquipmentCounter::class, 'eq_id');
    }
    */

}
