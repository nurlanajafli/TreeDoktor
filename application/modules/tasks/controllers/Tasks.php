<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use application\modules\clients\models\Client;
use application\modules\tasks\models\Task;
use Illuminate\Http\JsonResponse;

class Tasks extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Tasks Controller;
//*************
//*******************************************************************************************************************	

	function __construct()
	{

		parent::__construct();


		if (!isUserLoggedIn()) {
			redirect('login');
		}
		if ($this->session->userdata('TSKS') === '0')
			redirect('dashboard');

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_client_tasks', 'mdl_client_tasks');
		$this->load->model('mdl_categories');
		//$this->load->model('administration/mdl_categories', 'mdl_categories');
		$this->load->model('mdl_user', 'mdl_users');
		$this->load->model('mdl_clients', 'mdl_clients');
		//$this->load->model('reports/mdl_reports', 'mdl_reports');

		//Load Libraries
		$this->load->library('form_validation');
		$this->load->library('googlemaps');
	}

//*******************************************************************************************************************
//*************
//*************																							Index Function;
//*************
//*******************************************************************************************************************	

	public function index()
	{
        if (request()->ajax()) {
            $this->ajax_search_tasks();
            return;
        }
		$this->load->view("index", [
            'title' => $this->_title . ' - Tasks'
        ]);
	}

    /**
     * Ajax Method get task data for task list page
     */
    private function ajax_search_tasks()
    {
        $request = request();

        $where[]['task_status'] = 'new';
        if($this->session->userdata('TSKS') === '2') {
            $where[]['task_author_id'] = $this->session->userdata['user_id'];
        }

        $taskQuery = (new Task())->getTasksQuery($where)->withoutExpiredTaskByDay();
        $totalQueryTasks = Task::countAggregate($taskQuery);
        $tasks = $taskQuery->offset($request->start)->limit($request->length)->orderBy('task_start_date', 'desc')->get();

        return $this->response([
            'data' => (new JsonResponse($tasks)),
            'recordsTotal' => $totalQueryTasks,
            'recordsFiltered' => $totalQueryTasks,
        ]);
    }

    /**
     * @param $id
     */
    public function ajax_get_modal_form($id)
    {
        $task = (new Task())->getTasksQuery([
            [Task::tableName() . '.task_id' => $id]
        ])->first();

        $this->load->view("modal_form", [
            'task' => $task
        ]);
    }

