<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * employee model
 * 
 */

class Mdl_employees extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'employees';
		$this->primary_key = 'employees.employee_id';
	}

	//insert data
	function insert_employee($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->table, $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}


	/*
	 * function get_employee
	 *
	 * param select, wheredata, ...
	 * returns rows or false
	 *
	 */

	function get_employee($select = '', $wdata = '', $order = '')
	{
		if ($select != '') {
			$this->db->select($select);
		}
		if ($wdata != '') {
			$this->db->where($wdata);
		}
		if ($order)
			$this->db->order_by($order);
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}


	/*
	 * function update_employee
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update_employee($update_data=null, $wdata=null)
	{
		if (!$update_data || !$wdata)
			return false;

		$this->db->where($wdata);
		$update = $this->db->update($this->table, $update_data);
		if ($this->db->affected_rows() > 0)
			return TRUE;
		
		return FALSE;
	}


	/*
	 * function delete employee
	 *
	 * param wheredata;
	 * returns bool;
	 *
	 */

	function delete_employee($data)
	{
		if ($data) {
			$this->db->where('id', $data);
			$this->db->delete($this->table);

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	
	function find_employee($id)
	{
		$this->db->select('*');
		$this->db->join('users', 'employees.emp_username = users.emailid', 'left');
		$this->db->where($this->primary_key, $id);
		$query = $this->db->get($this->table)->row();
		return $query;
	}
	
	function all_emp_without_user()
	{
		$sql = 'SELECT employee_id FROM employees 
				  WHERE employee_id NOT IN
				  (SELECT employee_id FROM `employees`
				  JOIN users ON emp_name = CONCAT(users.firstname, " ", users.lastname))';
		$query = $this->db->query($sql)->result_array();
		return $query;
		
	}
	function get_followup_variables($id)
	{
		$this->load->model('mdl_client_tasks');
		$this->load->model('mdl_user');

		$task = $this->mdl_client_tasks->get_all(['task_id' => $id], 1);
		$user = $this->mdl_user->get_usermeta(['users.id' => $task['ass_id']])->row();
		
		$date = date('h:i A', strtotime($task['task_date'] . ' ' . $task['task_start']));
		$fullDate =  $task['task_date'];
		/*if($date < 10)
			$time = '(between 8AM and 10AM)';
		elseif($date >= 10 && $date < 12)
			$time = '(between 10AM and 12AM)';
		elseif($date >= 12 && $date < 14)
			$time = '(between 12AM and 2PM)';
		elseif($date >= 14 && $date <= 17)
			$time = '(between 2PM and 5PM)';
		else
			$time = '(after 5PM)';*/
		
		
		//$task = $this->mdl_workorders->wo_find_by_id($event->event_wo_id);
		//echo '<pre>'; var_dump($estimate); die;
		$result['JOB_ADDRESS'] = $task['task_address'];
		$result['EMAIL'] = $task['cc_email'];
		$result['PHONE'] = $user->emp_phone;
		$result['NAME'] = $task['ass_firstname'] . ' ' . $task['ass_lastname'];
		$result['NO'] = '';
		$result['LEAD_NO'] = '';
		$result['ESTIMATE_NO'] = '';
		$result['INVOICE_NO'] = '';
		$result['ESTIMATOR_NAME'] = $task['client_name'];
		
		$result['TIME'] = $date;
		$result['TIME_AND_DATE'] = $fullDate . ' ' .  $date;
		$result['DATE'] = $fullDate;
		
		 
		$result['AMOUNT'] = '';
		$result['TOTAL_DUE'] = '';
		$result['CCLINK'] = '';

		return $result;
	}
}

//end of file employee_model.php
