<?php


namespace application\modules\leads\models;
use application\core\Database\EloquentModel;

class LeadReasonStatus extends EloquentModel
{
    protected $primaryKey = 'reason_id';
    protected $table = 'lead_reason_status';
}