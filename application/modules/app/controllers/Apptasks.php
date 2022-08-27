<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Apptasks extends APP_Controller
{

	function __construct()
	{
		parent::__construct();
        $this->load->model('mdl_client_tasks');
        $this->load->model('mdl_user');
        $this->load->model('mdl_leads');
        
        $this->load->library('form_validation');
	}
    
    public function get_task_categories()
	{
        $categories = $this->mdl_client_tasks->get_task_categories();
        $this->response([
            'status' => TRUE,
            'data' => ['categories' => $categories]
        ], 200);
    }

    public function create_task()
	{
	    $anyTimeNotExist = $this->notRequiredIfAnytimeExists();
        $this->form_validation->set_rules('category', 'Category', 'required');
        $this->form_validation->set_rules('assigned_id', 'Assigned User', 'required');
        $this->form_validation->set_rules('start_date', 'Start Date', 'trim' . ($anyTimeNotExist ? '|required' : ''));
        $this->form_validation->set_rules('start_time', 'Start Time', 'trim' . ($anyTimeNotExist ? '|required' : ''));
        $this->form_validation->set_rules('end_time', 'End Time', 'trim' . ($anyTimeNotExist ? '|required' : ''));
        $this->form_validation->set_rules('address', 'Address', 'trim|required');
        $this->form_validation->set_rules('city', 'City', 'trim|required');
        $this->form_validation->set_rules('zip', 'Zip/Postal', 'trim|required');
        $this->form_validation->set_rules('country', 'Country', 'trim|required');

        if ($this->form_validation->run() == true) {
            $data['task_client_id'] = strip_tags($this->input->post('client_id'));
			$data['task_lead_id'] = $this->input->post('lead_id') ? strip_tags($this->input->post('lead_id')) : null;
            $data['task_desc'] = $this->input->post('description') ?: null;
            $data['task_author_id'] = $this->user->id;
            $data['task_category'] = strip_tags($this->input->post('category'));
            $data['task_status'] = "new";
            $data['task_date_created'] = date('Y-m-d');

            $data['task_date'] = $anyTimeNotExist ? date("Y-m-d", strtotime($this->input->post('start_date'))) : date('Y-m-d');
            $data['task_start'] = $anyTimeNotExist ? date('H:i', (300 * round(strtotime($this->input->post('start_time')) / 300))) : null;
            $data['task_end'] = $anyTimeNotExist ? date('H:i', (300 * round(strtotime($this->input->post('end_time')) / 300))) : null;
        
            $data['task_address'] = strip_tags($this->input->post('address'));
            $data['task_city'] = strip_tags($this->input->post('city'));
            $data['task_state'] = strip_tags($this->input->post('state'));
            $data['task_zip'] = strip_tags($this->input->post('zip'));
            $data['task_country'] = strip_tags($this->input->post('country'));
            $data['task_latitude'] = strip_tags($this->input->post('lat'));
            $data['task_longitude'] = strip_tags($this->input->post('lng'));		
            //$data['is_anytime'] = (bool)$this->input->post('anytime');

            if(!$data['task_latitude'] || !$data['task_longitude'])
            {
                $coords = get_lat_lon($data['task_address'], $data['task_city'], $data['task_state'], $data['task_zip'], $data['task_country']);
                $data['task_latitude'] = $coords['lat'];
                $data['task_longitude'] = $coords['lon'];
            }
    
            $data['task_assigned_user'] = $this->input->post('assigned_id');
			
			if(!$data['task_client_id'] && $data['task_lead_id']){
				$lead_data = $this->mdl_leads->get_leads(['lead_id' => $data['task_lead_id']], '')->row_array();
				$data['task_client_id'] = $lead_data['client_id'];
			}
			
            $task_id = $this->mdl_client_tasks->insert($data);
    
            if ($task_id) {
    
                make_notes($data['task_client_id'], 'I just created a new task "' . $task_id . '" for the client.', 'system', 0);
                $this->response([
					'status' => TRUE,
					'data' => ['task_id' => $task_id]
				], 200);
            } else {
                $this->response([
					'status' => FALSE,
					'message' => 'Error creating a task'
				], 400);
            }
        } else {
            $this->response([
				'status' => FALSE,
				'message' => 'Validation errors',
				'data' => $this->form_validation->error_array()
			], 400);
        }

	}
    
    public function get_categorized_users() {
        $data['estimators'] = $this->mdl_user->get_categorized_users_app(['em.emp_field_estimator' => '1']);
        $data['field_workers'] = $this->mdl_user->get_categorized_users_app(['em.emp_feild_worker' => '1']);
        $data['other'] = $this->mdl_user->get_categorized_users_app(['em.emp_field_estimator' => '0', 'em.emp_feild_worker' => '0']);
        $this->response([
            'status' => TRUE,
            'data' => $data
        ], 200);
    }

    public function fetch($id){
	    if(empty($id) || !is_numeric($id)){
	        $this->errorResponse('Incorrect Task ID');
	        return;
        }

        $task = $this->mdl_client_tasks->getAppTasks(['task_id' => $id]);
	    if(!empty($task) && is_array($task) && !empty($task[0])){
	        $this->successResponse(['task' => $task[0]], 'Success!');
	        return;
        }

        $this->errorResponse('Task Not Found!');
        return;
    }

    private function notRequiredIfAnytimeExists()
    {
        if (isset(request()->anytime) &&  (bool)request()->anytime) {
           return false;
        }

        return true;
    }
}
