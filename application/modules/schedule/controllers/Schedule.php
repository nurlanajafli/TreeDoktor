<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
use application\modules\leads\models\Lead;
use application\modules\estimates\models\Estimate;
use application\modules\workorders\models\WorkorderStatus;
use application\modules\workorders\models\Workorder;
use application\modules\schedule\models\ScheduleEvent;
use application\modules\schedule\models\ScheduleUpdate;

use application\modules\estimates\models\EstimatesServicesStatus;
use application\modules\clients\models\StatusLog;
use application\modules\clients\models\Client;
use application\modules\clients\models\ClientNote;
use application\modules\clients\models\ClientLetter;

use application\modules\invoices\models\Invoice;
use application\modules\user\models\User;
use application\modules\crew\models\Crew;
use application\modules\estimates\models\Service;

use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\ScheduleTeamsMember;
use application\modules\schedule\models\ScheduleAbsence;
use application\modules\schedule\models\ScheduleTeamsEquipment;
use application\modules\equipment\models\Equipment;

use application\modules\schedule\requests\ScheduleTeamRequest;
use application\modules\schedule\requests\ScheduleSaveEventRequest;
use application\modules\schedule\requests\ScheduleSaveEventTeamRequest;
use application\modules\schedule\requests\ScheduleWeekCrewsRequest;

use Illuminate\Validation\ValidationException;
use application\modules\employees\models\EmployeeWorked;

use application\modules\schedule\Resources\ScheduleTeamsCollection;
use application\modules\schedule\Resources\ScheduleEventCollection;

use application\modules\schedule\Resources\ScheduleAbsenceResource;
use application\modules\schedule\Resources\ScheduleEventResource;
use application\modules\schedule\Resources\ScheduleWeekCollection;
use application\modules\events\models\EventsReport;

use application\modules\schedule\Resources\ScheduleTeamItemsResource;
use application\modules\workorders\Resources\WorkorderStatusesScheduleCollection;
use application\modules\workorders\Resources\WorkordersByStatusesCollection;

use Carbon\CarbonPeriod;

class Schedule extends MX_Controller
{

	function __construct()
	{

		parent::__construct();

		if (!isUserLoggedIn() && $this->router->fetch_method() != 'ajax_get_traking_position' && $this->router->fetch_method() != 'workorder_overview') {
			redirect('login');
		}

		$this->_title = SITE_NAME;
		$this->load->model('mdl_schedule', 'mdl_schedule');
		$this->load->model('mdl_object', 'mdl_object');
		$this->load->model('mdl_crews', 'mdl_crews');
		$this->load->model('mdl_estimates', 'mdl_estimates');
		$this->load->model('mdl_user');
		$this->load->model('mdl_safety_pdf_signs');

		$this->load->model('mdl_est_equipment', 'mdl_est_equipment');
		$this->load->model('mdl_estimates_orm', 'mdl_estimates_orm');
		$this->load->model('mdl_services_orm', 'mdl_services_orm');
		$this->load->model('mdl_crews_orm', 'mdl_crews_orm');
		$this->load->model('mdl_equipment_orm', 'mdl_equipment_orm');
		$this->load->model('mdl_vehicles');

		$this->load->model('mdl_calls');
		$this->load->model('mdl_sms_messages');
	}

	public function index()
	{
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_reasons');
		$this->load->model('mdl_bonuses_types');
        $this->load->model('mdl_sms');
		$this->load->model('mdl_user');
		$this->load->library('googlemaps');
		$data['title'] = "Schedule";
		$data['estimators'] = [];

		$data['crews'] = Crew::active()->noDayOff()->get();

		$data['reasons'] = $this->mdl_reasons->get_many_by(array('reason_status' => 1));

		$dayOffCrew = $this->mdl_crews->find_by_id(0);
		$data['dayOffCrew'] = $dayOffCrew ? $dayOffCrew : [];
		$data['sections'] = $this->mdl_schedule->get_teams(array('team_date' => strtotime(date('Y-m-d'))));

        $estimators = $this->mdl_user->get_payroll_user(['emp_field_estimator' => '1', 'active_status' => 'yes'], 'emailid ASC');
		if($estimators)
			$data['estimators'] = $estimators->result();
        $data['objects'] = json_encode($this->mdl_object->get_all());
		$data['bonuses'] = $this->mdl_bonuses_types->find_all(array(), 'bonus_type_amount DESC');

        $finishedStatusId = $this->mdl_workorders->getFinishedStatusId();
		$statuses = $this->mdl_workorders->get_all_statuses(array('wo_status_id !=' => $finishedStatusId, 'wo_status_active' => 1));

		$config['center'] = config_item('map_center');
		$config['zoom'] = '10';
		$this->googlemaps->initialize($config);

        $allWo = [];
        $allDates = [];
        $allStatusId = 'All';

        $statusAll = [
            'wo_status_id' => $allStatusId,
            'wo_status_name' => 'All',
            'is_default' => 0
        ];
        array_unshift($statuses, $statusAll);

        $data['tracks'] = \application\modules\equipment\models\Equipment::query()
            ->select(['eq_code', 'eq_name', 'eq_gps_id'])
            ->whereNotNull('eq_gps_id')
            ->where('eq_gps_id', '!=', '')
            ->get()
            ->toArray();

        $activeStatuses = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
        array_unshift($activeStatuses, $statusAll);
		$data['wostatuses'] = $activeStatuses;

		$data['bonuses_tpl'] = json_encode(array('tpl' => $this->load->view('bonuses_list', $data, TRUE)));

		$this->load->model('mdl_letter');
        $emailTpls[0] = ClientLetter::where('system_label', '=', 'tree_services_schedule')->first();
		if($emailTpls[0]) {
            $emailTpls[0]->sms = 4;
        }
        $emailTpls[1] = ClientLetter::where('system_label', '=', 'stump_grinding_schedule')->first();
        if($emailTpls[1]) {
            $emailTpls[1]->sms = 7;
        }
        $emailTpls[2] = ClientLetter::where('system_label', '=', 'tree_work_reschedule')->first();
        if($emailTpls[2]) {
            $emailTpls[2]->sms = 8;
        }
        $emailTpls[3] = ClientLetter::where('system_label', '=', 'firewood_delivery_for')->first();
        if($emailTpls[3]) {
            $emailTpls[3]->sms = 10;
        }
		$data['emailTpls'] = $emailTpls;

		$data['stump_grinder'] = $this->mdl_vehicles->get(4);

		$data['emails_tpl'] = json_encode(array('tpl' => $this->load->view('dropdown_emails_tpl', $data, TRUE)));
		$data['team_stat_tpl'] = json_encode(array('tpl' => $this->load->view('team_stat_tpl', $data, TRUE)));

		$data['sms'] = (object)['sms_id'=>null];
        $data['sticker_sms'] = $this->mdl_sms->get(9);
		/* удалить условие так что бы выводились все пользователи в дей офф */

		$data['workorder_statuses'] = WorkorderStatus::active()->orderBy('wo_status_priority')->get();

        $data['estimates_services_status'] = EstimatesServicesStatus::get()->toJson();
        $this->load->view("index", $data);
	}

	function ajax_copy_team() {

        $request = request();
        $errors = [];

	    $teamId = intval($request->input('crew_id'));
        $postDates = explode('|', $request->input('date'));

        if(!$request->input('date') || !$postDates || !is_array($postDates))
            return $this->response(['status' => 'error', 'message' => 'Incorrect Date!']);

        $team = ScheduleTeams::with(['events.schedule_event_service', 'equipment', 'tools', 'members'])->find($teamId);

        if(!$team)
            return $this->response(['status' => 'error', 'message' => 'Incorrect Input!']);

        $itemsId = $team->equipment->pluck('eq_id');
        $toolsId = $team->tools->pluck('eq_id');
        $membersId = $team->members->pluck('id');

        foreach ($postDates as $postDate) {

            $date = (new DateTime())->createFromFormat(getDateFormat(), $postDate)->format('Y-m-d');
            $absences = $members = $items = collect([]);

            if($membersId->count()) {
                $members = ScheduleTeamsMember::whereIn('user_id', $membersId)->datesInterval($date, $date)->get();
                $absences = ScheduleAbsence::whereIn('absence_user_id', $membersId)->whereDate('absence_ymd', '=', $date)->get();
            }

            if($itemsId->count())
                $items = ScheduleTeamsEquipment::whereIn('equipment_id', $itemsId)->datesInterval($date, $date)->get();

            if(!$absences->count() && !$members->count() && !$items->count()) {
                $teamCopy = $team->replicate();
                $teamCopy->team_date_start = $date;
                $teamCopy->team_date_end = $date;

                $teamCopy->save();
                $teamCopy->members()->sync($membersId);
                $teamCopy->equipment()->sync($itemsId);
                $teamCopy->tools()->sync($toolsId);

                foreach ($team->events as $event)
                {
                    $event_start = strtotime($date . ' ' . date('H:i:s', $event->event_start));
                    $event_end = strtotime($date . ' ' . date('H:i:s', $event->event_end));

                    $eventCopy = $event->replicate();

                    $data = ScheduleEvent::baseEventData($teamCopy->team_id, $event_start, $event_end);
                    $eventCopy->fill($data);
                    $eventCopy->save();

                    $newEvent = ScheduleEvent::with('schedule_event_service')->find($data['id']);
                    $servicesId = $event->schedule_event_service->pluck('id');
                    $newEvent->schedule_event_service()->sync($servicesId);
                }

            }

            if($members->count())
                $errors[] = $postDate . ': Some of the selected crew members are busy for the chosen date!';

            if($absences->count())
                $errors[] = $postDate . ': Some of the selected crew members are off for the chosen date!';

            if($items->count())
                $errors[] = $postDate . ': Some of the selected crew equipments are busy for the chosen date!';
        }

        return $this->response(['status' => 'ok', 'warnings' => $errors]);
    }

    function workordersByStatuses(){

        $request = request();

        $status_query = WorkorderStatus::baseFields()->withCount(["workorders" => function ($q) {
            $q->permissions();
        }])->active();

        if($request->input('wo_status_id')===NULL)
            $status_query->default();
        elseif($request->input('wo_status_id'))
            $status_query->whereIn('wo_status_id', $request->input('wo_status_id'));

        $status = $status_query->select('wo_status_id')->get()->pluck('wo_status_id')->toArray();

        if((int)$request->input('event_id'))
            $event = ScheduleEvent::find($request->input('event_id'));

        $query = Workorder::select(Workorder::FOR_SCHEDULE_FIELDS)->with([
            'estimate' => function($query) use($status, $request){
                $query->select(Estimate::LIGHT_FIELDS);
                $query->withoutAppends();
                $query->withTotals($status, []);
                $query->withCount(['estimates_service AS total_time' => function ($query) {
                    $query->secvicesCalc()->newService();
                }]);

                $query->with([
                    'estimates_service'=>function($query){
                        $query->scheduleFields();
                        $query->doesntHave('bundle_service');
                        $query->with([
                            'service'=>function($query){ return $query->baseFields(); },
                            'bundle.estimate_service.service'=>function($query){ return $query->baseFields(); },
                            'equipments',
                            'crew:crew_name',
                            'tree_inventory.tree_inventory_work_types.work_type',
                            'status'
                        ]);
                     },

                    'estimate_status',
                    'crews:crew_name',
                    'estimates_services_crew'=>function($query){ $query->newService()->crewsNamesLine(); },

                    'client'=>function($query){ $query->baseFields(); },
                    'lead'=>function($query){ $query->scheduleFields()->withoutAppends(); },
                    'user'=>function($query){ $query->baseWithoutField(['users.picture']); },
                    'client_payments'=>function($query){ $query->baseFields()->orderBy('payment_date', 'DESC'); },
                    'invoice'=>function($query){ return $query->scheduleFields(); }
                ]);

                if($request->input('filter_crew')){
                    $query->with('estimates_new_services_crews');
                }

                return $query;
	    }]);

        $query->filters($request, $status);
        $query->permissions();

        $services = [];
        if(!$request->input('count_services'))
            $services = Service::baseFields()->active()->orderBy('service_priority')->get();

        return $this->response([
            'workorders' => (new WorkordersByStatusesCollection($query->get()))->toArray($request),
            'active_status' => $status,
            'statuses' => (new WorkorderStatusesScheduleCollection(WorkorderStatus::withCount(["workorders" => function ($q) {
                $q->permissions();
            }])->active()->notFinished()->orderBy('wo_status_priority')->get()))->toArray($request),
            'search_keyword' => $request->input('search_keyword'),

            'event' => $event??false,
            'estimators'=> (!$request->input('count_estimators'))?User::active()->estimator()->orderBy('emailid', 'ASC')->get()->keyBy('id'):[],
            'crews' => (!$request->input('count_crews'))?Crew::active()->noDayOff()->get()->keyBy('crew_id'):[],
            'services' => $services,
            'services_assoc' => (!empty($services))?$services->keyBy('service_id'):[],
            //'equipment' => (!$request->input('count_equipment'))?(new Equipment())->getItems(['eq_schedule' => 1, 'eq_repair' => 0])->keyBy('eq_id'):[],

            'filter_estimator' => $request->input('filter_estimator'),
            //'filter_equipment' => $request->input('filter_equipment'),
            'filter_crew' => $request->input('filter_crew'),
            'filter_service' => $request->input('filter_service'),
            'filter_product' => $request->input('filter_product'),
            'filter_bundle' => $request->input('filter_bundle'),
            'filter_estimates_services_status' => $request->input('filter_estimates_services_status')
        ], 200);
        /*
        $memory2 = round(memory_get_usage()/1048576, 2);
        var_dump($memory, $memory2);
        die;
        */
    }

