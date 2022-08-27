<?php


namespace application\modules\estimates\models;

use application\core\Database\EloquentModel;
use application\modules\tree_inventory\models\WorkType;
class TreeInventoryEstimateServiceWorkTypes extends EloquentModel
{
    protected $table = 'tree_inventory_estimate_service_work_types';
    protected $primaryKey = 'tieswt_id';

    protected $fillable = [
        'tieswt_ties_id',
        'tieswt_wt_id'
    ];

    function work_type()
    {
        return $this->hasOne(WorkType::class, 'ip_id','tieswt_wt_id');
    }
}