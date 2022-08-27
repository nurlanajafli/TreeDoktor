<?php


namespace application\modules\schedule\models;


use application\core\Database\EloquentModel;

class ScheduleAbsenceReason extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'reasons_absence';

    /**
     * @var string
     */
    protected $primaryKey = 'reason_id';

}