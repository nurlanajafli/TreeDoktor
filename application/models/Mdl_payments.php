<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Mdl_payments extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'payments';
		$this->primary_key = "id";
	}

	//*******************************************************************************************************************
//*************
//*************			Insert Invoice Function; Returns insert id or false; 
//*************
//*******************************************************************************************************************	

	function insert($data)
	{

		if ($data) {

			$insert = $this->db->insert($this->table, $data);

			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}

	//*******************************************************************************************************************
//*************
//*************			Get Payment details by ID
//*************
//*******************************************************************************************************************	

	function get($data)
	{

		if ($data) {
			$this->db->where($data);
			$res = $this->db->get($this->table);
			return $result = $res->result_array();
		} else {
			return [];
		}
	}


	//*******************************************************************************************************************
//*************
//*************			Delete Payment Record Function; Returns true or false; 
//*************
//*******************************************************************************************************************	

	function delete($id)
	{
		return $res = $this->db->delete($this->table, array("id" => $id));
	}

	//*******************************************************************************************************************
//*************
//*************			Update Payment Invoice Function; @params id
//*************
//*******************************************************************************************************************	

	function update($data, $cond)
	{

		if ($data) {
			$this->db->where($cond);
			$insert = $this->db->update($this->table, $data);

			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}
}

?>
