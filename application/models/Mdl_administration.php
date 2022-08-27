<?php

class Mdl_administration extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->table = 'administration';
		$this->primary_key = 'administration.id';
	}





	//*******************************************************************************************************************
	//*************
	//*************           insert_filename model
	//*************
	//*******************************************************************************************************************
	public function insert_filename($data = '')
	{
		if (!empty($data)) {
			$this->db->insert($this->table, $data);
			return true;
		} else {
			return FALSE;
		}
	}
	
	function insert_status($data)
	{
		if($this->db->insert('workorder_status', $data))
			return $this->db->insert_id();
		return FALSE;
		
	}
	
	function update_status($id, $data)
	{
		if($id == '')
			return FALSE;
		$this->db->where('wo_status_id', $id);
		if($this->db->update('workorder_status', $data))
			return TRUE;
		return FALSE;
		
	}

}

//End model.