//*******************************************************************************************************************
//*************
//*************																					Tasks_Mapper Function;
//*************
//*******************************************************************************************************************	

	public function tasks_mapper()
	{

		//Page Presets
		$data['title'] = $this->_title . ' - Tasks';
		$data['menu_tasks'] = "active";

		//Set the map:
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);

		//Get required leads data:
		$wdata['task_status'] = 'new';
		$wdata['task_category > '] = 0;
		//$wdata['task_date IS NOT NULL'] = NULL;
		//$wdata['employees.emp_field_estimator'] = '1';
		if($this->session->userdata('TSKS') === '2')
			$wdata['task_author_id'] = $this->session->userdata['user_id'];
		$arr = $this->mdl_client_tasks->get_all($wdata, false, false, true, true);
		//echo $this->db->last_query();die;
        //$arr = ($arr && $arr->num_rows()) ? $arr->result_array() : array();
		$this->load->model('mdl_user', 'mdl_users');
		
		$usersData = [];
		$users = $this->mdl_users->get_usermeta(array('emp_status' => 'current'));
		if($users)
			$usersData = $users->result_array();
		
		$users = array();
		
		foreach ($usersData as $user) {
			$users[$user['id']] = $user;
		}
		//Creating the markers for tasks:
		foreach ($arr as $row) {
			//echo '<pre>'; var_dump($row); die;
			$marker_style = NULL;
			$marker_date['client_id'] = $row['task_client_id'];
			$marker_date['name'] = $row['client_name'];
			$marker_date['category_name'] = $row['category_name'];
			$marker_date['phone'] = numberTo($row['cc_phone']);
			$marker_date['street'] = $row['task_address'];
			$marker_date['city'] = $row['task_city'];
			$marker_date['status'] = $row['task_status'];
			$marker_date['address'] = $marker_date['street'] . ", " . $marker_date['city'];
			$marker_date['task_date'] = getDateTimeWithDate($row['task_date_created'], 'Y-m-d');
			
			 
			$marker_date['task_schedule_date'] = $marker_date['task_schedule_start'] = $marker_date['task_schedule_end'] = NULL;
			if($row['task_date'])
			{
				$marker_date['task_schedule_date'] = date('m/d', strtotime($row['task_date']));
				$marker_date['task_schedule_start'] = getTimeWithDate($row['task_start'], 'H:i:s');
				$marker_date['task_schedule_end'] = getTimeWithDate($row['task_end'], 'H:i:s');
			}
			
			
			$marker_date['task_body_dirty'] = $row['task_desc'];
			$marker_date['task_body']= preg_replace("/[^\p{L}\p{N}]/u", ' ', $marker_date['task_body_dirty']);
			$marker_date['task_id'] = $row['task_id'];
			$marker_date['task_name_creator'] = $row['firstname'] . ' ' . $row['lastname'];
			$marker_date['task_assigned'] = $row['ass_firstname'] . ' ' . $row['ass_lastname'];

			//Base Marker information:
			$marker_date['marker_link'] = base_url($marker_date['client_id']);
			$marker_content = str_replace(array("\n", "\r"), '', $this->load->view('marker', $marker_date, TRUE));
			if ($row['ass_color'])
				$marker_style = task_pin($row['ass_color'], ($marker_date['task_schedule_date']) ? $marker_date['task_schedule_date'] : '&#9899;', FALSE, '#000');
			else
				$marker_style = task_pin('#00E64D', ($marker_date['task_schedule_date']) ? $marker_date['task_schedule_date'] : '&#9899;', FALSE, '#000'); 

			$marker = array();
			if($row['task_latitude'] && $row['task_longitude'])// && $row['task_longitude'] < 0 BY RG 22.04.2020
				$marker['position'] = $row['task_latitude'] . ',' . $row['task_longitude'];
			else
				$marker['position'] = $row['task_address'] . ',' . $row['task_city'] . ',' . $row['task_state'] . ','.config_item('office_country').','.$row['task_zip'];
			
			$marker['infowindow_content'] = $marker_content;
			$marker['icon'] = $marker_style;
			$this->googlemaps->add_marker($marker);
		}

		$polygons = config_item('tasks_polygons') ? config_item('tasks_polygons') : [];

        foreach ($polygons as $polygon) {
            $this->googlemaps->add_polygon($polygon);
		}

		$data['map'] = $this->googlemaps->create_map();
		
		//$data['users'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes'))->result();
		$this->load->view('map', $data);

		//var_dump($arr);

	}

