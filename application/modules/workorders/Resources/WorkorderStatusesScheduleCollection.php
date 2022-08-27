<?php
namespace application\modules\workorders\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WorkorderStatusesScheduleCollection extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        return $this->collection->map(function ($status, $key){
            return [
                'wo_status_id' => $status->wo_status_id,
                'wo_status_name' => $status->wo_status_name,
                'workorders_count'=>$status->workorders_count
            ];
        });
    }
}