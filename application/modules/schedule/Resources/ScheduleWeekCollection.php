<?php


namespace application\modules\schedule\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
class ScheduleWeekCollection extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        $teams = $this->collection;

        $sections = collect($request->input('interval'))->map(function ($current, $key) use ($teams, $request){
            $team = [];
            if($request->input('user_id')){
                $team = $teams->filter(function ($item) use ($current) {
                    return (strtotime($item->team_date_start) <= strtotime($current) && strtotime($item->team_date_end) >= strtotime($current));
                })->first();
            }

            if(!empty($team)){
                $result = clone $team;
                $result_array = $result->toArray();
                $result_array['team_date_start'] = $current;
                $result_array['team_date_end'] = $current;
                $result_array['key'] = $current;
                $result_array['statistic'] = new ScheduleTeamStatisticResource($team);
                return $result_array;
            }
            return ['key' => $current, 'team_id'=>0, 'statistic'=>[]];
        });

        $total = $sections->sum('statistic.actual_team_amount');
        $total_hrs = $sections->sum('statistic.team_man_hours');

        $sections = $sections->keyBy("key");

        return [
            'teams' => $sections,
            'members' => $request->input('members'),
            'user_id' => $request->input('user_id'),
            'team_crew_id' => (int)$request->input('team_crew_id'),
            'total' => round($total,2),
            'total_hrs'=>round($total_hrs,2)
        ];
    }
}