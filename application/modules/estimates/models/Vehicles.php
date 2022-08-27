<?php

namespace application\modules\estimates\models;

use application\core\Database\EloquentModel;
use DB;

class Vehicles extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'vehicles';

    /**
     * @var string
     */
    protected $primaryKey = 'vehicle_id';

    /**
     * @var array
     */
    protected $fillable = [
        'vehicle_name',
        'vehicle_trailer',
        'vehicle_options',
        'vehicle_tool',
        'vehicle_disabled',
        'vehicle_per_hour_price',
    ];

    /**
     * @var array
     */
    public $base_fields = [
        'vehicles.vehicle_id',
        'vehicles.vehicle_name',
        'vehicles.vehicle_trailer',
        'vehicles.vehicle_options',
        'vehicles.vehicle_tool',
        'vehicles.vehicle_disabled',
        'vehicles.vehicle_per_hour_price',
    ];

    /**
     * @param $query
     * @return mixed
     */
    function scopeBaseFields($query)
    {
        $query_string = implode(',', $this->base_fields);
        return $query->select(DB::raw($query_string));
    }

    public function estimates_services_equipments()
    {
        return $this->hasOne(EstimatesServicesEquipments::class, 'equipment_attach_id', 'vehicle_id');
    }
}