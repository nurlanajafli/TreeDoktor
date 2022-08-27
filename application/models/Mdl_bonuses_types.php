<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: March - 2015
 */

class Mdl_bonuses_types extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'bonuses_types';
		$this->primary_key = "bonuses_types.bonus_type_id";
	}

	function get_all()
	{
		
		$query = $this->db->get($this->table);
		return $query->result_array();
	}

	public function update_bonus($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert_bonus($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_bonus($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
