<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Mdl_stumps extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		// Variables set for MY_Controller
		$this->table = 'stumps';
		$this->table2 = 'stumps_client';
		$this->primary_key = "stumps.stump_id";
		//$this->primary_key = "stumps_oak.ID";
	}

	function find_stump($like = [], $wdata = []) {
		$this->db->like($like);
		$this->db->where($wdata);
		return $this->db->get($this->table)->row_array();
	}

	function insert_stumps($data)
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
			exit;
		}
	}

	function get_all_in($field, $wdata, $where_in)
	{
		$this->db->select("stumps.*, SUBSTRING(stump_address,POSITION(' ' IN stump_address)) as stump_street, grinded.color as grinded_color");
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		if($where_in && !empty($where_in))
			$this->db->where_in($field, $where_in);

		$this->db->join('users grinded', 'grinded.id = stumps.stump_assigned', 'left');

		$query = $this->db->get($this->table);
		//if($query)
			return $query->result_array();
		//return FALSE;
	}

	function get_xlsx_data($status = 'new', $removal_date = NULL, $where = [])
	{
		$removal_date = $removal_date ? $removal_date : date('Y-m-d 23:59:59');
		$this->db->select('stumps.*, grinded.color as grinded_color, grinded.firstname as gfirstname, grinded.lastname as glastname, cleaned.firstname as cfirstname, cleaned.lastname as clastname');
		$this->db->where('stump_status', $status);
		$this->db->where('stump_archived IS NULL');
		if($where && !empty($where))
			$this->db->where($where);
		if($status == 'grinded') {
			$this->db->where("((stump_removal <= '" . $removal_date . "' AND stump_removal >= '2018-09-13') OR (stump_last_status_changed <= '" . $removal_date . "' AND stump_last_status_changed >= '2018-09-13'))");
		}
		$this->db->join('users grinded', 'grinded.id = stumps.stump_assigned AND grinded.system_user = 0', 'left');
		$this->db->join('users cleaned', 'cleaned.id = stumps.stump_assigned AND cleaned.system_user = 0', 'left');
		//$this->db->order_by('stump_removal');
		$this->db->order_by('stump_last_status_changed');
		//$this->db->order_by('stump_unique_id + 0');
		return $this->db->get('stumps')->result_array();
	}
	
	function get_all($search_keyword = '', $wdata = array(), $limit = 0, $start = 0, $order = [])
	{
		//$this->db->select('*');
		//$this->db->join('users', 'users.id = stumps.stump_assigned', 'left');

		$sql_query = 'SELECT';
		if($order && !empty($order))
			$sql_query .= " SUBSTRING(stump_address,POSITION(' ' IN stump_address)) as stump_street,";
		$sql_query .=	" stumps.*, grinded.color as grinded_color, grinded.firstname as gfirstname, grinded.lastname as glastname, cleaned.firstname as cfirstname, cleaned.lastname as clastname FROM stumps
					LEFT JOIN users grinded
					ON grinded.id = stumps.stump_assigned AND grinded.system_user = 0 
					LEFT JOIN users cleaned
					ON cleaned.id = stumps.stump_clean_id AND cleaned.system_user = 0 
					WHERE 1 = 1 AND stump_archived IS NULL";
		
		if (!empty($wdata)) {
			foreach ($wdata as $key => $val)
				$sql_query .= ' AND ' . $key . "='" . $val . "'";
		}
		if (isset($search_keyword) && $search_keyword != "") {
            $search_keyword = addslashes($search_keyword);
			$sql_query .= " AND (stumps.stump_address LIKE '%" . $search_keyword . "%'
			                    OR CONCAT(stumps.stump_house_number, ' ', stumps.stump_address) LIKE '%" . $search_keyword . "%'
								OR stumps.stump_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_city LIKE '%" . $search_keyword . "%'
								OR stumps.stump_state LIKE '%" . $search_keyword . "%'
								OR stumps.stump_desc LIKE '%" . $search_keyword . "%'
								OR stumps.stump_unique_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_map_grid LIKE '%" . $search_keyword . "%'
								OR stumps.stump_side LIKE '%" . $search_keyword . "%'
								OR stumps.stump_range LIKE '%" . $search_keyword . "%'
								OR stumps.stump_locates LIKE '%" . $search_keyword . "%'
								OR stumps.stump_contractor_notes LIKE '%" . $search_keyword . "%'
								OR stumps.stump_removal LIKE '%" . $search_keyword . "%'
								OR stumps.stump_clean LIKE '%" . $search_keyword . "%')";
		}



		if(!empty($order) && $order) {
			$sql_query .= ' ORDER BY ';
			foreach ($order as $field => $type)
				$sql_query .= $field . ' ' . $type . ',';
			$sql_query = rtrim($sql_query, ',');
		}
		else {
			$sql_query .= ' ORDER BY stump_unique_id ASC';
		}

		if ($limit)
			$sql_query .= " LIMIT " . $start;

		if ($limit)
			$sql_query .= ", " . $limit;

		$query = $this->db->query($sql_query);

		return $query->result_array();
	}

	function get_my_all($search_keyword = '', $wdata = array(), $limit = 0, $start = 0, $order = [])
	{
		//$this->db->select('*');
		//$this->db->join('users', 'users.id = stumps.stump_assigned', 'left');

		$sql_query = 'SELECT';
		if($order && !empty($order))
			$sql_query .= " SUBSTRING(stump_address,POSITION(' ' IN stump_address)) as stump_street,";
		$sql_query .=	" stumps.*,grinded.color as grinded_color FROM stumps
					LEFT JOIN users grinded
					ON grinded.id = stumps.stump_assigned
					LEFT JOIN users cleaned
					ON cleaned.id = stumps.stump_clean_id
					WHERE 1 = 1 AND stump_archived IS NULL";
		
		if (!empty($wdata)) {
			foreach ($wdata as $key => $val)
				$sql_query .= ' AND ' . $key . "='" . $val . "'";
		}
		if (isset($search_keyword) && $search_keyword != "") {
            $search_keyword = addslashes($search_keyword);
			$sql_query .= " AND (stumps.stump_address LIKE '%" . $search_keyword . "%'
			                    OR CONCAT(stumps.stump_house_number, ' ', stumps.stump_address) LIKE '%" . $search_keyword . "%'
								OR stumps.stump_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_city LIKE '%" . $search_keyword . "%'
								OR stumps.stump_state LIKE '%" . $search_keyword . "%'
								OR stumps.stump_desc LIKE '%" . $search_keyword . "%'
								OR stumps.stump_unique_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_map_grid LIKE '%" . $search_keyword . "%'
								OR stumps.stump_side LIKE '%" . $search_keyword . "%'
								OR stumps.stump_range LIKE '%" . $search_keyword . "%'
								OR stumps.stump_locates LIKE '%" . $search_keyword . "%'
								OR stumps.stump_contractor_notes LIKE '%" . $search_keyword . "%'
								OR stumps.stump_removal LIKE '%" . $search_keyword . "%'
								OR stumps.stump_clean LIKE '%" . $search_keyword . "%')";
		}
		$sql_query .= " AND (stump_assigned = " . $this->session->userdata['user_id'] . " OR stump_clean_id = " . $this->session->userdata['user_id'] . ")";

		if(!empty($order) && $order) {
			$sql_query .= ' ORDER BY ';
			foreach ($order as $field => $type)
				$sql_query .= $field . ' ' . $type . ',';
			$sql_query = rtrim($sql_query, ',');
		}
		else {
			$sql_query .= ' ORDER BY stump_unique_id ASC';
		}

		if ($limit)
			$sql_query .= " LIMIT " . $start;

		if ($limit)
			$sql_query .= ", " . $limit;
		
		$query = $this->db->query($sql_query);
		return $query->result_array();
	}
	
	function count_all($search_keyword = '', $wdata = array(), $limit = 0, $start = 0)
	{
		//$this->db->select('*');
		//$this->db->join('users', 'users.id = stumps.stump_assigned', 'left');
		
		$sql_query =	"SELECT * FROM stumps
					LEFT JOIN users grinded
					ON grinded.id = stumps.stump_assigned
					LEFT JOIN users cleaned
					ON cleaned.id = stumps.stump_clean_id
					WHERE 1 = 1 AND stump_archived IS NULL";
		
		if (!empty($wdata)) {
			foreach ($wdata as $key => $val)
				$sql_query .= ' AND ' . $key . "='" . $val . "'";
		}
		if (isset($search_keyword) && $search_keyword != "") {
            $search_keyword = addslashes($search_keyword);
			$sql_query .= " AND (stumps.stump_address LIKE '%" . $search_keyword . "%'
			                    OR CONCAT(stumps.stump_house_number, ' ', stumps.stump_address) LIKE '%" . $search_keyword . "%'
								OR stumps.stump_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_city LIKE '%" . $search_keyword . "%'
								OR stumps.stump_state LIKE '%" . $search_keyword . "%'
								OR stumps.stump_desc LIKE '%" . $search_keyword . "%'
								OR stumps.stump_unique_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_map_grid LIKE '%" . $search_keyword . "%'
								OR stumps.stump_side LIKE '%" . $search_keyword . "%'
								OR stumps.stump_range LIKE '%" . $search_keyword . "%'
								OR stumps.stump_locates LIKE '%" . $search_keyword . "%'
								OR stumps.stump_contractor_notes LIKE '%" . $search_keyword . "%'
								OR stumps.stump_removal LIKE '%" . $search_keyword . "%'
								OR stumps.stump_clean LIKE '%" . $search_keyword . "%')";
		}
		if ($limit)
			$sql_query .= " LIMIT " . $limit;

		if ($limit)
			$sql_query .= ", " . $start;
		
		$query = $this->db->query($sql_query);
		return $query->num_rows();
	}

	function count_my_all($search_keyword = '', $wdata = array(), $limit = 0, $start = 0)
	{
		//$this->db->select('*');
		//$this->db->join('users', 'users.id = stumps.stump_assigned', 'left');
		
		$sql_query =	"SELECT * FROM stumps
					LEFT JOIN users
					ON users.id = stumps.stump_assigned
					WHERE 1 = 1 AND stump_archived IS NULL";
		
		if (!empty($wdata)) {
			foreach ($wdata as $key => $val)
				$sql_query .= ' AND ' . $key . "='" . $val . "'";
		}
		if (isset($search_keyword) && $search_keyword != "") {
            $search_keyword = addslashes($search_keyword);
			$sql_query .= " AND (stumps.stump_address LIKE '%" . $search_keyword . "%'
			                    OR CONCAT(stumps.stump_house_number, ' ', stumps.stump_address) LIKE '%" . $search_keyword . "%'
								OR stumps.stump_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_city LIKE '%" . $search_keyword . "%'
								OR stumps.stump_state LIKE '%" . $search_keyword . "%'
								OR stumps.stump_desc LIKE '%" . $search_keyword . "%'
								OR stumps.stump_unique_id LIKE '%" . $search_keyword . "%'
								OR stumps.stump_map_grid LIKE '%" . $search_keyword . "%'
								OR stumps.stump_side LIKE '%" . $search_keyword . "%'
								OR stumps.stump_range LIKE '%" . $search_keyword . "%'
								OR stumps.stump_locates LIKE '%" . $search_keyword . "%'
								OR stumps.stump_contractor_notes LIKE '%" . $search_keyword . "%'
								OR stumps.stump_removal LIKE '%" . $search_keyword . "%'
								OR stumps.stump_clean LIKE '%" . $search_keyword . "%')";
		}
		$sql_query .= " AND (stump_assigned = " . $this->session->userdata['user_id'] . " OR stump_clean_id = " . $this->session->userdata['user_id'] . ")";
		if ($limit)
			$sql_query .= " LIMIT " . $limit;

		if ($limit)
			$sql_query .= ", " . $start;
		
		$query = $this->db->query($sql_query);
		return $query->num_rows();
	}


	function update_stumps($update_data, $wdata)
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
			//echo "data not received";
		}
	}

	function update_batch_stumps($update_data, $wdata)
	{
		$data = [];
		foreach ($wdata as $key => $stump_id)
		{
			$data[$key] = ['stump_id' => $stump_id];
			foreach ($update_data as $field => $value) {
				$data[$key][$field] = $value;
			}
		}
		if($data && !empty($data))
			$this->db->update_batch($this->table, $data, 'stump_id');
		return TRUE;
	}


	function delete_stumps($id)
	{
		if ($id) {
			$this->db->where('stump_id', $id);
			$this->db->delete($this->table);

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function insert_stumps_client($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->table2, $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
			exit;
		}
	}
	
	function get_all_client($wdata = array())
	{
		$this->db->select('*');
		if(!empty($wdata))
			$this->db->where($wdata);
		$this->db->where('cl_hidden', 0);
		$query = $this->db->get($this->table2);
		return $query->result_array();
	}


	function update_stumps_client($update_data, $wdata)
	{
		if ($update_data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table2, $update_data);
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


	function delete_stumps_client($id)
	{
		if ($id) {
			$this->db->where('stump_client_id', $id);
			$this->db->delete($this->table);
			$this->db->where('cl_id', $id);
			$this->db->delete($this->table2);
			
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function get_grinded_stat($wdata = []) {
		$this->db->select('SUM(stump_range) as cm, COUNT(stump_id) as stps, firstname, lastname, cl_name, cl_lastname');
		$this->db->join('users', 'stump_assigned = id', 'left');
		$this->db->join('stumps_client', 'cl_id = stump_client_id', 'left');
		$this->db->where($wdata);
		$this->db->where("(stump_status = 'grinded' OR stump_status = 'cleaned_up')");
		$this->db->group_by('stump_assigned, stump_client_id');
		$this->db->order_by('cm', 'DESC');
		return $this->db->get($this->table)->result_array();
	}

	function get_cleaned_stat($wdata = []) {
		$this->db->select('SUM(stump_range) as cm, COUNT(stump_id) as stps, firstname, lastname, cl_name, cl_lastname');
		$this->db->join('users', 'stump_clean_id = id', 'left');
		$this->db->join('stumps_client', 'cl_id = stump_client_id', 'left');
		$this->db->where($wdata);
		$this->db->where("stump_status = 'cleaned_up'");
		$this->db->group_by('stump_clean_id, stump_client_id');
		$this->db->order_by('cm', 'DESC');
		return $this->db->get($this->table)->result_array();
	}

	function get_all_with_team($wdata = [], $orderBy = NULL) {
		$this->db->select("stumps.*, CONCAT(grinded.firstname, ' ', grinded.lastname) as grinded_crew, CONCAT(cleaned.firstname, ' ', cleaned.lastname) as cleaned_crew", FALSE);
		$this->db->join('users grinded', 'grinded.id = stump_assigned', 'left');
		$this->db->join('users cleaned', 'cleaned.id = stump_clean_id', 'left');
		$this->db->where('stump_archived IS NULL');
		if (!empty($wdata)) {
			$this->db->where($wdata);
		}
		if ($orderBy) {
			$this->db->order_by($orderBy);
		}
		return $this->db->get($this->table)->result();
	}

	function get_max_unique_id()
	{
		$this->db->select_max('ABS(stump_unique_id)', 'stump_unique_id');
		return $this->db->get('stumps')->row_array();
	}
}
