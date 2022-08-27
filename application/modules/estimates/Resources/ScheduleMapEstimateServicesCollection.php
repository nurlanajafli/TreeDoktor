<?php


namespace application\modules\estimates\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ScheduleMapEstimateServicesCollection extends ResourceCollection
{
    public $preserveKeys = true;
    public function toArray($request)
    {
        return $this->collection->map(function ($estimate_service, $key){
            return [
                'id' => $estimate_service->id,
                'service_description' => $estimate_service->service_description,
                'service_time' => $estimate_service->service_time,
                'service_travel_time' => $estimate_service->service_travel_time,
                'service_price' => $estimate_service->service_price,
                'service_status' => $estimate_service->service_status,
                'service_disposal_time' => $estimate_service->service_disposal_time,
                'quantity' => $estimate_service->quantity,
                'cost' => $estimate_service->cost,
                'non_taxable' => $estimate_service->non_taxable,
                'estimate_service_ti_title' => $estimate_service->estimate_service_ti_title,
                'services_crew_count' => $estimate_service->crew->count(),

                'service' => [
                    'service_name'=>$estimate_service->service->service_name,
                    'is_bundle'=>$estimate_service->service->is_bundle,
                    'is_product'=>$estimate_service->service->is_product,
                ],

                'bundle' => $estimate_service->bundle,

                'equipments'=> $estimate_service->equipments->map(function ($equipment){
                    return [
                        'equipment_item_option' => $equipment->equipment_item_option,
                        'equipment_attach_option' => $equipment->equipment_attach_option,
                        'equipment_attach_tool' => $equipment->equipment_attach_tool,
                        'equipment_tools_option' => $equipment->equipment_tools_option,
                        'equipment' => [
                            'vehicle_name'=>$equipment->equipment->vehicle_name??false,
                        ],
                        'attachment' => [
                            'vehicle_name'=>$equipment->attachment->vehicle_name??false,
                        ],
                    ];
                }),

                'crew'=>$estimate_service->crew->map(fn($item)=>$item->only('crew_name')),

                'tree_inventory'=>$estimate_service->tree_inventory,
                'status'=>$estimate_service->status,
            ];
        });
    }
}