    function workorderProfile()
    {
        $request = request();
        $response['wo_id'] = $request->input('wo_id');
        $response['status_logs'] = StatusLog::with('workorder_status')->where(['status_item_id' => $request->input('wo_id'), 'status_type' => 'workorder'])->orderBy('status_date')->get();

        $path = 'clients_files/' . $request->input('client_id') . '/estimates/' . $request->input('estimate_no') . '/';
        $pictures = bucketScanDir('uploads/' . $path);

        $response['pictures'] = [];
        foreach($pictures as $key=>$file)
        {
            $response['pictures'][] = [
                'file'=>$file,
                'fileinfo' => pathinfo('uploads/' . $path . $file),
                'filepath' => 'uploads/'. $path . $file,
                'fileurl' => base_url('uploads/'. $path . $file),
                'estimate_id' => $request->input('estimate_id')
            ];
        }

        $response['client'] = Client::baseFields()->find($request->input('client_id'));

        return $this->response($response, 200);
    }

    function deleteCrewEvent(){
        $prefix = $this->input->post('ids');
        $id = $this->input->post($prefix . '_id');
        $status = $this->input->post($prefix . '_!nativeeditor_status');

        $event = ScheduleEvent::with(['team', 'schedule_event_service'])->find($id);
        if(!$event)
            return $this->scheduleResponse($status, $prefix, NULL, ['reload'=>1]);

        $team = $event->team;

        $event->schedule_event_service()->detach();
        $event->delete();

        $team->amountRecalculation();
        $team->optimizedRoute();

        $this->generate_followUp($event->id, FALSE);
        $uid = ScheduleUpdate::create(['update_time' => $event->event_start]);
        $this->scheduleResponse($status, $prefix, NULL, [
            'uid' => $uid->update_id,
            'team_amount' => money($team->team_amount),
            'team_id' => $team->team_id,
            'team' => $team->toJson()
        ]);
        return TRUE;
    }

    function saveCrewEvent(){

        try {
            $request = app(ScheduleSaveEventRequest::class);
        } catch (ValidationException $e) {
            return $this->scheduleResponse('error', request()->input('ids'), NULL, [
                'message' => collect($e->validator->errors()->messages())->flatten()->implode('\n')
            ]);
        }

        $data = $request->all();

        if($request->input('mode')=='timeline'){

            try {
                $teamRequest = app(ScheduleSaveEventTeamRequest::class);
            } catch (ValidationException $e) {
                return $this->scheduleResponse('error', $request->input('ids'), NULL, [
                    'message' => collect($e->validator->errors()->messages())->flatten()->implode('\n')
                ]);
            }

            if(!$request->input('event_team_id')){
                $team = $this->save_team($teamRequest->all());
            }
            else{
                $team = $teamRequest->input('team');
                $team->team_date_start = $teamRequest->input('team_date_start');
                $team->team_date_end = $teamRequest->input('team_date_end');
                $team->save();
            }

            $data['event_team_id'] = $team->team_id;
            $reload = 1;
        }

        unset($data['event_services']);

        $event = ScheduleEvent::find($request->input('id'));
        $event_before_change = ($event)?clone $event:NULL;
        if(!$event) {
            $event = new ScheduleEvent();
        } elseif(
            isset($data['event_start']) &&
            date('Y-m-d', $data['event_start']) !== date('Y-m-d', $event->event_start)
        ) { // if event date was changed - reset event state
            $data['event_state'] = 0;
        }

        $event->fill($data)->save();

        $changes = (count($event->getChanges()))?$event->getChanges():[];

        $db_services = $event->schedule_event_service->pluck('id');
        $post_services = collect($request->input('event_services'));

        $event = ScheduleEvent::find($request->input('id'));
        $event->schedule_event_service()->sync($request->input('event_services'));

        if(isset($changes['event_team_id']) && $changes['event_team_id'])
            $event_before_change->team->amountRecalculation();

        if($post_services->diff($db_services)->count() || $db_services->diff($post_services)->count()){
            $event->event_price = $event->schedule_event_service->sum('service_price');
            $event->save();
        }

        $event->team->amountRecalculation();
        $event->team->optimizedRoute();

        $this->generate_followUp($data['id'], TRUE);

        $eventResource = (new ScheduleEventResource($event))->toArray($request);

        $result = collect($eventResource)->merge([
            'old_team_amount' => $event_before_change->team->team_amount_money_format??NULL,
            'old_team_id' => $event_before_change->event_team_id??NULL,
            'reload' => $reload??0,
        ])->all();

        $result['uid'] = ScheduleUpdate::create(['update_time' => $event->event_start])->update_id;
        $this->scheduleResponse($request->input('!nativeeditor_status'), $request->input('ids'), NULL, $result);
        return TRUE;
    }

    function getCrewScheduleEvents(){

        $request = request();
        $to = date("Y-m-d", strtotime($request->input('to')."-1 day"));

        $eventsQuery = ScheduleEvent::datesInterval($request->input('from'), $to);
        if($request->input('user_id'))
            $eventsQuery->withMember($request->input('user_id'));
        if($request->input('team_crew_id'))
            $eventsQuery->teamCrew($request->input('team_crew_id'));

        $events = $eventsQuery->get();

        $this->response([
            'count'=>$events->count(),
            'data'=>(new ScheduleEventCollection($events))->toArray($request)
        ]);
    }

	function data()
	{
        if($this->input->post()){

            $prefix = $this->input->post('ids');
            if($this->input->post($prefix . '_!nativeeditor_status') == 'deleted')
                return $this->deleteCrewEvent();

            return $this->saveCrewEvent();
        }

        return $this->getCrewScheduleEvents();
	}

    function optimizeRoute(){
	    $request = request();

	    $eventsQuery = ScheduleEvent::whereIn('id', $request->input('event_id'))->get();
        $events = $eventsQuery->keyBy('id');
        $team = ScheduleTeams::find($request->input('event_team_id'));


        foreach ($request->input('event_id') as $key => $id) {
            $events[$id]->event_start = strtotime($request->input('event_start.'.$key));
            $events[$id]->event_end = strtotime($request->input('event_end.'.$key));
            $events[$id]->save();
	    }

        if($team){
            $team->team_route_optimized = 1;
            $team->team_route_hash = md5(collect($request->input('event_id'))->implode(','));
            $team->save();
        }

        return $this->response([
            "events" => (new ScheduleEventCollection(
                ScheduleEvent::whereIn('id', $request->input('event_id'))->get())
            )->toArray($request),
            'team' => $team
        ], 200);
    }

	private function scheduleResponse($action, $id, $new_id = NULL, $result = [])
	{
		$new_id = $new_id ? $new_id : $id;
		$this->session->set_userdata(array('id' => $id, 'action' => $action));
		$data['result'] = $result;
		$data['result']['type'] = $action;
		$data['result']['sid'] = $id;
		$data['result']['tid'] = $new_id;
		$this->load->view_xml('crew_schedule_response', $data);
	}

	function ajax_workorders_search()
	{
		$this->load->model('mdl_workorders');
		$search_keyword = $this->input->post('search_keyword');
		if(!$search_keyword)
			die(json_encode(array('status' => 'error')));
		$workorders = $this->mdl_workorders->get_workorders($search_keyword, '', '', '');
        $data['workorders'] = ($workorders && $workorders->num_rows()) ? $workorders->result_array() : array();
		if(!$data["workorders"] || empty($data["workorders"]))
			die(json_encode(array('status' => 'error')));
		$result['status'] = 'ok';
		$result['html'] = $this->load->view('wo_popup_label', $data, TRUE);
		return $this->response($result);
	}

	function ajax_workorder_details()
	{
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_services_orm');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_clients');
		$this->load->helper('estimates');

		$wo_id = $this->input->post('wo_id');

		$data['workorder_data'] = $this->mdl_workorders->wo_find_by_id($wo_id);
		if(!$data['workorder_data']) {
		    return $this->response([
		        'status' => 'error',
                'html' => 'Workorder not found'
            ]);
        }

		$data['wo_statuses'] = $this->mdl_services_orm->get_service_status();
		$estimate_id = $data['workorder_data']->estimate_id;
		$client_id = $data['workorder_data']->client_id;

