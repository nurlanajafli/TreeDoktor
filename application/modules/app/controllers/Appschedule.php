<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\employees\models\EmployeeWorked;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\ScheduleEventService;


use application\modules\schedule\models\ScheduleTeamsEquipment;
use application\modules\schedule\models\ScheduleTeamsMember;
use application\modules\schedule\models\ScheduleAbsence;
use application\modules\schedule\models\ScheduleUpdate;
use application\modules\equipment\models\Equipment;
use application\modules\user\models\User;

use Illuminate\Validation\ValidationException;
use application\modules\schedule\requests\ScheduleTeamRequest;
use application\modules\schedule\requests\ScheduleSaveEventRequest;
use application\modules\schedule\requests\TeamChangeOrder;

use  application\modules\app\resources\AppScheduleTeamCollection;

use Illuminate\Foundation\Http\FormRequest;
class Appschedule extends APP_Controller
{

    /**
     * Appschedule constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->load->library('Common/ScheduleActions');
    }

    /**
     * @param string $data
     */
    function scheduleCrews($date_start, $date_end = null){
        $date_end = (!$date_end)?$date_start:$date_end;

        $teams = ScheduleTeams::with([
            'members'=>function($query){
                return $query->select(User::APP_MEMBER);
            },
            'team_leader'=>function($query){
                return $query->select(User::APP_MEMBER);
            },
            'equipment',
            'tools',
            'events'=>function($query) use ($date_start, $date_end){
                $query->datesInterval($date_start, $date_end)->with('workorder.estimate');
            },
        ])->datesInterval($date_start, $date_end)->groupBy('team_id')->orderBy('team_id')->get();

        $response = (new AppScheduleTeamCollection($teams, $date_start, $date_end))->toArray(request());

        return $this->response($response, 200);
    }