//*******************************************************************************************************************
//*************
//*************																				Create tasks function;
//*************
//*******************************************************************************************************************


	public function create_task()
	{
		$data['task_client_id'] = strip_tags($this->input->post('task_client_id'));
		$data['task_desc'] = nl2br($this->input->post('new_task_desc'));
		$data['task_author_id'] = $this->session->userdata['user_id'];
		$data['task_category'] = strip_tags($this->input->post('new_task_cat'));
		$data['task_lead_id'] = strip_tags($this->input->post('new_task_lead'));
		$data['task_status'] = strip_tags($this->input->post('new_task_status'));
		$data['task_date_created'] = date('Y-m-d');
        $data['task_lead_id'] = intval($this->input->post('new_task_lead')) ? intval($this->input->post('new_task_lead')) : null;

		if($this->input->post('scheduled'))
		{
			$data['task_date'] = date("Y-m-d", strtotime($this->input->post('from')));
			$data['task_start'] = $this->round_time($this->input->post('start_time'));
			$data['task_end'] = $this->round_time($this->input->post('end_time'));
		}
		if ($this->input->post('new_task_add')) {
			$data['task_address'] = strip_tags($this->input->post('new_task_address'));
			$data['task_city'] = strip_tags($this->input->post('new_task_city'));
			$data['task_state'] = strip_tags($this->input->post('new_task_state'));
			$data['task_zip'] = strip_tags($this->input->post('new_task_zip'));
			$data['task_country'] = strip_tags($this->input->post('new_task_country'));
			$data['task_latitude'] = strip_tags($this->input->post('new_task_lat'));
			$data['task_longitude'] = strip_tags($this->input->post('new_task_lon'));
		} else {
			$client = $this->mdl_clients->find_by_id($data['task_client_id']);
			$data['task_address'] = $client->client_address;
			$data['task_city'] = $client->client_city;
			$data['task_state'] = $client->client_state;
			$data['task_zip'] = $client->client_zip;
			$data['task_country'] = $client->client_country;
			$data['task_latitude'] = $client->client_lat;
			$data['task_longitude'] = $client->client_lng;
		}
		
		if(!$data['task_latitude'] || !$data['task_longitude'])
		{
			$coords = get_lat_lon($data['task_address'], $data['task_city'], $data['task_state'], $data['task_zip'], $data['task_country']);
			$data['task_latitude'] = $coords['lat'];
			$data['task_longitude'] = $coords['lon'];
		}

		$data['task_assigned_user'] = $this->input->post('new_task_assigned_user');

		$data['task_id'] = $this->mdl_client_tasks->insert($data);

		$data['is_client_sms'] = $this->input->post('is_client_sms');
		$data['is_client_email'] = $this->input->post('is_client_email');
		$data['is_estimator_sms'] = $this->input->post('is_estimator_sms');
		$data['is_estimator_email'] = $this->input->post('is_estimator_email');

		$data['country'] = element('new_client_country', $data, config_item('office_country'));
		$address_array = [element('task_address', $data, ''), element('task_city', $data, ''), element('task_state', $data, ''), element('task_zip', $data, ''), $data['country']];
		$address = implode(', ', array_diff($address_array, array('', NULL, false)));
		$data['formatted_address'] = element('formatted_address', $data, $address);

		$this->load->model('mdl_user');
		$this->mdl_client_tasks->scheduled_notification($data);

		if ($data['task_id']) {

				if (make_notes($data['task_client_id'], 'I just created a new task "' . $data['task_id'] . '" for the client.', 'system', 0)) {
					//All done. All good. Redirecting with success message.
					$link = base_url($data['task_client_id']);
					$mess = message('success', 'New Task added successfuly!');
					$this->session->set_flashdata('user_message', $mess);
					redirect($link);
				}

		}

	}

// End. Create_task;

//*******************************************************************************************************************
//*************
//*************																					Edit task function;
//*************
//*******************************************************************************************************************


	public function edit($task_id)
	{
		if (!isset($task_id)) { // NB: Set to redirect to index if variable is null or not set;
			show_404();
		} else {//
			$data['title'] = $this->_title . ' - Tasks';
			$data['menu_tasks'] = "active";
			$data['task_categories'] = $this->mdl_categories->get_all('category_active = 1');
			//Get lead informations - using common function from MY_Models;
			$id = $task_id;
			$data['task'] = $this->mdl_client_tasks->get_all(array('task_id' => $id), 1);
			if(!$data['task']) {
                show_404();
            }
			$data['active_users'] = $this->mdl_users->get_usermeta(array('active_status' => 'yes','emp_status' => 'current', 'users.id <>' => 0))->result();
			//get client id and information
			$id = $data['task']['task_client_id'];
			$data['client_data'] = $this->mdl_clients->find_by_id($id);
			$data['client_contacts'] = $this->mdl_clients->get_client_contacts(array('cc_client_id' => $id));
			if($id != 0){
                $ClientModel = Client::with('tags')->find($id);
            $data['client_tags'] = ($ClientModel)?$ClientModel->tags->map(function($tag) {
                    return [ 'id'=>$tag->tag_id, 'text'=>$tag->name];
                }):[];
            }//Get client contacts
			$client_id = 0;
			$name = null;
			if($data['client_data']){
				$client_id = $data['client_data']->client_id;
				$name = $data['client_data']->client_name;	
			}
			
			$street = $data['task']['task_address'];
			
			$city = $data['task']['task_city'];
			$address = $street . "+" . $city;
				//Set the map:
			$config['center'] = config_item('map_center');
			$config['zoom'] = '10';
			$data['user_id'] = $this->session->userdata['user_id'];
			$this->googlemaps->initialize($config);
			$marker = array();
			$marker['position'] = $address;
			$marker['icon'] = mappin_svg('#FD7567', '&#9899;', FALSE, '#000'); 
			if(isset($data['task']['category_color'])) 
				$marker['icon'] = mappin_svg('#6991FD', '&#9899;', FALSE, '#000');
				
			
			$this->googlemaps->add_marker($marker);
			$data['address'] = $address;
			$data['map'] = $this->googlemaps->create_map();
			//load view
			$this->load->view("details", $data);
		}
	}

