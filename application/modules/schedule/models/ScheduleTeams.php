<?php
namespace application\modules\schedule\models;

use application\core\Database\EloquentModel;

use application\modules\equipment\models\Equipment;
use application\modules\user\models\User;
use application\modules\workorders\models\Workorder;
use application\modules\estimates\models\EstimatesService;
use application\modules\common\models\traits\Select2Trait;
use application\modules\schedule\models\ScheduleTeamsBonuse;
use application\modules\crew\models\Crew;

class ScheduleTeams extends EloquentModel
{
    use Select2Trait;

    const DEFAULT_TEAM_COLOR = '#5785fa';

    /**
     * @var string
     */
    protected $table = 'schedule_teams';

    /**
     * @var string
     */
    protected $primaryKey = 'team_id';

    /**
     * @var array
     */
    protected $appends = [
        'team_man_hours',
        'team_date_start_view',
        'team_date_end_view',
        'timeline_id',
        'team_amount_money_format'
    ];

    protected $fillable = [
        'team_crew_id', 'team_leader_user_id', 'team_color', 'team_date_start', 'team_date_end', 'team_man_hours', 'team_note'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function schedule(){
        return $this->hasOne(ScheduleEvent::class, 'event_team_id', 'team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function team_leader()
    {
        return $this->hasOne(User::class, 'id', 'team_leader_user_id')->where('id', '<>', 0);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function schedule_teams_members_user()
    {
        return $this->belongsToMany(User::class, 'schedule_teams_members', 'employee_team_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function members()
    {
        return $this->schedule_teams_members_user()->withPivot('weight');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function schedule_teams_members()
    {
        return $this->hasMany(ScheduleTeamsMember::class, 'employee_team_id', 'team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function schedule_teams_equipments()
    {
        return $this->belongsToMany(Equipment::class, 'schedule_teams_equipment', 'equipment_team_id', 'equipment_id' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function equipment()
    {
        return $this->schedule_teams_equipments()->withPivot('weight');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function schedule_teams_tools()
    {
        return $this->belongsToMany(Equipment::class, 'schedule_teams_tools', 'stt_team_id', 'stt_item_id' )->withPivot('stt_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function tools()
    {
        return $this->schedule_teams_tools();
    }

    function schedule_equipments()
    {
        return $this->hasMany(ScheduleTeamsEquipment::class, 'equipment_team_id', 'team_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    function events() {
        return $this->hasMany(ScheduleEvent::class, 'event_team_id', 'team_id');
    }

    function bonuses() {
        return $this->hasMany(ScheduleTeamsBonuse::class, 'bonus_team_id', 'team_id');
    }

    function crew() {
        return $this->hasOne(Crew::class, 'crew_id', 'team_crew_id');
    }

    function scopeAppDashboard($query){
        return $query->select(['team_id', 'team_leader_user_id', 'team_note', 'team_date_start', 'team_date_end']);
    }

    function scopeTeamLead($query, $user_id){
        return $query->where('team_leader_user_id', '=', $user_id);
    }

    function scopeDatesInterval($query, $from, $to){
        return $query->where(function ($query_sub) use ($from, $to){
            $query_sub->whereDate('team_date_start', '<=', $from)
                ->whereDate('team_date_end', '<=', $to)
                ->whereDate('team_date_end', '>=', $from);
        })
        ->orWhere(function ($query_sub) use ($from, $to){
            $query_sub->whereDate('team_date_start', '>=', $from)
                ->whereDate('team_date_end', '>=', $to)
                ->whereDate('team_date_start', '<=', $to);
        })
        ->orWhere(function ($query_sub) use ($from, $to){
            $query_sub->whereDate('team_date_start', '>=', $from)
                ->whereDate('team_date_end', '<=', $to);
        })
        ->orWhere(function ($query_sub) use ($from, $to){
            $query_sub->whereDate('team_date_start', '<=', $from)
                ->whereDate('team_date_end', '>=', $to);
        });
    }

    function scopeCrewType($query, $team_crew_id){
        $query->where('team_crew_id', $team_crew_id);
    }

    function scopeWithMember($query, $user_id){
        $query->whereHas('schedule_teams_members', function ($query) use ($user_id){
            $query->where('user_id', $user_id);
        });
    }

    function getTeamDateStartViewAttribute(){
        return getDateTimeWithDate($this->attributes['team_date_start'], 'Y-m-d', false, false, true);
    }

    function getTeamDateEndViewAttribute(){
        return getDateTimeWithDate($this->attributes['team_date_end'], 'Y-m-d', false, false, true);
    }

    function getTimelineIdAttribute(){
        if((int)$this->attributes['team_leader_user_id'])
            return $this->attributes['team_leader_user_id'];

        return $this->attributes['team_id'];
    }

    function getTeamLeaderUserIdAttribute(){
        return (int)$this->attributes['team_leader_user_id'];
    }

    function getTeamAmountMoneyFormatAttribute(){
        if(!isset($this->attributes['team_amount']))
            return money(0);

        return money((int)$this->attributes['team_amount']);
    }

    function getActualTeamAmountAttribute(){
        $result = $this->attributes['team_amount'];
        if(!empty($this->events))
            $result = $result-$this->events->sum('event_total_expenses');

        return $result;
    }

    function getActualPerHourAttribute(){
        if(empty($this->attributes['team_man_hours']))
            return 0;
        $team_damage = (!empty($this->events))?$this->events->sum('event_damage'):0;

        if($this->actual_team_amount && $this->attributes['team_man_hours'])
            return round((($this->actual_team_amount - $team_damage) / $this->attributes['team_man_hours']), 2);

        return 0;
    }

    function getTeamStatisticColorAttribute(){
        if(!$this->actual_per_hour)
            return false;

        if($this->actual_per_hour < GOOD_MAN_HOURS_RETURN)
            return '#fa5542';
        if($this->actual_per_hour > GOOD_MAN_HOURS_RETURN && $this->actual_per_hour < GREAT_MAN_HOURS_RETURN)
            return '#ffc333';
        if($this->actual_per_hour > VERY_GREAT_MAN_HOURS_RETURN)
            return 'linear-gradient(45deg,#277700 3%, #3bc63b 22%,#52b152 30%,#11e603 54%,#4ace04 72%,#277700 98%)';

        return '#8ec165';
    }

    function getTeamManHoursAttribute(){
        if(empty($this->attributes['team_man_hours']))
            return 0;

        return ($this->attributes['team_man_hours'] < 0)?0:$this->attributes['team_man_hours'];
    }

    public function amountRecalculation(){
        $this->team_amount = $this->events->sum('event_price');
        $this->save();
    }

    public function optimizedRoute(){
        if(md5($this->events->sortBy('event_start')->implode('id', ',')) != $this->team_route_hash){
            $this->team_route_optimized = 0;
            $this->save();
        }
    }

    public static function updateTeamDates($current, $event_time)
    {
        $current_start = strtotime($current->team_date_start.' 00:00');
        $current_end = strtotime($current->team_date_end.' 23:59:59');

        $result = ['team_date_start'=>$current->team_date_start, 'team_date_end'=>$current->team_date_end];

        if($current_end < $event_time->team_date_start || $current_start > $event_time->team_date_end)
            return [
                'is_new'=>true,
                'team_date_start'=>date("Y-m-d", $event_time->team_date_start),
                'team_date_end'=>date("Y-m-d", $event_time->team_date_end)
            ];

        if($event_time->team_date_start < $current_start){
            $result['team_date_start'] = date("Y-m-d", $event_time->team_date_start);
            $result['team_date_end'] = $current->team_date_end;
        }
        if($event_time->team_date_end > $current_end){
            $result['team_date_start'] = $current->team_date_start;
            $result['team_date_end'] = date("Y-m-d", $event_time->team_date_end);
        }

        return $result;
    }
}