    /**
     * @throws Exception
     */
    public function teamCreateUpdate()
    {
        $response = [];
        try {
            $request = app(ScheduleTeamRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(400, $e->validator->errors());
        }

        $teamId = $this->saveTeam($request->all());
        ScheduleUpdate::create(['update_time' => strtotime($request->input('team_date_start'))]);

        return $this->response([
            'status' => true,
        ], 200);
    }

    private function saveTeam($data){
        $data['team_man_hours'] = EmployeeWorked::whereIn('worked_user_id', $data['team_members'])
            ->whereDate('worked_date', '>=', $data['team_date_start'])
            ->whereDate('worked_date', '<=', $data['team_date_end'])->sum('worked_hours');

        //$data['team_leader_user_id'] = $data['team_leader_user_id']?:collect($data['team_members'])->filter()[0];

        $Team = new ScheduleTeams();
        if(isset($data['team_id']) && $data['team_id'])
            $Team = ScheduleTeams::find($data['team_id']);

        $Team->fill($data);
        $Team->save();

        $Team->schedule_teams_members_user()->sync($data['team_members_assoc']);
        $Team->schedule_teams_equipments()->sync($data['team_items_assoc']);

        $Team->schedule_teams_tools()->detach();
        $Team->schedule_teams_tools()->attach($data['team_tools']);
        return $Team;
    }

    /**
     * Get Free Members method
     */
    function freeMembers($id = 0){

        $team = ScheduleTeams::find($id);

        if(!$id || !$team)
            return $this->response(['error'=>'Team is not defined'], 400);

        $teams = ScheduleTeams::with([
            'members'=>function($query){
                return $query->select(User::APP_MEMBER);
            },
            'team_leader'=>function($query){
                return $query->select(User::APP_MEMBER);
            },
            'equipment'
        ])->datesInterval($team->team_date_start, $team->team_date_end)->groupBy('team_id')
            ->orderBy('team_date_start')->get();

        $response = (new AppScheduleTeamCollection($teams, $team->team_date_start, $team->team_date_end, [
            'free_members'=>true,
            'free_items'=>true
        ]))->toArray(request());

        return $this->response($response, 200);
    }

    /**
     * Team change order method
     */
    /*
    public function teamChangeOrder()
    {
        $response = [];
        try {
            $request = app(TeamChangeOrder::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(400, $e->validator->errors());
        }

        $team = ScheduleTeams::find($request->input('team_id'));
        if($request->has('members'))
            $team->members()->sync($request->input('members'));

        if($request->has('equipments'))
            $team->equipment()->sync($request->input('equipments'));
        
        ScheduleUpdate::create(['update_time' => strtotime($request->input('date'))]);

        return $this->response([
            'status' => true,
        ], 200);
    }
    */
    /**
     * Team save note method
     */
    /*
    public function team_save_note()
    {
        $field = 'team_note';
        $team_id = intval($this->input->post('team_id'));
        $note = $this->input->post('team_note', TRUE);
        $date = strtotime($this->input->post('team_date'));

        if ($this->input->post('hidden_team_note', TRUE) !== FALSE) {
            $field = 'team_hidden_note';
            $note = $this->input->post('hidden_team_note', TRUE);
        }
        if(!$note) {
            $note = NULL;
        }

        $this->load->library('Common/ScheduleActions');
        $this->scheduleactions->teamSaveNote($team_id, $field, $note, $date);

        return $this->response([
            'status' => true,
        ], 200);
    }
    */
    /**
     * Team save amount method
     */
    /*
    function team_save_amount()
    {
        $team_id = intval($this->input->post('team_id'));
        $team_amount = str_replace(',', '.', preg_replace("/[^0-9.,-]/", '', $this->input->post('team_amount', TRUE)));
        $date = strtotime($this->input->post('team_date'));

        $this->load->library('Common/ScheduleActions');
        $this->scheduleactions->teamSaveAmount($team_id, $team_amount, $date);

        return $this->response([
            'status' => true,
        ], 200);
    }
    */
    /**
     * Team change leader method
     */
    /*
    function team_change_leader()
    {
        $leader_id = intval($this->input->post('leader_id')) ? intval($this->input->post('leader_id')) : NULL;
        $team_id = intval($this->input->post('team_id'));
        $date = strtotime($this->input->post('date'));
        if(!$team_id) {
            return $this->response([
                'status' => false,
                'message' => 'Bad request'
            ], 400);
        }

        $this->load->library('Common/ScheduleActions');
        $this->scheduleactions->teamChangeLeader($team_id, $leader_id, $date);

        return $this->response([
            'status' => true,
        ], 200);
    }
    */
    /**
     * Team change color method
     */
    /*
    function team_change_color()
    {
        $team_color = $this->input->post('team_color');
        $team_id = intval($this->input->post('team_id'));
        $date = strtotime($this->input->post('date'));

        if(!$team_id) {
            return $this->response([
                'status' => false,
                'message' => 'Bad request'
            ], 400);
        }

        $this->load->library('Common/ScheduleActions');
        $this->scheduleactions->teamChangeLeader($team_id, $team_color, $date);

        return $this->response([
            'status' => true,
        ], 200);
    }
    */
    /**
     * Schedule change damage
     */
    /*
    function event_change_damage()
    {
        $id = $this->input->post('id');
        $event_damage = $this->input->post('event_damage');
        if (!$id || !$event_damage) {
            return $this->response([
                'status' => false,
                'message' => 'Bad Request',
            ], 400);
        }
        $event = ScheduleEvent::find($id);
        if (is_null($event)) {
            return $this->response([
                'status' => false,
                'message' => 'Bad Request, schedule undefined',
            ], 400);
        }
        $this->load->library('Common/ScheduleActions');
        $this->scheduleactions->updateScheduleData($id, ['event_damage' => $event_damage]);
        $team_amount = $this->scheduleactions->getCalculatedTeamAmount($event->event_team_id);

        return $this->response([
            'status' => true,
            'team_id' => $event->event_team_id,
            'team_amount' => $team_amount,
        ], 200);
    }
    */
    /**
     * Schedule change complain
     */
    /*
    function event_change_complain()
    {
        $id = $this->input->post('id');
        $event_complain = $this->input->post('event_complain');
        if (!$id || !$event_complain) {
            return $this->response([
                'status' => false,
                'message' => 'Bad Request',
            ], 400);
        }

        $this->load->library('Common/ScheduleActions');
        $this->scheduleactions->updateScheduleData($id, ['event_complain' => $event_complain]);

        return $this->response([
            'status' => true,
        ], 200);
    }
    */

    /**
     * Schedule update method
     */
    public function eventUpdate()
    {
        $request = request();
        $events_id = collect($request->all())->pluck('id');
        if(!$events_id->count())
            return $this->response(['status' => true], 200);

        $events = ScheduleEvent::whereIn('id', $events_id)->orderBy('id')->get();

        $data = collect($request->all())->sortBy('id')->values()->all();
        $result = $events->map(function ($event, $key) use ($request, $data){
            $params = $data[$key];
            request()->merge($params);

            $message = false;
            try {
                $scheduleSaveEventRequest = app(ScheduleSaveEventRequest::class);
            } catch (ValidationException $e) {
                return ['error'=>collect($e->validator->errors()->messages())->flatten()->implode('\n'), 'id'=>$params['id']];
            }

            $post_data = $scheduleSaveEventRequest->all();
            unset($post_data['event_services']);

            $event_before_change = ($event)?clone $event:NULL;
            $event->fill($post_data)->save();

            $changes = (count($event->getChanges()))?$event->getChanges():[];
            $event->schedule_event_service()->sync($scheduleSaveEventRequest->input('event_services'));

            if(isset($changes['event_team_id']) && $changes['event_team_id'] && $event_before_change->team)
                $event_before_change->team->amountRecalculation();

            $event->event_price = $event->schedule_event_service->sum('service_price');
            $event->save();

            $event->team->amountRecalculation();
            $this->scheduleactions->generateScheduleFollowUp($event->id, TRUE);
            ScheduleUpdate::create(['update_time' => $event->event_start]);

            return ['id'=>$params['id'], 'status'=>true];
        });

        return $this->response([
            'status' => true,
            'message' => $result,
        ], 200);
    }

    /**
     * Schedule create method
     */
    public function eventCreate()
    {
        try {
            $request = app(ScheduleSaveEventRequest::class);
        } catch (ValidationException $e) {
            return $this->response([
                'status' => false,
                'message' => collect($e->validator->errors()->messages())->flatten()->implode('\n'),
            ], 400);
        }

        $data = $request->all();
        unset($data['event_services']);

        $event = new ScheduleEvent();
        $event->fill($data)->save();

        $event = ScheduleEvent::find($request->input('id'));

        $event->schedule_event_service()->sync($request->input('event_services'));
        $event->event_price = $event->schedule_event_service->sum('service_price');
        $event->save();

        $event->team->amountRecalculation();

        $this->scheduleactions->generateScheduleFollowUp($event->id, TRUE);

        ScheduleUpdate::create(['update_time' => $event->event_start]);

        return $this->response([
            'status' => true,
        ], 200);
    }

    /**
     * Schedule delete method
     */
    public function eventDelete()
    {
        $event = ScheduleEvent::with(['team', 'schedule_event_service'])->find(request()->input('id'));
        if(!$event)
            return $this->response(['status' => true], 200);

        $team = $event->team;
        $event->schedule_event_service()->detach();
        $event->delete();

        $team->amountRecalculation();

        $this->scheduleactions->generateScheduleFollowUp(request()->input('id'), false);
        return $this->response([
            'status' => true,
        ], 200);
    }

    function deleteTeam($team_id)
    {
        $Team = ScheduleTeams::with(['events'=>function($query){
            $query->whereHas('workorder', function ($query){
                return $query->whereNotNull('id');
            });
        }, 'members'])->find($team_id);

        if($Team && $Team->events && $Team->events->count())
            return $this->response(['error'=>'Ooops! Team has events.'], 400);

        if(!$Team)
            return $this->response(['status' => 'ok']);

        $Team->schedule_teams_members_user()->sync([]);
        $Team->schedule_teams_equipments()->sync([]);
        $Team->delete();

        ScheduleUpdate::create(['update_time' => strtotime($Team->team_date_start)]);
        return $this->response(['status'=>'ok']);
    }
}
