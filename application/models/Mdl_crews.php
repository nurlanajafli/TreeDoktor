<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: october - 2014
 */

class Mdl_crews extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'crews';
		$this->primary_key = "crews.crew_id";
	}

	function get_crews($wdata = array(), $orderBy = 'crew_id ASC', $limit = FALSE)
	{
		$this->db->where($wdata);
		$this->db->join('employees', 'crews.crew_leader = employees.employee_id', 'LEFT');
		if ($orderBy) {
			$this->db->order_by($orderBy);
		}
		if($limit == 1)
			return $this->db->get('crews')->row();
		return $this->db->get('crews')->result();
	}

	function get_crews_app($wdata = array(), $orderBy = 'crew_id ASC', $limit = FALSE) {
		$this->db->select('crew_id, crew_name, crew_full_name, crew_rate');
		$this->db->from('crews');		
		$this->db->where($wdata);		
		if ($orderBy) {
			$this->db->order_by($orderBy);
		}
		if($limit == 1)
			return $this->db->get()->row();
		return $this->db->get()->result();
	}

}
