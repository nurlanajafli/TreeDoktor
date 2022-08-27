<?php


namespace application\resources\data;
use Illuminate\Http\Resources\Json\ResourceCollection;
use function foo\func;

class EstimateServicesResource extends ResourceCollection
{
    public $preserveKeys = false;

    public $response_vars = [
        'default' => ['data', 'status'],
        'app_event_services'=> ['id', 'service_description', 'service_time', 'service_travel_time', 'service_disposal_time', 'quantity', 'estimate_service_ti_title']
    ];

    public function toArray($request, $key = false)
    {
        if(!$key || !isset($this->response_vars[$key]))
            $key = 'default';

        $result = collect([
            'default'=>['data'=>$this->collection, 'status'=>true],
            'app_event_services' => []
        ]);

        $result->app_event_services = $this->collection->map(function($item){

            $item->service_name = $item->service->service_name;
            $item->is_bundle = $item->service->is_bundle;
            $item->is_product = $item->service->is_product;
            $item->items = [];

            if($item->is_bundle){
                $item->items = $item->bundle->map(function($item){
                    $item->service_name = $item->estimate_service->service->service_name;
                    $item->is_bundle = $item->estimate_service->service->is_bundle;
                    $item->is_product = $item->estimate_service->service->is_product;

                    $bundle_response = [
                        'id' => $item->estimate_service->id,
                        'service_description' => $item->estimate_service->service_description,
                        'service_time' => $item->estimate_service->service_time,
                        'service_travel_time' => $item->estimate_service->service_travel_time,
                        'service_disposal_time' => $item->estimate_service->service_travel_time,
                        'quantity' => $item->estimate_service->quantity,
                        'estimate_service_ti_title' => $item->estimate_service->estimate_service_ti_title,
                        'service_name' => $item->estimate_service->service->service_name,
                        'is_bundle' => $item->estimate_service->service->is_bundle,
                        'is_product' => $item->estimate_service->service->is_product,
                        'service_status' => $item->estimate_service->service_status
                    ];

                    if(config_item('show_workorder_pdf_amounts')){
                        $bundle_response['service_price'] = $item->estimate_service->service_price;
                    }

                    return $bundle_response;
                });
            }

            if($item->tree_inventory){
                $item->tree_inventory->tree;

                $item->job_estimate_service_ti_title = $item->estimate_service_ti_title;
                $item->job_service_description = $item->service_description;
                if($item->tree_inventory && $item->estimate_service_ti_title)
                    $item->job_estimate_service_ti_title .=  ', Priority: ' . $item->tree_inventory->ties_priority;

                $workTypes = ($item->tree_inventory->tree_inventory_work_types)?$item->tree_inventory->tree_inventory_work_types->implode('work_type.ip_name', ', '):'';
                if(!empty($workTypes)){
                    $workTypesDescription = 'Work Types: ' . $workTypes . '<br>';
                    $item->job_service_description = $workTypesDescription . $item->service_description;
                }
            }

            $item->service_price = $item->service_price;

            $response = [
                'id',
                'service_description',
                'service_time',
                'service_travel_time',
                'service_disposal_time',
                'quantity',
                'estimate_service_ti_title',
                'service_name',
                'is_bundle',
                'is_product',
                'items',
                'job_estimate_service_ti_title',
                'job_service_description',
                'tree_inventory',
                'service_status'
            ];
            if(config_item('show_workorder_pdf_amounts')){
                $response[] = 'service_price';
            }
            return $item->only($response);
        });

        return $result->$key;
    }
}