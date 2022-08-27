<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Report extends MX_Controller
{

	function __construct()
	{

		parent::__construct();
		$this->_title = SITE_NAME;
		$this->load->model('mdl_report', 'mdl_report');
	}

	
	function ajax_save_report()
	{
		
		$emp_id = $this->session->userdata('user_id');
		if(!$emp_id)
			$emp_id = $this->session->userdata('emp_user_id');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_user');
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_estimates');
		//$employee_login_details = $this->employee_login_model->get_last_login_details($emp_id)->row_array();
		//$emp = $this->mdl_employees->get_employee('emp_username', 'employee_id = ' . $emp_id, '')->row_array();
		//$user = $this->mdl_user->get_user('id', 'emailid = "' . $emp['emp_username'] . '"')->row_array();
		$estimates = $this->mdl_estimates->record_count(array(), array('user_id' => $emp_id, 'date_created >=' => strtotime(date('Y-m-d 00:00:00')))); 
		$tasks = $this->mdl_client_tasks->record_count(array(), array('task_user_id_updated' => $emp_id, 'task_date_updated >=' => date('Y-m-d')));
		
		//tasks/ajax_change_status
		$data['total_tasks'] = $tasks;
		$data['total_estimates'] = $estimates;
		$data['report_user_id'] = $emp_id;
		//$data['report_emp_id'] = $emp_id;
		$data['report_date'] = date('Y-m-d H:i:s');
		
		/*
		$data['est_appointments'] = $this->input->post('appointments');
		$data['est_free_estimates'] = $this->input->post('free_estimates');
		$data['est_no_go'] = $this->input->post('no_go');
		$data['est_already_done'] = $this->input->post('already_done');
		
		$data['task_construction_arb_report'] = $this->input->post('construction_arb_report');
		$data['task_regular_arb_report'] = $this->input->post('regular_arb_report');
		$data['task_exemption'] = $this->input->post('exemption');
		$data['task_payment_follow_up'] = $this->input->post('payment_follow_up');
		$data['task_assessment'] = $this->input->post('assessment');
		$data['task_meeting_with_client'] = $this->input->post('meeting_with_client');
		$data['task_secondary_visit'] = $this->input->post('secondary_visit');
		$data['task_quality_control'] = $this->input->post('quality_control');
		*/
		$report = $this->mdl_report->get_all(array('report_user_id' => $emp_id, 'report_date >' => date('Y-m-d 00:00:00'), 'report_date <' => date('Y-m-d 23:59:59')), FALSE, FALSE);
		//var_dump($report, $$this->db->last_query()); die;
		if(!empty($report))
		{	
			$data['report_comment'] = $this->input->post('comment');
			$estReport = $this->mdl_report->update($report[count($report) - 1]['report_id'], $data);
		}
		else
		{
			$data['report_comment'] = $this->input->post('comment');
			$estReport = $this->mdl_report->insert($data);
		}
		if(!$estReport)
			die(json_encode(json_encode($result)));
		$result['status'] = 'ok';
		die(json_encode($result));
	}
	function ajax_confirm_report()
	{
		$result['status'] = 'error';
		$report_id = $this->input->post('report_id');
		$report = $this->mdl_report->find_by_id($report_id);

		if($report)
		{
			$this->mdl_report->update($report_id, array('report_confirm' => 1));
			$result['status'] = 'ok';
		}
		die(json_encode($result));
	}

}
