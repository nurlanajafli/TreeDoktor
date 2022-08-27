<?php


namespace application\modules\estimates\models;
use application\core\Database\EloquentModel;

class EstimatesServicesStatus extends EloquentModel
{

    /**
     * Estimate table primary key name
     * @var string
     */
    protected $primaryKey = 'services_status_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'estimates_services_status';

    function scopeNew($query)
    {
        return $query->where('services_status_id', '=', 0);
    }
}