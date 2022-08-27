<?php use application\modules\leads\models\Lead;

if (!defined('BASEPATH')) exit('No direct script access allowed.');

class Appleads extends APP_Controller
{

	function __construct() {
		parent::__construct();
		$this->load->model('mdl_leads');
		$this->load->model('mdl_clients');
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_reports');
		
		$this->load->library('Common/LeadsActions');
		$this->load->library('Common/ServicesActions');
	}
	
	function create_lead()
	{
		$data['client_id'] = strip_tags(json_decode($this->input->post('client_id')));
		$data['lead_body'] = nl2br($this->input->post('new_client_lead'));
		$data['lead_created_by'] = $this->user->firstname . " " . $this->user->lastname;
		$data['lead_author_id'] = $this->user->id;
		$data['lead_estimator'] = $this->user->id;
		$data['lead_date_created'] = date('Y-m-d H:i:s');


		$data['lead_reffered_client'] = NULL;
		$data['lead_reffered_user'] = NULL;
		
		if ($this->input->post('reffered') != '') {
			$reffered = $this->input->post('reffered');
			if($reffered == 'client')
			{
				if($this->input->post('lead_reff_id') != '')
				{
					$data['lead_reffered_client'] = $this->input->post('lead_reff_id');
					$data['lead_reffered_by'] = $reffered;
				}				
			}
			elseif($reffered == 'user')
			{
				
				if($this->input->post('lead_reff_id') != '')
				{
					$data['lead_reffered_user'] = $this->input->post('lead_reff_id');
					$data['lead_reffered_by'] = $reffered;
				}
			}
			elseif($reffered == 'other')
				$data['lead_reffered_by'] = $this->input->post('other_comment');
			else
				$data['lead_reffered_by'] = $reffered;
		}
        $client = $this->mdl_clients->find_by_id($data['client_id']);
		if ($this->input->post('new_add')) {
			$data['lead_address'] = strip_tags($this->input->post('new_address'));
			$data['lead_city'] = strip_tags($this->input->post('new_city'));
			$data['lead_state'] = strip_tags($this->input->post('new_state'));
			$data['lead_zip'] = strip_tags($this->input->post('new_zip'));
			$data['lead_country'] = strip_tags($this->input->post('lead_country'));
			$data['latitude'] = strip_tags($this->input->post('new_lat'));
			$data['longitude'] = strip_tags($this->input->post('new_lon'));
            $data['lead_add_info'] = $this->input->post('lead_add_info') ? $this->input->post('lead_add_info', true) : '';
		} else {
			$data['lead_address'] = $client->client_address;
			$data['lead_city'] = $client->client_city;
			$data['lead_state'] = $client->client_state;
			$data['lead_zip'] = $client->client_zip;
			$data['lead_country'] = $client->client_country;
			$data['latitude'] = $client->client_lat;
			$data['longitude'] = $client->client_lng;
            $data['lead_add_info'] = $client->client_main_intersection;
		}
		if((empty($data['latitude']) || empty($data['longitude'])) && !empty($data['lead_address']))
		{
			$coords = get_lat_lon($data['lead_address'], $data['lead_city'], $data['lead_state'], $data['lead_zip'], $data['lead_country']);
			$data['latitude'] = $coords['lat'];
			$data['longitude'] = $coords['lon'];
		}
		$data['lead_neighborhood'] = get_neighborhood(['latitude' => $data['latitude'], 'longitude' => $data['longitude']]);
		
		$data['lead_scheduled'] = 0;
		
		$data['timing'] = $this->input->post('new_lead_timing') ? $this->input->post('new_lead_timing') : 'Right Away';
		$data['lead_priority'] = $this->input->post('new_lead_priority') ? $this->input->post('new_lead_priority') : 'Regular';
		$data['preliminary_estimate'] = $this->input->post('preliminary_estimate');
		$data['lead_call'] = $this->input->post('lead_call') ? 1 : 0;
		$postpone = $this->input->post('postpone_date');
		$data['lead_postpone_date'] = date('Y-m-d');
		if($postpone != '')
			$data['lead_postpone_date'] = $postpone;
			
		$post = $this->input->post();
		$servicesEst = $this->input->post('est_services') ? $this->input->post('est_services') : '';
		$productsEst = $this->input->post('est_products') ? $this->input->post('est_products') : '';
		$bundlesEst = $this->input->post('est_bundles') ? $this->input->post('est_bundles') : '';
		if($productsEst != ''){
		    $servicesEst = $servicesEst . '|' . $productsEst;
        }
		if($bundlesEst != ''){
		    $servicesEst = $servicesEst . '|' . $bundlesEst;
        }

		$preuploaded_files = [];
		if($this->input->post('pre_uploaded_files') != null && is_countable($this->input->post('pre_uploaded_files')) && count($this->input->post('pre_uploaded_files')) > 0){
			$preuploaded_files = $this->input->post('pre_uploaded_files');
		}
		$isDraft = false;
		if(!empty($this->input->post('is_draft')))
            $isDraft = true;
		if($leadId = $this->leadsactions->create($data, $post, $servicesEst, $preuploaded_files, $isDraft)){
			$this->response(array(
				'status' => TRUE,
				'data' => ['client_id' => $data['client_id'], 'lead_id' => $leadId]
			), 200);
		} else {
			$this->response(array(
				'status' => FALSE,
				'data' => [],
				'message' => 'Error creating a lead'
			), 400);
		}
		
	}

