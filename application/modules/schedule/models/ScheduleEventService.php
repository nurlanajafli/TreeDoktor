<?php


namespace application\modules\schedule\models;
use application\core\Database\EloquentModel;
use application\modules\estimates\models\EstimatesService;
use application\modules\estimates\models\Service;


class ScheduleEventService extends EloquentModel
{
    protected $table = 'schedule_event_services';
    protected $primaryKey = 'id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function estimate_service_new_status(){
        return $this->hasOne(EstimatesService::class, 'id', 'service_id')->with(['service', 'bundle.estimate_service.service'])->newService();
    }

}