        $data['client_data'] = $this->mdl_clients->find_by_id($client_id);
		$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id))[0];
		//$data['estimate_data'] = $this->mdl_estimates->find_by_id($estimate_id);
		$data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);
		$data['estimate_crews_data'] = $this->mdl_estimates->find_estimate_crews($estimate_id);
		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));
		$data['schedule'] = TRUE;

		$data['types'] = array('all', 'info', 'attachment', 'system', 'email'); //notes types
        if(config_item('phone'))
            $data['types'][] = 'calls';
        if(config_item('messenger'))
            $data['types'][] = 'sms';
		$limit = $this->config->item('per_page_notes');
		foreach ($data['types'] as $type)
		{
			$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $data['estimate_data']->client_id)); //Get client contacts
			if($type == 'calls')
			{
				$calls = [];
				$wh = '';

                foreach($data['client_contacts'] as $k=>$v)
				{
					if(isset($v['cc_phone']) && $v['cc_phone'])
						$wh .= '(call_to LIKE "%' . $v['cc_phone'] . '%" OR call_from LIKE "%' . $v['cc_phone'] . '%") OR ';

                }
				$wh = rtrim($wh, ' OR ');
				if($wh != '')
					$calls = $this->mdl_calls->get_calls($wh, $limit);

                //echo '<pre>'; var_dump($this->db->last_query()); die;
				$data['client_notes'][$type . '_count'] = $limit;
				$data['client_notes'][$type] = $calls;
				$data['client_notes'][$type . '_more'] = count($data['client_notes'][$type]) < $limit ? 0 : 1;
			}
			elseif($type == 'sms')
			{
				$data['client_notes'][$type . '_count'] = 0;
				$data['client_notes'][$type] = [];
				$data['client_notes'][$type . '_more'] = 0;
				foreach($data['client_contacts'] as $k=>$v)
				{
					$sms = $this->mdl_sms_messages->get_messages(array('sms_number' => $v['cc_phone']), $limit);
				//echo '<pre>'; var_dump($this->db->last_query()); die;
					$data['client_notes'][$type . '_count'] = $limit;
					$data['client_notes'][$type] += $sms;
					$data['client_notes'][$type . '_more'] = count($data['client_notes'][$type]) < $limit ? 0 : 1;
				}
			}
			elseif($type == 'all_client_notes')
			{
				$data['client_notes'][$type . '_count'] = $limit;
				$data['client_notes'][$type] = $this->mdl_clients->get_notes($data['estimate_data']->client_id, 'all', array(), $limit); //Get client notes
				$data['client_notes'][$type . '_more'] = count($data['client_notes'][$type]) < $limit ? 0 : 1;
			}
			else
			{
				$data['client_notes'][$type . '_count'] = $limit;
				$data['client_notes'][$type] = $this->mdl_clients->get_notes($data['estimate_data']->client_id, $type, array('(lead_id = '  . $data['estimate_data']->lead_id . ' OR lead_id IS NULL)' => NULL), $limit); //Get client notes
				$data['client_notes'][$type . '_more'] = count($data['client_notes'][$type]) < $limit ? 0 : 1;
			}
		}
			//foreach ($data['types'] as $type)
				//$data['client_notes'][$type] = $this->mdl_clients->get_notes($data['estimate_data']->client_id, $type); //Get client notes
		$this->load->model('mdl_vehicles');
		$data["tools"] = $this->mdl_vehicles->get_many_by(array('vehicle_trailer' => 2));

        $data['statuses'] = $this->mdl_workorders->get_wo_status_log(array('status_item_id' => $wo_id, 'status_type' => 'workorder'));
		$result['status'] = 'ok';

		$result['html'] = NULL;
		if($this->input->post('id')) {
			$data['event'] = $this->mdl_schedule->get_events(array('schedule.id' => $this->input->post('id')), 1);
			$result['html'] = $this->load->view('wo_event_note', $data, TRUE);
		}
		//$result['html'] .= $this->load->view('workorders/profile_workorder_requirements', $data, TRUE);
		//$result['html'] .= $this->load->view('workorders/schedule_profile_workorder_details', $data, TRUE);
		$result['html'] .= $this->load->view('estimates/estimate_data_display', $data, TRUE);
		$result['html'] .= $this->load->view('schedule/wo_statuses_log', $data, TRUE);
		$result['html'] .= str_replace('"', "'", $this->load->view('clients/client_information_notes', $data, TRUE));
		die(json_encode($result));
	}

	//function saveTeam(){
    function ajax_new_team(){
        $response = [];
        try {
            $request = app(ScheduleTeamRequest::class);
        } catch (ValidationException $e) {
            return $this->errorResponse(400, $e->validator->errors());
        }

        $teamId = $this->save_team($request->all());
        ScheduleUpdate::create(['update_time' => time()]);

        $response['status'] = 'ok';
        return $this->response($response);
    }

    private function save_team($data){

        $data['team_man_hours'] = EmployeeWorked::whereIn('worked_user_id', $data['team_members'])
            ->whereDate('worked_date', '>=', $data['team_date_start'])
            ->whereDate('worked_date', '<=', $data['team_date_end'])->sum('worked_hours');

        $data['team_leader_user_id'] = (int)$data['team_leader_user_id']?:(int)collect($data['team_members'])->filter()->first();

        $Team = new ScheduleTeams();
        if(isset($data['team_id']) && $data['team_id'])
            $Team = ScheduleTeams::find($data['team_id']);

        $Team->fill($data);
        $Team->save();

        $team_members = collect($data['team_members'])->filter()->flip()->map(function ($item){
            return ['weight'=>$item+1];
        })->toArray();
        $team_items = collect($data['team_items'])->filter()->flip()->map(function ($item) use ($team_members){
            return ['weight'=>count($team_members)+$item+1];
        })->toArray();

        $Team->schedule_teams_members_user()->sync($team_members);
        $Team->schedule_teams_equipments()->sync($team_items);

        return $Team;
    }


	function ajax_save_note()
	{
		$date = strtotime($this->input->post('date'));
		$text = $this->input->post('text', TRUE);
		$this->mdl_schedule->update_date_note(array('note_date' => $date, 'note_text' => $text));
		$this->mdl_schedule->insert_update(array('update_time' => $date));
		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();
		die(json_encode(array('status' => 'ok', 'update' => $update)));
	}

	function ajax_save_team_note()
	{
		$team_id = intval($this->input->post('team_id'));
		$field = 'team_note';
		$note = $this->input->post('team_note', TRUE);

        if($this->input->post('hidden_team_note', TRUE) !== FALSE)
		{
			$field = 'team_hidden_note';
			$note = $this->input->post('hidden_team_note', TRUE);
		}

		$date = strtotime($this->input->post('team_date'));
		if(!$team_id)
		{
			$dayOffTeam = $this->mdl_schedule->get_teams(array('team_date' => $date, 'team_crew_id' => 0));
			if(!$dayOffTeam)
				$this->mdl_schedule->insert_team(array('team_date' => $date, 'team_crew_id' => 0, $field => $note, 'team_color' => ''));
			else
				$this->mdl_schedule->update_team(FALSE, array($field => $note), array('team_crew_id' => 0, 'team_date' => $date));
			$this->mdl_schedule->insert_update(array('update_time' => $date));
			$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
			if(!$update)
				$update['update_time'] = time();
			die(json_encode(array('status' => 'ok', 'update' => $update)));
		}
		if(!$note)
			$note = NULL;

		$this->mdl_schedule->update_team($team_id, array($field => $note));
		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		die(json_encode(array('status' => 'ok', 'update' => $update)));
	}

    function ajax_save_team_amount()
	{
		$team_id = intval($this->input->post('team_id'));
		$team_amount = str_replace(',', '.', preg_replace("/[^0-9.,-]/", '', $this->input->post('team_amount', TRUE)));

        $date = strtotime($this->input->post('team_date'));
		if(!$team_id)
		{
			$dayOffTeam = $this->mdl_schedule->get_teams(array('team_date' => $date, 'team_crew_id' => 0));
			if(!$dayOffTeam)
				$this->mdl_schedule->insert_team(array('team_date' => $date, 'team_crew_id' => 0, 'team_amount' => $team_amount, 'team_color' => ''));
			//else
				//$this->mdl_schedule->update_team(FALSE, array('team_amount' => $team_amount), array('team_crew_id' => 0, 'team_date' => $date));
			//echo $this->db->last_query();
			$this->mdl_schedule->insert_update(array('update_time' => $date));

			$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
			if(!$update)
				$update['update_time'] = time();

            die(json_encode(array('status' => 'ok', 'update' => $update)));
		}
		if($team_id)
		{
			$this->mdl_schedule->update_team($team_id, array('team_amount' => $team_amount));
			$this->mdl_schedule->insert_update(array('update_time' => $date));
		}

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

        die(json_encode(array('status' => 'ok', 'update' => $update)));
	}


	function ajax_team_change_leader()
	{
		//var_dump($_POST); die;
		$leader_id = intval($this->input->post('leader_id')) ? intval($this->input->post('leader_id')) : NULL;
		$team_id = intval($this->input->post('team_id'));
		$date = strtotime($this->input->post('date'));
		if(!$team_id)
			die(json_encode(array('status' => 'error')));
		$this->mdl_schedule->update_team($team_id, array('team_leader_user_id' => $leader_id));
		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		die(json_encode(array('status' => 'ok', 'update' => $update)));
	}

	function ajax_team_change_color()
	{
		$team_color = $this->input->post('team_color');
		$team_id = intval($this->input->post('team_id'));
		$date = strtotime($this->input->post('date'));
		if(!$team_id)
			die(json_encode(array('status' => 'error')));
		$this->mdl_schedule->update_team($team_id, array('team_color' => $team_color));
		$this->mdl_schedule->insert_update(array('update_time' => $date));
		die(json_encode(array('status' => 'ok')));
	}

	function scheduleTimelineCrews(){
        $response = [];
        $request = request();

	    $date_from = $request->input('from');
        $date_to = DateTime::createFromFormat('Y-m-d', $request->input('to'))->modify('-1 day')->format('Y-m-d');

        $teams = ScheduleTeams::with(['schedule_teams_members_user', 'team_leader', 'schedule_teams_equipments', 'crew'])
            ->datesInterval($date_from, $date_to)
            ->groupBy('team_id')
            ->orderBy('team_date_start')
            ->get();

        $response['team_leaders'] = $teams->filter(function ($item){
            return ($item->team_leader_user_id);
        })->sortBy('team_leader.firstname')->unique('timeline_id')->values();

        $response['teams'] = $teams->groupBy('timeline_id');

        $where_in = $teams->unique('timeline_id')->pluck('team_leader_user_id')->filter()->toArray();

        $where_in_teams = $teams->pluck('team_id')->toArray();

        $response['members'] = ScheduleTeamsMember::whereIn('employee_team_id', $where_in_teams)
            ->with('team')
            ->inTeamNoLeader()
            ->get()
            ->groupBy('user_id')
            ->map(function ($teams, $key){
                return $teams->map(function($team, $kteam){
                    return ['team_id'=>$team->team->team_id, 'team_leader_user_id'=>$team->team->team_leader_user_id, 'team_date_start'=>$team->team->team_date_start, 'team_date_end'=>$team->team->team_date_end];
                });
            });

        $response['absence'] = ScheduleAbsence::whereIn('absence_user_id', $where_in)
            ->whereDate('absence_ymd', '>=', $date_from)
            ->whereDate('absence_ymd', '<', $date_to)
            ->get()->groupBy('absence_user_id');

        $response['update'] = ScheduleUpdate::where('update_time', '>=', strtotime($request->input('from')))
            ->where('update_time', '<', strtotime($request->input('to'))+86400)->first();

        return $this->response($response, 200);
    }

    function scheduleWeekCrews()
    {
        try {
            $request = app(ScheduleWeekCrewsRequest::class);
        } catch (ValidationException $e) {
            return $this->response(['error'=>"Data is not valid"], 400);
        }

        $teamsQuery = ScheduleTeams::with(['members', 'team_leader', 'equipment', 'crew', 'events'=>function($query) use ($request){
            return $query->datesInterval($request->input('from'), $request->input('to'))
                ->with('schedule_event_service.services_crew')
                ->withCount(['expenses AS event_total_expenses'=>function($query){ return $query->sumAmount(); }]);
            }])
            ->datesInterval($request->input('from'), $request->input('to'))
            ->where('team_leader_user_id', '>', 0);

        if($request->input('user_id'))
            $teamsQuery->withMember($request->input('user_id'));

        if($request->input('team_crew_id'))
            $teamsQuery->crewType($request->input('team_crew_id'));

        $teamsQuery->orderBy('team_date_start');
        $teams = $teamsQuery->get();

        return $this->response([
            'data'=>(new ScheduleWeekCollection($teams))->toArray($request)
        ]);
    }

    function scheduleFreeMembers(){
	    $request = request();
        $response = ['team_date_start'=>$request->input('team_date_start'), 'team_date_end'=>$request->input('team_date_end')];
        $date_from = $request->input('team_date_start');
        $date_to = $request->input('team_date_end');

        $teams = ScheduleTeams::with(['schedule_teams_members_user', 'team_leader', 'schedule_teams_equipments'])
            ->datesInterval($date_from, $date_to)
            ->groupBy('team_id')
            ->get();

        $teamsData = (new ScheduleTeamsCollection($teams))->toArray($request);

        /* absence members for the date range */
        $absence = ScheduleAbsence::with('user')
            ->dateRange($request->input('team_date_start'), $request->input('team_date_end'))
            ->get();
        $absenceData = (new ScheduleAbsenceResource($absence))->toArray($request);

        /* free members for the date range */
        $response['members_not_in'] = $members_not_in = $teamsData['members_id']->concat($absenceData['users_id'])->transform(function ($item, $key) {
            return intval($item);
        });

        $response['free_members'] = User::active()->fieldWorker()->whereNotIn('id', $members_not_in)
            ->orderBy('firstname')->get()
            ->forSelect2('id', 'full_name');

        /* free items for the date range */
        $response['free_items'] = (new Equipment())
            ->getItems(['eq_schedule' => 1, 'eq_repair' => 0], $teamsData['items_id']->toArray())
            ->forSelect2('eq_id', 'eq_name');

        $response['crews'] = Crew::active()->noDayOff()->get();

        if($request->input('team_id')){
            $response['team'] = $team = ScheduleTeams::with( 'schedule_teams_members_user', 'team_leader', 'schedule_teams_equipments')->find($request->input('team_id'));
            $team_members = $team->schedule_teams_members_user->forSelect2('id', 'full_name');
            $team_items = $team_items = $team->schedule_teams_equipments->forSelect2('eq_id', 'eq_name');

            $team_members_id = $team->schedule_teams_members_user->pluck('id')->toArray();
            $team_items_id = $team->schedule_teams_equipments->pluck('eq_id')->toArray();

            $members_without_team = ScheduleTeamsMember::with(['team', 'user'])->whereHas('team', function ($query) use ($request, $date_from, $date_to){
                return $query->datesInterval($date_from, $date_to)
                    ->where('team_id', '<>', $request->input('team_id'));
            })->whereIn('user_id', $team_members_id)->get()->map(function ($item){
                return ['id'=>$item->user->id, 'text'=>$item->user->full_name];
            });

            $response['busy_items_in_other_teams'] = $items_without_team = ScheduleTeamsEquipment::with('team', 'equipment')->whereHas('team', function ($query) use ($request, $date_from, $date_to){
                return $query->datesInterval($date_from, $date_to)
                    ->where('team_id', '<>', $request->input('team_id'));
            })->whereIn('equipment_id', $team_items_id)->get();

            $response['busy_members_in_other_teams'] = $busy = $absenceData['users']->whereIn('id', $team_members_id)->concat($members_without_team)->unique('id');
            $response['busy_members_in_other_teams'] = $response['busy_members_in_other_teams']->values();


            $response['team_members'] = $team_members->whereNotIn('id', $busy->pluck('id'))->values()->toArray();
            $response['team_items'] = $team_items->whereNotIn('eq_id', $items_without_team->pluck('equipment_id'))->values()->toArray();

            $response['free_members'] = collect($response['free_members'])->whereNotIn('id', collect($response['team_members'])->pluck('id'))->values();
            $response['free_items'] = collect($response['free_items'])->whereNotIn('eq_id', collect($response['team_items'])->pluck('eq_id'))->values();
        }

        return $this->response($response);
    }

    function ajax_crews_members()
    {
        $date = strtotime($this->input->post('date'));
        $dateYmd = $this->input->post('date');

        $request = request();
        $date_from = $request->input('from');
        $date_to = DateTime::createFromFormat('Y-m-d', $request->input('to'))->modify('-1 day')->format('Y-m-d');

        $result['absences'] = $absences = ScheduleAbsence::with(['user.employee', 'reason'])->whereHas('user', function($query){
            $query->noSystem()->active();
        })->whereDate('absence_ymd', '=', $dateYmd)->get();

        $membersCollect = ScheduleTeamsMember::with('team', 'user.employee')
            ->whereHas('team', function ($query) use ($date_from, $date_to){
                $query->datesInterval($date_from, $date_to);
            })->get();

        $members = $membersCollect->sortBy('weight');
        $members = $members->concat($absences);

        $sorted_teams = ScheduleTeamsMember::with('team', 'user.employee')
            ->whereHas('team', function ($query) use ($date_from, $date_to){
                $query->datesInterval($date_from, $date_to);
            });

        $equipmentQuery = ScheduleTeamsEquipment::with(['team', 'equipment.group', 'driver'])
            ->whereHas('team', function ($query) use ($date_from, $date_to){
                $query->datesInterval($date_from, $date_to);
            });

        $result['sorted_teams'] = $sorted_teams->get();
        $equipment = $equipmentQuery->get();

        $result['sorted_teams'] = collect([$absences, $result['sorted_teams'], $equipment])
            ->collapse()->sortBy('weight')->values()->toArray();

        $result['free_members'] = User::with('employee')->noSystem()->active()->whereNotIn('id', $members->pluck("user.id")->toArray())->orderBy('firstname')->get();
        $result['free_items'] = (new Equipment())->getItems(['eq_schedule' => 1, 'eq_repair' => 0], $equipment->pluck('equipment.eq_id'))->map(function ($item){
            $item->group_id = intval($item->group_id);
            return $item;
        })->toArray();

        $result['free_tools'] = Equipment::with(['group'])->where(['eq_schedule_tool' => 1])->groupBy('eq_id')->orderBy('group_id')->orderBy('eq_id')->get();

        $sections = ScheduleTeams::with([
            'team_leader',
            'events'=>function($query) use ($date_from, $date_to){
                return $query->datesInterval($date_from, $date_to)
                    ->with('schedule_event_service.services_crew')
                    ->withCount(['expenses AS event_total_expenses'=>function($query){ return $query->sumAmount(); }]);
            },
            'bonuses',
            'crew',
            'schedule_teams_equipments.group',
            'schedule_teams_tools',
            'members.employee'
        ])->datesInterval($date_from, $date_to)->get();

        $result['items'] = $sections->pluck('schedule_teams_equipments')->collapse();
        $result['tools'] = $sections->pluck('schedule_teams_tools')->collapse();

        $result['sections'] = $sections->map(function ($item) use ($request){

            //$items = (new ScheduleTeamItemsResource($item))->toArray($request);

            if($item->team_man_hours < 0){
                $item->team_man_hours = 0;
                $item->update();
            }
            $item->team_estimated_hours = 0;
            $item->team_estimated_amount = 0;
            $item->team_damage = 0;
            //$item->items = $items;

            if(!$item->events->count())
                $item->team_amount = 0;

            if($item->events->count()){

                $item->team_damage = $item->events->sum('event_damage');
                $item->events = $item->events->map(function ($event){
                    $event->schedule_event_service = $event->schedule_event_service->map(function ($service){
                        $service->count_members = $service->services_crew->count();
                        $service->team_service_estimated_hours = $service->count_members*($service->service_time + $service->service_disposal_time + $service->service_travel_time);
                        return $service;
                    });

                    $event->team_estimated_hours = $event->schedule_event_service->sum('team_service_estimated_hours');
                    $event->team_estimated_amount = round($event->schedule_event_service->sum('service_price'), 2);
                    return $event;
                });

                $item->team_estimated_hours = round($item->events->sum('team_estimated_hours'), 1);
                $item->team_estimated_amount = round($item->events->sum('team_estimated_amount'), 2);
                $item->total_expenses = $item->events->sum('event_total_expenses');
            }

            return $item;
        });

        $result['teams'] = $result['sections']->keyBy('team_id');

        $result['bonuses'] = $sections->pluck('bonuses')->collapse()->toArray();
        $result['note'] = $this->mdl_schedule->get_note(['note_date' => $date]);

        $result['status'] = 'ok';
        $result['members'] = $members->pluck('user')->toArray();

        $update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
        if(!$update)
            $update['update_time'] = time();
        $result['update'] = $update;

        return $this->response($result);
    }

	function ajax_change_team_order()
	{
		$members = $this->input->post('members');
		$equipments = $this->input->post('equipments');
		$teamId = $this->input->post('team_id');
		$date = strtotime($this->input->post('date'));

		if($equipments)
			$this->mdl_schedule->updateEquipmentsOrder($teamId, $equipments);
		if($members)
			$this->mdl_schedule->updateMembersOrder($teamId, $members);

		$this->mdl_schedule->insert_update(array('update_time' => $date));
		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		$result['status'] = 'ok';
		$result['update'] = $update;
		die(json_encode($result));
	}

	function ajax_showed_sections()
	{
		$startDate = strtotime($this->input->post('start_date'));
		$endDate = strtotime($this->input->post('end_date'));
		$sections = $this->mdl_schedule->get_teams(array('team_date >=' => $startDate, 'team_date <' => $endDate));
		$note = $this->mdl_schedule->get_note(array('note_date' => strtotime(date('Y-m-d', $startDate + 1))));
		die(json_encode(array('status' => 'ok', 'sections' => $sections, 'note' => $note)));
	}

	function ajax_add_equipment()
	{
		$this->load->model('mdl_crews');
		$old_team_id = intval($this->input->post('old_crew_id'));
		$team_id = intval($this->input->post('crew_id'));
		$item_id = intval($this->input->post('item_id'));
		$date = strtotime(date('Y-m-d', strtotime($this->input->post('date'))));

        $busy = ScheduleTeamsEquipment::with(['team'])->whereHas('team', function ($query) use ($date){
            $query->datesInterval(date("Y-m-d", $date), date("Y-m-d", $date));
        })->where('equipment_id', '=', $item_id)->first();

        if(($old_team_id > 0 && !$busy) || ($busy && $busy->team->team_id != $old_team_id))
		{
			$result['status'] = 'error';
			$result['errMsg'] = 'Schedule of this day was changed, please reload your workspace';

            die(json_encode($result));
		}


        if($old_team_id > 0)
			$this->mdl_schedule->delete_team_item(array('equipment_id' => $item_id, 'equipment_team_id' => $old_team_id));
		if($team_id > 0)
			$this->mdl_schedule->insert_team_item(array('equipment_id' => $item_id, 'equipment_team_id' => $team_id));
		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		$result['status'] = 'ok';
		$result['update'] = $update;
		die(json_encode($result));
	}

	function ajax_delete_equipment()
	{
		$this->load->model('mdl_crews');
		$team_id = intval($this->input->post('crew_id'));
		$item_id = intval($this->input->post('item_id'));
		$date = strtotime($this->input->post('date'));

		$this->mdl_schedule->delete_team_item(array('equipment_id' => $item_id, 'equipment_team_id' => $team_id));
		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		return $this->response(['status' => 'ok', 'update'=>$update]);
	}

	function ajax_add_tool()
	{
		$team_id = intval($this->input->post('team_id'));
		$tool_id = intval($this->input->post('tool_id'));
		$date = strtotime($this->input->post('date'));

		$stt_id = $this->mdl_schedule->insert_team_tool([
			'stt_team_id' => $team_id,
			'stt_item_id' => $tool_id
		]);

		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		$result['status'] = 'ok';
		$result['stt_id'] = $stt_id;
		$result['update'] = $update;
		die(json_encode($result));
	}

	function ajax_delete_tool()
	{
		$stt_id = intval($this->input->post('stt_id'));
		$date = strtotime($this->input->post('date'));

		$stt_id = $this->mdl_schedule->delete_team_tool([
			'stt_id' => $stt_id
		]);

		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		$result['status'] = 'ok';
		$result['update'] = $update;
		die(json_encode($result));
	}

	function ajax_add_member()
	{
		$this->load->model('mdl_crews');
		$this->load->model('mdl_worked');
		$team_id = intval($this->input->post('crew_id'));
		$old_team_id = intval($this->input->post('old_crew_id'));
		$employee_id = intval($this->input->post('employee_id'));
		$date = strtotime(date('Y-m-d', strtotime($this->input->post('date'))));

        $busy = ScheduleTeamsMember::with(['team', 'user'])->whereHas('team', function ($query) use ($date){
            return $query->datesInterval(date("Y-m-d", $date), date("Y-m-d", $date));
        })->where('user_id', $employee_id)->first();

        if(($old_team_id>0 && !$busy) || ($busy && $busy->team->team_id != $old_team_id))
		{
			$result['status'] = 'error';
			$result['errMsg'] = 'Schedule of this day was changed, please reload your workspace';

            die(json_encode($result));
		}

        if($old_team_id > 0)
			$this->mdl_schedule->delete_team_member(array('user_id' => $employee_id, 'employee_team_id' => $old_team_id));

		if($team_id)
			$this->mdl_schedule->insert_team_member(array('user_id' => $employee_id, 'employee_team_id' => $team_id));


        $this->mdl_schedule->insert_update(array('update_time' => strtotime($date)));

        $workedDate = new DateTime($this->input->post('date'));

		$worked = $this->mdl_worked->get_by(array('worked_user_id' => $employee_id, 'worked_date' =>  $workedDate->format('Y-m-d')));

		$result['old_team'] = [];
		$result['new_team'] = [];

		if($worked)
		{
			if($old_team_id) {
				$totalMHRS = 0;
				$oldMembers = $this->mdl_schedule->get_team_members(['schedule_teams.team_id' => $old_team_id]);
				foreach ($oldMembers as $key => $value) {
					$totalMHRS += $value['worked_time'];
				}
				$this->mdl_schedule->update_team($old_team_id, array('team_man_hours' => $totalMHRS));
			}
			if($team_id) {
				$totalMHRS = 0;
				$newMembers = $this->mdl_schedule->get_team_members(['schedule_teams.team_id' => $team_id]);
				foreach ($newMembers as $key => $value) {
					$totalMHRS += $value['worked_time'];
				}
				$this->mdl_schedule->update_team($team_id, array('team_man_hours' => $totalMHRS));
			}
		}
		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();
		if($old_team_id)
			$result['old_team'] = $this->mdl_schedule->get_teams(['schedule_teams.team_id' => $old_team_id], 1);
		if($team_id)
			$result['new_team'] = $this->mdl_schedule->get_teams(['schedule_teams.team_id' => $team_id], 1);
		$result['status'] = 'ok';
		$result['update'] = $update;
		die(json_encode($result));
	}

	function ajax_delete_member()
	{
		$this->load->model('mdl_worked');
		$team_id = intval($this->input->post('team_id'));
		$employee_id = intval($this->input->post('user_id'));
		$date = strtotime($this->input->post('date'));
		$this->mdl_schedule->delete_team_member(array('user_id' => $employee_id, 'employee_team_id' => $team_id));
		$this->mdl_schedule->insert_update(array('update_time' => $date));

        $worked = $this->mdl_worked->get_by(array('worked_user_id' => $employee_id, 'worked_date' =>  $this->input->post('date') ));

		if($worked)
		{
			$team = $this->mdl_schedule->find_team_by(array('team_id' => $team_id))[0];
			$man_hours = $team->team_man_hours - ($worked->worked_hours - $worked->worked_lunch);
			$this->mdl_schedule->update_team($team_id, array('team_man_hours' => $man_hours));
		}

        $update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();


		return $this->response(['status' => 'ok', 'update' => $update]);
	}

	function ajax_add_member_absence()
	{
	    $office_schedule_start = $this->config->item('office_schedule_start');
        $office_schedule_end = $this->config->item('office_schedule_end');

		$this->load->model('mdl_client_tasks', 'mdl_client_tasks');
		$this->load->model('mdl_client_tasks');
		$this->load->library('googlemaps');

        $data['task_desc'] = 'Day off';
		$data['task_author_id'] = $this->session->userdata['user_id'];
		$data['task_category'] = -1;
		$data['task_no_map'] = 1;
		$data['task_date_created'] = date('Y-m-d');

        $data['task_date'] = date("Y-m-d", strtotime($this->input->post('date')));
		$data['task_start'] = $office_schedule_start . ':00:00';
		$data['task_end'] = $office_schedule_end . ':00:00';
		$data['task_assigned_user'] = $this->input->post('employee_id');
		$data['task_address'] = config_item('office_address');
		$data['task_city'] = config_item('office_city');
		$data['task_state'] = config_item('office_state');
		$data['task_zip'] = config_item('office_zip');

        $coords = get_lat_lon($data['task_address'], $data['task_city'], $data['task_state']);
		$data['task_latitude'] = $coords['lat'];
		$data['task_longitude'] = $coords['lon'];
		$task_id = $this->mdl_client_tasks->insert($data);


        $employee_id = (int)$this->input->post('employee_id');
		$reason_id = (int)$this->input->post('reason_id');
		$date = strtotime($this->input->post('date'));
		$dateYmd = $this->input->post('date');
		$this->mdl_schedule->insert_member_absence(array('absence_reason_id' => $reason_id, 'absence_user_id' => $employee_id, 'absence_date' => $date, 'absence_ymd' => $dateYmd));
		//$this->mdl_schedule->insert_member_absence(array('absence_reason_id' => $reason_id, 'absence_employee_id' => $employee_id, 'absence_date' => $date));
		$this->mdl_schedule->insert_update(array('update_time' => $date));

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));

        if(!$update)
			$update['update_time'] = time();

		return $this->response([
		    'status' => 'ok',
            'update'=>$update,
            'user'=>ScheduleAbsence::with(['user.employee', 'reason'])->whereDate('absence_ymd', '=', $dateYmd)->where('absence_user_id', '=', $employee_id)->first()
        ]);
	}

	function ajax_delete_member_absence()
	{
		$this->load->model('mdl_client_tasks', 'mdl_client_tasks');
		$employee_id = (int)$this->input->post('employee_id');
		$date = strtotime($this->input->post('date'));
		$dateYmd = $this->input->post('date');
		$this->mdl_schedule->delete_member_absence(array('absence_user_id' => $employee_id, 'absence_ymd' => $dateYmd));
		//$this->mdl_schedule->delete_member_absence(array('absence_employee_id' => $employee_id, 'absence_date' => $date));
		$this->mdl_schedule->insert_update(array('update_time' => $date));
		$task = $this->mdl_client_tasks->find_by_fields(['task_assigned_user'=>$employee_id, 'task_date'=>date("Y-m-d", $date), 'task_category'=>-1]);

        if(!empty($task))
			$this->mdl_client_tasks->delete($task->task_id);

		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();

		$result['status'] = 'ok';
		$result['update'] = $update;
		die(json_encode($result));
	}

	function ajax_get_traking_position()
	{
		set_time_limit(0);
        $response = [];
		//trackingLogin();
        $this->load->driver('gps');
        if(!$this->gps->enabled()){
            //show_error('GPS driver is disabled!');
            die(json_encode($response));
        }

		//$tracks = json_decode(sendPost('http://login.genuinetrackingsolutions.com/GTS/trackerlist', ['op' => 'currentlist']));
        $tracks = json_decode($this->gps->currentTracks());
		//867965024639474
		foreach ($tracks as $key => $value) {
			$team_members = 0;
            $team = $this->mdl_schedule->get_team_items(array(
                'equipment.eq_gps_id' => $value->trackerId,
                'schedule_teams.team_date' => strtotime($value->serverDate)
            ));
			if(!empty($team))
				$team_members = $this->mdl_schedule->get_team_members(array('employee_team_id' => $team[0]['equipment_team_id']));
			$name = $value->trackerName;

            if($team_members)
			{
				foreach($team_members as $k=>$v)
					$name .= '<br>' . $v['emp_name'];
			}

            $response[] = [
				'item_code' => substr($value->trackerName, 0, 6),
				'item_name' => $name,
				'latitude' => $value->latitude,
				'longitude' => $value->longitude,
			];
		}
		die(json_encode($response));
	}

	function ajax_add_bonus()
	{
		$this->load->model('mdl_bonuses_types');
		$result['status'] = 'error';
		$bonus = $this->mdl_bonuses_types->find_by_id($this->input->post('bonus_type_id'));
		$team = $this->mdl_schedule->get_teams(array('team_id' => $this->input->post('team_id')), 1);
		if($this->input->post('bonus_type_id'))
		{
			if($bonus && $team)
			{
				$data['bonus_team_id'] = $team->team_id;
				$data['bonus_type_id'] = $bonus->bonus_type_id;
				$data['bonus_amount'] = $bonus->bonus_type_amount;
				if($this->mdl_schedule->insert_team_bonus($data))
					$result['status'] = 'ok';
			}
		}
		elseif($this->input->post('bonus_amount') !== FALSE && $this->input->post('bonus_title') !== FALSE)
		{
			if($team)
			{
				$data['bonus_team_id'] = $team->team_id;
				$data['bonus_type_id'] = 0;
				$data['bonus_amount'] = $this->input->post('bonus_amount');
				$data['bonus_title'] = $this->input->post('bonus_title', TRUE);
				$insert_id = $this->mdl_schedule->insert_team_bonus($data);
				if($insert_id)
				{
					$result['bonus_id'] = $insert_id;
					$result['status'] = 'ok';
				}
			}
		}
		die(json_encode($result));
	}

	function ajax_delete_bonus()
	{
		$this->load->model('mdl_bonuses_types');
		$result['status'] = 'error';
		if($this->input->post('bonus_type_id'))
		{
			$bonus = $this->mdl_bonuses_types->find_by_id($this->input->post('bonus_type_id'));
			$team = $this->mdl_schedule->get_teams(array('team_id' => $this->input->post('team_id')), 1);
			if($bonus && $team)
			{
				$wdata['bonus_team_id'] = $team->team_id;
				$wdata['bonus_type_id'] = $bonus->bonus_type_id;
				if($this->mdl_schedule->delete_team_bonus($wdata))
					$result['status'] = 'ok';
			}
		}
		elseif($this->input->post('bonus_id'))
		{
			$wdata['bonus_id'] = $this->input->post('bonus_id');
			if($this->mdl_schedule->delete_team_bonus($wdata))
				$result['status'] = 'ok';
		}
		die(json_encode($result));
	}

	function ajax_confirm_report()
	{
        $report_id = request()->input('report_id');
        $report = EventsReport::find($report_id);
		if(empty($report))
		    return $this->response(['status'=>'error']);

        $er_report_date = (!$report->er_report_date)?$report->er_event_date:$report->er_report_date;
        $report->er_report_confirmed = 1;
        $report->er_report_date = $er_report_date;

        $report->save();

        return $this->response(['status'=>'ok']);
	}

    function map()
	{
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_crews');
		$this->load->model('mdl_user');
		$this->load->library('googlemaps');
		$data['title'] = "Schedule Map";
		$data['globe'] = TRUE;

        $finishedStatusId = $this->mdl_workorders->getFinishedStatusId();
		$statuses = $this->mdl_workorders->get_all_statuses(array('wo_status_id !=' => $finishedStatusId));
		$data['crews'] = $this->mdl_crews->find_all(array('crew_status' => 1, 'crew_id >' => 0));

        $data['estimators'] = [];
		$estimators = $this->mdl_user->get_payroll_user(['emp_field_estimator' => '1', 'active_status' => 'yes'], 'emailid ASC');
		if($estimators)
			$data['estimators'] = $estimators->result();

        $config['center'] = config_item('map_center');
		$config['zoom'] = '10';
		$this->googlemaps->initialize($config);
		//$this->googlemaps->loadAsynchronously = TRUE;
		foreach ($statuses as $key => $status) {
			$data['statuses'][$key]['name'] = $status['wo_status_name'];
			$data['statuses'][$key]['id'] = $status['wo_status_id'];
			$data['statuses'][$key]['count'] = $this->mdl_workorders->workorders_record_count('', $status['wo_status_id']);
			$workorders = $this->mdl_workorders->get_workorders('', $status['wo_status_id'], '', '');

			$data['workorders'][$key] = array();
			if ($workorders)
				$data['workorders'][$key] = $workorders->result_array();

			$dates = array();
			foreach ($data['workorders'][$key] as $jkey=>$workorder_data) {
				$status_date = $this->mdl_workorders->get_status_date($workorder_data['id']);
				$dates[] = $status_date ? $status_date['status_date'] : strtotime($data['workorders'][$key][$jkey]['date_created']);
				if($status_date)
					$data['workorders'][$key][$jkey]['change_date'] = $status_date['status_date'];
				else
					$data['workorders'][$key][$jkey]['change_date'] = strtotime($data['workorders'][$key][$jkey]['date_created']);
				$crews = $this->mdl_estimates->find_estimate_crews($workorder_data['estimate_id']);


				$crewsStr = NULL;
				$crewsTypes = [];
				$data['workorders'][$key][$jkey]['crew_id'] = 0;
				foreach($crews as $num => $crew) {
					$workerTypes = explode(',', $crew['crew_name']);
					foreach ($workerTypes as $k => $type) {
						$type = str_replace(' ', '-', strtolower($type));
                        $crewsTypes[$type] = isset($crewsTypes[$type]) ? $crewsTypes[$type] + 1 : 1;
					}
					$crewsStr .= $crew['crew_name'] . ',';
				}
				$crewsStr = rtrim($crewsStr, ',');
				$data['workorders'][$key][$jkey]['crews'] = $crewsStr;
				$data['workorders'][$key][$jkey]['crewsTypes'] = $crewsTypes;
				$mapAttrs = NULL;
				foreach ($crewsTypes as $kt => $kc) {
					$mapAttrs .= ' data-' . str_replace(' ', '-', strtolower($kt)) . '="' . $kc . '"';
				}


				$crewsStr = NULL;
				$data['workorders'][$key][$jkey]['crew_id'] = 0;
				foreach($crews as $num => $crew) {
					//if(!$num)
						//$data['workorders'][$key][$jkey]['crew_id'] = $crew['crew_id'];
					$crewsStr .= $crew['crew_name'] . ',';
				}
				$crewsStr = rtrim($crewsStr, ',');
				$data['workorders'][$key][$jkey]['crews'] = $crewsStr;
				$data['workorders'][$key][$jkey]['equipment'] = $this->mdl_estimates->find_estimate_equipments($workorder_data['estimate_id']);

				foreach ($data['workorders'][$key][$jkey]['equipment'] as $kt => $kq) {
					$mapAttrs .= ' data-equipment-' . $kq['equipment_item_id'];
				}

                $marker_style = base_url("/assets/img/colorpicker/blank.gif");
				$marker = array();
				$marker['position'] = $workorder_data['client_address'] . '+' . $workorder_data['client_city'] . '+' . $workorder_data['client_state'] . '+' . $workorder_data['client_country'];
				$marker['infowindow_content'] = '<div class="map-pin" data-map-crew-type="' . $crewsStr . '" data-map-wo-status="'. $status['wo_status_id'] .'" data-map-wo-price="' . intval($workorder_data['total']) . '" data-map-wo-id="' . $workorder_data['id'] . '" ' . $mapAttrs . '>' . $workorder_data['client_address'] . '</div>';
				$marker['icon'] = $marker_style;
				$this->googlemaps->add_marker($marker);
			}
			array_multisort($dates, SORT_ASC, $data['workorders'][$key]);
		}

        $data['wostatuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
		//$data['crews_types'] = $this->mdl_crews->find_all(array(), 'crew_weight');
		$data['map1'] = $this->googlemaps->create_map();
//		$trackingItems = $this->mdl_equipments->get_items(array('item_tracker_name <>' => 'NULL', 'item_tracker_name <>' => ''));
		$data['objects'] = json_encode($this->mdl_object->get_all());
//		$data['tracks'] = array();

//		foreach($trackingItems as $item)
//			$data['tracks'][] = array('item_code' => $item->item_code, 'item_name' => $item->item_name, 'item_tracker_name' => $item->item_tracker_name);
        $data['tracks'] = \application\modules\equipment\models\Equipment::query()
            ->select(['eq_code', 'eq_name', 'eq_gps_id'])
            ->whereNotNull('eq_gps_id')
            ->where('eq_gps_id', '!=', '')
            ->get()
            ->toArray();
		$data['stump_grinder'] = $this->mdl_vehicles->get(4);
		$this->load->view('workorders_popup_tpl', $data);
	}

	function ajax_check_any_updates()
	{
		$date = strtotime($this->input->post('date'));
		$lastUpdate = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		die(json_encode(array($lastUpdate)));
	}

    function ajax_change_event_damage()
	{
		$id = $this->input->post('id');
		$event_damage = $this->input->post('event_damage');
		$event = $this->mdl_schedule->find_by_id($id);


        $this->mdl_schedule->update($id, ['event_damage' => $event_damage]);
		$team = $this->mdl_schedule->get_teams(['team_id' => $event->event_team_id], 1);
		$dmg = $this->mdl_schedule->sum_demage_complain(['team_id' => $event->event_team_id], 1);
		$mhrs = 0;

        if($team->team_man_hours)
			$mhrs = round(($team->team_amount - $dmg['event_damage']) / $team->team_man_hours, 2);

        die(json_encode([
			'status' => 'ok',
			'team_id' => $event->event_team_id,
			'team_amount' => money($mhrs),
		]));
	}
	function ajax_change_event_complain()
	{
		$id = $this->input->post('id');
		$event_complain = $this->input->post('event_complain');


        $this->mdl_schedule->update($id, ['event_complain' => $event_complain]);

        die(json_encode([
			'status' => 'ok'
		]));
	}


    function work($id) //1443615154639
	{
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_tracking');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_crews', 'mdl_crews');
		$this->load->model('mdl_employee');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_user', 'mdl_user');
		$this->load->helper('estimates_helper');

		$this->load->library('googlemaps');

        if (!isset($id)) { // NB: Set to redirect to index if variable is null or not set;
			redirect('schedule/', 'refresh');
		} else {
			//Set title:
			$data['title'] = $this->_title . ' - Work Services';
			$data['menu_workorders'] = "active";
			$data['schedule_event'] = $this->mdl_schedule->get_events(array('schedule.id' => $id))[0];

            $workorder_id = $data['schedule_event']['event_wo_id'];
			//Get workorder data
			$data['workorder_data'] = $this->mdl_workorders->wo_find_by_id($workorder_id);
			//echo '<pre>'; var_dump($event[0]); die;
			if (!$data['workorder_data'])
				show_404();

			$estimate_id = $data['workorder_data']->estimate_id;
			$data['statuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
			//var_dump($data['statuses']); die;
			//Get invoice data and interest if workorder is finished
			if (!$data['workorder_data']->wo_status) {
				$data['invoice_data'] = $this->mdl_invoices->getEstimatedData($estimate_id);
				$data['invoice_interest_data'] = $this->mdl_invoices->getInterestData($data['invoice_data']->id);
			}

			//Get estimate informations - using common function from MY_Models;
			$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id))[0];

            $data['service_ids'] = (array)json_decode($data['schedule_event']['event_services']);

            if (!$data['estimate_data'])
				show_404();
			//estimate services
			$data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);
			//estimate crews
			$data['estimate_crews_data'] = $this->mdl_estimates->find_estimate_crews($estimate_id);
			//echo '<pre>'; var_dump($data['estimate_services_data']); die;
			$data['wo_statuses'] = $this->mdl_services_orm->get_service_status();

			$data['lead_data'] = $this->mdl_leads->find_by_id($data['estimate_data']->lead_id);

			//employees
			$users = $this->mdl_user->get_usermeta(array('emp_status' => 'current', 'emp_feild_worker' => 1));
			$data['employees'] = $users ? $users->result() : [];


            $crews = $this->mdl_crews->get_crews();

			//events
			$data['events'] = $this->mdl_schedule->get_events(array('schedule.event_wo_id' => $workorder_id));

			$range = 0.4; //Radius in kilometers; ~60-100 meters diameter. ///0.019 CHANGED BY GLEBA RUSLAN
			$lat_range = $range / 69.172;
			$lon_range = abs($range / (cos($data['lead_data']->latitude) * 69.172));
			$data['min_lat'] = number_format($data['lead_data']->latitude - $lat_range, "6", ".", "");
			$data['max_lat'] = number_format($data['lead_data']->latitude + $lat_range, "6", ".", "");
			$data['min_lon'] = number_format($data['lead_data']->longitude - $lon_range, "6", ".", "");
			$data['max_lon'] = number_format($data['lead_data']->longitude + $lon_range, "6", ".", "");

			$data['tracking'] = array();
			foreach($data['events'] as $event) {
				$data['members'][$event['id']] = $this->mdl_schedule->get_team_members(array('employee_team_id' => $event['team_id']));
				$data['items'][$event['id']] = $this->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));
				if(!empty($data['members'][$event['id']]))
				{
					foreach($data['members'][$event['id']] as $key=>$member) {
						$where['id'] = $member['employee_id'];
						$where['start_date'] =  date('Y-m-d 00:00:00', $member['team_date']);
						$where['end_date'] =  date('Y-m-d 23:59:59', $member['team_date']);
                        $data['members'][$event['id']][$key]['logouts'] = $this->mdl_employee->get_emp_login_data_biweekly($where);
						//var_dump($this->db->last_query()); die;
					}
				}
				foreach($data['items'][$event['id']] as $item)
				{
                    $wdata['tracking_device_name'] = $item['eq_gps_id'];
					$wdata['tracking_start_time >='] = date('Y-m-d 00:00:00', $event['event_start']);
					$wdata['tracking_start_time <='] = date('Y-m-d 23:59:59', $event['event_start']);
					$wdata['tracking_lat >='] = $data['min_lat'];
					$wdata['tracking_lon >='] = $data['min_lon'];
					$wdata['tracking_lat <='] = $data['max_lat'];
					$wdata['tracking_lon <='] = $data['max_lon'];
					$parkings = $this->mdl_tracking->get_tracking($wdata, $event);
					foreach($parkings as $parks)
						$data['tracking'][] = $parks;
				}
			}

			//Get client_id and retrive client's information:
			$client_id = $data['workorder_data']->client_id;
			$data['client_data'] = $this->mdl_clients->find_by_id($client_id);

			//Get client_id and retrive client's information:
			$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));

			//Discount data
			$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

			//Enable options:
			$data['workorder_options'] = TRUE;

			//users
			$data['active_users'] = $this->mdl_user->get_usermeta(array('active_status' => 'yes'))->result();

			//Check files:
			$this->load->helper('file');
			$path = FCPATH . 'uploads/payment_files/' . $client_id . '/' . $data['estimate_data']->estimate_no;
			$data['files'] = bucketScanDir($path);
			if (!$data['files'])
				$data['files'] = array();
			sort($data['files'], SORT_NATURAL);

            $data['types'] = array('all', 'info', 'attachment', 'system', 'email'); //notes types
            if(config_item('phone'))
                $data['types'][] = 'calls';
            if(config_item('messenger'))
                $data['types'][] = 'sms';

			$limit = $this->config->item('per_page_notes');

            foreach ($data['types'] as $type)
			{
				$data['client_notes'][$type . '_count'] = $limit;
				$data['client_notes'][$type] = $this->mdl_clients->get_notes($client_id, $type, array(), $limit); //Get client notes
				$data['client_notes'][$type . '_more'] = count($data['client_notes'][$type]) < $limit ? 0 : 1;
			}

            $id = $data['estimate_data']->user_id;
			$data['user_data'] = $this->mdl_user->find_by_id($id);


            $client_id = $data['estimate_data']->client_id;
			$name = $data['client_data']->client_name;

            $street = $data['estimate_data']->lead_address;

            $city = $data['estimate_data']->lead_city;
			$address = $street . "+" . $city;
				//Set the map:
			$config['center'] = config_item('map_center');
			$config['zoom'] = '10';

			$this->googlemaps->initialize($config);

            $marker = array();
			$marker['position'] = $address;
			$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000');
			$this->googlemaps->add_marker($marker);
			$data['address'] = $address;
			$data['map'] = $this->googlemaps->create_map();
			$data['unsortable'] = TRUE;//DISABLE SORTABLE
			//Load view
			$this->load->view('workorders/profile', $data);
		}
		// end else;
	}
	function work_pdf($id)
	{
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_tracking');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_crews', 'mdl_crews');
		$this->load->model('mdl_employee');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_user', 'mdl_user');

        $this->load->library('mpdf');

        if (!isset($id)) { // NB: Set to redirect to index if variable is null or not set;
			redirect('schedule/', 'refresh');
		} else {
			//Set title:
			$data['title'] = $this->_title . ' - Leads';

			$folders = $pdfs = array();

			$data['schedule_event'] = $this->mdl_schedule->get_events(array('schedule.id' => $id))[0];

            $workorder_id = $data['schedule_event']['event_wo_id'];

            //Get workorder informations - using common function from MY_Models;
			$data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

			//Get estimate informations - using common function from MY_Models;
			$estimate_id = $data['workorder_data']->estimate_id;
			//$data['estimate_data'] = $this->mdl_estimates->find_by_id($estimate_id);
			$data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id))[0];
			$service_ids = json_decode($data['schedule_event']['event_services']);
			//var_dump($service_ids); die;
			foreach($service_ids as $key=>$val)
				$services[] = (array) $this->mdl_estimates_orm->get_full_service_data($val);
			if(!empty($services))
				$data['estimate_data']->mdl_services_orm = $services;

			$estClPath = 'uploads/clients_files/' . $data['estimate_data']->client_id . '/estimates/' . $data['estimate_data']->estimate_no . '/';
			$pictures['files'] = bucketScanDir($estClPath);

			foreach($pictures['files'] as $file)
			{
				if(pathinfo($estClPath . $file)['extension'] != 'pdf') {
                    $data['estFiles'][] = $estClPath . $file;
                } else {
                    $pdfs[] = $file;
                }
            }
			foreach($services as $key => $service)
			{
				$estClPath = 'uploads/clients_files/' . $data['estimate_data']->client_id . '/estimates/' . $data['estimate_data']->estimate_no . '/' . $service['id'] . '/';
				$pictures['files'] = bucketScanDir($estClPath);
				if(!$pictures['files'])
					$pictures['files'] = array();
				foreach($pictures['files'] as $k => $file)
				{
					if(pathinfo($estClPath . $file)['extension'] != 'pdf') {
                        $data['estFiles'][] = $estClPath . $file;
                    } else {
                        $pdfs[] = $file;
                    }
				}
			}

			//estimate services
			//$data['estimate_services_data'] = $this->mdl_estimates->find_estimate_services($estimate_id);
			$data['estimate_services_data'] = $services;
			//echo '<pre>'; var_dump($data['estimate_services_data']); die;
			//Get user_id and retrive estimator information:
			$user_id = $data['estimate_data']->user_id;
			$users = $this->mdl_user->get_payroll_user(array('emp_status' => 'current', 'emp_feild_worker' => 1));
			$data['emp_data'] = $data['user_data'] = $users ? $users->row() : [];
			//$data['user_data'] = $this->mdl_user->find_by_id($user_id);
			//var_dump($data['user_data']); die;
			$this->load->model('mdl_employees');
			//$data['emp_data'] = $this->mdl_employees->find_by_fields(array('emp_username' => $data['user_data']->emailid));
			//Get client_id and retrive client's information:
			$id = $data['estimate_data']->client_id;
			$data['client_data'] = $this->mdl_clients->find_by_id($id);

			$this->load->model('mdl_schedule', 'mdl_schedule');
			$data['events'] = $this->mdl_schedule->get_events(array('schedule.event_wo_id' => $workorder_id));
			foreach($data['events'] as $event) {
				$data['members'][$event['id']] = $this->mdl_schedule->get_team_members(array('employee_team_id' => $event['team_id']));
				$data['items'][$event['id']] = $this->mdl_schedule->get_team_items(array('equipment_team_id' => $event['team_id']));

            }

            $html = $this->load->view('workorders/workorder_pdf', $data, TRUE);

			$this->mpdf->WriteHTML($html);
            foreach ($pdfs as $file) {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 5, 5);
            }
			$file = "workorder_" . $estimate_id . '.pdf';
			$this->mpdf->Output($file, 'I');

            foreach($folders as $key=>$folder)
			{
				$files = bucket_get_filenames($folder);
				foreach($files as $jkey=>$val)
					unlink($folder . $val);
				rmdir($folder);
			}
		}
		// end else;
	}

    function invoice_pdf($id)
	{
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_clients', 'mdl_clients');

        $data['schedule_event'] = $this->mdl_schedule->get_events(array('schedule.id' => $id))[0];

        $workorder_id = $data['schedule_event']['event_wo_id'];
		//Set title:
		$data['title'] = $this->_title . ' - Leads';

		//Get workorder informations - using common function from MY_Models;
		$data['workorder_data'] = $this->mdl_workorders->find_by_id($workorder_id);

		//Get estimate informations - using common function from MY_Models;
		$estimate_id = $data['workorder_data']->estimate_id;

        $data['estimate_data'] = $this->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimate_id' => $estimate_id))[0];
		$service_ids = json_decode($data['schedule_event']['event_services']);
        $services = [];
		if($service_ids) {
            foreach($service_ids as $key=>$val)
                $services[] = (array) $this->mdl_estimates_orm->get_full_service_data($val);
        }

		if(!empty($services))
			$data['estimate_data']->mdl_services_orm = $services;

        //estimate services
		$data['invoice_data'] = NULL;
		$data['estimate_services_data'] = $services;

		$data['payments_data'] = $this->mdl_clients->get_payments(array('client_payments.estimate_id' => $estimate_id));
		//Discount data
		$data['discount_data'] = $this->mdl_clients->get_discount(array('discounts.estimate_id' => $estimate_id));

        //Get client_id and retrive client's information:
		$id = $data['workorder_data']->client_id;
		$data['client_data'] = $this->mdl_clients->find_by_id($id);

		$data['file'] = "Invoice"  . " - " . str_replace('/', '_', $data['client_data']->client_address);

        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/invoice_pdf', 'includes', 'views/');
        if($result) {
            $data['html'] = $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'invoice_pdf', $data, TRUE);
        } else {
            $data['html'] = $this->load->view('includes/pdf_templates/invoice_pdf', $data, TRUE);
        }

        $this->load->library('mpdf');
		$this->mpdf->WriteHTML($data['html']);

        $this->mpdf->Output($data['file'], 'I');
    }

	/***********************OBJECTS**************************/

    function objects()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('ST_OBJ') != 1) {
			show_404();
		}
		$data['title'] = "Static Objects";

        $data['objects'] = $this->mdl_object->get_all();
		$this->load->view('index_objects', $data);
	}

    function ajax_save_object()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('ST_OBJ') != 1) {
			show_404();
		}


        $id = $this->input->post('id');
		$data['object_name'] = strip_tags($this->input->post('name', TRUE));
		$data['object_desc'] = strip_tags($this->input->post('desc', TRUE));
		$data['object_color'] = strip_tags($this->input->post('color', TRUE));
		$data['object_street'] = strip_tags($this->input->post('street', TRUE));
		$data['object_city'] = strip_tags($this->input->post('city', TRUE));

        //$geo =  simplexml_load_file('https://maps.google.com/maps/api/geocode/xml?address='.$data['object_city'] . ',' . $data['object_street']);
		$data['object_latitude'] = strip_tags($this->input->post('lat', TRUE));
		$data['object_longitude'] = strip_tags($this->input->post('lon', TRUE));

        if ($id != '') {
			$this->mdl_object->update_object($id, $data);
			die(json_encode(array('status' => 'ok')));
		}
        $this->mdl_object->insert_object($data);
		die(json_encode(array('status' => 'ok')));
	}
	function ajax_delete_object()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('ST_OBJ') != 1) {
			show_404();
		}

        $id = $this->input->post('id');
		if ($id != '')
		{
			$this->mdl_object->delete_object($id);
			die(json_encode(array('status' => 'ok')));
		}
		die(json_encode(array('status' => 'error')));
	}

    /***********************END OBJECTS**********************/

    function ajax_my_task()
	{
		$this->load->model('mdl_user');
		$id = $this->session->userdata('user_id');
		$data['user_task'] = NULL;
		if($this->input->post('my_task') == 'true')
			$data['user_task'] = 1;
		$this->mdl_user->update_user($data, array('id' => $id));

        die(json_encode(array('status' => 'ok', 'id' => $id)));
	}

	function ajax_close_team()
	{
        $checked = intval($this->input->post('check'));
		$data['team_id'] = $this->input->post('team_id');
		$data['total'] = floatval($this->input->post('amount'));

		$result['status'] = 'error';
		if($checked)
		{
			$events = $this->mdl_schedule->get_events(array('schedule_teams.team_id' => $data['team_id']));
			$members = $this->mdl_schedule->get_team_members(array('schedule_teams.team_id' => $data['team_id']));
			if(!empty($events))
			{
				$users = [];
				foreach($events as $key=>$val)
				{
					if($val['user_id'] && array_search($val['user_id'], $users) === FALSE) {
						$users[] = $val['user_id'];
						$data['user_id'] = $val['user_id'];
						$this->mdl_schedule->insert_estimator_stat($data);
					}
				}
				unset($data['user_id']);
			}
			if(!empty($members))
			{
				$users = [];
				foreach($members as $key=>$val)
				{
					if($val['employee_id'] && array_search($val['employee_id'], $users) === FALSE) {
						$users[] = $val['employee_id'];
						$data['emp_id'] = $val['employee_id'];
						$this->mdl_schedule->insert_crews_stat($data);
					}
				}
				unset($data['emp_id']);
			}
			$result['status'] = 'ok';
			$result['msg'] = 'inserted';
		}
		else
		{
			$this->mdl_schedule->delete_estimator_stat($data['team_id']);
			$this->mdl_schedule->delete_crews_stat($data['team_id']);
			$result['status'] = 'ok';
			$result['msg'] = 'deleted';
		}
		$this->mdl_schedule->update_team($data['team_id'], array('team_closed' => $checked));
		die(json_encode($result));
	}


    function getEventInfo() {
		$wdata['id'] = $this->input->post('id');

		$events = $this->mdl_schedule->get_events_dashboard($wdata);

        $this->load->model('mdl_workorders');
		$this->load->model('mdl_est_equipment');
		$this->load->model('mdl_crews_orm');
		$woStatuses = $this->mdl_services_orm->get_service_status();
		$stickerData['statuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
		foreach($events as $key => $event)
		{
			$event['event_services'] = array();
			$eventServices = $this->mdl_schedule->get_event_services(array('event_id' => $event['id']));

			if(!empty($eventServices))
			{
				$event['event_crew'] = NULL;
				$event['event_equipment'] = NULL;
				foreach($eventServices as $jkey=>$val) {
					$servequipment = $this->mdl_vehicles->get_service_equipment(['equipment_service_id' => $val['event_service_id']]);
					$crew = $this->mdl_crews_orm->get_service_crew_in_string(['crew_service_id' => $val['event_service_id']]);
					foreach ($servequipment as $jkey => $jvalue) {
						if($jvalue['item_name']) {
							$event['event_equipment'] .= $jvalue['item_name'];
                            $options = $jvalue['equipment_item_option'] ? implode(' OR ', json_decode($jvalue['equipment_item_option'])) : 'Any';
							$event['event_equipment'] .= ' (' . $options . '), ';
						}

						if($jvalue['attach_name']) {
							$event['event_equipment'] .= $jvalue['attach_name'];
                            $options = $jvalue['equipment_attach_option'] ? implode(' OR ', json_decode($jvalue['equipment_attach_option'])) : 'Any';
							$event['event_equipment'] .= ' (' . $options . '), ';
						}
					}

					if(isset($crew['crews_names']))
						$event['event_crew'] .= $crew['crews_names'] . ', ';
					$event['event_services'][$val['event_service_id']] = $val['event_service_id'];
				}
				$event['event_equipment'] = rtrim($event['event_equipment'], ', ');
				$event['event_crew'] = rtrim($event['event_crew'], ', ');
				$event['event_services'] = json_encode($event['event_services']);
			}

			$service_ids = $event['event_services'] ? json_decode($event['event_services']) : array();
			$event['total_for_services'] = 0;
			$event['total_service_time'] = 0;

			/*
			*/
			foreach($service_ids as $jkey=>$val)
			{
				//$event['total_for_services'] += $this->mdl_estimates_orm->get_full_service_data($val)->service_price;
				$serv = $this->mdl_estimates_orm->get_full_service_data($val);
				if(!$serv)
					continue;
				$event['total_for_services'] += $serv->service_price;
				if($serv->service_time)
				{

					$event['total_service_time'] += $serv->service_time * count($serv->crew);
				}
				if($serv->service_travel_time)
				{

					$event['total_service_time'] += $serv->service_travel_time * count($serv->crew);
				}
				if($serv->service_disposal_time)
				{

					$event['total_service_time'] += $serv->service_disposal_time * count($serv->crew);
				}
			}
			//var_dump($event); die;
			//event_services
			$data[$key]['id'] = $event['id'];
			$data[$key]['section_id'] = $event['event_team_id'];
			$data[$key]['crew_id'] = $event['event_team_id'];
			$data[$key]['client_unsubscribe'] = $event['client_unsubscribe'];
			$data[$key]['start_date'] = date('Y-m-d H:i:s', $event['event_start']);
			$data[$key]['end_date'] = date('Y-m-d H:i:s', $event['event_end']);
			$data[$key]['date'] = date('Y-m-d', $event['event_start']);
			$data[$key]['wo_id'] = $event['event_wo_id'];
			$data[$key]['wo_status'] = $event['wo_status'];
			$data[$key]['estimator'] = $event['emailid'];//$event['firstname'] . ' ' . $event['lastname'];
			$data[$key]['team_color'] = $event['team_color'];
			$data[$key]['services'] = $event['event_services'];

            $data[$key]['emailid'] = $event['emailid'];
			// $data[$key]['count_events'] = $event['count_events']; delete if all ok
			$data[$key]['color'] = $event['wo_status_color'] ? $event['wo_status_color'] : $event['team_color'];
			$stickerData['event'] = $event;
			$stickerData['wo_statuses'] = $woStatuses;
			$stickerData['estimate_total'] = $this->mdl_estimates->get_total_for_estimate($event['estimate_id'])['sum'];

            $details = $this->load->view('event_sticker_tpl', $stickerData, TRUE);
			$data[$key]['text'] = $data[$key]['details'] = $details;
		}

		die($details);
	}

	function ajax_change_event_price() {
		$id = $this->input->post('id');
		$event_price = floatval($this->input->post('event_price'));
		$event = $this->mdl_schedule->find_by_id($id);

		$this->mdl_schedule->update($id, ['event_price' => $event_price]);
		$amount = $this->mdl_schedule->get_events_amount(['event_team_id' => $event->event_team_id]);
		$this->mdl_schedule->update_team($event->event_team_id, array('team_amount' => $amount['event_price']));

		$woAmount = $this->mdl_schedule->get_events_amount(['event_wo_id' => $event->event_wo_id]);

		die(json_encode([
			'status' => 'ok',
			/*'total_services_price' => $event_price,*/
			'event_price' => $event_price,
            'total_for_services' => money($event_price),
			'team_amount' => money($amount['event_price']),
            'wo_amount' => money($woAmount['event_price']),
		]));
	}

	function ajax_change_team_man_hours() {
		$teamId = $this->input->post('team_id');
		$team_man_hours = floatval($this->input->post('team_man_hours'));

        $this->mdl_schedule->update_team($teamId, ['team_man_hours' => $team_man_hours]);

        die(json_encode([
			'status' => 'ok',
		]));
	}

	function ajax_change_driver() {
		$driverId = $this->input->post('driver_id') ? $this->input->post('driver_id') : NULL;
		$itemId = $this->input->post('item_id');
		$teamId = $this->input->post('team_id');
		$userId = $this->input->post('user_id');
		if(!$userId)
			$this->mdl_schedule->update_team_item(['equipment_driver_id' => $driverId], [
				'equipment_id' => $itemId,
				'equipment_team_id' => $teamId
			]);
		else
			$this->mdl_schedule->update_team_item(['equipment_driver_id' => $driverId], [
				'equipment_driver_id' => $userId,
				'equipment_team_id' => $teamId
			]);

		die(json_encode([
			'status' => 'ok',
		]));
	}

    function workorder_overview($team_id)
	{

        $this->load->library('mpdf');

        if (!$team_id)
			return redirect('schedule');

        $origin = $destination = config_item('office_location');
        //Set title:
        $data['title'] = $this->_title . ' - Workorders overview';

        $data['team'] = ScheduleTeams::with([
            'schedule_teams_members_user',
            'schedule_teams_equipments',
            'schedule_equipments.driver',
            'events'=>function($query){
                return $query->with(['workorder.estimate'=>function($query){
                    $query->with(['lead', 'estimates_service.equipments']);
                }]);
            }
            //'schedule_teams_tools'
        ])->find($team_id);
        $data['drivers'] = $data['team']->schedule_equipments->pluck('driver', 'equipment_id');

        $waypoints = $data['team']->events->pluck('workorder.estimate.lead.waypoint')->toArray();
        $jobs = $data['team']->events->pluck('workorder.estimate.user.emailid');
        //$data['tools'] = $data['team']->schedule_teams_tools->implode('eq_name', ', ');

        $data['tools'] = $data['team']->events->pluck('workorder.estimate.estimates_service')
            ->collapse()->pluck('equipments')
            ->collapse()->pluck('equipment_tools_option_array')
            ->flatten()->unique()->implode(', ');


        $googleData = getStaticGmapURLForDirection($origin, $destination, $waypoints, '739x500', $jobs);

        $data['map_url'] = $googleData['link'];
        $html = $this->load->view('workorder_overview', $data, TRUE);

        $this->mpdf->WriteHTML($html);

        if(count($this->mpdf->pages) % 2 && $data['team']->events->count()) {
            $this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 0, 0, 0);
            $this->mpdf->SetHtmlFooter('');
        }

        foreach($data['team']->events as $k=>$v)
        {
            $woPDF = Modules::run('workorders/workorders/_workorder_html_generate', $v->event_wo_id, $v->id);
            $this->mpdf->_setPageSize('Letter', $this->mpdf->DefOrientation);
            $this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 0, 0, 0);
            $this->mpdf->SetHtmlFooter('');
            $this->mpdf->WriteHTML($woPDF['html']);
            foreach ($woPDF['pdf_files'] as $file) {
                $this->mpdf->AddPage();
                $this->mpdf->Thumbnail(bucket_get_stream($file), 1, 10, 5, 5);
            }
            if(count($this->mpdf->pages) % 2 && $k + 1 != $data['team']->events->count()) {
                $this->mpdf->AddPage('P', 'Letter', 0, '', 0, 0, 0, 0, 0, 0);
                $this->mpdf->SetHtmlFooter('');
            }

        }

        $file = "workorder_overview.pdf";

        $this->mpdf->Output($file, 'I');
		// end else;

	}

	public function current_jobs()
	{
		//echo '<pre>'; var_dump($_POST); die;
		$data['title'] = 'Curent Jobs';
		$this->load->library('googlemaps');
		$where = array();
		$estimator_id = $this->input->post('est_id', TRUE, NULL);
		$team_id = $this->input->post('team_id', TRUE, NULL);

        if($estimator_id)
			$data['current_est'] = $where['estimates.user_id'] = $estimator_id;
		if($team_id)
			$data['current_team'] = $where['team_id'] = $team_id;

        $where['schedule.event_start >='] = strtotime(date('Y-m-d 00:00:00'));
		$where['schedule.event_end <'] = strtotime(date('Y-m-d 23:59:59'));

        $events = $this->mdl_schedule->get_events($where, FALSE, 'schedule.event_start');
		//echo '<pre>'; var_dump($this->db->last_query()); die;
		$data['items'] = array();
		$data['estimators'] = $this->mdl_schedule->getEstimatorsForTeams(date('Y-m-d'), $team_id);

        $data['teams'] = $this->mdl_schedule->getTeamsForEstimator(date('Y-m-d'), $estimator_id);

        /*foreach($data['teams'] as $k=>$v)
        {
            $items = $this->mdl_schedule->get_team_items(array('team_date >' => $where['schedule.event_start >='] - 4000,  'team_date <' => $where['schedule.event_end <'], 'equipment_team_id' => $v['team_id']));
            $data['items'] = array_merge_recursive($data['items'], $items);
        }*/

        $config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

        $symbols = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q");
		$keyForTeam = [];
		foreach ($events as $key => $row) {
			$data['items'][$row['team_id']] = $this->mdl_schedule->get_team_items(array('team_date >' => $where['schedule.event_start >='] - 4000,  'team_date <' => $where['schedule.event_end <'], 'equipment_team_id' => $row['team_id']));
			$events[$key]['event_number'] = $this->mdl_schedule->getCountEvents(['event_start <' => $row['event_start'], 'event_team_id' => $row['event_team_id']]) + 1;
			// = array_merge_recursive($data['items'], $items);
			if(!isset($keyForTeam[$row['team_id']]))
				$keyForTeam[$row['team_id']] = 0;

			$street = $row['lead_address'];
			$state = $row['lead_state'];
			$city = $row['lead_city'];
			$country = $row['lead_country'];
			$address = ($row['latitude'] && $row['longitude']) ? $row['latitude'] . ', ' . $row['longitude'] : $street . "," . $city . ',' . $state . ',' . $country;
			$color = $row['team_color'] ? $row['team_color'] : '#ffffff';

            //$marker_style = 'https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=' . $events[$key]['event_number'] . '|' . str_replace('#', '', $color);
			$marker_style = mappin_svg($color, $events[$key]['event_number'], FALSE, '#000');

            $marker = array();
			$marker['position'] = $address;//$row['latitude'] . ',' . $row['longitude'];
			$marker['icon'] = $marker_style;
			$pinData['address'] = $row['lead_address'];
			$pinData['start_time'] = $row['event_start'];
			$pinData['end_time'] = $row['event_end'];
			$pinData['wo_id'] = $row['wo_id'];
			$pinData['workorder_no'] = $row['workorder_no'];
			$pinData['estimator'] = $row['firstname'] . ' ' . $row['lastname'];
			$pinData['items'] = $this->mdl_schedule->getTeamsMembersWithOrder(NULL, $row['team_id']);
			$marker['infowindow_content'] = trim(str_replace(["\n", "\r\n"], "", $this->load->view('current_jobs_marker_content', $pinData, TRUE)));
			//$marker['cursor'] = $row['stump_id'];


            $this->googlemaps->add_marker($marker);
			$keyForTeam[$row['team_id']]++;
		}

		//echo '<pre>'; var_dump($data); die;
		$data['map'] = $this->googlemaps->create_map();

		//$data['map'] = $this->googlemaps->create_map();


        $this->load->view('current_jobs', $data);
	}

    function ajax_get_currentJobs_track()
	{
		set_time_limit(0);
		//var_dump($_POST); die;
		//trackingLogin();
        $this->load->driver('gps');
        if(!$this->gps->enabled()){
            //show_error('GPS driver is disabled!');
            die('');
        }

		$date = date('Y-m-d');
		$tracks = $this->input->post('trucks');
		$response['parkings'] = [];
		$response['positions'] = [];
		if($tracks)
		{
			foreach($tracks as $k=>$track)
			{
				foreach($track as $key=>$val)
				{
                    if ($val['eq_gps_id'])
					{
//						$queryString =
//							'op=detailreport&' .
//							'reportType=parking&' .
//							'unitList=' . $val['item_tracker_name'] . '&' .
//							'sdate=' . date('d/m/Y%2000:00', strtotime($date)) . '&' .
//							'edate=' . date('d/m/Y%2023:59', strtotime($date)) . '&' .
//							'parkingMin=3';
//
//
//						$parkingsResponse = sendPost('http://login.genuinetrackingsolutions.com/GTS/report?' . $queryString, [
//							'start' => '0',
//							'length' => '50000',
//						]);
                        $parkingsResponse = $this->gps->parkings($val['eq_gps_id'], $date);
						$response['parkings'][] = json_decode($parkingsResponse);
					}

                }
			}
		}

//		$lastPositionsData = json_decode(sendPost('http://login.genuinetrackingsolutions.com/GTS/trackerlist', [
//			'op' => 'currentlist'
//		]));
        $lastPositionsData = json_decode($this->gps->currentTracks());

		foreach ($lastPositionsData as $key => $value) {
			$response['positions'][$value->trackerId] = [
				'lat' => $value->latitude,
				'lng' => $value->longitude,
				'date' => $value->gpsDateString
			];
		}

        die(json_encode($response));
	}

    function generate_followUp($eventId, $action)
	{
		$this->load->model('mdl_followup_settings');
		$this->load->model('mdl_followups');
		$this->load->model('mdl_user');
		$fsSettings = $this->mdl_followup_settings->get_many_by(['fs_disabled' => '0', 'fs_table' => 'schedule']);
		$this->mdl_followups->delete_by(['fu_item_id' => $eventId]);

        $fsConfig = $this->config->item('followup_modules')['schedule'];
		if($action && $fsSettings)
		{
			foreach ($fsSettings as $key => $value) {
				$statuses = json_decode($value->fs_statuses);
				$data = $this->mdl_schedule->get_followup(['schedule.id' => $eventId], $statuses);

                if(!empty($data))
				{
					$variables = $this->mdl_schedule->get_followup_variables($eventId);

                    //$existsNewFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'new', 'fu_client_id' => $item['client_id']]);
					//$existsPostponedFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'postponed', 'fu_client_id' => $item['client_id']]);

					$fuData = [];

                    $fuData = [
						'fu_fs_id' => $value->fs_id,
						'fu_date' => date('Y-m-d', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
						'fu_module_name' => $value->fs_table,
						'fu_action_name' => $fsConfig['action_name'],
						'fu_client_id' => $data[0]['client_id'],
						'fu_item_id' => $data[0][$fsConfig['id_field_name']],
						'fu_estimator_id' => $data[0]['estimator_id'],
						'fu_status' => 'new',
						'fu_time' => date('H:i:s', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
						'fu_variables' => json_encode($variables)
					];

                    //if(!$existsNewFu || !$existsPostponedFu) {
						$this->mdl_followups->insert($fuData);
					//}

                    $variables = $this->mdl_user->get_followup_variables($eventId);

                    //$existsNewFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'new', 'fu_client_id' => $item['client_id']]);
					//$existsPostponedFu = $this->mdl_followups->get_many_by(['fu_module_name' => $module, 'fu_status' => 'postponed', 'fu_client_id' => $item['client_id']]);

					$fuData = [];

                    $fuData = [
						'fu_fs_id' => $value->fs_id,
						'fu_date' => date('Y-m-d', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
						'fu_module_name' => 'users',
						'fu_action_name' => $fsConfig['action_name'],
						'fu_client_id' => $data[0]['client_id'],
						'fu_item_id' => $data[0][$fsConfig['id_field_name']],
						'fu_estimator_id' => $data[0]['estimator_id'],
						'fu_status' => 'new',
						'fu_time' => date('H:i:s', ($data[0]['this_status_date'] - $value->fs_time_periodicity*3600)),
						'fu_variables' => json_encode($variables)
					];

                    //if(!$existsNewFu || !$existsPostponedFu) {
						$this->mdl_followups->insert($fuData);
					//}
				}
			}
		}
	}


	function open_team()
	{
		$this->load->model('mdl_schedule');
		$data['title'] = 'Schedule Open Teams';

        $data['from'] = strtotime(date('Y-m-01'));
		$data['to'] = strtotime(date('Y-m-t'));

        if ($this->input->post('from'))
		{
            $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            $data['from'] = strtotime($from->format('Y-m-d') . " 00:00:00") ;
		}
		if ($this->input->post('to'))
		{
            $to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
            $data['to'] = strtotime($to->format('Y-m-d')  . " 23:59:59");
		}
		$data['teams'] = $this->mdl_schedule->get_event_members(array('schedule.event_start >=' => $data['from'], 'schedule.event_end <=' => $data['to'], 'schedule_teams.team_closed' => 0));
		//echo '<pre>'; var_dump($this->db->last_query()); die;
		$this->load->view('index_open_team', $data);

    }

    function tasks_list($user_id, $from, $to = NULL)
	{
		$this->load->library('mpdf');
        $this->load->helper('user_helper');
		$this->load->model('mdl_client_tasks');

        if(!$user_id || $user_id === 'false')
			redirect('schedule/office');
		if(!$to)
		    $to = $from;
		$wdata['task_assigned_user'] = ($user_id === 'true') ? $this->session->userdata('user_id') : $user_id;
		$wdata['task_date >='] = date('Y-m-d', strtotime($from . ' 00:00:00'));
		$wdata['task_date <='] = date('Y-m-d', strtotime($to . ' 23:59:59'));

		$data['date'] = ($from == $to) ? date('F d, Y', strtotime($from)) : date('F d', strtotime($from)) . ' - ' . date('F d, Y', strtotime($to));
		$data['events'] = $this->mdl_client_tasks->get_all($wdata, FALSE, 'task_start_date ASC');
        
		if(empty($data['events']))
			redirect('schedule/office');
		$origin = $destination = config_item('office_location');
		$jobs = [];
		foreach($data['events'] as $k=>$v)
		{
			if(!$k)
				$data['name'] = $v['ass_firstname'] . ' ' . $v['ass_lastname'];
			$waypoints[] = $v['latitude'] ? $v['latitude'].','. $v['longitude'] : urlencode($v['task_address']).','. urlencode($v['task_city']).','. urlencode($v['task_state']).','. urlencode($v['task_zip']);
			$jobs['names'][] = $v['emailid'];
		}

		$googleData = getStaticGmapURLForDirection($origin, $destination, $waypoints, '739x500', $jobs);
		$data['map_url'] = $googleData['link'];
		

        $html = $this->load->view('schedule/task_lists', $data, TRUE);

        $this->mpdf->WriteHTML($html);

        $file = "task_list.pdf";
		$this->mpdf->Output($file, 'I');
        return TRUE;
	}

    function price_difference()
	{
		$data['title'] = 'Schedule|Estimate Price Difference';

        $data['from'] = strtotime(date('Y-m-01'));
		$data['to'] = strtotime(date('Y-m-t'));

        if ($this->input->post('from'))
        {
//            $data['from'] = strtotime($this->input->post('from') . " 00:00:00");
            $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            $data['from'] = strtotime($from->format('Y-m-d') . " 00:00:00");
        }
		if ($this->input->post('to'))
        {
//            $data['to'] = strtotime( $this->input->post('to')  . " 23:59:59");
            $to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
            $data['to'] = strtotime($to->format('Y-m-d')  . " 23:59:59");
        }


        $events = $this->mdl_schedule->get_events(['schedule.event_start >=' => $data['from'], 'schedule.event_end <=' => $data['to']], FALSE, 'schedule.event_team_id ASC, schedule.event_start ASC');

        $data['prices'] = [];
		foreach($events as $k=>$v)
		{
			$event_price = $this->mdl_schedule->get_events_amount(['event_wo_id' => $v['wo_id']]);

            if(!isset($data['prices'][$v['estimate_id']]['total_event_price']))
			{
				$data['prices'][$v['estimate_id']]['total_event_price'] = 0;
				$data['prices'][$v['estimate_id']]['total_event_price'] += $event_price['event_price'];
			}
			if(!isset($data['prices'][$v['estimate_id']]['total_price']))
			{
				$data['prices'][$v['estimate_id']]['total_price'] = 0;
				$data['prices'][$v['estimate_id']]['workorder_no'] = $v['workorder_no'];
				$data['prices'][$v['estimate_id']]['estimate_no'] = $v['estimate_no'];
			}
			$data['prices'][$v['estimate_id']]['total_price'] = $this->mdl_estimates->get_total_for_estimate($v['estimate_id'])['sum'];
		}

        foreach($data['prices'] as $k=>$v)
		{
			$data['prices'][$k]['price_diff'] = $v['total_event_price'] - $v['total_price'];
			if($data['prices'][$k]['price_diff'] == 0)
				unset($data['prices'][$k]);

        }
		$this->load->view('index_price_difference', $data);
	}
}