// End. edit(lead_id);
//*******************************************************************************************************************
//*************
//*************																					Update leads function;
//*************
//*******************************************************************************************************************	

	public function update_task()
	{
		$task_id = strip_tags($this->input->post('task_id'));
		
		$client_id = strip_tags($this->input->post('client_id'));
		//var_dump($_POST['set_lead_status']); die;
        $old_status = $this->mdl_client_tasks->get_all(array('task_id' => $task_id), 1);

		//if ($old_status->task_status != $this->input->post('task_status')) {
			//$status = array('status_type' => 'task', 'status_item_id' => $task_id, 'status_value' => $this->input->post('task_status'), 'status_date' => time());
			//$this->mdl_tasks->status_log($status);
		//} Если да, добавить статус "TASK"

		//Task Details;
		
		$data['task_date'] = $data['task_start'] = $data['task_end'] = NULL;
		if($this->input->post('scheduled'))
		{
		    $taskDate = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            if($taskDate) {
                $data['task_date'] = $taskDate->format('Y-m-d');
                $data['task_start'] = $this->round_time($this->input->post('start_time'));
                $data['task_end'] = $this->round_time($this->input->post('end_time'));
            }
		}
		$data['task_status'] = strip_tags($this->input->post('task_status'));
		$data['task_category'] = strip_tags($this->input->post('task_category'));
		$data['task_desc'] = strip_tags($this->input->post('task_desc'));

		$data['task_no_map'] = 0;
		$data['task_assigned_user'] = $this->input->post('task_assigned_user');

        if($this->input->post('new_address') !== false && $this->input->post('new_lat') !== false && $this->input->post('new_lon') !== false) {
            $data['task_address'] = strip_tags($this->input->post('new_address'));
            $data['task_city'] = strip_tags($this->input->post('new_city'));
            $data['task_state'] = strip_tags($this->input->post('new_state'));
            $data['task_zip'] = strip_tags($this->input->post('new_zip'));
            $data['task_latitude'] = strip_tags($this->input->post('new_lat'));
            $data['task_longitude'] = strip_tags($this->input->post('new_lon'));
            $data['task_country'] = strip_tags($this->input->post('new_country'));
        }
        $text = '';
        if($data['task_category'] != $old_status['task_category']) {
            $this->load->model('mdl_categories');
            $new_category = $this->mdl_categories->find_by_id($data['task_category']);
            $text .= '<br>Task category changed to: "' . $new_category->category_name . '"';
        }
        if($data['task_end'] != $old_status['task_end'])
            $text .= '<br>Date was changed to: ' . $data['task_start'] . ' - ' . $data['task_end'] . ' ' . $data['task_date'];
        if($data['task_status'] != $old_status['task_status'])
            $text .= '<br>Status was changed to: ' . $data['task_status'];
        if($data['task_desc'] != $old_status['task_desc'])
            $text .= '<br>Description: ' . $data['task_desc'];
        if(isset($data['task_address']) && $data['task_address'] != $old_status['task_address'])
            $text .= '<br>Task address changed: ' . $data['task_address'];
        if($data['task_assigned_user'] != $old_status['task_assigned_user']) {
            $user = $this->mdl_users->get_user_name($old_status['task_assigned_user'])->row();
            $text .= '<br>Task assigned to: ' . $user->firstname . ' ' . $user->lastname;
        }
		if ($this->mdl_client_tasks->update($task_id, $data)) {
			if (make_notes($client_id, 'Client Task "' . $task_id . '" updated' . $text, 'system', 0)) {

				$link = base_url($client_id);
				$mess = message('success', 'Task was updated!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($link);
			}
		} else {


			$link = ('tasks/edit/' . $task_id);
			$mess = message('alert', 'Task was not updated!');
			$this->session->set_flashdata('user_message', $mess);
			redirect($link);
		}
	}


//Update client profile.

//*******************************************************************************************************************
//*************
//*************																					Delete task function;
//*************
//*******************************************************************************************************************


	public function delete($task_id)
	{
		/*if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('TM') != 1) {
			show_404();
		} else {*/

			//Get Client_id:
			$id = $task_id;
            $task = $this->mdl_client_tasks->find_by_id($id);
            if(!$task)
                redirect('/');
			$client_id = isset($task->task_client_id) ? $task->task_client_id : NULL;

			If ($this->mdl_client_tasks->delete($task_id)) {

				$link = base_url($client_id);
                make_notes($client_id, 'I just deleted the task "' . $task_id . '"', 'system', 0);
				$mess = message('success', 'Task was succefully deleted!');
				$this->session->set_flashdata('user_message', $mess);
				redirect($link);
			}
		//}
	}

    function ajax_change_status()
	{
		$result['status'] = 'error';
		$task_id = $this->input->post('id');
		if(!$task_id)
			$result['msg'] = 'Incorrect task ID. Please try again later.';
		$old_status = $this->mdl_client_tasks->find_by_id($task_id);
		if(!$old_status)
			$result['msg'] = 'Incorrect task ID. Please try again later.';
		$description = $this->input->post('text');
		$data['task_date_updated'] = date('Y-m-d');
		$data['task_user_id_updated'] = $this->session->userdata('user_id');
		$data['task_desc'] = $description;
		$data['task_status'] = $this->input->post('status');

		if($old_status && $this->mdl_client_tasks->update($task_id, $data))
		{
		    $noteClientId = 0;
		    if ($old_status->task_client_id)
		        $noteClientId = $old_status->task_client_id;
            $text = 'Task status for "'. $task_id .'" was changed from: "' . $old_status->task_status . '" to: "' . $data['task_status'] . '".';
            if($description)
                $text .= ' Description: "' . $description . '"';
			if (make_notes($noteClientId, $text, 'system', 0)) {
                $result['task'] = Task::with('user.employee', 'client', 'owner', 'category')->find($task_id);
				$result['status'] = 'ok';
				$result['msg'] = 'Done! Status changed!';
                return $this->response($result, 200);
			}
		}
        $result['msg'] = 'Sorry. Update failed!';
        return $this->response($result, 400);
	}
	/***********************TASK CATEGORIES**********************/
    function ajax_change_category()
    {
        $task_id = $this->input->post('task_id');
        if(!$task_id)
            $result['msg'] = 'Incorrect task ID. Please try again later.';

        $old_category = $this->mdl_client_tasks->find_by_id($task_id);

        if(!$old_category)
            $result['msg'] = 'Incorrect task ID. Please try again later.';

        $data['task_date_updated'] = date('Y-m-d');
        $data['task_user_id_updated'] = $this->session->userdata('user_id');
        $data['task_category'] = $this->input->post('task_category');

        if($old_category && $this->mdl_client_tasks->update($task_id, $data))
        {
            $noteClientId = 0;
            if ($old_category->task_client_id)
                $noteClientId = $old_category->task_client_id;

            $text = 'Task category for "'
                . $task_id .'" was changed from: "'
                . $old_category->task_category . '" to: "'
                . $data['task_category'] . '".';

            if (make_notes($noteClientId, $text, 'system', 0)) {
                $result['task'] = Task::with('user.employee', 'client', 'owner', 'category')->find($task_id);
                $result['status'] = 'ok';
                $result['msg'] = 'Done! Category changed!';
                return $this->response($result, 200);
            }
        }
        $result['msg'] = 'Sorry. Update failed!';
        return $this->response($result, 400);
    }

	function tasks_categories()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('TM') != 1) {
			show_404();
		}
		$data['title'] = "Task Statuses";
		
		
		$data['categories'] = $this->mdl_categories->get_all();
		$this->load->view('index_categories', $data);
	}
	
	function ajax_save_category()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('TM') != 1) {
			show_404();
		}
		
		$id = $this->input->post('category_id');
		$data['category_name'] = strip_tags($this->input->post('category_name', TRUE));
		$data['category_color'] = strip_tags($this->input->post('category_color', TRUE));
		
		if ($id != '') {
			$this->mdl_categories->update_category($id, $data);
			die(json_encode(array('status' => 'ok')));
		}
		$this->mdl_categories->insert_category($data);	
		die(json_encode(array('status' => 'ok')));
	}
	
	function ajax_delete_category()
	{
		if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('TM') != 1) {
			show_404();
		}
		$id = $this->input->post('category_id');
		$status = $this->input->post('status');
		if ($id != '')
		{
			$this->mdl_categories->update_category($id, array('category_active' => $status));

			die(json_encode(array('status' => 'ok')));
		}
		die(json_encode(array('status' => 'error')));
	}

	function round_time($time)
	{
		//300 - round in seconds
		//$time - example 11:17
		return date('H:i', (300 * round(strtotime($time) / 300)));
	}
	/***********************END TASK CATEGORIES**********************/

    function appointment_sms_form() {
        $this->load->helper('user_tasks_helper');
        $task = $this->mdl_client_tasks->find_by_id($this->input->post('id'));
        $templateId = $this->input->post('sms-id') ? $this->input->post('sms-id') : NULL;

        if(!$task) {
            return $this->response([
                'status' => false,
                'message' => 'Incorrect Task'
            ]);
        }

        $address_array = [$task->task_address, $task->task_city, $task->task_state, $task->task_zip];
        $address = implode(', ', array_diff($address_array, array('', NULL, false)));
        $task->formatted_address = $address;
        $this->load->model('mdl_sms');
        $this->load->model('mdl_contacts');
        $this->load->model('mdl_user');
        $this->load->helper('user_tasks');
        $this->load->driver('messages');

        $client_sms_tpl = (object) $this->mdl_sms->get_by(['system_label' => 'client_schedule_appointment']);
        if($templateId)
            $client_sms_tpl = (object) $this->mdl_sms->get($templateId);

        $estimator = $this->mdl_user->get_usermeta(['users.id'=>$task->task_assigned_user])->result()[0];

        $contacts = $this->mdl_clients->get_client_contacts(['cc_client_id'=>$task->task_client_id, 'cc_print'=>1]);
        $client = $this->mdl_clients->find_by_id($task->task_client_id);
        $client_name = $client_email = $client_phone = '';

        $brand_id = get_brand_id([], $client);
        if(!empty($contacts)){
            $client_name = $contacts[0]['cc_name'];
            $client_phone = $contacts[0]['cc_phone_clean'];
        }
        $task->brand_id = $brand_id;
        $sms = [];
        $sms_footer = $this->load->view('clients/appointment/sms_footer_tpl', $task, true);

        if (config_item('messenger') !== null) {
            if($client_sms_tpl->system_label == 'client_schedule_appointment'){
                $sms['text'] = $client_sms_tpl->sms_text;
                $sms['text'] = item_branding($brand_id, $sms['text']);
                $sms['text'] = str_replace('[NAME]', $client_name, $sms['text']);
                $sms['text'] = str_replace('[ESTIMATOR_NAME]', $estimator->firstname.' '.$estimator->lastname, $sms['text']);
                $sms['text'] = str_replace('[ADDRESS]', $task->task_address, $sms['text']);
                $sms['text'] = str_replace('{address}', $task->formatted_address, $sms['text']).$sms_footer;
                $sms['number'] = $client_phone;
            }

            if($client_sms_tpl->system_label == 'estimator_schedule_appointment'){
                $sms['text'] = $client_sms_tpl->sms_text;
                $sms['client']['text'] = $client_sms_tpl->sms_text;
                $sms['text'] = item_branding($brand_id, $sms['client']['text']);
                $sms['text'] = str_replace('[NAME]', $client_name, $sms['text']);
                $sms['text'] = str_replace('[ESTIMATOR_NAME]', $estimator->firstname.' '.$estimator->lastname, $sms['text']);
                $sms['text'] = str_replace('[ADDRESS]', $task->task_address, $sms['text']);
                $sms['text'] = str_replace('{address}', $task->formatted_address, $sms['text']).$sms_footer;
                $sms['number'] = substr($estimator->emp_phone, 0, config_item('phone_clean_length'));
            }

            // TODO: probably need to activate sms send?
//            $sendResult = $this->messages->send($sms['number'], $sms['text']);

            /*if (!isset($sendResult[0])) {
                return $this->response([
                    'status' => false,
                    'message' => 'Message not sent. Unexpected error',
                    'body' => $sms['text']
                ]);
            }
            if(array_key_exists('error', $sendResult[0])) {
                return $this->response([
                    'status' => false,
                    'message' => $sendResult[0]['error'],
                    'body' => $sms['text']
                ]);
            }*/
        }

        $result = [
            'body' => $sms['text'] ?? '',
            'phone' => $sms['number'] ?? false,
            'client_id' => $task->task_client_id,
            'status' => true,
        ];

        $this->response($result);
    }
