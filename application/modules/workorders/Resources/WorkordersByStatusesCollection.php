<?php


namespace application\modules\workorders\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;
use application\modules\estimates\Resources\ScheduleMapEstimateResource;

class WorkordersByStatusesCollection extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        return $this->collection->map(function ($workorder, $key){
            return [
                'id' => $workorder->id,
                'workorder_no' => $workorder->workorder_no,
                'wo_office_notes'=>$workorder->wo_office_notes,
                'date_created_view' => $workorder->date_created_view,
                'days_from_creation'=>$workorder->days_from_creation,

                'estimate'=>new ScheduleMapEstimateResource($workorder->estimate),
            ];
        })->sortByDesc('days_from_creation')->values();
    }
}