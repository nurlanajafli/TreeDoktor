<?php


namespace application\modules\tree_inventory\models;


use application\core\Database\EloquentModel;

class TreeType extends EloquentModel
{
    protected $table = 'trees';
    protected $primaryKey = 'trees_id';
    protected $fillable = ['trees_name_eng', 'trees_name_lat'];

    public static $datatableSearchableColumns = [
        'trees_id',
        'trees_name_eng',
        'trees_name_lat',
    ];
}
