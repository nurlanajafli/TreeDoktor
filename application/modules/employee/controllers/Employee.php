<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Employee extends MX_Controller
{

	/**
	 * Employee controller
	 */
	function __construct()
	{

		parent::__construct();
		
		show_404();
		if (isAdmin()) {
			$this->logout();
		}

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_emp_login');
		$this->load->model('mdl_worked');
		$this->load->model('mdl_employees', 'employee_model');
		$this->load->model('mdl_user');
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_clients', 'mdl_clients');
		$this->load->model('mdl_employee', 'employee_login_model');
		$this->load->helper('utilities');
		ini_set('date.timezone', 'America/New_York');
	}

	/*
     * function dashboard shows employee dashboard 
     * 
     * param null
     * returns html view
     * 
     */

	public function index()
	{
		show_404();
		$emp_login = 1;
		if (!isEmployee()) {
			$emp_login = 0;
		}
		$data['title'] = $this->_title . " - Employee Login";
		$data['page_title'] = "Employee Dashboard";
		$data['page'] = "employee_panel/dashboard";
		$data['emp_login'] = $emp_login;
		$data["emp_id"] = @$this->session->userdata("emp_user_id");
		$data["emp_estimator"] = @$this->session->userdata("emp_estimator");
		$data["emp_name"] = @$this->session->userdata("emp_name");

		$this->load->view('index', $data);
	}

	/*
     * function login
     * shows employee login screen
     * 
     * param null
     * returns html view
     * 
     */

	public function login()
	{
		show_404();
		$data['title'] = $this->_title . " - Employee Login";
		$data['page_title'] = "Employee Login";
		$data['page'] = "employee_login";
		$this->load->view('login', $data);
	}

	function check_login()
	{
		show_404();
		$this->load->library('form_validation');
		$r = $this->input->post("r");
		
		if (!empty($r)) {
			$urdata = explode("---", decrypt($r));
			
			if (!empty($urdata)) {
				if (isset($r[0])) {
					$username = utf8_encode($urdata[0]);
					$username = str_replace("\0", "", $username);
				}

				if (isset($r[1])) {
					$password = $urdata[1];
				}

				$emp_details = $this->_emplogin_check($username, $password);
				
				if(!$this->session->userdata('user_id'))
					$this->session->set_userdata(array('user_id' => 0));
				if (!empty($emp_details)) {
					$emp_details = $emp_details->result_array();
					$sdata = array(
						'emp_user_id' => $emp_details[0]["employee_id"],
						'emp_estimator' => $emp_details[0]["emp_field_estimator"],
						'emp_name' => $emp_details[0]["emp_name"],
						'emp_logged_in' => TRUE,
						'user_type' => 'employee',
						'emp_hourly_rate' => $emp_details[0]["emp_hourly_rate"],
						'emp_start_time' => $emp_details[0]["emp_start_time"]
					);
					$this->session->set_userdata($sdata);
					echo "login done";
				} else {
					echo 'Incorrect Username or Password';
				}
			} else {
				echo json_encode(array("login" => 0));
			}
		}
	}

	/*
	*
	*	function to upload webcam image
	*/
	function recognize()
	{
		show_404();
		$image = $this->input->post("image");
		$rec_id = $this->input->post("rec_id");
		$new_rec_id = $this->input->post("new_rec_id");
		$ltype = $this->input->post("ltype");
		if (!empty($image)) {
			// Init dataURL variable
			$data = $image;
			// Extract base64 data (Get rid from the MIME & Data Type)
			if (strstr($data, ",")) {
				$parts = explode(',', $data);
				$data = $parts[1];
			}
			$data = str_replace("[removed]", "", $data);
			// Decode Base64 data
			$data = base64_decode($data);
			// Save data as an image
			$emp_imagename = 'employee_image_' . uniqid() . rand(999, 10000) . '.jpg';
			$employee_image = UPLOAD_EMPLOYEE_PIC . $emp_imagename;
			
			$fp = fopen($employee_image, 'a');
			fwrite($fp, $data);
			fclose($fp);


			$stamp = imagecreatefromjpeg('./assets/img/timebg.jpg');
			$im = imagecreatefrompng($employee_image);

			$marge_right = 7;
			$marge_bottom = 271;
			$sx = imagesx($stamp);
			$sy = imagesy($stamp);

			imagecopy($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp));

			imagejpeg($im, $employee_image, 80);

			$this->load->library('image_lib');

			/*timebackground.png*/
			$config = array();
			$config['source_image']	= $employee_image;
			$config['wm_text'] = date('Y-m-d H:i:s');
			$config['wm_type'] = 'text';
			$config['wm_hor_offset'] = '-20';
			$config['wm_font_size']	= '12';
			$config['wm_font_color'] = 'ffffff';
			$config['wm_shadow_color'] = '626262';
			$config['wm_shadow_distance'] = '1';
			$config['wm_vrt_alignment'] = 'top';
			$config['wm_hor_alignment'] = 'right';
			$config['wm_padding'] = '10';

			$this->image_lib->initialize($config);

			$this->image_lib->watermark();

			$info = getimagesize($employee_image);
			if ($info['mime'] == 'image/jpeg') {
				$udata = array();
				if ($ltype == "login") {
					$udata["login_image"] = $emp_imagename;
				} elseif ($ltype == "logout") {
					$udata["logout_image"] = $emp_imagename;
				}
				$this->mdl_emp_login->update($new_rec_id, $udata);
				echo 1;
			} else {
				echo 0;
			}
		} else {
			echo 0;
		}
	}

	function dashboard()
	{
		show_404();
		$data['title'] = $this->_title . " - Employee Dashboard";
		$data['page_title'] = "Employee Dashboard";
		$data['page'] = "employee_panel/dashboard";
		$data["login"] = false;
		$data["logout"] = false;
		$data["login_time"] = "00:00";
		$data["logout_time"] = "00:00";
		$data["login_rec_id"] = 0;
		$data["new_record"] = 0;
		$data["login_data"] = array();
		$data["show_web_cam"] = 1;
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_worked');

		$emp_id = $this->session->userdata("emp_user_id");

		$data['teams'] = $this->mdl_schedule->get_teams(array('team_leader_user_id' => $emp_id, 'team_date' => strtotime(date('Y-m-d'))));
		
		
		$userdata = $this->mdl_user->get_payroll_user(array('users.id' => $emp_id));
		if($userdata)
			$obj['emp_field_estimator'] = $userdata->result()[0]->emp_field_estimator;
		else
			$obj = $this->employee_model->get_employee('emp_field_estimator', array('employee_id' => $emp_id));
		if($userdata)
			$data['estimator'] = $obj ? $obj : NULL;
		else
			$data['estimator'] = $obj ? $obj->row_array() : NULL;
		
		$this->load->model('mdl_report');
		$est_report = $this->mdl_report->find_by_fields(array('report_user_id = ' => $emp_id, 'report_date >=' => date('Y-m-d 00:00:00'), 'report_date <=' => date('Y-m-d 23:59:59')));
		
		$data['est_report'] = '';
		if(!empty($est_report))
			$data['est_report'] = $est_report->report_comment;
		if($data['teams'] && !empty($data['teams']))
		{
			foreach($data['teams'] as $team)
			{
				$data['team_members'] = $this->mdl_schedule->get_team_members(array('employee_team_id' => $team->team_id, 'schedule_teams_members.user_id <>' => $emp_id, 'employee_logout' => NULL));
				
				$data['team_id'] = $team->team_id;
			}
			$data['events'] = $this->mdl_schedule->get_events(array('schedule.event_report' => NULL, 'team_leader_user_id' =>  $emp_id, 'team_date ' => strtotime(date('Y-m-d'))), FALSE, 'schedule.event_start ASC');
			
			if((!isset($data['team_members']) || empty($data['team_members'])) && empty($data['events']))
				unset($data['teams']);
		}
		$data["emp_id"] = $this->session->userdata("emp_user_id");
		$data["emp_name"] = $this->session->userdata("emp_name");
		//var_dump($data['events']); die;
		$employee_login_new_table = $this->mdl_emp_login->get_by(array('login_user_id' => $emp_id, 'login_date' => date('Y-m-d'), 'logout' => NULL));
		if(empty($employee_login_new_table))
			$employee_login_new_table = $this->mdl_emp_login->get_by(array('login_employee_id' => $emp_id, 'login_date' => date('Y-m-d'), 'logout' => NULL));
		
		
		if($employee_login_new_table && !empty($employee_login_new_table))
		{
			$data['new_record'] = $employee_login_new_table->login_id;
			$data["login"] = true;
			$data["login_time"] = date("H:i", strtotime($employee_login_new_table->login));
			$data["login_rec_id"] = $employee_login_new_table->login_id;
			$data["show_web_cam"] = 0;
		}
		else
		{
			$worked = $this->mdl_worked->get_by(array('worked_date' => date('Y-m-d'), 'worked_user_id' => $emp_id));
			if(!$worked)
				$worked = $this->mdl_worked->get_by(array('worked_date' => date('Y-m-d'), 'worked_employee_id' => $emp_id));
			if($worked)
			{
				$data["login"] = true;
				$data["login_time"] = date("H:i", strtotime($worked->worked_start));
				$data["logout"] = true;
				$data["logout_time"] = date("H:i", strtotime($worked->worked_end));
				$data["time_diff"] = $worked->worked_hours;
			}
		}


		//	post month year
		if (isset($_POST["monthyear"])) {
			list($data["month"], $data["year"]) = explode("/", $this->input->post("monthyear"));
		}

		if (empty($data["month"])) {
			$data["month"] = date("m");
		}

		if (empty($data["year"])) {
			$data["year"] = date("Y");
		}
		
		$data["cdate"] = $data["month"] . "/" . $data["year"];
		$this->load->view('dashboard', $data);
	}

	function getdatabymonth()
	{
		show_404();
		$emp_id = $this->session->userdata("emp_user_id");
		//	post month year
		if (isset($_POST["monthyear"])) {
			list($data["month"], $data["year"]) = explode("/", $_POST["monthyear"]);
		}

		if (empty($data["month"])) {
			$data["month"] = date("m");
		}

		if (empty($data["year"])) {
			$data["year"] = date("Y");
		}

		$firstDay = $data["year"] . '-' . $data["month"] . '-' . '01';
		$lastDay = $data["year"] . '-' . $data["month"] . '-' . date('t', strtotime($firstDay));

		$emp_login_details_by_month = $this->mdl_emp_login->get_many_by(array('login_date >= ' => $firstDay, 'login_date <= ' => $lastDay, 'login_user_id' => $emp_id));
		
		if(!$emp_login_details_by_month)
			$emp_login_details_by_month = $this->mdl_emp_login->get_many_by(array('login_date >= ' => $firstDay, 'login_date <= ' => $lastDay, 'login_employee_id' => $emp_id));
		
		$this->load->model('mdl_schedule');
		$whereBonus['team_date >='] = strtotime($firstDay);
		$whereBonus['team_date <='] = strtotime($lastDay . ' 23:59:59');
		$whereBonus['user_id'] = $emp_id;
		$bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);
		
		if(!$bonusesRows)
		{
			$whereBonus['employee_id'] = $emp_id;
			unset($whereBonus['user_id']);
			$bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);
		}
		
		$bonuses = array();

		foreach($bonusesRows as $bonus)
			$bonuses[date('Y-m-d', $bonus['team_date'])][] = array('bonus_amount' => $bonus['bonus_amount'], 'bonus_title' => $bonus['bonus_title'] ? $bonus['bonus_title'] : $bonus['bonus_type_name'], 'bonus_description' => $bonus['bonus_type_description']);

		$data['bonuses'] = $bonuses;

		$data['collectedBonuses'] = $this->mdl_schedule->get_collected_bonuses_sum1($emp_id, $lastDay . ' 23:59:59');

		$data["emp_login_details"] = array();
		if (!empty($emp_login_details_by_month)) {
			foreach ($emp_login_details_by_month as $kk => $details) {
				$data["emp_login_details"][$details->login_date][$kk]["login_time"] = date("H:i", strtotime($details->login));
				$data["worked"][$details->login_date] = isset($data["worked"][$details->login_date]) ? $data["worked"][$details->login_date] : $this->mdl_worked->get($details->login_worked_id);
				if ($details->logout) {

					$data["emp_login_details"][$details->login_date][$kk]["logout_time"] = date("H:i", strtotime($details->logout));
					
					$data["emp_login_details"][$details->login_date][$kk]["hourly_rate"] = $data["worked"][$details->login_date]->worked_hourly_rate;
				} else {
					$data["emp_login_details"][$details->login_date][$kk]["logout_time"] = "-";

				}
				$data["emp_login_details"][$details->login_date][$kk]["time_diff"] = isset($data["worked"][$details->login_date]->worked_hours) ? $data["worked"][$details->login_date]->worked_hours - $data["worked"][$details->login_date]->worked_lunch : "-";
			}
		}
		$this->load->view('monthly_report', $data);
	}
	/*
     * private function check employee login
     *      
     * param username, password
     * 
     */
	private function    _emplogin_check($username, $password)
	{
		show_404();
		$data['emailid'] = $username;
		$data['password'] = md5($password);
		$userdata = $this->mdl_user->get_payroll_user($data);
		if(!$userdata)
		{
			$password = encrypt_pass($password);
			$userdata = $this->employee_model->get_employee('', array("emp_username" => $username, "emp_pass" => $password));
		}
		
		return $userdata;
	}


	function timer()
	{ 
		show_404();
		$timer = $this->input->post("timer");
		$new_rec_id = $this->input->post("new_rec_id");
		$login_rec_id = $this->input->post("login_rec_id");

		$emp = $this->mdl_user->get_payroll_user(array('users.id' => $this->session->userdata("emp_user_id")));
		$employee_data = $emp ? $emp->row_array() : [];

		
		if ($timer == "start") {

			$login_time = date("Y-m-d H:i:s");
			if($this->input->post('lat') != 'undefined')
			{
				$new_data["login_lat"] = $data["login_lat"] = $this->input->post('lat');
				$new_data["login_lon"] = $data["login_lon"] = $this->input->post('lon');
			}
			
			$new_data['login_date'] = date('Y-m-d');
			$new_data['login'] = date('H:i', strtotime($login_time));
			$new_data['login_user_id'] = $this->session->userdata('emp_user_id');
	
			$new_rec_id = $this->mdl_emp_login->insert($new_data);
			// END Create to new table EMP_LOGIN
			
			if ($new_rec_id) {
				die(json_encode(array("res" => "SUCCESS", "rec_id" => $new_rec_id, "login_time" => date("H:i", strtotime($login_time)), "new_rec_id" => $new_rec_id)));
			}
		} elseif ($timer == "stop") {

			$newRow = $this->mdl_emp_login->get($new_rec_id);
			$logout_time = date("Y-m-d H:i:s");

			if($this->input->post('lat') != 'undefined')
			{
				$udata["logout_lat"] = $data["logout_lat"] = $this->input->post('lat');
				$udata["logout_lon"] = $data["logout_lon"] = $this->input->post('lon');
			}
			
			//New table record
			$udata['logout'] = date('H:i', strtotime($logout_time));
			$res = $this->mdl_emp_login->update($new_rec_id, $udata);
			//End new table record

			$worked = $this->mdl_worked->get($newRow->login_worked_id);
			$time_str = 'Today you worked for ' . $worked->worked_hours . ' hours. Please sign out !!';

			die(json_encode(array("res" => "SUCCESS", "logout_time" => date("H:i", strtotime($logout_time)), "time_diff" => round((strtotime($logout_time) - strtotime($newRow->login)) / 3600, 2), 'total_time_diff' => $worked->worked_hours, 'total_pay' => (($worked->worked_hours - $worked->worked_lunch) * $worked->worked_hourly_rate), 'time_str' => $time_str)));
		}
		echo json_encode(array("res" => "FAILURE"));
	}

	/*
	* function to logout employee
	*
	*/
	public function logout()
	{
		show_404();
		$this->session->sess_destroy();
	}
	
	function ajax_save_report()
	{
		show_404();
		$data = $this->input->post();
		$team_id = $this->input->post('team_id');
		
		foreach($data['event_start_hours'] as $key => $val)
		{
			$insert = array();
			$insert['event_start_work'] = $data['event_start_hours'][$key] . ':' . $data['event_start_min'][$key];
			$insert['event_finish_work'] = $data['event_finish_hours'][$key] . ':' . $data['event_finish_min'][$key];
			$insert['event_status_work'] = $data['status'][$key];
			if(isset($data['payment'][$key]))
				$insert['event_payment'] = $data['payment'][$key];
			if(isset($data['payment'][$key]))
				$insert['event_payment'] = $data['payment'][$key];
			if(isset($data['payment_type'][$key]))
				$insert['event_payment_type'] = $data['payment_type'][$key];
			if(isset($data['payment_amount'][$key]))
				$insert['event_payment_amount'] = money($data['payment_amount'][$key]);
			if(isset($data['time'][$key]))
				$insert['event_time_to_finish'] = $data['time'][$key];
			if(isset($data['work_description'][$key]))
				$insert['event_work_remaining'] = $data['work_description'][$key];
			$insert['event_damage'] = $data['damage'][$key];
			if(isset($data['demage_description'][$key]))
				$insert['event_damage_description'] = $data['demage_description'][$key];
			if(isset($data['event_description'][$key]))
				$insert['event_description'] = $data['event_description'][$key];
			$this->mdl_schedule->update($key, array('event_report' => json_encode($insert)));
			// make notes
				$leader_name = $this->mdl_schedule->get_teams(array('team_id' => $team_id));
				$workorder = $this->mdl_workorders->wo_find_by_id($data['wo_id'][$key]);
				
				$update_msg = $leader_name[0]->emp_name . ' filled report for <a href="' . base_url($workorder->workorder_no) . '#eventInfo-' . $key . '" data-toggle="modal">' . $workorder->workorder_no . '</a>';
				make_notes($workorder->client_id, $update_msg, 'system', intval($workorder->workorder_no));
			// end make notes
		}
		
		if(isset($data['malfunctions_description']))
			$this->mdl_schedule->update_team($team_id, array('team_fail_equipment' => $data['malfunctions_description']));
		if(isset($data['expenses_description']))
			$this->mdl_schedule->update_team($team_id, array('team_expenses' => $data['expenses_description']));
		
		if(isset($data['logout_time']))
		{
			foreach($data['logout_time'] as $key => $val)
			{
				
				$update = array();
				$update['employee_logout'] = $val;
				$this->mdl_schedule->update_team_member(array('user_id' => $key, 'employee_team_id' => $team_id), $update);
			}
		}
		$this->mdl_schedule->update_team_member(array('user_id' => $this->session->userdata("emp_user_id"), 'employee_team_id' => $team_id), array('employee_logout' => date('H:i:s')));
		$result['status'] = 'ok';
		die(json_encode($result));
	}
}
