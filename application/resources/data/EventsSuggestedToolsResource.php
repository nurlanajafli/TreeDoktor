<?php
namespace application\resources\data;
use Illuminate\Http\Resources\Json\ResourceCollection;
class EventsSuggestedToolsResource extends ResourceCollection
{
    public $preserveKeys = true;

    public function toArray($request, $key = false)
    {
        $all = $this->pluck('estimates_services_equipment')->collapse()->pluck('equipment_tools_option_array')->flatten()->unique();
        $result = [
            'list' => $all,
            'assoc' => $all->map(function ($item){ return ['name'=>$item]; })
        ];

        if($key && isset($result[$key]))
            return $result[$key];

        return $result;
    }
}