	function stat() {

	    $fromDate = date('Y-m-d');
	    $toDate = date('Y-m-d');
        $data['today'] = $this->mdl_reports->getEstimatorKPI($this->user->id, $fromDate, $toDate);

        $fromDate = date('Y-m-d', strtotime('-7 days'));
        $data['last_week'] = $this->mdl_reports->getEstimatorKPI($this->user->id, $fromDate, $toDate);

        $fromDate = date('Y-m-d', strtotime('-14 days'));
        $data['two_weeks'] = $this->mdl_reports->getEstimatorKPI($this->user->id, $fromDate, $toDate);

        $fromDate = date('Y-m-d', strtotime('-1 months'));
        $data['last_month'] = $this->mdl_reports->getEstimatorKPI($this->user->id, $fromDate, $toDate);

        return $this->response(array(
            'status' => TRUE,
            'data' => $data
        ), 200);
	}

	function agenda($date = NULL, $toDate = NULL) {
        $this->load->model('mdl_leads_services');

	    $dateSegments = explode('-', $date);

	    if($date !== 'anytime' &&  (!$date || count($dateSegments) != 3 || !checkdate($dateSegments[1], $dateSegments[2], $dateSegments[0])))
	        $date = date('Y-m-d');

        $toDate = $toDate ?: $date;
        /*if ($date === 'anytime') {
            $condition = [
                'task_status' => 'new',
                //'is_anytime' => '1'
            ];
        } else {
            $condition = [
                '(task_status = "done") OR (task_status = "new")' => null,// AND (is_anytime = "1" OR is_anytime = "0")  2nd AND is_anytime <> "1"
                'task_date' => $date
            ];
        }*/
        $leads = $this->mdl_client_tasks->getEstimatorAppointments($this->user->id, ['task_date >=' => $date, 'task_date <=' => $toDate, 'task_status <>' => 'canceled']);
	    //$leads = $this->mdl_client_tasks->getEstimatorAppointments($this->user->id, $condition);

        array_map(function (&$lead) {
            $services = $this->mdl_leads_services->get_lead_services(['lead_id' => $lead->task_lead_id]);
            $lead->services = array_map(function ($service) {
                $service->service_attachments = json_decode($service->service_attachments);
                return $service;
            }, $services);
        }, $leads);

        return $this->response(array(
            'status' => TRUE,
            'data' => $leads
        ), 200);
    }

