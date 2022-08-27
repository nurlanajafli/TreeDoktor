<?php
namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class ScheduleTeamsCollection extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        $members = $this->map(function ($team, $key){
            return $team->schedule_teams_members_user;
        })->collapse()->unique('id');

        $items = $this->map(function($team, $key){
            return $team->schedule_teams_equipments;
        })->collapse()->unique('eq_id');

        return [
            'all'=>$this,
            'members'=>$members,
            'items' => $items,
            'members_id'=>$members->pluck('id'),
            'items_id'=>$items->pluck('eq_id')
        ];

    }
}