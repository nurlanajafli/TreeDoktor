<?php

class Mdl_emp_productivity extends JR_Model
{
	protected $_table = 'employee_worked_productivity';
	protected $primary_key = 'prod_id';

	public $belongs_to = array('mdl_worked' => array('primary_key' => 'prod_worked_id', 'model' => 'mdl_worked'));

	public function __construct() {
		parent::__construct();
	}
	
	function get_producivity($wdata = array())
	{
		$this->db->select("employee_worked.*, employees.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, employee_worked_productivity.*, SUM(prod_per_mh) as sum, COUNT(employee_worked.worked_employee_id) as count", FALSE);
		$this->db->join('employee_worked_productivity', 'worked_id = prod_worked_id');
		$this->db->join('employees', 'employee_id = worked_employee_id');
		$this->db->join('users', 'users.id = worked_user_id', 'left');
		$this->db->where($wdata);
		$this->db->group_by('worked_employee_id');
		return $this->db->get('employee_worked')->result();
	}
}