    function show($id, $flag = null) {
	    $this->load->model('mdl_leads_services');
        $this->load->library('Common/EstimateActions');
	    $lead = $this->mdl_leads->find_by_id($id);
        if(!$lead)
            return $this->response(array(
                'status' => FALSE,
                'data' => []
            ), 400);
        if(empty($lead->lead_tax_name) && $flag == 'new_estimate')
        {
            $addressForAutoTax = [
                'Address' => $lead->lead_address,
                'City' => $lead->lead_city,
                'State' => $lead->lead_state,
                'Zip' => $lead->lead_zip
            ];
            $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
            if (!empty($autoTax)) {
                $this->mdl_leads->update_leads($autoTax['db'], ['lead_id' => $id]);
                $lead = $this->mdl_leads->find_by_id($id);
            }
        }
        $services = $this->mdl_leads_services->get_lead_services(['lead_id' => $id]);
				
        $lead->services = array_map(function ($service) {
            $service->service_attachments = json_decode($service->service_attachments);
            return $service;
        }, $services);
	    $client = $this->mdl_clients->find_by_id($lead->client_id);
        $client_primary_contact = $this->mdl_clients->get_primary_client_contact($lead->client_id);
		$files = bucketScanDir('uploads/clients_files/' . $lead->client_id . '/leads/' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-L/');		

		foreach($files as $key=>$file){
			$files[$key] = 'uploads/clients_files/' . $lead->client_id . '/leads/' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-L/' . $file;
		}

        if(!$client)
            return $this->response(array(
                'status' => FALSE,
                'data' => []
            ), 400);

        return $this->response(array(
            'status' => TRUE,
            'data' => [
                'lead' => $lead,
                'client' => $client,
                'primary_contact' => $client_primary_contact,
				'files' => $files
            ]
        ), 200);
    }
	
	function fetch($id, $flag = null) {
	    $this->load->model('mdl_leads_services');
        $this->load->library('Common/EstimateActions');
	    $lead = $this->mdl_leads->find_by_id($id);

        /************ КОСТЫЛЬ ДЛЯ APP 1.12 (удалить со временем) ***********/
	    if(!$lead) {
            $estimate = $this->mdl_estimates->find_by_id($id);
            $id = $estimate && isset($estimate->lead_id) ? $estimate->lead_id : FALSE;
            $lead = $this->mdl_leads->find_by_id($id);
        }
        /************ КОСТЫЛЬ ДЛЯ APP 1.12 (удалить со временем) ***********/

        if(!$lead)
            return $this->response(array(
                'status' => FALSE,
                'data' => []
            ), 400);
        if(empty($lead->lead_tax_name) && $flag == 'new_estimate')
        {
            $addressForAutoTax = [
                'Address' => $lead->lead_address,
                'City' => $lead->lead_city,
                'State' => $lead->lead_state,
                'Zip' => $lead->lead_zip
            ];
            $autoTax = $this->estimateactions->getTaxForUSCompany($addressForAutoTax);
            if (!empty($autoTax)) {
                $this->mdl_leads->update_leads($autoTax['db'], ['lead_id' => $id]);
                $lead = $this->mdl_leads->find_by_id($id);
            }
        }
        $services = $this->mdl_leads_services->get_lead_services(['lead_id' => $id]);


        foreach($services as $service){
            if($service->is_product){
                $lead->products[] = $service;
            }
            elseif($service->is_bundle){
                $service = $this->servicesactions->addRecordsInBundle($service);
                $lead->bundles[] = $service;
            }
            else{
                $service->service_attachments = $this->servicesactions->getServiceAttachment($service->service_attachments);
                $lead->services[] = $service;
            }

        }
	    $client = $this->mdl_clients->find_by_id($lead->client_id);
        $client_primary_contact = $this->mdl_clients->get_primary_client_contact($lead->client_id);
		$files = bucketScanDir('uploads/clients_files/' . $lead->client_id . '/leads/' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-L/');
		

		foreach($files as $key=>$file){
			$files[$key] = 'uploads/clients_files/' . $lead->client_id . '/leads/' . str_pad($id, 5, '0', STR_PAD_LEFT) . '-L/' . $file;
		}
        if(!$client)
            return $this->response(array(
                'status' => FALSE,
                'data' => []
            ), 400);

        return $this->response(array(
            'status' => TRUE,
            'data' => [
                'lead' => $lead,
                'client' => $client,
                'primary_contact' => $client_primary_contact,
				'files' => $files
            ]
        ), 200);
    }

    function assign_lead() {
	    $leadId = (int)$this->input->post('lead_id');
	    $userId = (int)$this->input->post('lead_estimator');

	    if($userId !== false && (int)$userId != $userId) {
            $userId = 'none';
        }

	    $date = $this->input->post('date');
	    $time = $this->input->post('time');
	    $taskCategory = $this->input->post('task_category');

	    $lead = $this->mdl_leads->find_by_id($leadId);
	    if(!$lead)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Lead ID'
            ), 400);
        $needToDeleteTasks = [];
	    if((int)$lead->lead_estimator) {
	        $needToDeleteTasks = $this->mdl_client_tasks->find_all([
                'task_lead_id' => $leadId,
                'task_assigned_user <>' => $this->user->id,
                'task_status' => 'new'
            ]);
	        if($needToDeleteTasks && count($needToDeleteTasks)) {
                $this->mdl_client_tasks->update_by(['task_status' => 'canceled'], [
                    'task_lead_id' => $leadId,
                    'task_assigned_user <>' => $this->user->id,
                    'task_status' => 'new'
                ]);
            }
        }

	    $this->mdl_leads->update($leadId, [
	        'lead_estimator' => $userId !== false ? (int)$userId : (int)$this->user->id
        ]);

        $lead = $this->mdl_leads->get_leads(['lead_id' => $leadId], 'not_estimated')->row();

        if(empty($lead)){
            return $this->response( [
                'status' => FALSE,
                'message' => 'Incorrect Lead Status']
                , 400);
        }
        if($needToDeleteTasks && count($needToDeleteTasks))
            $lead->deleted_tasks = $needToDeleteTasks;

        $findTodayAppointment = $this->mdl_client_tasks->find_by_fields([
            'task_date' => date('Y-m-d', strtotime($date)),
            'task_lead_id' => $leadId,
            'task_assigned_user' => $this->user->id,
        ]);

        if($date && $time && strtotime($date . ' ' . $time) > time()) {
            $length = config_item('AppointmentTaskLength') ?: 45;
            $taskData = [
                'task_author_id' => $this->user->id,
                'task_address' => $lead->lead_address,
                'task_city' => $lead->lead_city,
                'task_state' => $lead->lead_state,
                'task_zip' => $lead->lead_zip,
                'task_country' => $lead->lead_country,
                'task_client_id' => $lead->client_id,
                'task_date_created' => date('Y-m-d'),
                'task_desc' => 'Meeting with a client',
                'task_latitude' => $lead->latitude,
                'task_longitude' => $lead->longitude,
                'task_date_updated' => date('Y-m-d'),
                'task_assigned_user' => $this->user->id,
                'task_date' => $date,
                'task_start' => $time,
                'task_end' => date('H:i:s', strtotime($date . ' ' . $time) + ($length * 60)),
                'task_lead_id' => $leadId,
                'task_category' => $taskCategory ? $taskCategory : NULL,
                'task_status' => 'new',
            ];
            if(!$findTodayAppointment) {
                $taskId = $this->mdl_client_tasks->insert($taskData);
            } else {
                $taskId = $findTodayAppointment->task_id;
                $this->mdl_client_tasks->update($findTodayAppointment->task_id, $taskData);
            }

            $task = $this->mdl_client_tasks->get_all([
                'task_id' => $taskId
            ]);
            if($task && count($task)) {
                $lead->task = $task[0];
            }
        }

        return $this->response(array(
            'status' => TRUE,
            'data' => $lead
        ), 200);
    }

