<?php


namespace application\modules\estimates\models;
use application\core\Database\EloquentModel;

class EstimateReasonStatus extends EloquentModel
{

    const ATTR_REASON_ID = 'reason_id';
    const ATTR_REASON_NAME = 'reason_name';
    const ATTR_REASON_EST_STATUS_ID = 'reason_est_status_id';
    const ATTR_REASON_ACTIVE = 'reason_active';

    /**
     * Estimate table primary key name
     * @var string
     */
    protected $primaryKey = 'est_status_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'estimate_reason_status';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function estimate_status() {
        return $this->hasOne(EstimateStatus::class, 'reason_est_status_id', 'est_status_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeApiFields($query) {
        return $query->select([
            static::tableName() . '.' .self::ATTR_REASON_ID,
            static::tableName() . '.' .self::ATTR_REASON_NAME,
            static::tableName() . '.' .self::ATTR_REASON_EST_STATUS_ID,
        ]);
    }
}
