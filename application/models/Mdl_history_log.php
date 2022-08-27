<?php

class Mdl_history_log extends JR_Model
{
	protected $_table = 'user_history_log';
	protected $primary_key = 'log_id';

	function __construct()
	{
		parent::__construct();
	}
	
	function get_count_links_to_stats($where = array())
	{
		$this->db->select("COUNT(log_id) as count_links, CONCAT(users.firstname, ' ', users.lastname) as username", FALSE);
		$this->db->join('users', 'users.id = user_history_log.log_user_id');
		if(!empty($where))
			$this->db->where($where);
		$this->db->group_by('log_user_id');
		$this->db->order_by('count_links', 'DESC');
		$query = $this->db->get($this->_table);
		return $query->result_array();
	}
	/*
	function get_pop_links($where = [])
	{
		$this->db->select("COUNT(DISTINCT(log_id)) as count, log_url", FALSE);
		if($where && count($where))
			$this->db->where($where);
		$this->db->where("log_url NOT LIKE '%save%' AND log_url NOT LIKE '%update%' AND log_url NOT LIKE '%ajax%' AND log_url NOT LIKE '%send%' AND log_url NOT LIKE '' AND log_url NOT LIKE '%login%' AND log_url NOT LIKE '%schedule/%data%' AND log_url NOT LIKE '%add%' AND log_url NOT LIKE '%getdatabymonth' AND log_url NOT LIKE '%timer' AND log_url NOT LIKE '%assign%' AND log_url NOT LIKE '%check%'  AND log_url NOT LIKE '%set%'  AND log_url NOT LIKE '%change%'  AND log_url NOT LIKE '%.js%' AND log_url NOT LIKE '%create%'");
		$this->db->group_by('log_url');
		$this->db->order_by('count', 'desc');
		$this->db->limit(10);
		$query = $this->db->get($this->_table);
		return $query->result_array();
	}*/
}

//End model.
