<?php


namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleWorkorderResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'workorder_no'=>$this->workorder_no,
            'address'=>$this->estimate->lead->lead_address,
            'city'=>$this->estimate->lead->lead_city,
            'state'=>$this->estimate->lead->lead_state,
            'zip'=>$this->estimate->lead->lead_zip,
            'country'=>$this->estimate->lead->lead_country
        ];
    }
}