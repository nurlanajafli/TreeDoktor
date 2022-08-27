<?php


namespace application\modules\estimates\models;

use application\core\Database\EloquentModel;
use application\modules\clients\models\Client;
use application\modules\tree_inventory\models\TreeType;
use application\modules\tree_inventory\models\WorkType;

class TreeInventoryEstimateService extends EloquentModel
{
    protected $table = 'tree_inventory_estimate_services';
    protected $primaryKey = 'ties_id';

    protected $fillable = [
        'ties_number',
        'ties_type',
        'ties_size',
        'ties_priority',
        'ties_stump_cost',
        'ties_cost',
        'ties_estimate_service_id',
        'ti_id'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function tree()
    {
        return $this->hasOne(TreeType::class, 'trees_id', 'ties_type');
    }

    function estimate(){
        return $this->hasManyThrough(Estimate::class,EstimatesService::class, 'id','estimate_id','ties_estimate_service_id','estimate_id');
    }

    function estimates_services(){
        return $this->hasOne(EstimatesService::class, 'id', 'ties_estimate_service_id');
    }

    function work_types(){
        return $this->hasManyThrough(WorkType::class,TreeInventoryEstimateServiceWorkTypes::class, 'tieswt_ties_id','ip_id','ties_id','tieswt_wt_id');
    }

    function tree_inventory_work_types(){
        return $this->hasMany(TreeInventoryEstimateServiceWorkTypes::class, 'tieswt_ties_id', 'ties_id');
    }
}