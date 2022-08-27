<?php


namespace application\modules\leads\models;
use application\core\Database\EloquentModel;
use application\modules\leads\models\LeadReasonStatus;
class LeadStatus extends EloquentModel
{
    protected $primaryKey = 'lead_status_id';
    protected $table = 'lead_statuses';

    public function reasons(){
        return $this->hasMany(LeadReasonStatus::class, 'reason_lead_status_id', 'lead_status_id');
    }


    public function scopeActive($query){
        return $query->where('lead_status_active', '=', 1);
    }

    public function scopeDefault($query){
        return $query->where('lead_status_default', '=', 1);
    }

    public function scopeDeclined($query){
        return $query->where('lead_status_declined', '=', 1);
    }

    public function scopeEstimated($query){
        return $query->where('lead_status_estimated', '=', 1);
    }

    public function scopeForApproval($query){
        return $query->where('lead_status_for_approval', '=', 1);
    }

    public function scopeDraft($query){
        return $query->where('lead_status_draft', '=', 1);
    }

}