// End delete task;

    /**
     * Client task categories model sortable method
     */
    public function ajax_sort_client_task_categories() {
        if ($this->session->userdata('user_type') != "admin" && $this->session->userdata('CL') != 1) {
            show_404();
        }
        $data = $this->input->post('data');
        if (empty($data)) {
            die(json_encode(array('status' => 'error')));
        }

        foreach ($data as $key => $val) {
            if ($val) {
                $updateBatch[] = array('category_id' => $key, 'sort' => (int) $val);
            }
        }

        if (empty($updateBatch) || empty($updateBatch)) {
            die(json_encode(array('status' => 'error')));
        }

        if ($this->mdl_categories->update_priority($updateBatch)) {
            die(json_encode(array('status' => 'ok')));
        }

        die(json_encode(array('status' => 'error')));
    }

    function ajax_change_assigned_user()
    {
        $task_id = $this->input->post('task_id');
        if(!$task_id)
            $result['msg'] = 'Incorrect task ID. Please try again later.';

        $old_assigner = $this->mdl_client_tasks->find_by_id($task_id);

        if(!$old_assigner)
            $result['msg'] = 'Incorrect task ID. Please try again later.';

        $data['task_date_updated'] = date('Y-m-d');
        $data['task_user_id_updated'] = $this->session->userdata('user_id');
        $data['task_assigned_user'] = $this->input->post('task_assigned_user');

        if($old_assigner && $this->mdl_client_tasks->update($task_id, $data))
        {
            $noteClientId = 0;
            if ($old_assigner->task_client_id)
                $noteClientId = $old_assigner->task_client_id;

            $text = 'Task category for "'
                . $task_id .'" was changed from: "'
                . $old_assigner->task_assigned_user . '" to: "'
                . $data['task_assigned_user'] . '".';

            if (make_notes($noteClientId, $text, 'system', 0)) {
                $result['task'] = Task::with('user.employee', 'client', 'owner', 'category')->find($task_id);
                $result['status'] = 'ok';
                $result['msg'] = 'Done! Assigned user changed!';
                return $this->response($result, 200);
            }
        }

        $result['msg'] = 'Sorry. Update failed!';
        return $this->response($result, 400);
    }

    function ajax_change_schedule_date()
    {
        $task_id = $this->input->post('task_id');
        if(!$task_id)
            $result['msg'] = 'Incorrect task ID. Please try again later.';

        $old_schedule = $this->mdl_client_tasks->find_by_id($task_id);
        if(!$old_schedule)
            $result['msg'] = 'Incorrect task ID. Please try again later.';

        $data['task_date_updated'] = date('Y-m-d');
        $data['task_user_id_updated'] = $this->session->userdata('user_id');

        $data['task_date'] = $this->input->post('task_date');
        $data['task_start'] = $this->input->post('task_start');
        $data['task_end'] = $this->input->post('task_end');

        if($old_schedule && $this->mdl_client_tasks->update($task_id, $data))
        {
            $noteClientId = 0;
            if ($old_schedule->task_client_id)
                $noteClientId = $old_schedule->task_client_id;

            $text = 'Task schedule for "'
                . $task_id .'" was changed from: "'
                . $old_schedule->task_date . ' ' . $old_schedule->task_start . '-' . $old_schedule->task_date . '" to: "'
                . $data['task_date'] . ' ' . $data['task_start'] . '-' . $data['task_end'] .'".';

            if (make_notes($noteClientId, $text, 'system', 0)) {
                $result['task'] = Task::with('user.employee', 'client', 'owner', 'category')->find($task_id);
                $result['status'] = 'ok';
                $result['msg'] = 'Done! Assigned user changed!';
                return $this->response($result, 200);
            }
        }

        $result['msg'] = 'Sorry. Update failed!';
        return $this->response($result, 400);
    }
}
//end of file tasks.php
