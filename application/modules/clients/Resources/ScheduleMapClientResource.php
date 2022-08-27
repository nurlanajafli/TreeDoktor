<?php


namespace application\modules\clients\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use application\modules\clients\Resources\ScheduleMapPrimaryContactResource;
class ScheduleMapClientResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'client_id' => $this->client_id,
            'client_name' => $this->client_name,
            'client_address' => $this->client_address,

            'primary_contact' => new ScheduleMapPrimaryContactResource($this->primary_contact)
        ];
    }

}