<?php

class Mdl_worked_likes extends JR_Model
{
	protected $_table = 'employee_worked_likes';
	protected $primary_key = 'likes_id';


	public function __construct() {
		parent::__construct();
	}
	
	function get_likes_by($where = array())
	{
		$this->db->select('employee_worked_likes.*');
		if(isset($where) && !empty($where))
			$this->db->where($where);
		$data = $this->db->get($this->_table)->result();
		//var_dump($data); die;
		foreach($data as $key=>$val)
			$likes[$val->likes_user_id][$val->likes_type][$val->likes_id] = $val->likes_date;
		if(isset($likes))
			return $likes;
		else
			return $data;
	}
}
