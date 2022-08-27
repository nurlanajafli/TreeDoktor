<?php
namespace application\modules\clients\models;
use application\core\Database\EloquentModel;
use DB;
use application\modules\workorders\models\WorkorderStatus;

class StatusLog extends EloquentModel
{
    const ATTR_STATUS_DATE = 'status_date';
    const ATTR_STATUS_VALUE= 'status_value';
    const ATTR_STATUS_TYPE = 'status_type';
    const ATTR_STATUS_ITEM_ID = 'status_item_id';
    /**
     * @var string
     */
    protected $table = 'status_log';

    /**
     * @var string
     */
    protected $primaryKey = 'status_id';

    /**
     * @var array
     */
    protected $appends = ['status_date_view'];

    /**
     * @var array
     */
    protected $fillable = [
        'status_type', 'status_item_id', 'status_value',
        'status_date', 'status_user_id'
    ];

    function scopeLastChangeDate($query){
        return $query->select(DB::raw("MAX(status_date) as changedate"));
    }

    function workorder_status(){
        return $this->hasOne(WorkorderStatus::class, 'wo_status_id', 'status_value');
    }

    function getStatusDateViewAttribute(){
        return  date(getDateFormat(), $this->attributes['status_date']);
    }
}