<?php
namespace application\modules\estimates\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

use application\modules\clients\Resources\ScheduleMapClientResource;
use application\modules\estimates\Resources\ScheduleMapEstimateServicesCollection;
class ScheduleMapEstimateResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {

        return [
            'estimate_id' => $this->estimate_id,
            'estimate_crew_notes' => $this->estimate_crew_notes,
            'sum_without_tax' => $this->sum_without_tax,
            'sum_actual_without_tax'=> $this->sum_actual_without_tax,
            'total_time' => $this->total_time,

            'client' => new ScheduleMapClientResource($this->client),

            'client_payments'=> $this->client_payments->map(fn ($item) => ['payment_amount'=>$item->payment_amount]),

            'crews'=>$this->crews->map(fn($item)=>$item->only('crew_name')),

            //'estimates_services_crew_test'=>$this->crews->map(fn($item)=>$item->only('crew_name')),

            'estimates_services_crew'=>$this->estimates_services_crew,
            'estimate_status'=>$this->estimate_status,

            'estimates_service'=>new ScheduleMapEstimateServicesCollection($this->estimates_service),
            'lead'=>$this->lead,
            'user'=>$this->user,
            'invoice'=>$this->invoice

        ];
    }
}