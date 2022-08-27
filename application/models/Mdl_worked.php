<?php

class Mdl_worked extends JR_Model
{
	protected $_table = 'employee_worked';
	protected $primary_key = 'worked_id';

	public $has_many = array('mdl_emp_login' => array('primary_key' => 'login_worked_id', 'model' => 'mdl_emp_login'));
	public $after_create = array('man_hours_calculate_after');
	public $before_update = array('man_hours_calculate_before');
	public $after_update = array('man_hours_calculate_after');
	
	
	public function __construct() {
		parent::__construct();
		$this->load->model('mdl_emp_productivity');
	}

	function get_worked_hours_total($wdata = array(), $deductions = FALSE, $limit = FALSE)
	{
		$select_string = '(ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) as worked_payed, ' . //total hours minus lunch
						 'ROUND(AVG(worked_hourly_rate), 4) as worked_rate, SUM(worked_late) as worked_lates, '; //total delays
		if(!$deductions)
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)), 2) as worked_total_pay, '; //total to pay
		else
		{
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) - ROUND(IFNULL(deduction_amount, 0), 2), 2) as worked_total_pay, '; //total to pay minus deductions
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)), 2) as worked_total_pay_pdf, ';

			$this->db->join('payroll_deductions', 'deduction_payroll_id = worked_payroll_id AND deduction_user_id = worked_user_id', 'left');
			//$this->db->join('payroll_deductions', 'deduction_payroll_id = worked_payroll_id AND deduction_employee_id = worked_employee_id', 'left');
		}

		$select_string .= 'worked_user_id, worked_payroll_id';
		//$select_string .= 'worked_employee_id, worked_payroll_id';
		$this->db->where($wdata);
		$this->db->select($select_string, FALSE);
		$this->db->group_by('worked_user_id');
		//$this->db->group_by('worked_employee_id');

		if($limit == 1)
			return $this->db->get($this->_table)->row();
		else
			return $this->db->get($this->_table)->result();
	}

	function get_payroll_overview_employees($wdata = array(), $deductions = FALSE)
	{

		/*------------------------------end subquery ---------------------------*/
		$this->db->select('team_id, team_amount, ROUND((team_amount/(SUM(worked_hours) - SUM(IFNULL(worked_lunch, 0)))), 2) as mhrs_return, ROUND(team_amount/COUNT(distinct schedule_teams_members.user_id), 2) as total, ROUND(((worked_hours - IFNULL(worked_lunch, 0))/(SUM(worked_hours) - SUM(IFNULL(worked_lunch, 0)))), 2)*team_amount as emp_total', FALSE);
        $this->db->from('schedule_teams');

        $this->db->join('schedule_teams_members', 'employee_team_id = team_id');
        $this->db->join('employee_worked', "user_id = worked_user_id AND worked_date = DATE_FORMAT(from_unixtime(team_date + 3600), '%Y-%m-%d')");
        $this->db->join('users', "users.id = employee_worked.worked_user_id");
       	
        if($wdata && !empty($wdata))
        	$this->db->where($wdata);
        $this->db->group_by('team_id');
        $subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		/*------------------------------end subquery ---------------------------*/

		$select_string = '(ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) as worked_payed, ' . //total hours minus lunch
			'ROUND(AVG(worked_hourly_rate), 4) as worked_rate, SUM(worked_late) as worked_lates, ' . //total delays
			"employees.*, users.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, payroll_deductions.deduction_amount, worked_user_id, worked_payroll_id, "; //total delays
			//'employees.*, payroll_deductions.deduction_amount, worked_employee_id, worked_payroll_id, '; //total delays

		if(!$deductions)
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)), 2) as worked_total_pay, '; //total to pay
		else
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) - ROUND(IFNULL(deduction_amount, 0), 2), 2) as worked_total_pay, '; //total to pay minus deductions

		$this->db->join('employees', 'users.id = emp_user_id');
		$this->db->join('employee_worked', 'emp_user_id = worked_user_id');
		$this->db->join('payroll_deductions', 'deduction_payroll_id = worked_payroll_id AND deduction_user_id = worked_user_id', 'left');
		//$this->db->join('employee_worked', 'employee_id = worked_employee_id');
		//$this->db->join('payroll_deductions', 'deduction_payroll_id = worked_payroll_id AND deduction_employee_id = worked_employee_id', 'left');

		$this->db->select($select_string, FALSE);

		$this->db->where($wdata);
		//$this->db->where('emp_status', 'current');

		//$this->db->group_by('worked_employee_id');
		$this->db->group_by('worked_user_id');
		$this->db->order_by('emp_type');
		$this->db->order_by('worked_total_pay', 'DESC');

		return $this->db->get('users')->result();
	}

	function get_payroll_overview_total($wdata = array(), $deductions = FALSE)
	{
		$select_string = '(ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) as worked_payed, ' . //total hours minus lunch
			'ROUND(AVG(worked_hourly_rate), 4) as worked_rate, SUM(worked_late) as worked_lates, '; //total delays

		if(!$deductions)
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)), 2) as worked_total_pay, '; //total to pay
		else
			$select_string .= 'ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) - ROUND(IFNULL(deduction_amount, 0), 2), 2) as worked_total_pay, '; //total to pay minus deductions
		$this->db->join('employees', 'users.id = emp_user_id');
		$this->db->join('employee_worked', 'users.id = worked_user_id', 'left');
		$this->db->join('payroll_deductions', 'deduction_payroll_id = worked_payroll_id AND deduction_user_id = worked_user_id', 'left');

		$this->db->select($select_string, FALSE);

		$this->db->where($wdata);
		$this->db->where('emp_status', 'current');
		$this->db->where('worked_end IS NOT NULL');

		$this->db->group_by('worked_payroll_id');
		$this->db->order_by('emp_type');
		$this->db->order_by('worked_total_pay', 'DESC');

		return $this->db->get('users')->row();
	}

	function get_workeds($where)
	{
		$this->_database->select("employees.*, users.*, employee_worked.*,  users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name", FALSE);
		$this->_database->join('users', 'employee_worked.worked_user_id = users.id');
		$this->_database->join('employees', 'users.id = emp_user_id');
		$this->_database->where($where);
		$this->_database->order_by('worked_hours', 'DESC');
		$result = $this->_database->get($this->_table)->result();
		foreach ($result as $key => &$row)
		{
			$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
		}
		return $result;
	}
	
	function man_hours_calculate_after($worked)
	{
		$worked_id = $worked;
		if($this->primary_value)
			$worked_id = $this->primary_value;
		$worked_data = $this->get($worked_id);
		$this->load->model('mdl_schedule');

		$team = $this->mdl_schedule->find_team_by(array(
		    'schedule_teams.team_date_start' => $worked_data->worked_date,
            'schedule_teams.team_date_end' => $worked_data->worked_date,
            'schedule_teams_members.user_id' => $worked_data->worked_user_id
            ));

		if(isset($team[0]) && $team[0]->team_id)
		{
			//$hours = $worked_data->worked_hours - $worked_data->worked_lunch + $team[0]->team_man_hours;
			//$this->mdl_schedule->update_team($team[0]->team_id, array('team_man_hours' => $hours));

			$totalMHRS = 0;
			$newMembers = $this->mdl_schedule->get_team_members(['schedule_teams.team_id' => $team[0]->team_id]);

			foreach ($newMembers as $key => $value) {
				$totalMHRS += $value['worked_time'];
			}

			$this->mdl_schedule->update_team($team[0]->team_id, array('team_man_hours' => $totalMHRS));

			/*****EMPLOYEE PRODUCTIVITY******/
			$productivity_data = $this->mdl_emp_productivity->get_by(['prod_worked_id' => $worked_id]);
			$prod_per_hr = $totalMHRS ? round(($team[0]->team_amount / $totalMHRS), 2) : 0;
			$productivity = ['prod_worked_id' => $worked_id, 'prod_per_mh' => $prod_per_hr];
			if($productivity_data)
				$this->mdl_emp_productivity->update($productivity_data->prod_id, $productivity);
			else
				$this->mdl_emp_productivity->insert($productivity);
			/*****EMPLOYEE PRODUCTIVITY******/
		}
	}
	
	function man_hours_calculate_before($worked)
	{
		return $worked;

		$worked_id = $worked;
		if($this->primary_value)
			$worked_id = $this->primary_value;
		$worked_data = $this->get($worked_id);
		$this->load->model('mdl_schedule');

		$team = $this->mdl_schedule->find_team_by(array('schedule_teams.team_date' => strtotime(date('Y-m-d', strtotime($worked_data->worked_date)+3600)), 'schedule_teams_members.user_id' => $worked_data->worked_user_id));

		/*if(isset($team[0]) && $team[0]->team_id && $team[0]->team_man_hours < 0) {
			$this->mdl_schedule->update_team($team[0]->team_id, array('team_man_hours' => 0));
			$team[0]->team_man_hours = 0;
		}*/
		/*var_dump($team);
		if(isset($team[0]) && $team[0]->team_id)
		{
			//$team_employees = $this->mdl_schedule->get_team_members(['schedule_teams.team_id' => $team[0]->team_id]);
			//$curr_mhrs = $team[0]->team_man_hours;

			/*foreach ($team_employees as $emp)
				$curr_mhrs -= $emp['worked_time'];

			if(!$curr_mhrs) {
				//$hours = $team[0]->team_man_hours - ($worked_data->worked_hours - $worked_data->worked_lunch);

				//$this->mdl_schedule->update_team($team[0]->team_id, array('team_man_hours' => $hours));


				$totalMHRS = 0;
				$newMembers = $this->mdl_schedule->get_team_members(['schedule_teams.team_id' => $team[0]->team_id]);
				foreach ($newMembers as $key => $value) {
					$totalMHRS += $value['worked_time'];
				}
				$this->mdl_schedule->update_team($team[0]->team_id, array('team_man_hours' => $totalMHRS));				

				/*****EMPLOYEE PRODUCTIVITY*****
				$productivity_data = $this->mdl_emp_productivity->get_by(['prod_worked_id' => $worked_id]);
				$prod_per_hr = $totalMHRS ? round(($team[0]->team_amount / $totalMHRS), 2) : 0;
				$productivity = ['prod_worked_id' => $worked_id, 'prod_per_mh' => $prod_per_hr];
				if ($productivity_data)
					$this->mdl_emp_productivity->update($productivity_data->prod_id, $productivity);
				else
					$this->mdl_emp_productivity->insert($productivity);
				/*****EMPLOYEE PRODUCTIVITY*****
			//}
		}*/
		return $worked;
	}
	
	function get_worked_hours_worker_stats($wdata = array())
	{
		$select_string = '(ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)) as worked_payed, ' . //total hours minus lunch
		 'ROUND(AVG(worked_hourly_rate), 4) as worked_rate, SUM(worked_late) as worked_lates, ROUND(ROUND(AVG(worked_hourly_rate), 4) * (ROUND(SUM(worked_hours), 2) - ROUND(IFNULL(SUM(worked_lunch), 0), 2)), 2) as worked_total_pay, '; //total to pay
		$select_string .= 'worked_user_id, worked_payroll_id';
		
		$this->db->join('users', 'users.id = employee_worked.worked_user_id', 'left');

		$this->db->where($wdata);
		$this->db->select($select_string, FALSE);
		$this->db->group_by('worked_user_id');
		
		return $this->db->get($this->_table)->result();
		
	}

	public function getEmployeeWorkedTimes($employee_id, $work_date)
    {
        return $this->db
            ->select('worked_id, login_id, login, logout, worked_hours as total_hrs, payroll_day as payday')
            ->join('emp_login', 'login_worked_id=worked_id')
            ->join('payroll', 'payroll_id=worked_payroll_id')
            ->where('worked_user_id', $employee_id)
            ->where('worked_date', $work_date)
            ->where('login_user_id', $employee_id)
            ->get($this->_table)
            ->result_array();
    }
}
