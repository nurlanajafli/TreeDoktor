<?php


namespace application\modules\app\resources;


use Illuminate\Http\Resources\Json\JsonResource;

class AppClientResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        return [
            'client_id' => $this->client_id,
            'client_name' => $this->client_name,
            'client_type' => $this->client_type,
            'client_address' => $this->client_address,
            'client_city' => $this->client_city,
            'client_state' => $this->client_state,
            'client_zip' => $this->client_zip,
            'primary_contact' => collect($this->primary_contact)->only(['cc_name', 'cc_phone', 'cc_phone_view', 'cc_email'])
        ];
    }

}