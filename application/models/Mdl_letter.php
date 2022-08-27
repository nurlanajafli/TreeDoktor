<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: december - 2014
 */

class Mdl_letter extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'email_templates';
		$this->primary_key = "email_templates.email_template_id";
	}

	function get_all($where = array())
	{
		if($where && !empty($where))
			$this->db->where($where);
		$this->db->order_by('email_system_template', 'ASC');
		$query = $this->db->get($this->table);
		return $query->result_array();
	}

	public function update($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert_letter($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_letter($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
