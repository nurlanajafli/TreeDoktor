<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * user model
 * created by: gisterpages team
 * created on: august - 2012
 */

class Mdl_user extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'users';
		$this->_table2 = 'user_meta';
		$this->_table3 = 'user_module';
		$this->_table4 = 'modules_master';
		$this->_table5 = 'users_sms';
		$this->primary_key = "users.id";
	}

	//insert data
	function insert_user($data)
	{
		if (!$data)
			return FALSE;

		$insert = $this->db->insert($this->table, $data);
		if ($this->db->affected_rows() > 0)
			return $this->db->insert_id();
		
		return FALSE;
	}

	//insert data
	function insert_usermeta($data)
	{
		if (!$data)
			return FALSE;

		$insert = $this->db->insert($this->_table2, $data);
		if ($this->db->affected_rows() > 0)
			return $this->db->insert_id();

		return FALSE;
	}

	/*
	 * function get_user
	 *
	 * param select, wheredata, ...
	 * returns rows or false
	 *
	 */

	function get_user($select = null, $wdata = null, $order = '', $orwdata = null)
	{
		if ($select)
			$this->db->select($select, FALSE);
		
		if ($wdata)
			$this->db->where($wdata);

        if ($orwdata)
            $this->db->where($orwdata);
		
		if($order != '')
			$this->db->order_by($order);
		else
			$this->db->order_by('firstname');

		$this->db->join('employees', 'emp_user_id = users.id', 'left');
		$query = $this->db->get($this->table);
		
		//if ($query->num_rows() > 0)
			return $query;
		
		//return FALSE;
	}

	function getUserById($id)
	{
		$this->db->where(['id'=>$id]);
		$query = $this->db->get($this->table);
		//if(!$query)
			//return FALSE;
		
		return $query->result_array();
	}

	/*
	 * function get_usermeta
	 *
	 * param wheredata ...
	 * returns rows or false
	 *
	 */

	function get_usermeta($wdata = '', $notIn = array(), $order = [], $field_workers=true)
	{
		
		if($notIn && (is_array($notIn) && !empty($notIn)))
			$this->db->where_not_in('users.id', $notIn);
		$this->db->select("users.*, employees.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, user_meta.address1, user_meta.address2, user_meta.city, user_meta.state, user_meta.country, employees.emp_phone, employees.emp_feild_worker, ext_numbers.*", FALSE);

		$this->db->from('users', 'user_meta');
		if(!empty($order))
			$this->db->order_by($order[0], $order[1]);
		$this->db->order_by('firstname');

		$this->db->join('user_meta', 'users.id = user_meta.user_id', 'left');
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		$this->db->join('user_module', 'users.id = user_module.user_id', 'left');
		$this->db->join('ext_numbers', 'ext_numbers.extention_user_id = users.id', 'left');
		if ($wdata != '')
			$this->db->where($wdata);

		// Exclude only fieldworkers and only from office schedule
        if (false === $field_workers)
		    $this->db->where('NOT (employees.emp_feild_worker=1 AND employees.emp_field_estimator=0)');

		$this->db->group_by('users.id');

		$query = $this->db->get();
		//if ($query->num_rows() > 0)
			return $query;
		
		//return FALSE;
	}
	
	function get_categorized_users_app($wdata = []) {
		$this->db->select('us.id as user_id, CONCAT(us.firstname, " ", us.lastname) as user_name', false);
		$this->db->from('users us');
		$this->db->join('employees em', 'us.id = em.emp_user_id', 'left');
		$this->db->where('us.active_status', 'yes');
		if(!empty($wdata)){
			foreach($wdata as $key=>$value){
				$this->db->where($key, $value);
			}
		}
		$this->db->order_by('us.firstname');
		return $this->db->get()->result();
	}

	function get_payroll_user($wdata = '', $order = '')
	{

		$this->db->select("users.*, employees.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, user_meta.address1, user_meta.address2, user_meta.city, user_meta.state, user_meta.country", FALSE);
		$this->db->from('users', 'user_meta');
		
		$this->db->join('user_meta', 'users.id = user_meta.user_id', 'left');
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		
		
		if ($order)
			$this->db->order_by($order);

		if ($wdata)
			$this->db->where($wdata);
		
		$this->db->group_by('users.id');
		$query = $this->db->get();

		//if ($query->num_rows() > 0)
		return $query;
		
		//return FALSE;
	}
	
	function get_user_with_docs($wdata = '')
	{
		$this->db->select("users.*, employees.*, user_certificates.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name", FALSE);
		$this->db->join('user_certificates', 'user_certificates.us_user_id = users.id', 'left');
		
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		
		if ($wdata)
			$this->db->where($wdata);
		
		$query = $this->db->get($this->table);

		//if ($query->num_rows() > 0)
			return $query;
		
		//return FALSE;
	}
	/*
	 * function update_user
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update_user($update_data=NULL, $wdata=NULL)
	{
		if (!$update_data || !$wdata)
			return FALSE;

		$this->db->where($wdata);
		$update = $this->db->update($this->table, $update_data);
		if ($this->db->affected_rows() > 0)
			return TRUE;
		
		return FALSE;
	}

	/*
	 * function update_usermeta
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update_usermeta($update_data, $wdata)
	{
		if ($update_data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->_table2, $update_data);
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

	function delete_user($data)
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

	function get_user_name($id)
	{

		$sql_query = "SELECT users.firstname, users.lastname FROM users WHERE users.id = '$id'";
		$query = $this->db->query($sql_query);
		return $query;
	}

	/*
 * function get_module_options
 *
 *
 * returns rows of module opts or false
 *
 */

	function get_module_options()
	{
		$this->db->select('module_id,module_desc,id');
		$query = $this->db->get('modules_master');
		//if ($query->num_rows() > 0) {
			return $query->result();
		/*} else {
			return FALSE;
		}*/
	}

	//insert Module data
	function insert_userModules($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->_table3, $data);
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
	 * function get_usermeta
	 *
	 * param wheredata ...
	 * returns rows or false
	 *
	 */

	function get_userModules($id = '', $module_id = '')
	{

		$this->db->select('*');
		$this->db->from('user_module');
		if ($id != '') {
			$this->db->where('user_id', $id);
		}
		if ($module_id != '') {
			$this->db->where('module_id', $module_id);
		}

		$query = $this->db->get();

		//print_r($this->db->last_query());

		//if ($query->num_rows() > 0) {
			return $query->result();
		/*} else {
			return FALSE;
		}*/
	}


	/*
	 * function checkModuleExsist
	 *
	 * param wheredata ...
	 * returns rows or false
	 *
	 */

	function checkModuleExsist($id = '', $module_id)
	{

		$this->db->select('*');
		$this->db->from('user_module');
		if ($id != '') {
			$this->db->where('user_id', $id);
		}
		if ($module_id != '') {
			$this->db->where('module_id', $module_id);
		}

		$query = $this->db->get();

		//print_r($this->db->last_query());

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}

	/*
	 * function update_userModules
	 *
	 * param update data, wheredata;
	 * returns bool;
	 *
	 */

	function update_userModules($update_data, $wdata, $module_id)
	{
		if ($update_data != '' && $wdata != '') {

			$this->db->where('user_id', $wdata);
			$this->db->where('module_id', $module_id);
			$update = $this->db->update($this->_table3, $update_data);
			//echo $this->db->last_query();die();
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
	 * function delete_userModules user
	 *
	 * param wheredata;
	 * returns bool;
	 *
	 */

	function delete_userModules($data)
	{
		if ($data) {
			$this->db->where('user_id', $data);
			$this->db->delete($this->_table3);
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function get_users_activity($where = NULL, $limit = 100, $offset = 0, $count = FALSE)
	{
		if ($where)
			$this->db->where($where);
		$this->db->join('login_log', 'log_user_id = id', 'right');
		$this->db->order_by('log_time', 'DESC');
		if (!$count) {
			$this->db->limit($limit, $offset);
			return $this->db->get('users')->result_array();
		}
		return $this->db->count_all_results('users');
	}

	function get_users_activity_history($where = NULL, $limit = 100, $offset = 0, $count = FALSE)
	{
		if ($where)
			$this->db->where($where);
		$this->db->join('user_history_log', 'log_user_id = id', 'right');
		$this->db->order_by('log_date', 'DESC');
		if (!$count) {
			$this->db->limit($limit, $offset);
			return $this->db->get('users')->result_array();
		}
		return $this->db->count_all_results('users');
	}

	function insert_activity_log($data)
	{
        $this->db->insert('login_log', $data);
        if ($this->db->affected_rows() > 0)
            return $this->db->insert_id();
		return FALSE;
	}

	function vote($who, $user_id, $vote)
	{
		$this->db->where(array('vote_who' => $who, 'vote_user_id' => $user_id, 'vote_date >=' => (time() - 60 * 60)));
		$votes = $this->db->count_all_results('users_votes');
		if ($votes)
			return FALSE;
		$vote = ($vote) ? 1 : 0;
		$sign = ($vote) ? '+' : '-';
		$this->db->where('id', $user_id);
		$this->db->set('rate', '`rate`' . $sign . '1', FALSE);
		$this->db->update('users');
		$this->db->insert('users_votes', array('vote_who' => $who, 'vote_user_id' => $user_id, 'vote_date' => time(), 'vote' => $vote));
		return $this->db->insert_id();
	}
	
	function get_user_with_employee($wdata = '', $notIn = array())
	{
		
		if($notIn && !empty($notIn))
			$this->db->where_not_in('users.id', $notIn);
		$this->db->select("users.*, employees.*, user_meta.address1, user_meta.address2, user_meta.city, user_meta.state, user_meta.country, employees.emp_phone", FALSE);
		$this->db->from('users', 'user_meta');
		$this->db->order_by('firstname');
		$this->db->join('user_meta', 'users.id = user_meta.user_id');
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		if ($wdata != '') {
			$this->db->where($wdata);
		}
		$this->db->group_by('user_id');
		$query = $this->db->get();

		//print_r($this->db->last_query());

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}
	
	function userContactWS($wdata = array())
	{
		
		$this->db->select("users.id as contact_id, CONCAT(users.firstname, ' ', users.lastname) as name, REPLACE(employees.`emp_phone`, '.', '') as number", FALSE);

		$this->db->from('users');
		$this->db->order_by('firstname');
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		$this->db->where('employees.emp_phone IS NOT NULL');
		$this->db->where('employees.emp_phone != " "');
		if ($wdata != '')
			$this->db->where($wdata);

		$this->db->group_by('users.id');

		$query = $this->db->get();
		//if ($query->num_rows() > 0)
			return $query->result();
		
		//return FALSE;
	}
	
	function search_reff($reff)
	{

		$this->db->select("users.id as id, CONCAT(users.firstname, ' ', users.lastname) as text", FALSE);
		$this->db->where(array('active_status' => 'yes'));
		$array = array(
			"CONCAT(users.firstname, ' ', users.lastname)" => $reff,
		);
		$this->db->like($array);
		return $this->db->get('users');
	}
	
	function get_max($field, $where = array())
	{
		$this->db->select("users.id as id, MAX(" . $field . ") as " . $field);
		if(!empty($where))
			$this->db->where($where);
		return $this->db->get('users');
	}

	function insert_users_sms($data)
	{
		$this->db->insert($this->_table5, $data);
		if ($this->db->affected_rows() > 0)
			return $this->db->insert_id();
		return FALSE;
	}

	function check_sms_for_user($number)
	{
		$this->db->select('users_sms.*, users.*, employees.*, recipient.firstname as recipient_firstname, recipient.lastname as recipient_lastname,');
		$this->db->where('us_recipient', $number);
		$this->db->where('DATE_ADD(us_date, INTERVAL 1 DAY) >=', date('Y-m-d H:i:s'));
		$this->db->order_by('us_id', 'DESC');
		$this->db->join('users', 'users.id = us_user_id', 'left');
		$this->db->join('users recipient', 'recipient.id = us_recipient_user_id', 'left');
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		return $this->db->get($this->_table5)->row();
	}

	function get_chat_userlist() {
		$online = (time() - 40);
		$this->db->select("users.id, CONCAT(module_id, module_status) as module, IF(last_online >= $online, 1, 0) as online, CONCAT(firstname, 0x20, lastname) as name, user_type", FALSE);
		$this->db->join('user_module', 'users.id = user_module.user_id', 'left');
		//$this->db->where("(module_id = 'CHAT' OR user_type = 'admin')");
		$this->db->where([
			'active_status' => 'yes',
			'emailid <>' => 'screen',
			'users.id <>' => $this->session->userdata('user_id'),
			'system_user' => 0
		]);
		$this->db->group_by('users.id');
		$this->db->order_by('firstname, lastname');
		//$this->db->having("module = 'CHAT1' OR user_type = 'admin'"); 
		return $this->db->get('users')->result_array();
	}
	
	function get_followup_variables($id)
	{
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_schedule');
		
		$event = $this->mdl_schedule->find_by_id($id);
		if($event && !empty($event))
		{
			$wo = $this->mdl_workorders->wo_find_by_id($event->event_wo_id);
			
			$user = $this->get_usermeta(['users.id' => $wo->user_id])->row();
			
			$date = date('h:i A', $event->event_start);
			$fullDate = date('Y-m-d', $event->event_start);
			/*if($date < 11)
				$time = '(between 8AM and 11AM)';
			elseif($date >= 11 && $date < 14)
				$time = '(between 11AM and 2PM)';
			elseif($date >= 14 && $date <= 17)
				$time = '(between 2PM and 5PM)';
			else
				$time = '(after 5PM)';*/
			
			
			$result['JOB_ADDRESS'] = $wo->lead_address;
			$result['ADDRESS'] = $wo->client_address;
			$result['EMAIL'] = $wo->cc_email;
			$result['PHONE'] = $user->emp_phone;
			$result['NAME'] = $wo->firstname . ' ' . $wo->lastname;
			$result['NO'] = $wo->workorder_no;
			$result['LEAD_NO'] = $wo->lead_no;
			$result['ESTIMATE_NO'] = $wo->estimate_no;
			$result['INVOICE_NO'] = NULL;
			$result['ESTIMATOR_NAME'] = $wo->cc_name;
			
			$result['TIME'] = $date;
			$result['TIME_AND_DATE'] = $fullDate . ' ' .  $date;
			$result['DATE'] = $fullDate;
			
			$totalForEstimate = $this->mdl_estimates->get_total_for_estimate($wo->estimate_id);
            $result['AMOUNT'] = money($totalForEstimate['sum']);
            $result['TOTAL_DUE'] = money($this->mdl_estimates->get_total_estimate_balance($wo->estimate_id));
			$result['CCLINK'] = '<a href="' . $this->config->item('payment_link') . 'payments/' . md5($wo->estimate_no . $wo->client_id) . '">link</a>';
		}
		else
		{
			$where['us_notification'] = 1;
			$where['us_exp'] = date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d'))));
			$where['users.id'] = $id;
			
			$monthes_users = $this->mdl_user->get_user_with_docs($where);
			$users = [];
			if($monthes_users)
			{
				$userData = $monthes_users->result_array();
				
				foreach($userData as $k=>$v)
				{
					$result['NAME'] = $v['firstname'] . ' ' . $v['lastname'];
					$result['EMAIL'] = $v['user_email'];
					$result['PHONE'] = $v['emp_phone'];
					if(isset($result['DOCUMENTS']) && $result['DOCUMENTS'] != '')
						$result['DOCUMENTS'] .= ',<br>' . $v['us_name'] . ' ('.  $v['us_exp'] .')';
					else
						$result['DOCUMENTS'] = $v['us_name'] . ' ('.  $v['us_exp'] .')';
				}
			}
			$where['us_exp'] = date('Y-m-d', strtotime("+1 week", strtotime(date('Y-m-d'))));
			
			$monthes_users = $this->mdl_user->get_user_with_docs($where); 
			
			if($monthes_users)
			{
				$userData = $monthes_users->result_array();
				foreach($userData as $k=>$v)
				{
					$result['NAME'] = $v['firstname'] . ' ' . $v['lastname'];
					$result['EMAIL'] = $v['user_email'];
					$result['PHONE'] = $v['emp_phone'];
					if(isset($result['DOCUMENTS']) &&  $result['DOCUMENTS'] != '')
						$result['DOCUMENTS'] .= ',<br>' . $v['us_name'] . ' ('.  $v['us_exp'] .')';
					else
						$result['DOCUMENTS'] = $v['us_name'] . ' ('.  $v['us_exp'] .')';
				}
			}
			$result['TOTAL_DUE'] = FALSE;
		}
		return $result;
	}
	
	function logout_not_from_office($where = [])
	{
		$this->db->select("user_history_log.log_user_ip, user_history_log.log_date, CONCAT(users.firstname, ' ', users.lastname) as name, id, logout_lat, logout_lon", FALSE);
		$this->db->join('users', 'log_user_id = id');
		$this->db->join('emp_login', "DATE_FORMAT(log_date, '%Y-%m-%d %H:%i:00') = CONCAT(login_date, ' ', logout)", 'INNER', FALSE);
		if(!empty($where))
			$this->db->where($where);
		
		$this->db->where(['log_url' => 'dashboard/timer', 'log_user_ip !='=> '64.40.241.43']);
		$this->db->where('logout IS NOT NULL AND logout_lon != 0 AND logout_lon IS NOT NULL');
		$this->db->group_by('log_user_id');
		$this->db->order_by('user_history_log.log_user_id');
		$query = $this->db->get('user_history_log');
		return $query->result();
		/*
		SELECT user_history_log.*, CONCAT(users.firstname, ' ', users.lastname) FROM `user_history_log`
		JOIN users ON log_user_id = id
		JOIN emp_login ON DATE_FORMAT(log_date, '%Y-%m-%d %H:%i:00') = CONCAT(login_date, ' ', logout)
		WHERE log_date >= '2019-02-05 00:00:00' AND log_date <= '2019-02-05 23:59:59' AND log_url = 'dashboard/timer'
		GROUP BY log_user_id
		ORDER BY `user_history_log`.`log_user_id` ASC
		*/
	}
	
	function getActiveUsersWithTaskManager() {
		$this->db->select('user_module.*, users.*');
		$this->db->join('user_module', "users.id = user_module.user_id AND user_module.module_id = 'TM'");
		$this->db->where('active_status', 'yes');
		return $this->db->get('users')->result();
	}

	function deviceRegistration($data) {
	    $device = $this->getDeviceById($data['device_id']);

	    if($device) {
	        $this->db->where('device_id', $data['device_id']);
            $this->db->update('user_devices', $data);
        }
	    else
            $this->db->insert('user_devices', $data);
        return TRUE;
    }

    function deviceUnregistration($deviceData = NULL) {
	    if(!$deviceData)
	        return FALSE;
	    if(is_array($deviceData))
            $this->db->where($deviceData);
	    if(is_string($deviceData))
            $this->db->where('device_id', $deviceData);
        $this->db->delete('user_devices');
        return TRUE;
    }

    function getUserDevices($wdata) {
	    if(is_numeric($wdata))
            $this->db->where('device_user_id', $wdata);
	    else
    	    $this->db->where($wdata);
	    return $this->db->get('user_devices')->result_array();
    }

    function getDeviceById($deviceId) {
        $this->db->where('device_id', $deviceId);
        return $this->db->get('user_devices')->row_array();
    }

    function getDeviceByUserAndToken($userId, $deviceToken) {
        $this->db->where('device_user_id', $userId);
        $this->db->where('device_token', $deviceToken);
        return $this->db->get('user_devices')->row_array();
    }
	
	function get_followup()
	{
		$where['active_status'] = 'yes';
		$where['system_user'] = 0;
		$where['us_notification'] = 1;
		$where['us_exp'] = date('Y-m-d', strtotime("+1 month", strtotime(date('Y-m-d'))));
		
		
		$monthes_users = $this->mdl_user->get_user_with_docs($where);
		$users = [];
		if($monthes_users)
		{
			$userData = $monthes_users->result_array();
			
			foreach($userData as $k=>$v)
			{
				$users[$v['id']][''] = $v['id'];
				$users[$v['id']]['client_id'] = $v['id'];
				$users[$v['id']]['estimator_id'] = '';
				$users[$v['id']]['name'] = $v['firstname'] . ' ' . $v['lastname'];
				$users[$v['id']]['email'] = $v['user_email'];
				$users[$v['id']]['phone'] = $v['emp_phone'];
				$users[$v['id']]['documents'][] = $v['us_name']  . ' ('.  $v['us_exp'] .')';
			}
		}
		$where['us_exp'] = date('Y-m-d', strtotime("+1 week", strtotime(date('Y-m-d'))));
		
		$monthes_users = $this->mdl_user->get_user_with_docs($where); 
		
		if($monthes_users)
		{
			$userData = $monthes_users->result_array();
			foreach($userData as $k=>$v)
			{
				$users[$v['id']][''] = $v['id'];
				$users[$v['id']]['client_id'] = $v['id'];
				$users[$v['id']]['estimator_id'] = '';
				$users[$v['id']]['name'] = $v['firstname'] . ' ' . $v['lastname'];
				$users[$v['id']]['email'] = $v['user_email'];
				$users[$v['id']]['phone'] = $v['emp_phone'];
				$users[$v['id']]['documents'] = $v['us_name']  . ' ('.  $v['us_exp'] .')';
			}
		}
		
		return $users;
	}

    function getChatUsersWithLastMessage($userId) {
        $userId = intval($userId);
        $this->db->select("MAX(chat.id) as id, IF(`from` = $userId, `to`, `from`) as chat_with", FALSE);
        $this->db->from('chat');
        $this->db->where('from', $userId);
        $this->db->or_where('to', $userId);
        $this->db->group_by('chat_with');
        $this->db->having('ABS(chat_with) > 0');
        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select('users.id, users.firstname, users.lastname, users.picture, employees.emp_phone, chat.message, sent, recd, from, to, type');
        $this->db->from('users');
        $this->db->join('employees', 'emp_user_id = users.id', 'left');
        $this->db->join('user_module', 'users.id = user_id', 'left');
        $this->db->join("($subquery) last", "users.id = last.chat_with", 'left');
        $this->db->join("chat", "last.id = chat.id", 'left');

        //$this->db->where("((module_id = 'CHAT' AND user_type = 'user') OR user_type = 'admin')");
        //$this->db->where("user_type = 'user'");
        $this->db->where("(chat.from = $userId OR chat.to = $userId OR chat.id IS NULL)");
        $this->db->where('active_status', 'yes');
        $this->db->where('system_user', 0);
        $this->db->where('users.id <>', $userId);

        $this->db->group_by('users.id');
        $this->db->order_by('chat.sent', 'DESC');
        $this->db->order_by('users.firstname', 'ASC');
        $this->db->order_by('users.lastname', 'ASC');

        return $this->db->get()->result();
    }
	
}

//end of file user_model.php
