<?php
class Mdl_numbers extends JR_Model
{
	protected $_table = 'ext_numbers';
	protected $primary_key = 'extention_id';
	
	public function __construct() {
		parent::__construct();
	}
	
	function get_emergency($id = null)
	{
		$this->_database->from($this->_table);
		$this->_database->join('users', 'users.id = ext_numbers.extention_user_id', 'left');
		if($id)
			$this->_database->where($this->primary_key, $id);
		$this->_database->where('extention_emergency', 1);
		$this->_database->order_by('extention_order');
		$query = $this->_database->get()->result();
		return $query;
	}
	
	function get_extention_numbers($where = array())
	{
		$this->db->select('ext_numbers.*, users.*, employees.emp_phone');
		$this->_database->from($this->_table);
		$this->_database->join('users', 'users.id = ext_numbers.extention_user_id', 'left');
		$this->_database->join('employees', 'users.id = employees.emp_user_id', 'left');
		if(!empty($where))
			$this->_database->where($where);
		$query = $this->_database->get()->result();
		return $query;
		
	}
	
	function get_extention_number($where)
	{
		$this->db->select('ext_numbers.*, users.*, employees.emp_phone');
		$this->_database->from($this->_table);
		$this->_database->join('users', 'users.id = ext_numbers.extention_user_id', 'left');
		$this->_database->join('employees', 'users.id = employees.emp_user_id', 'left');
		if(!empty($where))
			$this->_database->where($where);
		$query = $this->_database->get()->row();
		return $query;
	}
}
