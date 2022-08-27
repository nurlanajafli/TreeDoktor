<?php


namespace application\modules\schedule\models;


use application\core\Database\EloquentModel;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\user\models\User;

class ScheduleTeamsMember extends EloquentModel
{
    /**
     * @var string
     */
    protected $table = 'schedule_teams_members';

    protected $casts = [
        'weight'=>'int'
    ];
    /**
     * @var string
     */
    protected $primaryKey = null;

    public function team()
    {
        return $this->hasOne(ScheduleTeams::class, 'team_id', 'employee_team_id');
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeIsTeamLeader($query){
        return $query->join('schedule_teams', function($join){
            $join->on('schedule_teams.team_id', '=', 'schedule_teams_members.employee_team_id')->on('schedule_teams.team_leader_user_id', '=', 'schedule_teams_members.user_id');
        });
    }

    public function scopeInTeamNoLeader($query){
        return $query->join('schedule_teams', function($join){
            $join->on('schedule_teams.team_id', '=', 'schedule_teams_members.employee_team_id')->on('schedule_teams.team_leader_user_id', '<>', 'schedule_teams_members.user_id');
        });
    }

    public function scopeDatesInterval($query, $from, $to){
        return $query->whereHas('team', function ($subQuery) use ($from, $to){
            return $subQuery->datesInterval($from, $to);
        });
    }

}