<?php


namespace application\modules\schedule\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use application\modules\schedule\Resources\ScheduleTeamResource;

class ScheduleEventResource extends JsonResource
{
    public static $wrap = null;
    public function toArray($request)
    {
        $total_service_time = $this->schedule_event_service->sum(function ($service){
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


        if($request->input('mode')=='month'){
            return [
                'id' => $this->id,
                'section_id' => ($request->input('mode')=='timeline')?($this->team->team_leader_user_id??0):($this->team->team_id??0),
                'client_name' => $this->workorder->client->client_name??'',
                'start_date' => date('Y-m-d H:i:s', $this->event_start),
                'end_date' => date('Y-m-d H:i:s', $this->event_end),
                'date' => date('Y-m-d', $this->event_start),
                'workorder_no' => $this->workorder->workorder_no??'',
                'wo_status_name' => $this->workorder->status->wo_status_name??0,
                'event_services'=> $this->schedule_event_service->map(function ($item){
                    return [
                        'service_price'=>$item->service_price,
                        'service' => ($item->service)?['service_name'=>$item->service->service_name]:null
                    ];
                }),
                'lead_state' => $this->workorder->estimate->lead->lead_state??'',
                'lead_city' => $this->workorder->estimate->lead->lead_city??'',
                'lead_address' => $this->workorder->estimate->lead->lead_address??'',
                'lead_country' => $this->workorder->estimate->lead->lead_country??'',
                'team_amount' =>  $this->team->team_amount_money_format??'',
                'event_price' => $this->event_price,
                'color' => $color,
                'total_for_services' => money($this->event_price, false),
                'total_service_time' => round($total_service_time, 2),
                'total_hours' => round($total_service_time / (count($this->team->members??[])?:1), 2),
            ];
        }

        $event_crew = $this->schedule_event_service->pluck('services_crew')->flatten()->pluck('crew')->implode('crew_name', ', ');
        $brand_id = get_brand_id($this->workorder->estimate??[], $this->workorder->client??[]);

        //var_dump(new ScheduleTeamResource($this->team, 'event'));
        //die;

        return [
            'id' => $this->id,
            'section_id' => ($request->input('mode')=='timeline')?($this->team->team_leader_user_id??0):($this->team->team_id??0),
            'crew_id' => $this->event_team_id,
            'event_crew_id' => $this->event_team_id,
            'team_leader_user_id' => $this->team->team_leader_user_id??0,

            'client_unsubscribe'=>$this->workorder->client->client_unsubscribe??'',
            'client_name' => $this->workorder->client->client_name??'',
            'client_id' => $this->workorder->client_id??'',
            'client'=>$this->workorder->client??[],

            'primary_contact'=>$this->workorder->client->primary_contact??[],
            'estimate'=>$this->workorder->estimate??[],

            'brand_name'=>brand_name($brand_id),
            'brand_email'=>brand_email($brand_id),
            'brand_phone'=>brand_phone($brand_id),
            'brand_address'=>brand_address($brand_id, config_item('office_address') . ', ' . config_item('office_city') . ', ' . config_item('office_zip')),
            'brand_name'=>brand_name($brand_id, true),
            'brand_site'=>brand_site($brand_id),

            'start_date' => date('Y-m-d H:i:s', $this->event_start),
            'end_date' => date('Y-m-d H:i:s', $this->event_end),
            'date' => date('Y-m-d', $this->event_start),

            'wo_id' => $this->workorder->id??0,
            'wo_no' => $this->workorder->workorder_no??0,
            'workorder_no' => $this->workorder->workorder_no??'',
            'wo_status' => $this->workorder->status->wo_status_id??0,
            'wo_status_name' => $this->workorder->status->wo_status_name??0,
            'estimate_id'=>$this->workorder->estimate_id??0,

            'estimator' => $this->workorder->estimate->user->emailid??'',
            'team_color' => $this->team->team_color??'',

            'services' => json_encode($this->schedule_event_service->pluck('id', 'id')??[]),
            'event_services'=> $this->schedule_event_service??[],
            'event_services_string'=>$this->schedule_event_service->pluck('service.service_name')->implode(', '),
            'count_members'=>(count($this->team->members??[])?:1),
            'address_tags' => str_replace(array(' ', '#'), array('+', ''), $this->workorder->estimate->lead->lead_address??''),

            'lead_state' => $this->workorder->estimate->lead->lead_state??'',
            'lead_city' => $this->workorder->estimate->lead->lead_city??'',
            'lead_address' => $this->workorder->estimate->lead->lead_address??'',
            'lead_country' => $this->workorder->estimate->lead->lead_country??'',

            'team_id' => $this->event_team_id,
            'team_amount' =>  $this->team->team_amount_money_format??'',
            'event_price' => $this->event_price,
            'event_damage' => $this->event_damage,

            'event_complain' => (float)$this->event_complain,
            'tags' => str_replace(array(' ', '#'), array('+', ''), $this->workorder->estimate->lead->lead_address??''),
            'state' => $this->workorder->estimate->lead->lead_state??'',
            'city' => $this->workorder->estimate->lead->lead_city??'',
            'address' => $this->workorder->estimate->lead->lead_address??'',
            'event_note' => $this->event_note,
            'color' => $color,

            'total_for_services' => money($this->event_price, false),
            'total_service_time' => round($total_service_time, 2),
            'total_hours' => round($total_service_time / (count($this->team->members??[])?:1), 2),
            'event_crew' => $event_crew,

            'event_equipment' => (new ScheduleEventEquipmentCollection($this->estimates_services_equipment, 'equipment_string'))->toArray($request),
            'emailid'=>$this->workorder->estimate->user->emailid??'',

            'team' => collect((new ScheduleTeamResource($this->team, 'event'))->toArray($request))
        ];
    }
}