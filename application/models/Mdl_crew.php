<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * user model
 * created by: gisterpages team
 * created on: august - 2012
 */

class Mdl_crew extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'crews';
		$this->primary_key = "crews.crew_id";
	}

	//insert data
	function insert_crew($data)
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


	/*
	 * function get_crew
	 *
	 * param select, wheredata, ...
	 * returns rows or false
	 *
	 */

	function get_crew($select = '', $wdata = '')
	{
		if ($select != '') {
			$this->db->select($select);
		}
		if ($wdata != '') {
			$this->db->where($wdata);
		}
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}

	/*
	 * function get_crewdata
	 *
	 * param wheredata ...
	 * returns rows or false
	 *
	 */

	function get_crewdata($wdata = '')
	{
		if ($wdata != '') {
			$this->db->where($wdata);
		}
		$query = $this->db->get($this->table);

		//print_r($this->db->last_query());

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}

	/*
	 * function update_crew
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update_crew($update_data, $wdata)
	{

		if ($update_data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table, $update_data);
			//echo $this->db->last_query();
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}


	/*
	 * function delete user
	 *
	 * param wheredata;
	 * returns bool;
	 *
	 */

	function delete_crew($data)
	{
		if ($data) {
			$this->db->where('id', $data);
			$this->db->delete($this->table);

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}


}

//end of file user_model.php
