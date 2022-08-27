<?php


namespace application\modules\app\resources;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\ResourceCollection;
use application\resources\data\EstimateServicesResource;

class AppAgendaResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public $preserveKeys = false;
    public $response = [
        'jobs'=>[
            'events'=>[],
            'waypoints'=>[]
        ],
        'destination'=>'',
        'origin'=>''
    ];
    /*
    public $response_fields = [
        'id', 'event_start', 'formatted_event_start', 'event_end', 'formatted_event_end', 'event_state',
        'client_name', 'client_address', 'client_contact', 'cc_phone', 'cc_phone_clean', 'cc_name', 'cc_email',
        'firstname', 'lastname', 'team_leader_user_id', 'team_note', 'event_note', 'ev_start_time', 'files', 'workorder_no',
        'lead_address', 'lead_city', 'lead_state', 'lead_zip', 'latitude', 'longitude', 'estimate_item_note_crew',
        'estimate_services', 'event_report'
    ];
    */
    public function toArray($request)
    {
        $date_from = $request->segment(4);
        $date_to = $request->segment(5);

        $date_from = $date_from ?? date("Y-m-d");
        $date_to = $date_to ?? $date_from;

        $from = new Carbon($date_from);
        $difference = $from->diff($date_to)->days;
        $events = $this->collection->pluck('events')->collapse()->map(function ($item, $key) {
            $item->event_start = (int)$item->event_start;
            $item->event_end = (int)$item->event_end;
            return $item;
        });

        $days_agenda = collect([]);

        for ($i = 0; $i <= $difference; $i++) {

            $from = new Carbon($date_from);
            $current = $from->addDays($i);
            $days_agenda[$i] = $events->filter(function ($item) use ($current) {
                return (strtotime($item->event_date) <= $current->timestamp && strtotime($item->event_end_date) >= $current->timestamp);
            });

            $days_agenda[$i] = $days_agenda[$i]->map(function ($item) use ($i){
                $result = clone $item;
                $result->days_offset = $i;
                return $result;
            });
        }

        $agenda = collect($days_agenda)->collapse();
        $this->response['jobs']['events'] = $agenda->map(function ($event, $key) use ($date_from, $request){
            return (new AppEventResource(clone $event, $date_from))->toArray(request());
        })->sortBy('event_start')->values()->all();


        $this->response['jobs']['waypoints'] = $this->collection->pluck('events')->collapse()->pluck('workorder.estimate.lead.waypoint');
        $this->response['origin'] = $this->response['destination'] = config_item('office_location');
        return [
            'data' => $this->response,
            'status' => true
        ];
    }


}