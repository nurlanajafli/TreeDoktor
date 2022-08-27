<?php

class Mdl_sms_messages extends JR_Model
{
	protected $_table = 'sms_messages';
	protected $primary_key = 'sms_id';
	
	
	function __construct()
	{
		parent::__construct();
	}
	function get_messages($where = array(), $limit = 200, $start = 0)
	{
		if(!empty($where))
			$this->db->where($where);
		
		$this->db->limit($limit, $start);
		$this->db->order_by('sms_date', 'desc');
		$query = $this->db->get($this->_table);
		return $query->result_array();
	}
}

//End model.
