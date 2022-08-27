<?php


namespace application\modules\schedule\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ScheduleEventEquipmentCollection extends ResourceCollection{
    public static $wrap = null;

    private $key;
    public function __construct($collection, $key = false) {

        parent::__construct($collection);
        $this->key = $key;
    }

    public function toArray($request)
    {
        $equipment = $this->collection->filter()->map(function ($item){
            $result = [];
            if($item->equipment)
                $result[] = $item->equipment->vehicle_name . $item->equipment_item_option_string;
            if($item->attachment)
                $result[] = $item->attachment->vehicle_name . $item->equipment_attach_option_string;

            return $result;
        });

        $result = [
            'equipment' => $equipment,
            'equipment_string' => $equipment->flatten()->implode(', '),
        ];

        if($this->key && isset($result[$this->key]))
            return $result[$this->key];

        return $result;
    }
}