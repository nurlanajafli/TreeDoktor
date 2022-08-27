<?php


namespace application\modules\app\resources;

use application\resources\data\EstimateServicesResource;
use Illuminate\Http\Resources\Json\JsonResource;
use application\modules\app\resources\AppClientResource;
use application\modules\schedule\Resources\ScheduleWorkorderResource;
use application\modules\schedule\Resources\ScheduleEventEquipmentCollection;

class AppScheduleEventResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        $total_services_time = $this->schedule_event_service->sum(function ($service){
            $count = count($service->services_crew);
            return ($service->service_time+$service->service_travel_time+$service->service_disposal_time)*$count;
        });

        $color = '#717171';
        if($this->workorder){
            /* Event Sticker - Workorder Status Color */
            $color = $this->workorder->status->wo_status_color;

            /* Event Sticker - Team Color */
            if($this->workorder->status->wo_status_use_team_color)
                $color = $this->team->team_color;

            /* Event Sticker - Estimator Color */
            if($this->workorder->status->wo_status_use_estimator_color)
                $color = $this->workorder->estimate->user?$this->workorder->estimate->user->color:'#ffffff';
        }

        return [
            "id" => $this->id,
            "event_team_id" => $this->event_team_id,
            "event_wo_id" => $this->event_wo_id,
            "event_start" => $this->event_start,
            "event_end" => $this->event_end,
            "multiday" => (date("Y-m-d", $this->event_start)!=date("Y-m-d", $this->event_end)),
            "event_note" => $this->event_note,
            "event_damage" => $this->event_damage,
            "event_price" => $this->event_price,
            "event_expenses" => $this->event_expenses,
            "event_state" => $this->event_state,
            "event_date" => $this->event_date,
            "event_date_time" => $this->event_date_time,

            "event_end_date" => $this->event_end_date,
            "event_end_date_time" => $this->event_end_date_time,

            "workorder"=>($this->workorder)?(new ScheduleWorkorderResource($this->workorder))->toArray($request):null,
            "client"=>($this->workorder)?(new AppClientResource($this->workorder->client))->toArray($request):null,
            'event_equipment' => (new ScheduleEventEquipmentCollection($this->estimates_services_equipment, 'equipment_string'))->toArray($request),

            'event_crew' => $this->schedule_event_service->pluck('services_crew')->flatten()->pluck('crew')->implode('crew_name', ', '),
            'event_services'=>$this->schedule_event_service->pluck('id'),
            'color' => $color,
            'total_mhr' => $total_services_time,
            'total_mh' => round($total_services_time / (count($this->team->members??[])?:1), 2)
        ];
    }
}