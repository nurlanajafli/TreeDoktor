<?php


namespace application\modules\workorders\models;
use application\core\Database\EloquentModel;
use application\modules\workorders\models\Workorder;
use DB;

class WorkorderStatus extends EloquentModel
{
    const ATTR_ID = 'wo_status_id';
    const ATTR_NAME = 'wo_status_name';
    const ATTR_COLOR = 'wo_status_color';
    const ATTR_ACTIVE = 'wo_status_active';
    const ATTR_PRIORITY = 'wo_status_priority';
    const ATTR_IS_DEFAULT = 'is_default';
    const ATTR_IS_CONFIRM_BY_CLIENT = 'is_confirm_by_client';
    const ATTR_IS_FINISHED_BY_FIELD = 'is_finished_by_field';
    const ATTR_IS_FINISHED = 'is_finished';
    const ATTR_IS_PROTECTED = 'is_protected';
    const ATTR_IS_DELETE_INVOICE = 'is_delete_invoice';
    const ATTR_WO_STATUS_USE_TEAM_COLOR = 'wo_status_use_team_color';
    const ATTR_WO_STATUS_USE_ESTIMATOR_COLOR = 'wo_status_use_estimator_color';

    /**
     * Workorder table primary key name
     * @var string
     */
    protected $primaryKey = 'wo_status_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'workorder_status';

    protected $fillable = [
        'wo_status_name',
        'wo_status_color',
        'is_finished_by_field',
        'is_confirm_by_client',
        'is_delete_invoice',
        'wo_status_active',
        'is_protected',
        'wo_status_use_team_color',
        'wo_status_use_estimator_color'
    ];


    public function workorders(){
        return $this->hasMany(Workorder::class, 'wo_status', 'wo_status_id');
    }

    /********************* Sorts **********************/
    public function scopeActiveDescending($query){
        return $query->orderBy('wo_status_active', 'DESC');
    }

    public function scopeActiveAscending($query){
        return $query->orderBy('wo_status_active', 'ASC');
    }

    public function scopePriorityDescending($query){
        return $query->orderBy('wo_status_priority', 'DESC');
    }

    public function scopePriorityAscending($query){
        return $query->orderBy('wo_status_priority', 'ASC');
    }

    /********************* Sorts **********************/


    public function scopeActive($query){
        return $query->where('wo_status_active', '=', 1);
    }

    public function scopeDefault($query){
        return $query->where('is_default', '=', 1);
    }

    public function scopeConfirmByClient($query){
        return $query->where('is_confirm_by_client', '=', 1);
    }

    public function scopeFinishedByField($query){
        return $query->where('is_finished_by_field', '=', 1);
    }

    public function scopeFinished($query){
        return $query->where('is_finished', '=', 1);
    }

    public function scopeNotFinished($query){
        return $query->where('is_finished', '!=', 1);
    }

    public function scopeProtected($query){
        return $query->where('is_protected', '=', 1);
    }

    public function scopeDeleteInvoice($query){
        return $query->where('is_delete_invoice', '=', 1);
    }

    /**
     * @param $id
     * @return bool
     */
    public static function isStatusFinished($id)
    {
        $status = WorkorderStatus::select(static::ATTR_IS_FINISHED)->find($id);
        return (bool) $status->getAttribute(static::ATTR_IS_FINISHED);
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeApiFields($query)
    {
        return $query->select([
            WorkorderStatus::tableName() . '.' . WorkorderStatus::ATTR_ID,
            WorkorderStatus::tableName() . '.' . WorkorderStatus::ATTR_NAME,
            WorkorderStatus::tableName() . '.' . WorkorderStatus::ATTR_COLOR
        ]);
    }

    /**
     * @param $query
     * @return mixed
     */
    function scopeBaseFields($query)
    {
        return $query->select([
            WorkorderStatus::tableName() . '.' . WorkorderStatus::ATTR_ID,
            WorkorderStatus::tableName() . '.' . WorkorderStatus::ATTR_NAME,
            WorkorderStatus::tableName() . '.' . WorkorderStatus::ATTR_COLOR
        ]);
    }

    public static function selectStatuses2FormatData()
    {
        $services = self::active()->get();
        return $services->mapWithKeys(function ($item, $index) {
            return [
                $index => [
                    'id' => $item['wo_status_id'],
                    'text' => $item['wo_status_name'],
                ]
            ];
        })->toJson();
    }
}