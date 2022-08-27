<?php


namespace application\modules\estimates\models;
use application\core\Database\EloquentModel;
use application\modules\estimates\models\Service;


class EstimatesBundle extends EloquentModel
{
    protected $table = 'estimates_bundles';
    protected $primaryKey = 'eb_id';

    function estimate_service()
    {
        return $this->hasOne(EstimatesService::class, 'id', 'eb_service_id');
    }
}