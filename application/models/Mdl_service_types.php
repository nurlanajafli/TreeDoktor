<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: february - 2014
 */

class Mdl_service_types extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'equipment_service_types';
		$this->primary_key = "equipment_service_types.equipment_service_id";
	}

	function get_all()
	{
		$query = $this->db->get($this->table);
		return $query->result_array();
	}

	public function update_service_type($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert_service_type($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_service_type($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
