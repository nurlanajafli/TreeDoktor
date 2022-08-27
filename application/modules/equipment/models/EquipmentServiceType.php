<?php

namespace application\modules\equipment\models;

use application\core\Database\Casts\AppDateTime;
use application\core\Database\EloquentModel;

/**
 * application\modules\equipment\models\EquipmentServiceType
 *
 * @property int $service_type_id
 * @property int $service_type_form
 * @property string|null $service_type_name
 * @property string|null $service_type_description
 * @property string $service_type_created_at
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType query()
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType whereServiceTypeCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType whereServiceTypeDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType whereServiceTypeForm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType whereServiceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\application\modules\equipment\models\EquipmentServiceType whereServiceTypeName($value)
 * @mixin \Eloquent
 * @property-read mixed $service_type_form_str
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentServiceReport[] $reports
 * @property-read int|null $reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\application\modules\equipment\models\EquipmentService[] $services
 * @property-read int|null $services_count
 */
class EquipmentServiceType extends EloquentModel
{
    const FORM_SERVICE = 1;
    const FORM_RENEWAL = 2;
    const FORMS = [
        self::FORM_SERVICE => 'Service',
        self::FORM_RENEWAL => 'Renewal',
    ];

    protected $table = 'equipment_service_types';
    protected $primaryKey = 'service_type_id';
    protected $fillable = [
        'service_type_form',
        'service_type_name',
        'service_type_description',
        'service_type_created_at'
    ];
    protected $appends = [
        'service_type_form_str'
    ];

    const CREATED_AT = 'service_type_created_at';

    /**
     * The supported cast types are: integer, real, float, double, decimal:<digits>, string, boolean,
     * object, array, collection, date, datetime, and timestamp.
     * When casting to decimal, you must define the number of digits (decimal:2).
     * @var array
     */
    protected $casts = [
        'service_type_form' => 'integer',
        'service_type_created_at' => AppDateTime::class
    ];

    public function services()
    {
        return $this->hasMany(EquipmentService::class, 'service_type_id');
    }

    public function reports()
    {
        return $this->hasMany(EquipmentServiceReport::class, 'service_type_id');
    }

    public function getServiceTypeFormStrAttribute()
    {
        return self::FORMS[$this->attributes['service_type_form']];
    }
}
