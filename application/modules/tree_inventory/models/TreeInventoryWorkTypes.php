<?php


namespace application\modules\tree_inventory\models;

use application\core\Database\EloquentModel;
use application\modules\tree_inventory\models\WorkType;

class TreeInventoryWorkTypes extends EloquentModel
{
    protected $table = 'tree_inventory_work_types';
    protected $primaryKey = 'tiwt_id';

    protected $fillable = [
        'tiwt_tree_id',
        'tiwt_work_type_id',
    ];

    function work_type()
    {
        return $this->hasOne(WorkType::class, 'ip_id','tiwt_work_type_id');
    }
}