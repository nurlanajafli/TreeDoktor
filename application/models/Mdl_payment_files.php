<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Mdl_payment_files extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'payment_files';
		$this->primary_key = "payment_files.id";
	}

//*******************************************************************************************************************
//*************
//*************			function to get payment file
//*************
//*******************************************************************************************************************	
	function get_payment_file($data)
	{
		$this->db->select('payment_file,id');
		$this->db->where("invoice_id", $data["invoice_id"]);
		$this->db->where("(payment_file !='' OR payment_file IS NOT NULL)");
		$this->db->order_by("id", "DESC");
		$rows = $this->db->get($this->table);
		$result = $rows->result_array();
		//if (count($result > 0)) {
			return $result;
		/*} else {
			return array();
		}*/
	}


	//*******************************************************************************************************************
//*************
//*************			function to get payment file by id
//*************
//*******************************************************************************************************************	
	function get_payment_file_by_id($id)
	{
		$this->db->select('payment_file,id');
		$this->db->where("id", $id);
		$this->db->where("(payment_file !='' OR payment_file IS NOT NULL)");
		$this->db->order_by("id", "DESC");
		$rows = $this->db->get($this->table);
		$result = $rows->result_array();
		//if (count($result > 0)) {
			return $result;
		/*} else {
			return array();
		}*/
	}

//*******************************************************************************************************************
//*************
//*************			Insert function
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
//*************								Update invoice function
//*************
//*******************************************************************************************************************	

	function update($data, $wdata)
	{
		if ($data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table, $data);
			//echo $this->db->last_query();
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}// End. update_estimates ();

//*******************************************************************************************************************
//*************
//*************													Delete function
//*************
//*******************************************************************************************************************	

	function delete($id)
	{
		if ($id) {
			$this->db->where('id', $id);
			$this->db->delete($this->table);

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}


}

?>
