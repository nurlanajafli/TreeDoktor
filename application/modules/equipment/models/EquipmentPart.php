<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDate;
use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;
use application\modules\equipment\models\traits\PostNoteTrait;
use application\modules\user\models\User;
use Illuminate\Support\Arr;

/**
 * application\modules\equipment\models\EquipmentPart
 *
 * @property int $part_id
 * @property int $user_id
 * @property int $eq_id
 * @property int|null $repair_id
 * @property int|null $service_report_id
 * @property string $part_name
 * @property string $part_number
 * @property string|null $part_seller
 * @property float $part_price
 * @property string|null $part_description
 * @property string|null $part_purchased_date
 * @property \Illuminate\Support\Carbon $part_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart whereEqId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartPurchasedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartSeller($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart whereRepairId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart whereServiceReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart whereUserId($value)
 * @mixin \Eloquent
 * @property string|null $part_tax_name
 * @property float $part_tax_rate
 * @property float $part_price_with_tax
 * @property-read \application\modules\equipment\models\Equipment $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentFile[] $files
 * @property-read int|null $files_count
 * @property-read mixed $part_purchased_at
 * @property-read \application\modules\equipment\models\EquipmentRepair|null $repair
 * @property-read \application\modules\equipment\models\EquipmentServiceReport|null $service_report
 * @property-read \application\modules\user\models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartPriceWithTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentPart wherePartTaxRate($value)
 */
class EquipmentPart extends EloquentModel
{
    use PostNoteTrait;

    public static $noteTitle = "Part";
    protected $table = 'equipment_parts';
    protected $primaryKey = 'part_id';
    protected $fillable = [
        'user_id',
        'eq_id',
        'repair_id',
        'service_report_id',
        'part_name',
        'part_number',
        'part_seller',
        'part_price',
        'part_tax_name',
        'part_tax_rate',
        'part_description',
        'part_purchased_date',
        'part_created_at'
    ];

    const COLUMNS = [
        'user_id' => 'User',
        'eq_id' => 'Equipment',
        'repair_id' => 'Repair Request',
        'service_report_id' => 'Service Report',
        'part_name' => 'Name',
        'part_number' => 'Number',
        'part_seller' => 'Seller',
        'part_price' => 'Price',
        'part_tax_name' => 'Tax Name',
        'part_tax_rate' => 'Tax Rate',
        'part_description' => 'Description',
        'part_purchased_date' => 'Purchased Date',
        'part_created_at' => 'Created At'
    ];
    const ENT_NAME = 'Part';
    const NAME_COL = 'part_name';

    const CREATED_AT = 'part_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'note_type' => 'integer',
        'part_purchased_date' => AppDate::class,
        'part_created_at' => AppDateTime::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            if (($user = request()->user()) && !isset($query->attributes['user_id'])) {
                $query->user_id = $user->id;
            }
        });

        static::saving(function ($query) {
            $query->attributes['part_price_with_tax'] =
                (int)Arr::get($query->attributes, 'part_price', 0)
                * (int)Arr::get($query->attributes, 'part_tax_rate', 1);
        });

        static::deleting(function ($query) {
            $query->files()->delete();
        });
    }

    public function files()
    {
        return $this->hasMany(EquipmentFile::class, 'part_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
}
