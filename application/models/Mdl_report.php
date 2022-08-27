<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * schedule model
 * created by: Ruslan Gleba
 * created on: Nov - 2014
 */

class Mdl_report extends MY_Model
{

	function __construct()
	{
		parent::__construct();


		$this->table = 'employee_reports';
		$this->primary_key = "report_id";
	}
	
	function get_all($wdata = array(), $order = '')
	{
		if (!empty($wdata)) {
			$this->db->where($wdata);
		}
		if ($order) {
			$this->db->order_by($order);
		}
		$this->db->select("employee_reports.*, users.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name", FALSE);
		//$this->db->join('employees', 'employees.employee_id = employee_reports.report_emp_id', 'left');
		$this->db->join('users', 'users.id = employee_reports.report_user_id', 'left');
		//$this->db->group_by('report_id');
		$query = $this->db->get($this->table)->result_array();
		return $query;
	}

}

//end of file report.php
