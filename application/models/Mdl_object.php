<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: december - 2014
 */

class Mdl_object extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'static_objects';
		$this->primary_key = "static_objects.object_id";
	}

	function get_all()
	{
		
		$query = $this->db->get($this->table);
		return $query->result_array();
	}

	public function update_object($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert_object($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_object($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
