<?php


namespace application\modules\leads\models;
use application\core\Database\EloquentModel;

class LeadService extends EloquentModel
{
    protected $primaryKey = 'id';
    protected $table = 'lead_services';
}