<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: february - 2014
 */

class Mdl_sale extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'sales';
		$this->primary_key = "sales.sale_id";
	}

	function get_all($where = array())
	{
		if(!empty($where))
			$this->db->where($where);
		$this->db->order_by('sale_date', 'ASC');
		$query = $this->db->get($this->table);
		return $query->result_array();
	}

	public function update_sale($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert_sale($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_sale($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