    function assign_task() {
        $taskId = intval($this->input->post('task_id'));
        $task = $this->mdl_client_tasks->find_by_id($taskId);

        if(!$task)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Task ID'
            ), 400);
        if($task->task_assigned_user)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Task is already assigned'
            ), 400);

        $this->mdl_client_tasks->update($taskId, [
            'task_assigned_user' => $this->user->id
        ]);

        $task = $this->mdl_client_tasks->get_all([
            'task_id' => $taskId
        ], TRUE);

        return $this->response(array(
            'status' => TRUE,
            'data' => $task
        ), 200);
    }

    function update_lead() {
        $this->load->model('mdl_leads_services');
	    $leadId = intval($this->input->post('lead_id'));

        $lead = $this->mdl_leads->find_by_id($leadId);
        if(!$lead)
            return $this->response([
                'status' => FALSE,
                'message' => 'Incorrect Lead ID'
            ], 400);

        $update['lead_call'] = $this->input->post('lead_call') !== FALSE ? (int)boolval($this->input->post('lead_call')) : $lead->lead_call;
        $update['lead_body'] = $this->input->post('lead_body') ? $this->input->post('lead_body', true) : $lead->lead_body;
        $update['lead_address'] = $this->input->post('lead_address') ? $this->input->post('lead_address', true) : $lead->lead_address;
        $update['lead_city'] = $this->input->post('lead_city') ? $this->input->post('lead_city', true) : $lead->lead_city;
        $update['lead_state'] = $this->input->post('lead_state') ? $this->input->post('lead_state', true) : $lead->lead_state;
        $update['lead_country'] = $this->input->post('lead_country') ? $this->input->post('lead_country', true) : $lead->lead_country;
        $update['lead_zip'] = $this->input->post('lead_zip') ? $this->input->post('lead_zip', true) : $lead->lead_zip;
        $update['latitude'] = $this->input->post('latitude') ? $this->input->post('latitude', true) : $lead->latitude;
        $update['longitude'] = $this->input->post('longitude') ? $this->input->post('longitude', true) : $lead->longitude;
        $update['lead_priority'] = $this->input->post('lead_priority') ? $this->input->post('lead_priority', true) : $lead->lead_priority;
        $update['lead_reffered_by'] = $this->input->post('lead_reffered_by') ? $this->input->post('lead_reffered_by', true) : $lead->lead_reffered_by;
        $update['lead_reffered_client'] = $this->input->post('lead_reffered_client') ? $this->input->post('lead_reffered_client', true) : $lead->lead_reffered_client;
        $update['lead_reffered_user'] = $this->input->post('lead_reffered_user') ? $this->input->post('lead_reffered_user', true) : $lead->lead_reffered_user;
        $update['lead_add_info'] = $this->input->post('lead_add_info') ? $this->input->post('lead_add_info', true) : $lead->lead_add_info;

        $servicesEst = $this->input->post('est_services') ? $this->input->post('est_services') : '';
        $productsEst = $this->input->post('est_products') ? $this->input->post('est_products') : '';
        $bundlesEst = $this->input->post('est_bundles') ? $this->input->post('est_bundles') : '';
        if($productsEst != ''){
            $servicesEst = $servicesEst . '|' . $productsEst;
        }
        if($bundlesEst != ''){
            $servicesEst = $servicesEst . '|' . $bundlesEst;
        }
        $this->mdl_leads_services->delete_by(['lead_id' => $leadId]);
        $servicesEst = explode('|', $servicesEst);
        foreach($servicesEst as $k=>$v)
            $this->mdl_leads_services->insert(['lead_id' => $leadId, 'services_id' => intval($v)]);

        $this->mdl_leads->update($leadId, $update);
        return $this->response([
            'status' => TRUE
        ]);
    }

    function update_task() {
	    $allowedStatuses = ['new', 'canceled', 'done'];

        $taskId = intval($this->input->post('task_id'));
        $note = $this->input->post('task_desc', TRUE) ?: $this->input->post('description', TRUE);
        $taskCategory = $this->input->post('task_category', TRUE) ?: $this->input->post('category', TRUE);
        $taskStatus = $this->input->post('status') ?: $this->input->post('task_status');
        $status = array_search($taskStatus, $allowedStatuses) === FALSE ? NULL : $taskStatus;
        $address = $this->input->post('address', TRUE);
        $city = $this->input->post('city', TRUE);
        $country = $this->input->post('country', TRUE);
        $state = $this->input->post('state', TRUE);
        $zip = $this->input->post('zip', TRUE);
        $endTime = $this->input->post('end_time', TRUE);
        $startDate = $this->input->post('start_date', TRUE);
        $startTime = $this->input->post('start_time', TRUE);
        $lat = $this->input->post('lat', TRUE);
        $lon = $this->input->post('lng', TRUE);
        $assignedId = $this->input->post('assigned_id', TRUE);
        $client = $this->input->post('task_client', TRUE);
        $lead = $this->input->post('task_lead', TRUE);
        $task = $this->mdl_client_tasks->find_by_fields([
            'task_id' => $taskId
        ]);
        $anytime = $this->input->post('anytime');

        if(!$task)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Task ID'
            ), 400);
        if(!$status)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Status Value'
            ), 400);

        /*if($status == $task->task_status)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'This Task is already in the status "' . $status . '"'
            ), 400);*/

        $taskData = [
            'task_status' => $status
        ];
        if($note)
            $taskData['task_desc'] = $note;
        /*if ($anytime === '0' || $anytime === '1')
            $taskData['is_anytime'] = $anytime;*/
        if($taskCategory)
            $taskData['task_category'] = $taskCategory;
        if($address)
            $taskData['task_address'] = $address;
        if($city)
            $taskData['task_city'] = $city;
        if($state)
            $taskData['task_state'] = $state;
        if($zip)
            $taskData['task_zip'] = $zip;
        if($country)
            $taskData['task_country'] = $country;
        if($assignedId)
            $taskData['task_assigned_user'] = $assignedId;
        if($endTime)
            $taskData['task_end'] = $endTime;
        if($lat)
            $taskData['task_latitude'] = $lat;
        if($lon)
            $taskData['task_longitude'] = $lon;
        if($startDate)
            $taskData['task_date'] = $startDate;
        if($startTime)
            $taskData['task_start'] = $startTime;
        if($client && is_array($client) && !empty($client['client_id']))
            $taskData['task_client_id'] = $client['client_id'];
        elseif(is_null($client) && array_key_exists('task_client', $_POST)) {
            $taskData['task_client_id'] = null;
        }
