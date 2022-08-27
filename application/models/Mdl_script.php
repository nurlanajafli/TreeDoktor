<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: february - 2014
 */

class Mdl_script extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'scripts';
		$this->primary_key = "scripts.script_id";
	}

	function get_all()
	{
		
		$query = $this->db->get($this->table);
		return $query->result_array();
	}

	public function update_script($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert_script($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_script($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
