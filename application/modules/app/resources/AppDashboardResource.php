<?php
namespace application\modules\app\resources;
use Illuminate\Http\Resources\Json\JsonResource;
use application\resources\data\EventsSuggestedToolsResource;
use function foo\func;

class AppDashboardResource extends JsonResource
{
    //public $preserveKeys = true;
    public static $wrap = 'data';

    public function toArray($request){

        $this->team = [ 'team_note' => $this->team_note, 'team_leader_user_id' => $this->team_leader_user_id ];

        $payday = false;
        $this->teamId = $this->team_id;

        $expenses = $this->expenses->groupBy('user_id');
        $this->members = $this->members->map(function ($item) use ($expenses, &$payday){
           $item->expenses = (isset($expenses[$item->id]))?$expenses[$item->id]->first():[];
           if($item->employeeWorked->isEmpty())
           {
               $item->times = [];
               return $item;
           }
           $item->total_hrs = $item->employeeWorked->first()->worked_hours;
           $item->times = $item->employeeWorked->first()->logins->map(function($login, $key){
               return $login->only('login', 'logout', 'login_id', 'login_worked_id');
           });

           $payday = $item->employeeWorked->first()->payroll->payroll_day;
           return $item->only('id', 'name', 'picture', 'total_hrs', 'times', 'expenses');
        });
        $this->payday = $payday;

        return [
                'teamId'=>$this->team_id,
                'payday'=>$this->payday,
                'team'=>$this->team,
                'members'=>$this->members,
                'equipment'=>$this->equipment,
                'tools'=>$this->tools,
                'suggested_tools'=>(new EventsSuggestedToolsResource($this->events))->toArray(request(), 'assoc')

        ];
        return $data;
    }

    public function with($request)
    {
        return ['status' => true];
    }

}