//        else
//            $taskData['task_client_id'] = null;
        if($lead && is_array($lead) && !empty($lead['lead_id']))
            $taskData['task_lead_id'] = $lead['lead_id'];
        elseif(is_null($lead) && array_key_exists('task_lead', $_POST)) {
            $taskData['task_lead_id'] = null;
        }
//        else
//            $taskData['task_lead_id'] = null;

        $this->mdl_client_tasks->update($taskId, $taskData);

        $task = $this->mdl_client_tasks->get_all([
            'task_id' => $taskId
        ], TRUE);

        return $this->response(array(
            'status' => TRUE,
            'data' => $task
        ), 200);
    }

    function get() {

        $response = [
            'leads' => $this->mdl_leads->getAppLeads([
                'lead_postpone_date <=' => date('Y-m-d'),
                'lead_status_active' => TRUE,
                '(lead_status_default = 1 OR lead_status_draft = 1)' => null
            ]),
            'tasks' => $this->mdl_client_tasks->getAppTasks([
                'task_status' => 'new',
                'task_no_map' => 0,
                'employees.emp_field_estimator' => '1',
                //'task_category >' => 0,
                'estimates.estimate_id IS NULL' => NULL
            ]),

            'anytime_tasks' => []/*$this->mdl_client_tasks->getAppTasks([
                'task_status' => 'new',
                'task_no_map' => 0,
                'employees.emp_field_estimator' => '1',
                'estimates.estimate_id IS NULL' => NULL,
                //'is_anytime' => '1',
            ]),*/
        ];

        return $this->response(array(
            'status' => TRUE,
            'data' => $response
        ), 200);
    }

    function send() {
        $lead = $this->mdl_leads->find_by_id($this->input->post('lead_id'));
        if(!$lead)
            return $this->response(array(
                'status' => FALSE,
                'data' => []
            ), 400);
    }

    function statuses() {
	    $this->load->model('mdl_leads_status');
	    $this->load->model('mdl_leads_reason');
	    $statuses = $this->mdl_leads_status->get_many_by([
	        'lead_status_estimated' => 0,
	        'lead_status_for_approval' => 0,
	        'lead_status_active' => 1,
        ]);
	    $declineReasons = $this->mdl_leads_reason->get_many_by([
            'reason_active' => 1
        ]);

        return $this->response(array(
            'status' => TRUE,
            'data' => [
                'lead_statuses' => $statuses,
                'declined_reasons' => $declineReasons
            ]
        ), 200);
    }

    function update_status() {
        $this->load->model('mdl_leads_status');
        $this->load->model('mdl_leads_reason');

        $leadId = (int)$this->input->post('lead_id');
        $leadStatusId = (int)$this->input->post('lead_status_id');
        $leadReasonStatusId = (int)$this->input->post('lead_reason_status_id');

        $this->load->library('form_validation');
        $this->form_validation->set_rules('lead_id', 'Lead ID', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('lead_status_id', 'Lead Status ID', 'required|is_natural_no_zero');
        $this->form_validation->set_rules('lead_reason_status_id', 'Lead Decline Reason ID', 'is_natural_no_zero');
        if ($this->form_validation->run() == FALSE)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Validation Error',
                'errors' => validation_errors_array()
            ), 400);

        $lead = $this->mdl_leads->find_by_fields(['lead_id' => $leadId]);
        $status = $this->mdl_leads_status->get_by([
            'lead_status_id' => $leadStatusId,
            'lead_status_estimated' => 0,
            'lead_status_for_approval' => 0,
            'lead_status_active' => 1,
        ]);
        $reason = $this->mdl_leads_reason->get_by([
            'reason_id' => $leadReasonStatusId,
            'reason_active' => 1,
        ]);

        $updateData = [];

        if(!$lead) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Lead ID'
            ), 400);
        }

        if(!$status) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Incorrect Status ID'
            ), 400);
        }

        if($status->lead_status_declined && !$reason) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Reason Is Required'
            ), 400);
        } elseif ($status->lead_status_declined && $reason) {
            $updateData['lead_reason_status_id'] = $leadReasonStatusId;
        }

        $updateData['lead_status_id'] = $leadStatusId;
        $this->mdl_leads->update($leadId, $updateData);

        return $this->response(array(
            'status' => TRUE,
            'data' => $this->mdl_leads->get_leads(['lead_id' => $leadId], '')->row()
        ), 200);
    }
	
	function upload()
    {
        $leadId = $this->input->post('lead_id');
		$client_id = $this->input->post('client_id');
        		
        $lead = $this->mdl_leads->find_by_id($leadId);
        if(!$lead && $leadId != 0)
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Wrong lead ID provided'
            ), 400);
        
        $max = 1;
                      
		$to_tmp = '';
		if($leadId == 0){
			$to_tmp = 'tmp/';
		}
				
		if($client_id != 0) {
			$path = 'uploads/clients_files/' . $client_id . '/leads/' . $to_tmp . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '-L/';
		} else {
			$path = 'uploads/clients_files/tmp/lead/';
		}
        
		
		$files = bucketScanDir($path);
		if (count($files) && $files) {
			foreach($files as $file)
			{
				preg_match('/lead_no_' . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '-L.*?_([0-9]{1,})/is', $file, $num);
				if(isset($num[1]) && ($num[1] + 1) > $max)
					$max = $num[1] + 1;
				preg_match('/pdf_lead_no_' . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '-L.*?_([0-9]{1,})/is', $file, $num1);
				if(isset($num1[1]) && ($num1[1] + 1) > $max)
					$max = $num[1] + 1;
			}
		}

        $photos = [];
		 
        if (isset($_FILES['files']) && is_array($_FILES['files'])) {
            $this->load->library('upload');
			
            foreach ($_FILES['files']['name'] as $key => $val) {
				

                $_FILES['file']['name'] = $_FILES['files']['name'][$key];
                $_FILES['file']['type'] = $_FILES['files']['type'][$key];
                $_FILES['file']['tmp_name'] = $_FILES['files']['tmp_name'][$key];
                $_FILES['file']['error'] = $_FILES['files']['error'][$key];
                $_FILES['file']['size'] = $_FILES['files']['size'][$key];
                
				$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
				$suffix = $ext == 'pdf' ? 'pdf_' : NULL;
				$config['file_name'] = $suffix . 'lead_no_' . str_pad($leadId, 5, '0', STR_PAD_LEFT) . '-L_' . $max++ . '.' . $ext;
                
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|ogg|mp3|mp4|webm|aac|m4a|wav|GIF|JPG|JPEG|PNG|PDF|OGG|MP3|MP4|WEBM|AAC|M4A|WAV';
				$config['remove_spaces'] = TRUE;
                $config['encrypt_name'] = TRUE;

                $this->upload->initialize($config);
                if ($this->upload->do_upload('file')) {
                    $uploadData = $this->upload->data();
					
                    $photos[] = [
                        'filepath' => $path . $uploadData['file_name'],
                        'filename' => $uploadData['file_name']
                    ];
                    
                } else {
                    $photos[] = [
                        'error' => strip_tags($this->upload->display_errors())
                    ];
                }
												
            }
			return $this->response(array(
				'status' => TRUE,
				'data' => $photos
			));
        } else {
			return $this->response(array(
                'status' => FALSE,
                'message' => 'No files to upload'
            ), 400);
		}
        
    }
	
	function delete_file()
	{		
		$name = basename($this->input->post('name'));
		
		$client_id = $this->input->post('client_id');
		$lead_id = $this->input->post('lead_id');
		
		if($client_id == 0 || $client_id == null || $client_id == '') {
			$new_path = 'uploads/clients_files/tmp/lead/';
			if (is_bucket_file($new_path . $name)) {
				if (bucket_unlink($new_path . $name)) {                
					return $this->response(array(
						'status' => TRUE,
						'data' => []
					), 200);
				}
				return $this->response(array(
					'status' => FALSE,
					'message' => 'Permission denied'
				), 400);
								
			}
		}
		
		$path = 'uploads/clients_files/' . $client_id . '/leads/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/';
		if (is_bucket_file($path . $name)) {
			if (bucket_unlink($path . $name)) {                
                return $this->response(array(
					'status' => TRUE,
					'data' => []
				), 200);
            }
			return $this->response(array(
				'status' => FALSE,
				'message' => 'Permission denied'
			), 400);
			
		}
		$tmpPath = 'uploads/clients_files/' . $client_id . '/leads/tmp/' . str_pad($lead_id, 5, '0', STR_PAD_LEFT) . '-L/';
        if (is_bucket_file($tmpPath . $name)) {
            if (bucket_unlink($tmpPath . $name)){
				return $this->response(array(
					'status' => TRUE,
					'data' => []
				), 200);
            }
			return $this->response(array(
				'status' => FALSE,
				'message' => 'Permission denied'
			), 400);          
        }
		
		return $this->response(array(
			'status' => FALSE,
			'message' => 'Incorrect file'
		), 400);
			
	}

    /**
     * Get project details
     *
     * @param $leadId
     */
	public function getProjectDetails($leadId) {
	    if (empty($leadId)) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Wrong lead ID provided'
            ), 400);
        }

	    $projectDetails = Lead::getProjectDetails($leadId);

	    if (!$projectDetails) {
            return $this->response(array(
                'status' => FALSE,
                'message' => 'Wrong lead ID provided'
            ), 400);
        }

        return $this->response(array(
            'status' => TRUE,
            'data' => $projectDetails
        ), 200);
    }
}
