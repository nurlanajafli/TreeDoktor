<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * Lead Model
 * created by: Dmitriy Vashchenko
 * created on: 2015-31-03.
 */
use application\modules\clients\models\ClientLetter;
use application\modules\tasks\models\Task;
use Illuminate\Support\Carbon;

class Mdl_client_tasks extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		// Variables set for MY_Controller
		$this->table = 'client_tasks';
		$this->primary_key = "client_tasks.task_id";
	}
	
	function get_all($where = array(), $limit = FALSE, $order = FALSE, $field_workers=true, $without_expired_task_by_day = false)
	{
        $this->db->select($this->table . ".*, CONCAT(task_date, ' ', task_start) as task_start_date, CONCAT(task_date, ' ', task_end) as task_end_date, clients_contacts.*, users.worker_type, users.firstname, users.lastname, ass.id as ass_id, ass.firstname as ass_firstname, ass.lastname ass_lastname, ass.emailid, ass.color as ass_color, ass.user_email as ass_email, clients.client_phone, clients.client_email, clients.client_name, clients.client_unsubscribe, client_task_categories.*, lead_statuses.*, leads.*, clients.client_id", FALSE);
		if(isset($where['employees.emp_field_estimator'])) {
            $this->db->select(', employees.*');
        }
		if(false === $field_workers) {
            $this->db->select(', employees.emp_feild_worker, employees.emp_field_estimator');
        }
		if($where && !empty($where)) {
            $this->db->where($where);
        }
        if ($without_expired_task_by_day) {
            $appointmentTaskExpirationDay = config_item('AppointmentTaskExpirationDay');
            $this->db->where('NOW() BETWEEN task_date AND DATE_ADD(task_date, INTERVAL ' . $appointmentTaskExpirationDay . ' DAY)');
        }
		if($order) {
            $this->db->order_by($order);
        }
		$this->db->join('client_task_categories', 'client_task_categories.category_id = '. $this->table .'.task_category', 'left');
		$this->db->join('users', 'users.id = '. $this->table .'.task_author_id', 'left');
		$this->db->join('users as ass', 'ass.id = '. $this->table .'.task_assigned_user', 'left');
		if(isset($where['employees.emp_field_estimator']) || false === $field_workers) {
            $this->db->join('employees', 'employees.employee_id = ass.user_emp_id', 'left');
        }

		$this->db->join('clients', 'clients.client_id = '. $this->table .'.task_client_id', 'left');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('leads', 'leads.lead_id = '. $this->table .'.task_lead_id', 'left');
        $this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id', 'left');

        // Exclude only fieldworkers and only from office schedule
//        if (false === $field_workers)
//            $this->db->where('NOT (employees.emp_feild_worker=1 AND employees.emp_field_estimator=0)');

		$query = $this->db->get($this->table);
		//var_dump($this->db->last_query()); die();
		if($limit == 1) {
            return $query->row_array();
        }
        return $query->result_array();
	}

	function save_event($data, $update = TRUE)
	{
		if($update)
		{
		    $this->mdl_client_tasks->update($data['task_id'], $data);

			return TRUE;
		}
		else
		{
			if(isset($data['task_id'])) unset($data['task_id']);
			if(isset($this->session->userdata['user_id'])){
				$data['task_author_id'] = $this->session->userdata['user_id'];
			} else {
				$data['task_author_id'] = $this->user->id;
			}
			
			$data['task_date_created'] = date('Y-m-d');

			return $this->mdl_client_tasks->insert($data);
		}
		//return FALSE;
	}
	
	function update_by($data, $wdata)
	{
		if ($data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table, $data);
			
		} else {
			echo "data not received";
		}
	}
	
	function get_followup($where = [], $statusList)
	{
		//$followUpConfig = $this->config->item('followup_modules')['schedule'];		
		$followUpConfig = $this->config->item('followup_modules')['client_tasks'];	 	
		$dbStatuses = [];
		foreach ($statusList as $value) {
			if(isset($followUpConfig['statuses'][$value]))
				$dbStatuses[] = $value;
		}
		 
		$this->db->select("task_id, TIMEDIFF(CONCAT(task_date, ' ', task_start), '"  . date('Y-m-d H:i') .  "') as datediff,  CONCAT(task_date, ' ', task_start)  as this_status_date, clients.client_id, users.id as estimator_id, clients_contacts.*", FALSE);
		
		$this->db->join('clients', 'client_tasks.task_client_id = clients.client_id');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
		$this->db->join('users', 'users.id = client_tasks.task_assigned_user', 'left');
		
		if($where)
			$this->db->where($where);
		$this->db->where_in('task_category', $dbStatuses);
		
		$this->db->group_by('task_id');
		$query = $this->db->get($this->table);
		return $query->result_array();
	}
	
	function get_followup_variables($id)
	{
		$task = $this->get_all(['task_id' => $id], 1);
		
		$date = date('h:i A', strtotime($task['task_date'] . ' ' . $task['task_start']));
		$fullDate =  $task['task_date'];
		
		$result['JOB_ADDRESS'] = $task['task_address'];
		$result['EMAIL'] = $task['cc_email'];
		$result['PHONE'] = $task['cc_phone'];
		$result['NAME'] = $task['client_name'];
		$result['NO'] = '';
		$result['LEAD_NO'] = '';
		$result['ESTIMATE_NO'] = '';
		$result['INVOICE_NO'] = '';
		$result['ESTIMATOR_NAME'] = $task['ass_firstname'] . ' ' . $task['ass_lastname'];
		$result['TIME'] = $date;
		$result['TIME_AND_DATE'] = $fullDate . ' ' .  $date;
		$result['DATE'] = $fullDate;
		 
		$result['AMOUNT'] = '';
		$result['TOTAL_DUE'] = '';
		$result['CCLINK'] = '';

		return $result;
	}

	function getEstimatorAppointments($userId, $conditions = []) {
	    if(!intval($userId))
	        return FALSE;
        $this->db->select($this->table . ".*, CONCAT(task_date, ' ', task_start) as task_start_date, CONCAT(task_date, ' ', task_end) as task_end_date,
            leads.lead_priority, leads.lead_call, leads.latitude, leads.longitude, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_body, leads.preliminary_estimate, leads.lead_add_info,
            estimates.estimate_id, estimate_statuses.est_status_name, estimate_statuses.est_status_id, estimate_statuses.est_status_confirmed,
            estimate_statuses.est_status_declined, FROM_UNIXTIME(estimates.date_created, '%Y-%m-%d %H:%i') as estimate_date_created,  
            clients_contacts.*, clients.client_id, clients.client_name, clients.client_unsubscribe, clients.client_type, client_task_categories.*", FALSE);
        if($conditions && !empty($conditions))
            $this->db->where($conditions);
        $this->db->where('task_assigned_user', intval($userId));
        $this->db->order_by('task_date', 'ASC');
        $this->db->order_by('task_start', 'ASC');
        $this->db->join('client_task_categories', 'client_task_categories.category_id = '. $this->table .'.task_category', 'left');
        $this->db->join('leads', 'leads.lead_id = '. $this->table .'.task_lead_id', 'left');
        $this->db->join('estimates', 'leads.lead_id = estimates.lead_id', 'left');
        $this->db->join('estimate_statuses', 'estimate_statuses.est_status_id = estimates.status', 'left');
        $this->db->join('clients', 'clients.client_id = '. $this->table .'.task_client_id', 'left');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->group_by('task_id');

        return $this->db->get($this->table)->result();
    }
    function round_time($time)
    {
        //300 - round in seconds
        //$time - example 11:17
        return date('H:i', (300 * round(strtotime($time) / 300)));
    }
	function office_data($lead_data)
	{
		$data = ['task_status'=>'new', 'task_date_created'=>date('Y-m-d'), 'task_desc'=>'Meeting with a client', 'task_category'=>7];
		if(isset($lead_data['task_category']) && (int)$lead_data['task_category'])
			$data['task_category'] = (int)$lead_data['task_category'];

		$this->load->model('mdl_user');
        $this->load->model('mdl_clients');
		$this->load->model('mdl_leads', 'mdl_leads');

		if(!isset($lead_data['scheduled_user_id']) || !$lead_data['scheduled_user_id'])
			return false;

		$data['task_assigned_user'] = (int)$lead_data['scheduled_user_id'];

        $taskDate = DateTime::createFromFormat(getDateFormat(), $lead_data['scheduled_date']);
        $data['task_date'] = $taskDate->format('Y-m-d');

        $data['task_start'] = $this->round_time($lead_data['scheduled_start_time']);
        $data['task_end'] = $this->round_time($lead_data['scheduled_end_time']);
		
		$data['task_client_id'] = $lead_data['client_id'];
		$data['task_lead_id'] = $lead_data['lead_id'];

		//if not lead_address then new_address or new_client_address
		$data['task_address'] = element('lead_address', $lead_data, element('new_address', $lead_data, element('new_client_address', $lead_data, NULL)));
		$data['task_city'] 	= element('lead_city', $lead_data, element('new_city', $lead_data, element('new_client_city', $lead_data, NULL)));
		$data['task_state'] = element('lead_state', $lead_data, element('new_client_state', $lead_data, element('new_state', $lead_data, '') ));

		$data['task_zip'] 	= element('lead_zip', $lead_data, element('new_zip', $lead_data, element('new_client_zip', $lead_data, NULL)));

		$data['task_country'] 	= element('lead_country', $lead_data, element('new_country', $lead_data, element('new_client_country', $lead_data, NULL)));

		$data['task_latitude'] 	= element('latitude', $lead_data, element('new_lat', $lead_data, element('new_client_lat', $lead_data, NULL)));
		$data['task_longitude'] = element('longitude', $lead_data, element('new_lon', $lead_data, element('new_client_lon', $lead_data, NULL)));

        $data['task_id'] = $this->mdl_client_tasks->save_event($data, FALSE);

		if(!$data['task_id'])
			return false;

		/*------------------update lead--------------------*/
		$update_data = ['lead_estimator' => (int)$data['task_assigned_user'], 'lead_assigned_date' => date('Y-m-d')];
		$this->mdl_leads->update_leads($update_data, ['lead_id' => $lead_data['lead_id']]);
		/*------------------update lead--------------------*/

		$data['country'] = element('new_client_country', $lead_data, config_item('office_country'));
		$address_array = [element('task_address', $data, ''), element('task_city', $data, ''), element('task_state', $data, ''), element('task_zip', $data, '')/*, $data['country']*/];
		
		$address = implode(', ', array_diff($address_array, array('', NULL, false)));
		$data['formatted_address'] = element('formatted_address', $lead_data, $address);

		$data["is_client_sms"] = $lead_data["is_client_sms"];
  		$data["is_client_email"] = $lead_data["is_client_email"];
  		$data["is_estimator_sms"] = $lead_data["is_estimator_sms"];
  		$data["is_estimator_email"] = $lead_data["is_estimator_email"];

        $this->scheduled_notification($data);
		
		return TRUE;
	}


	function scheduled_notification($task)
	{
		$this->load->model('mdl_sms');
		$this->load->helper('user_tasks');
		$this->load->library('email');
		$this->email->clear(TRUE);
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();

		$config['mailtype'] = 'html';
		$this->email->initialize($config);

        $estimatorLetter = ClientLetter::where(['system_label' => 'estimator_schedule_appointment'])->first();
        $clientLetter = ClientLetter::where(['system_label' => 'client_schedule_appointment'])->first();
        $estimator_sms_tpl = $this->mdl_sms->get_by(['system_label' => 'estimator_schedule_appointment']);
		$client_sms_tpl = $this->mdl_sms->get_by(['system_label' => 'client_schedule_appointment']); 

        $task_data = Task::with(['user.employee', 'client'])->find($task['task_id']);
        $client_data = $task_data->client;

        $brand_id = get_brand_id([], ($client_data)?$client_data->toArray():[]);

		$emails = $sms = [];
		$sms_footer = $this->load->view('clients/appointment/sms_footer_tpl', $task, true);

		if($client_data->primary_contact && filter_var($client_data->primary_contact->cc_email, FILTER_VALIDATE_EMAIL) && $task['is_client_email']=='true')
		{
            $clientLetter = ClientLetter::compileLetter($clientLetter, $brand_id, [
                'client'    => $client_data,
                'task'      => $task_data??[],
            ]);
			$emails['client']=['to'=>$client_data->primary_contact->cc_email, 'subject'=>$clientLetter->email_template_title, 'message' => $clientLetter->email_template_text];
		}
		
		if(filter_var($task_data->user->user_email, FILTER_VALIDATE_EMAIL) && $task['is_estimator_email']=='true')
		{
            $estimatorLetter = ClientLetter::compileLetter($estimatorLetter, $brand_id, [
                'client'    => $client_data,
                'task'      => $task_data??[],
            ]);
			$emails['estimator']=['to' => $task_data->user->user_email, 'subject' => $estimatorLetter->email_template_title, 'message' => $estimatorLetter->email_template_text];
		}

        if (config_item('messenger') !== null) {
            if($task['is_client_sms'] == 'true'){
                $sms['client'] = ['number' => $client_data->primary_contact->cc_phone_clean, 'text' => $client_sms_tpl->sms_text];
                $sms['client']['text'] = item_branding($brand_id, $sms['client']['text']);
                $sms['client']['text'] = str_replace('[NAME]', $client_data->primary_contact->cc_name, $sms['client']['text']);
                $sms['client']['text'] = str_replace('[ESTIMATOR_NAME]', $task_data->user->full_name, $sms['client']['text']);
                $sms['client']['text'] = str_replace('[ADDRESS]', $task['task_address'], $sms['client']['text']);
                $sms['client']['text'] = str_replace('{address}', $task['task_address'], $sms['client']['text']).$sms_footer;
            }

            if ($task['is_estimator_sms'] == 'true') {
                $sms['estimator'] = [
                    'number' => substr($task_data->user->employee->emp_phone, 0, config_item('phone_clean_length')),
                    'text' => $estimator_sms_tpl->sms_text
                ];
                $sms['estimator']['text'] = item_branding($brand_id, $sms['estimator']['text']);
                $sms['estimator']['text'] = str_replace('[NAME]', $client_data->primary_contact->cc_name, $sms['estimator']['text']);
                $sms['estimator']['text'] = str_replace('[ESTIMATOR_NAME]',
                    $task_data->user->full_name, $sms['estimator']['text']);
                $sms['estimator']['text'] = str_replace('[ADDRESS]', $task['task_address'], $sms['estimator']['text']);
                $sms['estimator']['text'] = str_replace('{address}', $task['task_address'],
                        $sms['estimator']['text']) . $sms_footer;
            }

            $this->load->driver('messages');

            foreach ($sms as $key => $msg) {
                $this->messages->send($msg['number'], $msg['text']);
            }
        }

		foreach ($emails as $key => $email_tmp) {
			$this->email->to($email_tmp['to']);
			$this->email->from(brand_email($brand_id), brand_name($brand_id));
			$this->email->subject($email_tmp['subject']);
			$this->email->message($email_tmp['message']);
			$this->email->send();

            $entities = [
                ['entity' => 'client', 'id' => $client_data->client_id]
            ];
            $this->email->setEmailEntities($entities);
            make_notes($client_data->client_id, 'Sent email "' . $email_tmp['subject'] . '" to ' . $email_tmp['to'], 'email', NULL, $this->email);
		}
	}

	function add_absence_task($user_id, $day_off_from, $day_off_to) {

        $this->load->library('googlemaps');

        $office_schedule_start = $this->config->item('office_schedule_start');
        $office_schedule_end   = $this->config->item('office_schedule_end');

        $task = [];
        $task['task_desc'] = 'Day off';
        $task['task_date_created'] = date('Y-m-d');
        $task['task_category'] = -1;
        $task['task_status'] = 'done';
        $task['task_no_map'] = 1;
        $task['task_author_id'] = $this->session->userdata['user_id'];
        $task['task_start'] = $office_schedule_start . ':00:00';
        $task['task_end'] = $office_schedule_end . ':00:00';
        $task['task_address'] = config_item('office_address');
        $task['task_city'] = config_item('office_city');
        $task['task_state'] = config_item('office_state');
        $task['task_zip'] = config_item('office_zip');
        $task['task_country'] = config_item('office_country');

        $coords = get_lat_lon($task['task_address'], $task['task_city'], $task['task_state']);

        $task['task_latitude'] = $coords['lat'];
        $task['task_longitude'] = $coords['lon'];
        $task['task_assigned_user'] = $user_id;
        $days = getDates($day_off_from, $day_off_to);
		
        if(is_array($days) && !empty($days)){
            foreach ($days as $key => $day) {
                $task['task_date'] = $day;
                $exist = $this->mdl_client_tasks->find_by_fields(['task_assigned_user'=>$user_id, 'task_date'=>$day, 'task_category'=>-1]);
                if(!$exist)
                    $task_id = $this->mdl_client_tasks->insert($task);
            }
        }

        return true;
	}

	function closeAllLeadTasks($leadId, $userId) {
        $this->update_by(['task_status' => 'canceled'], [
            'task_lead_id' => $leadId,
            'task_assigned_user <>' => $userId,
            'task_status' => 'new'
        ]);

        $this->update_by(['task_status' => 'done'], [
            'task_lead_id' => $leadId,
            'task_assigned_user' => $userId,
            'task_status' => 'new'
        ]);

        $findTodayAppointment = $this->find_by_fields([
            'task_date' => date('Y-m-d'),
            'task_lead_id' => $leadId,
            'task_assigned_user' => $userId,
        ]);

        if(!$findTodayAppointment) {
            $this->load->model('mdl_leads');
            $lead = $this->mdl_leads->find_by_id($leadId);
            $minutesItervals = ['00', '15', '30', '45'];
            $nowMinutes = $minutesItervals[floor(intval(date('i')) / 15)];
            $time = date("H:{$nowMinutes}:00");
            $taskData = [
                'task_author_id' => $userId,
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
                'task_assigned_user' => $userId,
                'task_date' => date('Y-m-d'),
                'task_start' => $time,
                'task_end' => date('H:i:s', strtotime(date('Y-m-d') . ' ' . $time) + (45 * 60)),
                'task_lead_id' => $leadId,
                'task_status' => 'done'
            ];
            $taskId = $this->mdl_client_tasks->insert($taskData);
        }

        return TRUE;
    }

    function getAppTasks($wdata = [])
    {
        $this->db->select("client_tasks.task_date_created as added_on, client_tasks.task_id, client_tasks.task_lead_id, client_tasks.task_address, client_tasks.task_category, 
            client_tasks.task_city, client_tasks.task_zip, client_tasks.task_state,  client_tasks.task_date_created, client_tasks.task_desc, client_tasks.task_latitude, client_tasks.task_longitude, 
            client_tasks.task_status,client_tasks.is_anytime, CONCAT(task_date, ' ', task_start) as task_start_date, CONCAT(task_date, ' ', task_end) as task_end_date, clients_contacts.cc_name, clients_contacts.cc_phone, clients_contacts.cc_email, clients.client_name, clients.client_type, clients.client_id,
            users.firstname, users.lastname, ass.id as ass_id, ass.firstname as ass_firstname, ass.lastname ass_lastname, ass.emailid, ass.color as ass_color, 
            lead_statuses.lead_status_id, lead_statuses.lead_status_active, lead_statuses.lead_status_declined, client_task_categories.category_name, 
            lead_statuses.lead_status_default, lead_statuses.lead_status_estimated, lead_statuses.lead_status_for_approval, estimates.estimate_id, estimate_statuses.est_status_declined", FALSE);

        if($wdata && count($wdata))
            $this->db->where($wdata);

//        Removed to get anytime tasks also
//        $this->db->where('task_date IS NOT NULL');

        $this->db->order_by($this->table . '.task_id', 'DESC');
        $this->db->join('client_task_categories', 'client_task_categories.category_id = '. $this->table .'.task_category', 'left');
        $this->db->join('users', 'users.id = '. $this->table .'.task_author_id');
        $this->db->join('users as ass', 'ass.id = '. $this->table .'.task_assigned_user');
        $this->db->join('employees', 'employees.emp_user_id = ass.id', 'left');
        $this->db->join('clients', 'clients.client_id = '. $this->table .'.task_client_id', 'left');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('leads', 'leads.lead_id = '. $this->table .'.task_lead_id', 'left');
        $this->db->join('estimates', 'leads.lead_id = estimates.lead_id', 'left');
        $this->db->join('estimate_statuses', 'estimate_statuses.est_status_id = estimates.status', 'left');
        $this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id', 'left');

        $this->_scopeTasksPermissions();

        $query = $this->db->get($this->table);

        return $query->result_array();
    }

    private function _scopeTasksPermissions() {
        $CI =& get_instance();

        if(!isset($CI->user) || !isset($CI->user->permissions) || !isset($CI->user->permissions['CL'])) {
            return false;
        }

        if($CI->user->permissions['CL'] === \application\modules\clients\models\Client::PERM_NONE) {
            $this->db->where('leads.lead_id', -1);
        } elseif ($CI->user->permissions['CL'] === \application\modules\clients\models\Client::PERM_OWN) {
            $this->db->where("(client_tasks.task_assigned_user = {$CI->user->id} OR
                leads.lead_author_id = {$CI->user->id} OR leads.lead_estimator = {$CI->user->id})");
        }

        return true;
    }

	function get_task_categories($only_active = true) {
		$this->db->select('category_id, category_name, category_color');
		$this->db->from('client_task_categories');
		if($only_active){
			$this->db->where('category_active', 1);
		}
		return $this->db->order_by('sort', 'ASC')->get()->result();
	}

	function insert($data) {
	    if (!is_array($data) || empty($data)) {
	        return false;
        }

        $this->db->insert($this->table, $data);
        $taskId = $this->db->insert_id();

        if ($taskId) {
            $task = $this->find_by_id($taskId);

            if ($task->task_status === 'new' && !empty($data['task_date']) && !empty($data['task_start']) && $task->task_category > 0) {
                // send push notification
                $min = (int) config_item('client_task_push_reminder_min') ?? 60;

                if ($min > 0) {
                    $delay = (new Carbon($data['task_date'] . ' ' . $data['task_start']))->subMinutes($min);
                    $now = new Carbon();

                    if ($now->lt($delay)) {
                        pushJob('notifications/task_push', [
                            'task_id' => $taskId,
                            'task_date' => $data['task_date'],
                            'task_start' => $data['task_start'],
                            'notificationTime' => $delay->toDateTimeString()
                        ], $delay->timestamp);
                    }
                }
            }
        }

        return $taskId;
    }

    function update($id, $data) {
        if (empty($id) || !is_array($data) || empty($data)) {
            return false;
        }

        $task = $this->find_by_id($id);

        if (!$task) {
            return false;
        }

        $this->db->where('task_id', $id);
        $this->db->update($this->table, $data);
        $updatedRow = $this->db->affected_rows();

        // add push job
        if ($updatedRow &&
            ((!empty($data['task_date']) && $data['task_date'] !== $task->task_date) ||
            (!empty($data['task_start']) && $data['task_start'] !== $task->task_start) ||
            $data['task_status'] === 'new')
        ) {
            $updatedTask = $this->find_by_id($id);

            if ($updatedTask->task_status !== 'new' || empty($updatedTask->task_date) || empty($updatedTask->task_start) || $updatedTask->task_category <= 0) {
                return true;
            }

            $min = (int) config_item('client_task_push_reminder_min') ?? 60;

            if ($min <= 0) {
                return true;
            }

//            $prevTaskDate = new Carbon($task->task_date . ' ' . $task->task_start);

            $taskDate = new Carbon($updatedTask->task_date . ' ' . $updatedTask->task_start);
            $minReminderTime = (new Carbon())->addMinutes($min);

            if ($minReminderTime->gte($taskDate)) {
                return true;
            }

            $delay = $taskDate->subMinutes($min);

            // add new job
            pushJob('notifications/task_push', [
                'task_id' => $updatedTask->task_id,
                'task_date' => $updatedTask->task_date,
                'task_start' => $updatedTask->task_start,
                'notificationTime' => $delay->toDateTimeString()
            ], $delay->timestamp);
        }

        return true;
    }
}
//end of file lead_model.php
