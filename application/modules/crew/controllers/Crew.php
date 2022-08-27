<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Crew extends MX_Controller
{

	/**
	 * User controller
	 */
	function __construct()
	{

		parent::__construct();

		if (!isUserLoggedIn()) {
			redirect('login');
		}

		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_crew', 'crew_model');
		$this->load->model('mdl_employees', 'employees_model');
		$this->load->model('mdl_schedule', 'schedule_model');
	}

	/*
	 * function index
	 * shows login or list of user if user logged in;
	 *
	 * param null
	 * returns html view
	 *
	 */

	public function index()
	{
		show_404();
		$this->crew_list();
	}

	/*
	 * function list user
	 * lists all users with user type = user
	 *
	 * param null
	 * returns html view
	 *
	 */

	public function crew_list()
	{

		$data['title'] = $this->_title . " - Crew Management";
		$data['page_title'] = "Crew Management";
		$data['page'] = "user/index";

		$data['crew_row'] = $this->crew_model->get_crewdata();

		$this->load->view('index', $data);
	}

	/*
	 * function crew add form
	 *
	 * param $id = null
	 * returns html view
	 *
	 */

	public function crew_add()
	{

		$data['title'] = $this->_title . " - Crew Management";
		$data['page_title'] = "Crew Management";
		$data['page'] = "crew/form";
		$data['menus'] = "crew/menu";
		$emp_select = "employee_id,emp_name,emp_position,emp_feild_worker,emp_climber,emp_driver";
		$emp_where = "";
		$data['emp_row'] = $this->employees_model->get_employee($emp_select, $emp_where);
		$this->load->view('form', $data);
	}

	/*
	 * function showform
	 * is called both for add and edit function
	 *
	 * param $id = null
	 * returns html view
	 *
	 */

	public function crew_update($id = '')
	{

		if ($id != '') {

			$data['title'] = $this->_title . " - Crew Management";
			$data['page_title'] = "Crew Management";
			$data['page'] = "crew/form";
			$data['menus'] = "crew/menu";
			$data['edit'] = $id;
			$wdata['crew_id'] = $id;
			$data['crew_row'] = $this->crew_model->get_crewdata($wdata)->row();
			$emp_select = "employee_id,emp_name,emp_position,emp_feild_worker,emp_climber,emp_driver";
			$emp_where = "";
			$data['emp_row'] = $this->employees_model->get_employee($emp_select, $emp_where);
			$data['crew_emp_mem'] = $this->schedule_model->get_crew_member('', $wdata);
			$this->load->view('form', $data);
		} else {
			show_404();
		}
	}

	/*
	 * function save
	 * inserts or updated the user details
	 *
	 * param $id = null
	 * returns bool or error on failure
	 *
	 */

	public function save($id = '')
	{

		/* validation */
		$this->load->library('form_validation');
		$this->form_validation->set_rules('crew_name', 'Crew Name', 'required');
		$this->form_validation->set_rules('crew_color', 'Crew Color', 'required');
		$this->form_validation->set_rules('employee_id', 'Employee', 'required');
		//print_r($this->input->post());
		if ($this->form_validation->run() == FALSE) {
			//echo "validation false";
			if ($id != '')
				$this->crew_update($id);
			else
				$this->crew_add();
		} else {
			//echo "validation OK";
			$now_gmt_time = now(); // GMT time
			$now_time = mdate('%Y-%m-%d %H:%i:%s', $now_gmt_time);
			$data['crew_name'] = $this->input->post('crew_name');
			$data['crew_color'] = $this->input->post('crew_color');
			$emp_id = $this->input->post('employee_id');

			// die();
			if ($id == '') {
				//echo "add";
				$data['create_date'] = $now_time;

				$insert = $this->crew_model->insert_crew($data);
				if ($insert) {
					if (!empty($emp_id)) {
						foreach ($emp_id as $emp_id) {
							$sc_data['crew_id'] = $insert;
							$sc_data['employee_id'] = $emp_id;
							$insert_res = $this->schedule_model->insert_crew_member($sc_data);
						}
					}
					$mess = message('success', 'Crew Added!');
					$this->session->set_flashdata('user_message', $mess);
					redirect('crew/crew_list');
				}
			} else {
				//echo "edit";
				$data['updated_on'] = $now_time;
				$this->crew_model->update_crew($data, array('crew_id' => $id));
				/* $res= $this->schedule_model->get_crew_member('',array('employee_id'=>$emp_id,'crew_id'=>$data['crew_id']));
					if($res && $res->num_rows() > 0){}else{
					 $insert = $this->schedule_model->insert_crew_member($data);
					}*/
				$delete = $this->schedule_model->delete_crew_member(array('crew_id' => $id));
				if (!empty($emp_id)) {
					foreach ($emp_id as $emp_id) {
						$sc_data['crew_id'] = $id;
						$sc_data['employee_id'] = $emp_id;
						$insert = $this->schedule_model->insert_crew_member($sc_data);
					}
				}
				$mess = message('success', 'Crew Updated!');
				$this->session->set_flashdata('user_message', $mess);
				redirect('crew/crew_list');
			}
		}
	}

	/*
	 * function delete
	 * deletes the user
	 *
	 * param $id
	 * returns null / redirects the user
	 *
	 */

	public function crew_delete($id)
	{

		if ($id) {
			$delete = $this->crew_model->delete_crew($id);
			if ($delete) {
				$mess = message('success', 'Crew Deleted!');
				$this->session->set_flashdata('user_message', $mess);

				redirect('crew/crew_list');
			}
		}
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
