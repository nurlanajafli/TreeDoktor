<?php

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class Mdl_emp_login extends JR_Model
{
	protected $_table = 'emp_login';
	protected $primary_key = 'login_id';

	public $after_create = array('addon_login');
	public $before_update = array('addon_login');
	public $before_delete = array('recalculate_data');
	public $after_delete = array('recalculate');

	public $belongs_to = array('mdl_worked' => array('primary_key' => 'login_worked_id', 'model' => 'mdl_worked'));

	public $deleteRow = array();

	public function __construct() {
		parent::__construct();
	}

	function recalculate()
	{
		$this->load->model('mdl_worked');
		$this->load->model('mdl_user');
        $this->load->model('mdl_settings_orm');

        $lunch_state = (bool) $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_state')->stt_key_value;
        if (true === $lunch_state) {
            $lunch_after_hours = $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_after_workhours')->stt_key_value;
            $lunch_time = $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_time')->stt_key_value;
        }

        $deduction_state = (bool) $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_deduction_state')->stt_key_value;

		if(!isset($this->deleteRow->login_worked_id))
			return false;
		$allDayRows = $this->get_many_by('login_worked_id', $this->deleteRow->login_worked_id);
		$worked_hours = 0;

		$firstLogin = NULL;
		$lastLogout = NULL;

		if(empty($allDayRows))
		{
			$this->mdl_worked->delete($this->deleteRow->login_worked_id);
			return TRUE;
		}

        $skippedLogin = 0;
        $skippedLogout = 0;

		foreach($allDayRows as $key => $val)
		{
            if($val->login_from_app){
                if($skippedLogin !== $val->login){
                    $skippedLogin = $val->login;
                    continue;
                }
                if($skippedLogout !== $val->logout){
                    $skippedLogout = $val->logout;
                    continue;
                }
            }

			$worked_hours += round((strtotime($val->logout) - strtotime($val->login)) / 3600, 2);

			if($firstLogin > $val->login || !$firstLogin)
				$firstLogin = $val->login;

			if($lastLogout < $val->logout || !$lastLogout)
				$lastLogout = $val->logout;
		}

		$this->mdl_worked->update($this->deleteRow->login_worked_id, [
		    'worked_hours'  => $worked_hours,
            'worked_start'  => $firstLogin,
            'worked_end'    => $lastLogout
        ]);
		$worked = $this->mdl_worked->get($this->deleteRow->login_worked_id);

        if (true === $lunch_state && ($worked->worked_hours >= $lunch_after_hours && $worked->worked_lunch === NULL)) {
            $this->mdl_worked->update($worked->worked_id, array('worked_lunch' => $lunch_time));
        } elseif ($worked->worked_hours < $lunch_after_hours && $worked->worked_lunch) {
            $this->mdl_worked->update($worked->worked_id, array('worked_lunch' => NULL));
        }

		return TRUE;
	}

	function recalculate_data($id)
	{
		$this->deleteRow = $this->get($id);
		return TRUE;
	}

	function addon_login($row)
	{
		$this->load->model('mdl_worked');
		$this->load->model('mdl_payroll');
		$this->load->model('mdl_user');
        $this->load->model('mdl_settings_orm');

        $lunch_state = (bool) $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_state')->stt_key_value;
        if (true === $lunch_state) {
            $lunch_after_hours = $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_after_workhours')->stt_key_value;
            $lunch_time = $this->mdl_settings_orm->get_by('stt_key_name', 'payroll_lunch_time')->stt_key_value;
        }

		if($this->primary_value)
		{
			$currentLogin = $this->get($this->primary_value);

			$row['login'] = isset($row['login']) ? $row['login'] : $currentLogin->login;
			$row['logout'] = (isset($row['logout']) || is_null($row['logout'])) ? $row['logout'] : $currentLogin->logout;

			if(!isset($row['logout']) || $row['logout'] == '00:00' || !$row['logout'])
				$row['logout'] = NULL;
				
			if($row['logout'] && ($row['logout'] < $row['login']))
				$row['logout'] = $row['login'];

			$firstLogin = $row['login'];
			$lastLogout = $row['logout'];

			$allDayRows = $this->get_many_by('login_worked_id', $currentLogin->login_worked_id);
			$worked_hours = 0;


			$this->load->model('mdl_employees', 'employee_model');
			$this->load->model('mdl_user');
			$user = $this->mdl_user->get_payroll_user(array('users.id' => $currentLogin->login_user_id));
			$employee = $user ? $user->result()[0] : [];
			//$employee = $this->employee_model->find_by_id($currentLogin->login_employee_id);
			/************LATE CHECK*****************/
			$late = 0;
			
			if($employee->emp_start_time && $employee->emp_start_time != '00:00:00' && $employee->emp_check_work_time)
			{
				$late = ((strtotime($employee->emp_start_time) + 10 * 60) > strtotime($firstLogin)) ? 0 : 1;
				/*if(strtotime($row['login']) < strtotime($employee->emp_start_time))
					$firstLogin = $row['login'] = $employee->emp_start_time;*/
			}
			/************END LATE CHECK*****************/
			foreach($allDayRows as $key => $val)
			{
				if($val->login_id == $currentLogin->login_id)
					$worked_hours += ($row['logout'] && $row['login']) ? round((strtotime($row['logout']) - strtotime($row['login'])) / 3600, 2) : 0;
				else
				{
					$worked_hours += ($val->logout && $val->login) ? round((strtotime($val->logout) - strtotime($val->login)) / 3600, 2) : 0;

					if($firstLogin > $val->login)
						$firstLogin = $val->login;

					if($lastLogout < $val->logout)
						$lastLogout = $val->logout;
				}
			}
			$this->mdl_worked->update($currentLogin->login_worked_id, array('worked_hours' => $worked_hours, 'worked_start' => $firstLogin, 'worked_end' => $lastLogout, 'worked_late' => $late));
			$worked = $this->mdl_worked->get($currentLogin->login_worked_id);
			if($worked->worked_auto_logout)
				$this->mdl_worked->update($worked->worked_id, array('worked_auto_logout' => 0));
			//if(strtotime($row['login']) < strtotime($employee->emp_start_time))
				//$row['login'] = $employee->emp_start_time;
			
			$payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $worked->worked_date, 'payroll_end_date >=' => $worked->worked_date));

            $this->db->where('login_id', $this->primary_value);
            $this->db->update($this->_table, $row);
		}
		else
		{
			$row = (array) $this->get($row);
			$worked = $this->mdl_worked->get_by(array('worked_date' => $row['login_date'], 'worked_user_id' => $row['login_user_id']));

			if(!isset($row['logout']) || $row['logout'] == '00:00' || !$row['logout'])
				$row['logout'] = NULL;
			if(!$worked || empty($worked))
			{
				$workedData['worked_date'] = $row['login_date'];
				$hours = round((strtotime($row['logout']) - strtotime($row['login'])) / 3600, 2);
				$workedData['worked_hours'] = $hours >= 0 ? $hours : 0;

				if (true === $lunch_state && $hours >= $lunch_after_hours) {
                    $workedData['worked_lunch'] = (float) $lunch_time;
                }

				if(true === $lunch_state && ($hours >= $lunch_after_hours && !isset($row['worked_lunch'])))
					$workedData['worked_lunch'] = (float) $lunch_time;
				elseif(isset($row['worked_lunch']))
				{
					$workedData['worked_lunch'] = (float) $row['worked_lunch'];
					unset($row['worked_lunch']);
				}
				else
					$workedData['worked_lunch'] = NULL;

				$workedData['worked_user_id'] = $row['login_user_id'];

				$this->load->model('mdl_employees', 'employee_model');
				
				$user = $this->mdl_user->get_payroll_user(array('users.id' => $row['login_user_id']));
				$employee = $user ? $user->result_object()[0] : [];
				//$employee = $this->employee_model->find_by_id($row['login_employee_id']);

				$workedData['worked_late'] = 0;
				/************LATE AND CHECK {login_time} <= {db.emp_start_time}*****************/
				if($employee->emp_start_time && $employee->emp_start_time != '00:00:00' && $employee->emp_check_work_time)
				{
					$workedData['worked_late'] = ((strtotime($employee->emp_start_time) + 10 * 60) > strtotime($row['login'])) ? 0 : 1;
					//if(strtotime($row['login']) < strtotime($employee->emp_start_time))
						//$row['login'] = $employee->emp_start_time;
				}
				
				/************END LATE**********************************************************/

				$workedData['worked_hourly_rate'] = isset($row['worked_hourly_rate']) ? $row['worked_hourly_rate'] : $employee->emp_hourly_rate;
                $workedData['worked_hourly_rate'] = $workedData['worked_hourly_rate'] ?: 0;
				if(isset($row['worked_hourly_rate']))
					unset($row['worked_hourly_rate']);
				
				$payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $row['login_date'], 'payroll_end_date >=' => $row['login_date']));
				$workedData['worked_payroll_id'] = $payroll->payroll_id;
				$workedData['worked_start'] = $row['login'];
				$workedData['worked_end'] = $row['logout'];
				$worked_id = $this->mdl_worked->insert($workedData);
				$worked = $this->mdl_worked->get($worked_id);

			}
			else
			{
				$allDayRows = $this->get_many_by('login_worked_id', $worked->worked_id);

				$this->load->model('mdl_employees', 'employee_model');
				
				$user = $this->mdl_user->get_payroll_user(array('users.id' => $worked->worked_user_id));
				$employee = $user ? $user->result_object()[0] : [];
				//$employee = $this->employee_model->find_by_id($worked->worked_employee_id);

				$worked_hours = round((strtotime($row['logout']) - strtotime($row['login'])) / 3600, 2);
				if($worked_hours < 0)
					$worked_hours = 0;
				$firstLogin = $row['login'] ? $row['login'] : NULL;
				$lastLogout = $row['logout'] ? $row['logout'] : NULL;
				foreach($allDayRows as $key => $val)
				{
					$worked_hours += ($val->logout && $val->login) ? round((strtotime($val->logout) - strtotime($val->login)) / 3600, 2) : 0;

					if($firstLogin > $val->login && $val->login)
						$firstLogin = $val->login;

					if($lastLogout < $val->logout  && $val->logout && $lastLogout !== NULL)
						$lastLogout = $val->logout;
				}

				/************LATE CHECK*****************/
				$late = 0;
				if($employee->emp_start_time && $employee->emp_start_time != '00:00:00' && $employee->emp_check_work_time)
				{
					$late = ((strtotime($employee->emp_start_time) + 10 * 60) > strtotime($firstLogin)) ? 0 : 1;
					/*if(strtotime($row['login']) < strtotime($employee->emp_start_time))
						$row['login'] = $employee->emp_start_time;*/
				}
				/************END LATE CHECK*****************/

				$this->mdl_worked->update($worked->worked_id, array('worked_hours' => $worked_hours, 'worked_start' => $firstLogin, 'worked_end' => $lastLogout, 'worked_late' => $late));
				$worked = $this->mdl_worked->get($worked->worked_id);
				$payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $worked->worked_date, 'payroll_end_date >=' => $worked->worked_date));
			}
			$row['login_worked_id'] = $worked->worked_id;

            $this->db->where('login_id', $row['login_id']);
            $this->db->update($this->_table, $row);
		}
		
		/*****************DEDUCTION********************/

		$this->load->model('mdl_deductions');
		$deduction = $this->mdl_deductions->get_by(array('deduction_payroll_id' => $payroll->payroll_id, 'deduction_user_id' => $employee->employee_id));

		if($employee->deductions_state && !$deduction)
			$this->mdl_deductions->insert(array('deduction_user_id' => $employee->employee_id, 'deduction_payroll_id' => $payroll->payroll_id, 'deduction_amount' => $employee->deductions_amount));

        if (true === $lunch_state && $worked->worked_hours >= $lunch_after_hours)
            $this->mdl_worked->update($worked->worked_id, array('worked_lunch' => $lunch_time));
        else
            $this->mdl_worked->update($worked->worked_id, array('worked_lunch' => NULL));

		if(isset($row['worked_hourly_rate']))
			unset($row['worked_hourly_rate']);
		if(isset($row['worked_lunch']))
			unset($row['worked_lunch']);

		$CI = &get_instance();

		if(!isset($CI->timer) && $this->config->item('wsClient')) {
            $wsClient = new WSClient(new Version1X($this->config->item('wsClient') . '?chat=1&user_id=' . $employee->employee_id));
            if($wsClient) {
                $wsClient->initialize();
                $wsClient->emit('room', ['chat-' . $employee->employee_id]);
                $wsClient->emit('message', ['method' => 'trackerHistoryChanged', 'params' => ['date' => $worked->worked_date]]);
                $wsClient->close();
            }
        }

        return $row;
	}

    /*public function insert($data, $skip_validation = FALSE)
    {
        if ($skip_validation === FALSE)
        {
            $data = $this->validate($data);
        }
        if ($data !== FALSE)
        {
            $data = $this->trigger('before_create', $data);

            $this->_database->insert($this->_table, $data);
            $insert_id = $this->_database->insert_id();


            //$this->_database->where('login_id', $insert_id);
            //$this->_database->update($this->_table, $data);

            $this->trigger('after_create', $insert_id);
            return $insert_id;
        }
        else
        {
            return FALSE;
        }
    }*/

	function get_peoples($where)
	{
		$this->_database->select("users.*, employees.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, emp_login.*", FALSE);
		$this->_database->join('users', 'emp_login.login_user_id = users.id');
		$this->_database->join('employees', 'users.id = employees.emp_user_id');
		
		$this->_database->where($where);
		$this->_database->order_by('emp_login.login', 'ASC');
		$result = $this->_database->get($this->_table)->result();
		foreach ($result as $key => &$row)
		{
			$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
		}
		return $result;
	}

	public function getLastEmpDataByMemberID($emp_id)
    {
        return $this->db
            ->select('MAX(login_id) as login_id, worked_hours as total_hrs')
            ->join('employee_worked', 'login_worked_id=worked_id', 'left')
            ->where('login_user_id', $emp_id)
            ->get($this->_table)
            ->row_array();
    }

    public function getLastEmpDataByLoginID($login_id)
    {
        return $this->db
            ->select('login_id, worked_hours as total_hrs')
            ->join('employee_worked', 'worked_id=login_worked_id', 'left')
            ->where('login_id', $login_id)
            ->get($this->_table)
            ->row_array();
    }
}
