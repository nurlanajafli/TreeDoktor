<?php


namespace application\modules\tree_inventory\models;


use application\core\Database\EloquentModel;

class WorkType extends EloquentModel
{
    protected $table = 'work_types';
    protected $primaryKey = 'ip_id';
    protected $fillable = ['ip_name_short', 'ip_name'];

    public static $datatableSearchableColumns = [
        'ip_id',
        'ip_name_short',
        'ip_name',
    ];
}
