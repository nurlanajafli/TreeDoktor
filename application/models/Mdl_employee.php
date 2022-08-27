<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * employee model
 * 
 */

class Mdl_employee extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'employee_login';
		$this->primary_key = 'employee_login.employee_id';
	}

	//insert data
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


	/*
	 * function get
	 *
	 * param select, wheredata, ...
	 * returns rows or false
	 *
	 */

	function get($select = '', $wdata = '', $group_by = '', $firstLogin = FALSE)
	{
		if ($select != '') {
			$this->db->select($select);
		}
		if ($wdata != '') {
			$this->db->where($wdata);
		}
		if($group_by){
			$this->db->group_by($group_by);
		}
		if($firstLogin){
			$this->db->join('employee_login first_login', "employee_login.employee_id = first_login.employee_id AND DATE_FORMAT(employee_login.login_time, '%Y-%m-%d') = DATE_FORMAT(first_login.login_time, '%Y-%m-%d')");
			$this->db->order_by('employee_login.login_time');
		}
		$this->db->join('employees', 'employees.employee_id = employee_login.employee_id');
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}

	/*
	 * function get_last_login_details
	 *
	 * param select, wheredata, ...
	 * returns rows or false
	 *
	 */

	function get_last_login_details($id, $logout_time = 0)
	{
		$this->db->where("employee_id", $id);
		$this->db->where("last_logout", $logout_time);
		$this->db->order_by("id", "DESC");
		$this->db->limit(1);
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}


	/*
	 * function get_data_by_month data by month and id
	 *
	 * param month, year, id
	 * returns rows or false
	 *
	 */

	function get_data_by_month($month, $year, $id = '')
	{
		$this->db->where("MONTH(created_date)", $month);
		$this->db->where("YEAR(created_date)", $year);
		if (!empty($id)) {
			$this->db->where("employee_id", $id);
		}
		$this->db->order_by("created_date", "ASC");
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/
	}


	/*
     * function get_emp_login_data_biweekly data by month and id
     * 
     * param month, year, id
     * returns rows or false
     * 
     */

	function get_emp_login_data_biweekly($arr)
	{
		$this->db->select("*,ROUND((((DATE_FORMAT(time_diff,'%i')/60)*100)/100),2) + DATE_FORMAT(time_diff,'%H') AS time_diff", FALSE);
		$this->db->where("DATE(created_date) BETWEEN \"{$arr['start_date']}\" AND \"{$arr['end_date']}\"");
		if (!empty($arr["id"])) {
			$this->db->where("employee_id", $arr["id"]);
		}
		$this->db->order_by("login_time", "ASC");
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/
	}

	/*
	 * function get_overview_data_by_month
	 *
	 * param month, year, id
	 * returns rows or false
	 *
	 */

	function get_overview_data_by_month($month, $year)
	{
		$this->db->select("t.employee_id, SUM(t.total_pay) AS total_pay, e.emp_name", FALSE);
		$this->db->where("MONTH(t.created_date)", $month);
		$this->db->where("YEAR(t.created_date)", $year);
		$this->db->group_by("t.employee_id");
		$this->db->join("employees AS e", "e.employee_id = t.employee_id");
		$query = $this->db->get($this->table . " AS t");
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/
	}

	/*
     * function get overview employee data biweekly
     * 
     * param start date, last date
     * returns rows or false
     * 
     */

	function get_overview_report_biweekly1($arr)
	{
		$this->db->select("t.employee_id, SUM(t.total_pay) AS total_pay, SUM(ROUND(((DATE_FORMAT(time_diff, '%s')/60/60)), 3) + ROUND(((DATE_FORMAT(time_diff, '%i')/60)), 3) + DATE_FORMAT(time_diff, '%H')) AS seconds, e.emp_name", FALSE);
		$this->db->where("DATE(created_date) BETWEEN \"{$arr['start_date']}\" AND \"{$arr['end_date']}\"");
		$this->db->group_by("t.employee_id");
		$this->db->join("employees AS e", "e.employee_id = t.employee_id");
		$query = $this->db->get($this->table . " AS t");
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/
	}


	/*
     * function get overview employee data biweekly
     * 
     * param start date, last date
     * returns rows or false
     * 
     */

	function get_overview_report_biweekly($arr = array(), $order_by = '')
	{
		$this->db->select("employees.emp_name, employees.employee_id, employees.emp_type, employees.emp_hourly_rate, ROUND((((DATE_FORMAT(time_diff,'%i')/60)*100)/100),2) + DATE_FORMAT(time_diff,'%H') AS seconds, total_pay, login_time, logout_time, employee_hourly_rate, no_lunch", FALSE);
		$this->db->from('employee_login');
		$this->db->join("employees", "employees.employee_id = employee_login.employee_id", "right");
		if($arr && !empty($arr))
			$this->db->where($arr);

		if($order_by && !empty($order_by))
			$this->db->order_by($order_by);

		$this->db->order_by("employees.employee_id", "ASC");
		$query = $this->db->get();
		
		return $query->result_array();
	}


	/*
	 * function update_employee
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update($update_data, $wdata)
	{
		if ($update_data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table, $update_data);
//            echo $this->db->last_query();
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
	 * function update_logout_data for employee
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update_logout($arr)
	{
		$sql = "UPDATE `employee_login`
			SET 
			logout_time =  '" . $arr["logout_time"] . "',
			last_logout =  1,";
		if(isset($arr['logout_lat']))
			$sql .= "logout_lat = '".$arr["logout_lat"]."',";
		if(isset($arr['logout_lon']))
			$sql .= "logout_lon = '".$arr["logout_lon"]."',";
			
			$sql .= "time_diff =  TIMEDIFF('" . $arr["logout_time"] . "',login_time),
			total_pay =  (TIME_FORMAT(TIMEDIFF('" . $arr["logout_time"] . "',login_time),'%H.%i') * employee_hourly_rate)
	        WHERE id='" . $arr["id"] . "'";
		$res = $this->db->query($sql);
		return $res;
	}


	/*
     * function insert_ajax for employee
     * function used to insert login details thorugh
	 * ajax.
     * param update data, wheredata;
     * returns bool;
     * 
     */

	function insert_ajax($arr)
	{
		$arr["time_diff"] = "TIMEDIFF('" . $arr["logout_time"] . "','" . $arr["login_time"] . "')";
		$arr["total_pay"] = "(TIME_FORMAT(TIMEDIFF('" . $arr["logout_time"] . "','" . $arr["login_time"] . "'),'%H.%i') * '" . $arr["employee_hourly_rate"] . "')";
		$arr["last_logout"] = 1;
		return $this->insert($arr);
	}


	/*
     * function update_timedata for employee
     * through ajax
     * param update data, wheredata;
     * returns bool;
     * 
     */

	function update_timedata_ajax($arr)
	{
		$sql = "UPDATE `employee_login`
		SET 
		login_time = '" . $arr["login_time"] . "'";
		if(isset($arr["logout_time"]) && $arr["logout_time"])
		{
			$sql .= ", logout_time =  '" . $arr["logout_time"] . "',
			last_logout =  1,
			time_diff =  TIMEDIFF('" . $arr["logout_time"] . "','" . $arr["login_time"] . "'),
			total_pay =  (TIME_FORMAT(TIMEDIFF('" . $arr["logout_time"] . "','" . $arr["login_time"] . "'),'%H.%i') * employee_hourly_rate)";
		}
		if(isset($arr['employee_hourly_rate']) && $arr['employee_hourly_rate'])
			$sql .= ", employee_hourly_rate = " . $arr['employee_hourly_rate'];
			
        $sql .= " WHERE id=" . $arr["id"];
		$res = $this->db->query($sql);
		//echo "q--------".$this->db->last_query();
		return $res;
	}


	/*
	 * function delete employee
	 *
	 * param wheredata;
	 * returns bool;
	 *
	 */

	function delete($data)
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
	
	//function get_overview_report

}

//end of file employee_model.php
