<?php
use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\schedule\models\ScheduleUpdate;
use application\modules\crew\models\Crew;
class Teams extends MX_Controller
{
    function __construct()
    {

        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
    }

    function changeTeamCrew()
    {
        $request = request();
        $team = ScheduleTeams::find($request->input('team_id'));
        if(!$team)
            return $this->response(['status'=>'error'], 400);

        $crew = Crew::find($request->input('team_crew_id'));
        $team->fill(['team_color'=>$crew->crew_color, 'team_crew_id'=>$request->input('team_crew_id')]);
        $team->save();

        ScheduleUpdate::create(['update_time' => strtotime($team->team_date_start)]);

        return $this->response(['status' => 'ok']);
    }

    function deleteTeam()
    {
        $request = request();

        $Team = ScheduleTeams::with(['events'=>function($query){
            $query->whereHas('workorder', function ($query){
                return $query->whereNotNull('id');
            });
        }, 'members'])->find($request->input('team_id'));

        if($Team && $Team->events && $Team->events->count()){
            $error = $Team->events->pluck('event_start')->map(function ($item){
                return getDateTimeWithTimestamp($item);
            })->unique()->implode(', ');
            return $this->response(['error'=>'Ooops! Error! Team has events. Dates:'.$error], 400);
        }


        if(!$Team)
            return $this->response(['status' => 'ok']);

        $Team->schedule_teams_members_user()->sync([]);
        $Team->schedule_teams_equipments()->sync([]);
        $Team->delete();

        ScheduleUpdate::create(['update_time' => strtotime($Team->team_date_start)]);
        return $this->response(['status'=>'ok']);
    }
}
