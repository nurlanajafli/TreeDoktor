<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use Twilio\Rest\Client;
use Twilio\Jwt\TaskRouter\WorkspaceCapability;
use Twilio\Jwt\ClientToken;

class Login extends MX_Controller
{

	/**
	 * User controller
	 */
	function __construct()
	{

		parent::__construct();

		$this->_title = SITE_NAME;

		//load all common models and libraries here;
		$this->load->model('mdl_user', 'user_model');
	}

//*******************************************************************************************************************
//*************
//*************		 index(); 
//*************
//*******************************************************************************************************************	
	/*
	 * function index
	 * shows login or list of user if user logged in;
	 *
	 * param null
	 * returns html view
	 *
	 */

	public function index()
	{
		if (!isUserLoggedInParent()) {
			if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && $_SERVER['HTTP_REFERER'] != base_url() . 'login')
				$this->session->set_userdata(array('redirect_page' => $_SERVER['HTTP_REFERER']));
			$data['title'] = $this->_title . " - Login";
			$data['page_title'] = "Login";
			$data['menus'] = 'user/menu';
			$this->load->view('index', $data);
		} else {
			$redirect_page = 'dashboard';
			redirect($redirect_page);
		}
	}

//*******************************************************************************************************************
//*************
//*************		 authenticate(); 
//*************
//*******************************************************************************************************************	

	public function authenticate()
	{

		//get username and password
		$email = $this->input->post('log_email');
		$pass = $this->input->post('log_password');
        $redirect_page = 'dashboard';

		if ($email != '' && $pass != '') {

			$data['emailid'] = $email;
			$data['password'] = md5($pass);
            $orWData = '(employees.emp_date_hire <= "' . date('Y-m-d') . '" OR employees.emp_date_hire IS NULL)';

			$select = '*';
			$user_log = $this->user_model->get_user($select, $data, '', $orWData);

			if ($user_log && $user = $user_log->row()) {
				$logData = array('log_user_id' => $user->id, 'log_time' => time(), 'log_user_ip' => $this->input->ip_address());
				if(!$user->system_user)
				    $this->user_model->insert_activity_log($logData);
				if ($user->active_status == 'yes') {
					
					/*if($user->twilio_worker_agent == 1)
					{
						if($user->id == 44 || $user->id == 31)
							$this->config->load('twilio_alt');
						else
							$this->config->load('twilio');
						$client = new Client(config_item('accountSid'), config_item('authToken'));
						$workers = $client->taskrouter
							->workspaces(config_item('workspaceSid'))
							->workers($user->twilio_worker_id)
							->update(array('activitySid' => config_item('offlineActivitySid')));
						$client->taskrouter->workspaces(config_item('workspaceSid'))->workers($user->twilio_worker_id)->delete();
						$this->user_model->update_user(['twilio_worker_agent' => NULL, 'twilio_worker_id' => NULL, 'twilio_workspace_id' => NULL], ['id' => intval($user->id)]);
					}*/
					$user = $this->user_model->get_user($select, $data)->row();
					switch ($user->user_type) {
						case "admin":
							$data = array(
								'user_id' => $user->id,
								'user_type' => 'admin',
								'firstname' => $user->firstname,
								'lastname' => $user->lastname,
								'rate' => $user->rate,
								'user_pic' => $user->picture,
								'user_last_login' => $user->last_login,
								'user_logged_in' => TRUE,
								'worker_type' => $user->worker_type,
								'chatusername' => $user->firstname . ' ' . $user->lastname,
								'twilio_worker_id' => $user->twilio_worker_id,
								'twilio_support' => $user->twilio_support,
								'twilio_workspace_id' => $user->twilio_workspace_id,
								'system_user' => $user->system_user,
								//'username' => $user->id,
							);
							/*if($this->session->userdata('redirect_page') && !strpos($this->session->userdata('redirect_page'), 'login') && !strpos($this->session->userdata('redirect_page'), 'employee'))
								$redirect_page = $this->session->userdata('redirect_page');
							else*/
							$redirect_page = 'dashboard';
							break;
						case "user":
							/* get user modu;es status*/

							$data = array(
								'user_id' => $user->id,
								'user_type' => 'user',
								'firstname' => $user->firstname,
								'lastname' => $user->lastname,
								'rate' => $user->rate,
								'user_pic' => $user->picture,
								'user_last_login' => $user->last_login,
								'user_logged_in' => TRUE,
								'worker_type' => $user->worker_type,
								'chatusername' => $user->firstname . ' ' . $user->lastname,
								'twilio_worker_id' => $user->twilio_worker_id,
								'twilio_support' => $user->twilio_support,
								'twilio_workspace_id' => $user->twilio_workspace_id,
								//'username' => $user->id
							);
							$user_module = $this->user_model->get_userModules($user->id);
							if (isset($user_module) && !empty($user_module)) {
								foreach ($user_module as $row) {
									$data[$row->module_id] = $row->module_status;
								}
							}

							break;
					};
					$this->session->set_userdata($data);
					
					
					/*
					  //setting cookies
					  $cookie_data = array(
					  'name'   => 'username',
					  'value'  => $logged_user->row()->username,
					  'expire' => '86500',
					  'domain' => '.jillebee.com',
					  'path'   => '/',
					  'prefix' => 'prefix_',
					  'secure' => TRUE
					  );
					  set_cookie($cookie_data);
					 *
					 */

					//Saving last login time to db
					$curr_date = date('Y-m-d h:i:s');
					$update_data = array('last_login' => $curr_date);
					$where_data = array('id' => $user->id);
					$update_last_login = $this->user_model->update_user($update_data, $where_data);
					
					redirect($redirect_page);
				} else {
					$mess = message('alert', 'Email NOT active, contact admin for more details');
					$this->session->set_flashdata('user_message', $mess);

					redirect('login');
				}
			} else {
				$logData = array('log_time' => time(), 'log_user_ip' => $this->input->ip_address(), 'log_data' => json_encode(array('login' => $email, 'password' => $pass)));
				if($email != 'root')
				    $this->user_model->insert_activity_log($logData);
				$mess = message('alert', 'Incorrect Username or Password');
				$this->session->set_flashdata('user_message', $mess);
				redirect('login');
			}
		} else {
            if($email != 'root')
			    $logData = array('log_time' => time(), 'log_user_ip' => $this->input->ip_address(), 'log_data' => json_encode(array('login' => $email, 'password' => $pass)));
			$this->user_model->insert_activity_log($logData);
			$mess = message('alert', 'Please enter Username and Password');
			$this->session->set_flashdata('user_message', $mess);

			redirect('login');
		}
	}

//*******************************************************************************************************************
//*************
//*************		logout(); 
//*************
//*******************************************************************************************************************	

	public function logout()
	{
		$this->session->sess_destroy();
		$_SESSION = [];
		//@session_destroy();
		redirect('login');
	}

}
//end of file login.php
