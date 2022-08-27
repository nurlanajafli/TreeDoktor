<?php


namespace application\modules\schedule\Resources;
use application\modules\equipment\models\Equipment;
use Illuminate\Http\Resources\Json\JsonResource;
class ScheduleTeamItemsResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        $items = $this->members->concat($this->equipment->map(function($item){
            return $item->only(array_merge(Equipment::APP_EQUIPMENT, ['pivot']));
        }));

        $items = $items->sortBy('pivot.weight')->values();

        return $items->map(function($item){
            if(isset($item['eq_id']))
                return ['id'=>$item['eq_id'], 'name'=>$item['eq_name'], 'type'=>'equipment'];

            return ['id'=>$item['id'], 'name'=>$item['full_name'], 'type'=>'user'];
        });
    }

}