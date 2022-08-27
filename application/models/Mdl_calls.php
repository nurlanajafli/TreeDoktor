<?php

class Mdl_calls extends JR_Model
{
	protected $_table = 'clients_calls';
	protected $primary_key = 'call_id';

	function __construct()
	{
		parent::__construct();
	}

	function get_calls($where = array(), $limit = 200, $start = 0, $client_voice = FALSE)
	{
		
		$this->db->where($where);
		if($client_voice)
			$this->db->where('call_voice IS NOT NULL');
		$this->db->join('users', 'users.id = clients_calls.call_user_id', 'left');
		$this->db->join('clients', 'clients.client_id = clients_calls.call_client_id', 'left');
		
		
		$this->db->limit($limit, $start);
		$this->db->order_by('call_date', 'desc');
		$query = $this->db->get($this->_table);
		return $query->result_array();
	}
	
	function get_calls_for_stats($where = array(), $limit = 200, $start = 0)
	{
		
		$this->db->select('SUM(call_duration) as income_duration, COUNT(call_id) as count_income_calls, call_user_id as uid');
        if(!empty($where))
			$this->db->where($where);
        $this->db->where(['call_route' => 1]);
        $this->db->where('(FLOOR(call_to) > 0 OR FLOOR(call_from) > 0)');
        $this->db->from($this->_table);
		$this->db->join('users', 'clients_calls.call_user_id =users.id');
		
		$this->db->group_by('call_user_id');

        $subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		$this->db->select('SUM(call_duration) as outcome_duration, COUNT(call_id) as count_outcome_calls, call_user_id as cuid');
		if(!empty($where))
			$this->db->where($where);
        $this->db->where(['call_route' => 0]);
		$this->db->where('(FLOOR(call_to) > 0 OR FLOOR(call_from) > 0)');
        $this->db->from($this->_table);
		$this->db->join('users', 'clients_calls.call_user_id = users.id');
		$this->db->group_by('call_user_id');

        $subquery2 = $this->db->_compile_select();
		$this->db->_reset_select();
		
		
		$this->db->select("SUM(call_duration) as duration, COUNT(call_id) as count_calls,  income.*, outcome.*, CONCAT(users.firstname, ' ', users.lastname) as username", FALSE);
		if(!empty($where))
			$this->db->where($where);
		$this->db->where('(FLOOR(call_to) > 0 OR FLOOR(call_from) > 0)');
		
		
		$this->db->join('users', 'users.id = clients_calls.call_user_id');
		$this->db->join('clients', 'client_id = clients_calls.call_client_id', 'left');
		$this->db->join("($subquery) income", "clients_calls.call_user_id=income.uid", 'left');
		$this->db->join("($subquery2) outcome", "clients_calls.call_user_id=outcome.cuid", 'left');
		$this->db->group_by('call_user_id');
		$this->db->order_by('count_calls', 'DESC');
		
		$this->db->limit($limit);
		
		$query = $this->db->get($this->_table);

		return $query->result_array();
	}

}

//End model.
