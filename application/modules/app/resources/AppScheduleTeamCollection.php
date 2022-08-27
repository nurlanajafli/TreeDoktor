<?php


namespace application\modules\app\resources;

use application\modules\equipment\models\Equipment;
use application\modules\user\models\User;
use application\modules\schedule\models\ScheduleAbsence;
use Illuminate\Http\Resources\Json\ResourceCollection;
use application\modules\schedule\Resources\ScheduleTeamItemsResource;

use application\modules\app\resources\AppScheduleEventsCollection;

class AppScheduleTeamCollection extends ResourceCollection
{
    public static $wrap = null;
    private $date_start;
    private $date_end;
    private $fields;
    public function __construct($collection, $date_start, $date_end, $fields = []) {
        // Ensure we call the parent constructor
        parent::__construct($collection);

        $this->date_start = $date_start;
        $this->date_end = $date_end;
        $this->fields = $fields;
    }

    public function toArray($request)
    {
        $absence_users = ScheduleAbsence::dateRange($this->date_start, $this->date_end)->get()->pluck("absence_user_id");

        $busy_members = $this->collection->pluck('members')->flatten()->pluck('id')->concat($absence_users)->unique();
        $busy_items = $this->collection->pluck('equipment')->flatten()->pluck('eq_id')->unique();

        $response = [];

        /* free members for the date range */
        $free_members = User::select(User::APP_MEMBER)->active()->fieldWorker()->whereNotIn('id', $busy_members)->orderBy('firstname')->get();
        /* free items for the date range */
        $free_items = Equipment::whereNotIn('eq_id', $busy_items)->orderBy('group_id')->orderBy('eq_id')->get();
        /* free tools for the date range */
        $free_tools = Equipment::where('eq_schedule_tool', '=', 1)->get();

        if(!count($this->fields) || isset($this->fields['teams'])){
            $response['teams'] = $this->collection->map(function ($team) use ($request) {
                $items = (new ScheduleTeamItemsResource($team))->toArray($request);
                $events = (new AppScheduleEventsCollection($team->events))->toArray($request);

                return [
                    "team_id" => $team->team_id,
                    "team_crew_id" => $team->team_crew_id,
                    "team_leader_id" => $team->team_leader_id,
                    "team_leader_user_id" => $team->team_leader_user_id,
                    "team_color" => $team->team_color,
                    "team_date" => $team->team_date,
                    "team_date_start" => $team->team_date_start,
                    "team_date_end" => $team->team_date_end,
                    "team_note" => $team->team_note,
                    "team_hidden_note" => $team->team_hidden_note,
                    "team_fail_equipment" => $team->team_fail_equipment,
                    "team_expenses" => $team->team_expenses,
                    "team_amount" => $team->team_amount,
                    "team_man_hours" => $team->team_man_hours,
                    "team_closed" => $team->team_closed,
                    "team_rating" => $team->team_rating,
                    "team_leader" => $team->team_leader,
                    'events'=>$events,
                    'items'=>$items,
                    'tools'=>$team->tools->map(function ($item){ return $item->only(Equipment::APP_EQUIPMENT); })
                ];

            });
        }

        if(!count($this->fields) || isset($this->fields['free_members'])){
            $response['free_members'] = $free_members;
        }
        if(!count($this->fields) || isset($this->fields['free_items'])){
            $response['free_items'] = $free_items->map(function ($item){ return $item->only(Equipment::APP_EQUIPMENT); });
        }
        if(!count($this->fields) || isset($this->fields['free_tools'])){
            $response['free_tools'] = $free_tools->map(function ($item){ return $item->only(Equipment::APP_EQUIPMENT); });
        }

        return $response;
    }
}