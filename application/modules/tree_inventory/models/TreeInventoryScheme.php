<?php

namespace application\modules\tree_inventory\models;
use application\core\Database\EloquentModel;

class TreeInventoryScheme extends EloquentModel
{
    protected $primaryKey = 'tis_id';
    protected $table = 'tree_inventory_scheme';
    protected $fillable = [
        'tis_name',
        'tis_client_id',
        'tis_address',
        'tis_city',
        'tis_state',
        'tis_zip',
        'tis_country',
        'tis_lat',
        'tis_lng',
        'created_at',
        'updated_at',
        'tis_id'
    ];
    public $timestamps = true;

    public function markers(){
        return $this->hasMany(TreeInventory::class, 'ti_tis_id', 'tis_id');
    }

    protected $casts = [
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp'
    ];
}