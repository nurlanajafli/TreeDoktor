<?php

namespace application\modules\tree_inventory\models;
use application\core\Database\EloquentModel;

class TreeInventory extends EloquentModel
{
    protected $primaryKey = 'ti_id';
    protected $table = 'tree_inventory';

    protected $fillable = [
        'ti_lead_id',
        'ti_lat',
        'ti_lng',
        'ti_client_id',
        'ti_tree_number',
        'ti_tree_type',
        'ti_tree_priority',
        'ti_prune_type_id',
        'ti_remark',
        'ti_title',
        'ti_size',
        'ti_file',
        'ti_cost',
        'ti_stump_cost',
        'ti_work_type',
        'ti_map_type',
        'ti_tis_id'
    ];
}