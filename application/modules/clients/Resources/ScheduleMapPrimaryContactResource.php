<?php
namespace application\modules\clients\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ScheduleMapPrimaryContactResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'cc_name' => $this->cc_name,
            'cc_phone'=> $this->cc_phone,
            'cc_email'=> $this->cc_email,
        ];
    }

}