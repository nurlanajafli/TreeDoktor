<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Dmitriy Vashchenko
 * created on: March - 2015
 */

class Mdl_categories extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'client_task_categories';
		$this->primary_key = "client_task_categories.category_id";
	}

	function get_all($where = FALSE)
	{
		if($where) {
            $this->db->where($where);
        }
		$query = $this->db->order_by('sort', 'ASC')->get($this->table);
		return $query->result_array();
	}

	public function update_category($id, $data)
	{
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

    function update_priority($updateBatch)
    {
        $this->db->update_batch($this->table, $updateBatch, 'category_id');
        return TRUE;
    }

	public function insert_category($data)
	{
		if ($this->db->insert($this->table, $data))
			return TRUE;
		return FALSE;
	}

	function delete_category($id)
	{
		$this->db->where($this->primary_key, $id);
		$this->db->delete($this->table);
		return TRUE;
	}
}
