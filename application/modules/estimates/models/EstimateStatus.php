<?php


namespace application\modules\estimates\models;
use application\core\Database\EloquentModel;

class EstimateStatus extends EloquentModel
{

    const ACTIVE_FLAG = 'est_status_active';
    const DEFAULT_FLAG = 'est_status_default';
    const CONFIRMED_FLAG = 'est_status_confirmed';
    const DECLINED_FLAG = 'est_status_declined';
    const SENT_FLAG = 'est_status_sent';

    /**
     * Estimate table primary key name
     * @var string
     */
    protected $primaryKey = 'est_status_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'estimate_statuses';

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function reason() {
        return $this->hasMany(EstimateReasonStatus::class, 'reason_est_status_id')
            ->where([EstimateReasonStatus::tableName() . '.' . EstimateReasonStatus::ATTR_REASON_ACTIVE => 1]);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeApiFields($query) {
        return $query->select([
            'estimate_statuses.est_status_id',
            'estimate_statuses.est_status_declined'
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('est_status_active', '=', 1);
    }

    public function scopeDefault($query)
    {
        return $query->where('est_status_default', '=', 1);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('est_status_confirmed', '=', 1);
    }

    public function scopeDeclined($query)
    {
        return $query->where('est_status_declined', '=', 1);
    }

    public function scopeSent($query)
    {
        return $query->where('est_status_sent', '=', 1);
    }
}
