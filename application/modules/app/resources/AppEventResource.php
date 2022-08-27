<?php


namespace application\modules\app\resources;
use application\modules\estimates\models\EstimatesService;
use application\resources\data\EstimateServicesResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AppEventResource extends JsonResource
{
    private $current;
    public $response_fields = [
        'id', 'event_start', 'formatted_event_start', 'event_end', 'formatted_event_end', 'event_state', 'event_wo_id', 'event_team_id',
        'client_name', 'client_id', 'client_address', 'client_contact', 'cc_phone', 'cc_phone_clean', 'cc_name', 'cc_email',
        'firstname', 'lastname', 'team_leader_user_id', 'team_note', 'event_note', 'ev_start_time', 'files', 'workorder_no', 'invoice_id', 'wo_office_notes',
        'lead_address', 'lead_city', 'lead_state', 'lead_zip', 'latitude', 'longitude', 'estimate_item_note_crew', 'estimate_crew_notes', 'estimate_id',
        'estimate_services', 'event_report', 'event_works_reports', 'exists_unfinished'
    ];

    public function __construct($resource, $current) {
        // Ensure we call the parent constructor
        parent::__construct($resource);

        $this->resource = $resource;
        $this->current = $current;
    }

    public function toArray($request)
    {
        $crew_schedule_start = (config_item('crew_schedule_start') ?? 7).':00';
        $crew_schedule_end = (config_item('crew_schedule_end') ?? 20).':00';

        if(!$this->resource)
            return [];

        $item = $this->resource;

        $from = Carbon::createFromTimestamp($item->event_start);
        if($this->current) {
            $from = new Carbon($this->current);
        }

        $current = $from->addDays((int)$item->days_offset);

        if(strtotime($item->event_date) < $current->timestamp){
            $item->event_start = strtotime($current->format("Y-m-d").' '.$crew_schedule_start);
            $item->event_date_time = $current->format("Y-m-d").' '.$crew_schedule_start;
        }

        if(strtotime($item->event_end_date) > $current->timestamp){
            $item->event_end = strtotime($current->format("Y-m-d").' '.$crew_schedule_end);
            $item->event_end_date_time = $current->format("Y-m-d").' '.$crew_schedule_end;
        }

        $item->formatted_event_start = $item->event_date_time;
        $item->formatted_event_end = $item->event_end_date_time;

        $item->client_id = $item->workorder->estimate->lead->client->client_id;
        $item->client_name = $item->workorder->estimate->lead->client->client_name;
        $item->client_address = $item->workorder->estimate->lead->client->client_address;
        $item->client_contact = $item->workorder->estimate->lead->client->client_contact;

        $item->cc_phone = $item->workorder->estimate->lead->client->primary_contact->cc_phone??null;
        $item->cc_phone_clean = $item->workorder->estimate->lead->client->primary_contact->cc_phone_clean??null;
        $item->cc_name = $item->workorder->estimate->lead->client->primary_contact->cc_name??null;
        $item->cc_email = $item->workorder->estimate->lead->client->primary_contact->cc_email??null;

        $item->firstname = $item->workorder->estimate->user->firstname;
        $item->lastname = $item->workorder->estimate->user->lastname;

        $item->team_leader_user_id = $item->team->team_leader_user_id;
        $item->team_note = $item->team->team_note;

        $item->event_work = $item->event_works->where('ev_date', '=', $current->format("Y-m-d"))->first();
        $item->event_work_report = $item->event_works_reports->where('er_report_date_original', '=', $current->format("Y-m-d"))->first();

        //$item->members = $item->event_works->where('ev_date', '=', date("Y-m-d", $item->event_start))->safety_pdf_signs;

        $item->event_state = ($item->event_work && $item->event_work->ev_id && !$item->event_work->ev_start_work)?1:$item->event_state;
        $item->event_state = ($item->event_work && $item->event_work->ev_id && $item->event_work->ev_start_work)?2:$item->event_state;
        $item->event_state = ($item->event_work && $item->event_work->ev_id && $item->event_work->ev_start_work && $item->event_work_report && $item->event_work_report->er_id)?3:$item->event_state;
        $item->event_report = $item->event_work;

        $item->ev_start_time = $item->event_work->ev_start_time??null;

        $item->workorder_no = $item->workorder->workorder_no;
        $item->invoice_id = ($item->workorder && $item->workorder->estimate->invoice)?$item->workorder->estimate->invoice->id:null;
        $item->wo_office_notes = $item->workorder->wo_office_notes;
        $item->files = $item->workorder->files_array;

        $item->lead_address = $item->workorder->estimate->lead->lead_address;
        $item->lead_city = $item->workorder->estimate->lead->lead_city;
        $item->lead_state = $item->workorder->estimate->lead->lead_state;
        $item->lead_zip = $item->workorder->estimate->lead->lead_zip;

        $item->latitude = $item->workorder->estimate->lead->latitude;
        $item->longitude = $item->workorder->estimate->lead->longitude;
        $item->estimate_item_note_crew = $item->workorder->estimate->estimate_item_note_crew;
        $item->estimate_crew_notes = $item->workorder->estimate->estimate_crew_notes;
        $item->estimate_id = $item->workorder->estimate_id;

        $item->estimate_services = (new EstimateServicesResource($item->schedule_event_service))->toArray($request, 'app_event_services');

        $unfinishedServices = $item->workorder->estimate->estimates_service()
            ->doesnthave('bundle_service')
            ->whereNotIn('service_status', EstimatesService::FINISHED_SERVICE_STATUSES)
            ->count();
        $item->exists_unfinished = $unfinishedServices ? true : false;

        if(!empty($item->files) && !empty($item->workorder->estimate->estimates_service)){
            $dir = 'uploads/clients_files/' . $item->workorder->client_id . '/estimates/' . $item->workorder->estimate->estimate_no . '/';
            $servicesInJob = json_decode($item->estimate_services, true);
            $servicesInJobWithBundleServices = [];
            array_walk($servicesInJob, function ($item, $key) use (&$servicesInJobWithBundleServices) {
                if(isset($item['items']))
                    $servicesInJobWithBundleServices = array_merge($item['items'], $servicesInJobWithBundleServices);
                $servicesInJobWithBundleServices[] = $item;
            });
            $servicesInJob = array_column($servicesInJobWithBundleServices, 'id');
            $servicesWithBundleServices = array_filter(json_decode($item->workorder->estimate->estimates_service), function($v) use($servicesInJob){
                return in_array($v->id, $servicesInJob) === false;
            });
            foreach ($servicesWithBundleServices as $service){
                $servicePath = $dir . $service->id . '/';
                $item->files = array_values(array_filter($item->files, function($v) use($servicePath){
                    return strpos($v, $servicePath) === false;
                }));
            }
        }

        return $item->only($this->response_fields);
    }
}
