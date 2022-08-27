<?php
namespace application\modules\schedule\models;
use application\core\Database\EloquentModel;

use application\modules\estimates\models\EstimatesServicesEquipments;
use application\modules\schedule\models\ScheduleTeamsMember;
use application\modules\user\models\User;
use application\modules\workorders\models\Workorder;
use application\modules\estimates\models\EstimatesService;
use application\modules\schedule\models\Expense;
use application\modules\events\models\Event;
use application\modules\events\models\EventsReport;

class ScheduleEvent extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'schedule';

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'event_team_id',
        'event_wo_id',
        'event_start',
        'event_end',
        'event_note',
        'event_report',
        'event_report_confirmed',
        'event_services',
        'event_damage',
        'event_complain',
        'event_compliment',
        'event_price',
        'event_expenses',
        'event_state'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'event_date',
        'event_date_time',
        'event_end_date',
        'event_end_date_time',
        'event_time_interval_string'
    ];

    protected $casts = [
        'event_start' => 'integer',
        'event_end' => 'integer'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function workorder(){
        return $this->hasOne(Workorder::class, 'id', 'event_wo_id')->with(['estimate']);
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function team(){
        return $this->hasOne(ScheduleTeams::class, 'team_id', 'event_team_id');
    }

    /**
     * @return \application\models\Relations\HasManySyncable
     */
    public function members(){
        return $this->hasMany(ScheduleTeamsMember::class, 'employee_team_id', 'event_team_id');
    }

    /*function event_service(){
        return $this->hasMany(ScheduleEventService::class, 'event_id', 'id');
    }*/

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function schedule_event_service(){
        return $this->belongsToMany(EstimatesService::class, 'schedule_event_services', 'event_id', 'service_id', 'id')->with(['service']);
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function estimates_services_equipment(){
        return $this->belongsToMany(EstimatesServicesEquipments::class, 'schedule_event_services', 'event_id', 'service_id', 'id', 'equipment_service_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function event_report(){
        return $this->hasOne(Event::class, 'ev_event_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManySyncable
     */
    function event_works(){
        return $this->hasMany(Event::class, 'ev_event_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManySyncable
     */
    function event_works_reports(){
        return $this->hasMany(EventsReport::class, 'er_event_id', 'id');
    }
    /**
     * @return \application\models\Relations\HasManySyncable
     */
    function expenses(){
        return $this->hasMany(Expense::class, 'expense_event_id', 'id');
    }

    /**
     * @return false|string
     */
    public function getEventDateAttribute(){
        return date('Y-m-d', $this->attributes['event_start']);
    }

    /**
     * @return false|string
     */
    public function getEventDateTimeAttribute(){
        return date('Y-m-d H:i', $this->attributes['event_start']);
    }

    /**
     * @return false|string
     */
    public function getEventEndDateAttribute(){
        return date('Y-m-d', $this->attributes['event_end']);
    }

    /**
     * @return false|string
     */
    public function getEventEndDateTimeAttribute(){
        return date('Y-m-d H:i', $this->attributes['event_end']);
    }

    /**
     * @return string
     */
    public function getEventTimeIntervalStringAttribute(){
        $date = date('H', $this->attributes['event_start']);
        if($date >= 0 && $date < 5)
            $time = '(arrival time between 2AM and 5AM)';
        elseif($date >= 5 && $date < 7)
            $time = '(arrival time between 5AM and 8AM)';
        elseif($date >= 7 && $date < 11)
            $time = '(arrival time between 8AM and 11AM)';
        elseif($date >= 11 && $date < 14)
            $time = '(arrival time between 11AM and 2PM)';
        elseif($date >= 14 && $date < 17)
            $time = '(arrival time between 2PM and 5PM)';
        elseif($date >= 17 && $date <= 20)
            $time = '(arrival time between 5PM and 8PM)';
        else
            $time = '(arrival time after 8PM)';

        $time .= ' on ' . date('F d', $this->attributes['event_start']);

        return $time;
    }

    function scopeWithTeam($query, $team_id){
        return $query->where('event_team_id', '=', $team_id);
    }

    function scopeWithMember($query, $user_id){
        $query->whereHas('members', function ($query) use ($user_id){
            $query->where('user_id', '=', $user_id);
        });
    }

    function scopeTeamCrew($query, $crew_id){
        $query->whereHas('team', function ($query) use ($crew_id){
            $query->where('team_crew_id', '=', $crew_id);
        });
    }

    /*
    public function setEventStartAttribute($value){
        return isset($this->attributes['event_start'])?(int)$value:0;
    }

    public function setEventEndAttribute($value){
        return isset($this->attributes['event_end'])?(int)$value:0;
    }
    */

    /**
     * This will be called when fetching the element.
     */
    public function getNumberAttribute($value)
    {
        return (int)$value;
    }

    function scopeDatesInterval($query, $from, $to, $timestamps = false){

        $time_from = $from;
        $time_to = $to;

        if($timestamps===false){
            $time_from = (int)strtotime($from." 00:00:01");
            $time_to = (int)strtotime($to." 23:59:59");
        }

        return $query->where(function ($query_sub) use ($time_from, $time_to){
            $query_sub->where('event_start', '<=', $time_from)
                ->where('event_end', '<=', $time_to)
                ->where('event_end', '>=', $time_from);
        })
            ->orWhere(function ($query_sub) use ($time_from, $time_to){
                $query_sub->where('event_start', '>=', $time_from)
                    ->where('event_end', '>=', $time_to)
                    ->where('event_start', '<=', $time_to);
            })
            ->orWhere(function ($query_sub) use ($time_from, $time_to){
                $query_sub->where('event_start', '>=', $time_from)
                    ->where('event_end', '<=', $time_to);
            })
            ->orWhere(function ($query_sub) use ($time_from, $time_to){
                $query_sub->where('event_start', '<=', $time_from)
                    ->where('event_end', '>=', $time_to);
            });
    }

    public static function baseEventData($team_id, $event_start, $event_end){
        if(!$team_id || !$event_start || !$event_end)
            return [];

        $id = intval(microtime(true) * 1000);
        return [
            'id' => $id,
            'event_team_id' => $team_id,
            'event_start' => $event_start,
            'event_end' => $event_end,
            'event_report' => NULL,
            'event_state' => 0,
            'event_report_confirmed' => 0,
            'event_damage' => 0,
            'event_complain' => 0,
            'event_compliment' => NULL
        ];
    }

}