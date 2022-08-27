<?php
namespace application\modules\estimates\models;

use application\core\Database\EloquentModel;

class EstimatesServicesExpenses extends EloquentModel
{
    const ATTR_EQUIPMENT_ID = 'ese_id';
    const ATTR_EQUIPMENT_SERVICE_ID = 'ese_estimate_service_id';
    const ATTR_EQUIPMENT_ITEM_ID = 'ese_title';
    const ATTR_EQUIPMENT_ESTIMATE_ID = 'ese_price';
    const ATTR_EQUIPMENT_ATTACH_ID = 'ese_estimate_id';

    protected $table = 'estimates_services_expenses';
    protected $primaryKey = 'equipment_id';

    public function estimate_service() {
        return $this->hasOne(EstimatesService::class, 'ese_estimate_service_id', 'service_id');
    }
}
