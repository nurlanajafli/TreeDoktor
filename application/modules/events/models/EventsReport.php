<?php


namespace application\modules\events\models;

use application\modules\estimates\models\Estimate;
use application\core\Database\EloquentModel;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\workorders\models\Workorder;
use application\modules\events\models\Event;

class EventsReport extends EloquentModel
{
    protected $primaryKey = 'er_id';
    /**
     * Table  name
     * @var string
     */
    protected $table = 'events_reports';
    protected $appends = ['er_estimator_time', 'er_estimator_time_class', 'er_report_date_original', 'er_report_date_view', 'er_travel_time_original'];

    public function workorder(){
        return $this->hasOne(Workorder::class, 'id', 'er_wo_id');
    }

    public function estimate(){
        return $this->hasOne(Estimate::class, 'estimate_id', 'er_estimate_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function team(){
        return $this->hasOne(ScheduleTeams::class, 'team_id', 'er_team_id');
    }

    public function event_work(){
        return $this->hasOne(Event::class, 'ev_event_id', 'er_event_id');
    }

    public function schedule_event(){
        return $this->hasOne(ScheduleEvent::class, 'id', 'er_event_id');
    }

    public function scopeNoConfirmed($query){
        return $query->where('er_report_confirmed', '=', 0);
    }

    public function getErReportDateViewAttribute(){
        if(!$this->attributes['er_report_date']) {
            return getDateTimeWithDate($this->attributes['er_event_date'], "Y-m-d");
        }
        return getDateTimeWithDate($this->attributes['er_report_date'], "Y-m-d");
    }

    public function getErReportDateOriginalAttribute(){
        if(!$this->attributes['er_report_date'])
            return $this->attributes['er_event_date'];

        return $this->attributes['er_report_date'];
    }

    public function getErEventPaymentAttribute(){
        if(!$this->attributes['er_event_payment'])
            return 'No';
        return $this->attributes['er_event_payment'];
    }

    public function getErEventPaymentTypeAttribute(){
        if(!$this->attributes['er_event_payment'] || $this->attributes['er_event_payment']=='No')
            return '';

        $this->attributes['er_event_payment_type'] = ($this->attributes['er_event_payment_type'])?$this->attributes['er_event_payment_type']:'Cash';
        return $this->attributes['er_event_payment_type'];
    }

    public function getErPaymentAmountAttribute(){
        if(!$this->attributes['er_event_payment'] || $this->attributes['er_event_payment']=='No')
            return '';
        return $this->attributes['er_payment_amount'];
    }

    public function getErEventWorkRemainingAttribute(){
        return ($this->attributes['er_event_work_remaining'])?$this->attributes['er_event_work_remaining']:'';
    }

    public function getErEventStartTravelAttribute(){
        if(!$this->attributes['er_event_start_travel'])
            return "00:00";

        return date(getPHPTimeFormatWithOutSeconds(), strtotime($this->attributes['er_event_start_travel']));
    }

    public function getErEventStartWorkAttribute(){
        if(!$this->attributes['er_event_start_work'])
            return "00:00";

        return date(getPHPTimeFormatWithOutSeconds(), strtotime($this->attributes['er_event_start_work']));
    }

    public function getErEventFinishWorkAttribute(){
        if(!$this->attributes['er_event_finish_work'])
            return "00:00";

        return date(getPHPTimeFormatWithOutSeconds(), strtotime($this->attributes['er_event_finish_work']));
    }

    public function getErTravelTimeAttribute(){
        return (intval($this->attributes['er_travel_time']))?gmdate("H:i", $this->attributes['er_travel_time']):'00:00';
    }

    public function getErTravelTimeOriginalAttribute(){
        return $this->attributes['er_travel_time']??0;
    }

    public function getErOnSiteTimeAttribute(){
        return (intval($this->attributes['er_on_site_time']))?gmdate("H:i", $this->attributes['er_on_site_time']):'00:00';
    }

    public function getErEstimatorTimeAttribute(){
        $time_estimator = 0;
        if($this->workorder && $this->workorder->estimate->estimates_service)
            $time_estimator = $this->workorder->estimate->estimates_service->sum('service_time');

        $hours = floor($time_estimator);
        $minutes = ($time_estimator - $hours)*60;
        return $hours . ':' . str_pad(floor($minutes), 2, '0', STR_PAD_LEFT);
    }

    public function getErEstimatorTimeClassAttribute(){
        //estimator_time_class
        $time_estimator = 0;
        if($this->workorder && $this->workorder->estimate->estimates_service)
            $time_estimator = $this->workorder->estimate->estimates_service->sum('service_time');

        return ''; //($data['full_time'] > $time_estimator*3600)?'text-danger':'text-success';
    }


}