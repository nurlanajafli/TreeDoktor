<?php


namespace application\modules\schedule\models;


use application\core\Database\EloquentModel;
use application\modules\user\models\User;
use application\modules\schedule\models\ScheduleAbsenceReason;

class ScheduleAbsence extends EloquentModel
{
    const ABSENCE_COLOR = "#f21b1b";
    /**
     * @var string
     */
    protected $table = 'schedule_absence';

    /**
     * @var string
     */
    protected $primaryKey = false;
    /**
     * @var array
     */
    protected $appends = [
        'absence_color',
    ];

    public function user(){
        return $this->hasOne(User::class, 'id', 'absence_user_id');
    }

    public function reason(){
        return $this->hasOne(ScheduleAbsenceReason::class, 'reason_id', 'absence_reason_id');
    }

    function scopeDateRange($query, $from, $to){
        return $query->whereDate('absence_ymd', '>=', $from)
            ->whereDate('absence_ymd', '<=', $to);
    }

    public function getAbsenceColorAttribute(){
        return ScheduleAbsence::ABSENCE_COLOR;
    }

}