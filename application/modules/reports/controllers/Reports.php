<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\classes\models\QBClass;
use application\modules\invoices\models\Invoice;
use application\modules\user\models\User;
use application\modules\references\models\Reference;
use application\modules\employees\models\EmployeeWorked;
use application\modules\schedule\models\Expense;
use application\modules\schedule\models\ExpenseType;
use Illuminate\Support\Facades\Auth;

class Reports extends MX_Controller
{

//*******************************************************************************************************************
//*************
//*************																						Reports Controller;
//*************
//*******************************************************************************************************************	
	function __construct()
	{

		parent::__construct();

		//Checking if user is logged in;
		if (!isUserLoggedIn()) {
			redirect('login');
		}
		//if($this->session->userdata('user_type') != "admin"){ show_404();}

		$this->_title = SITE_NAME;
		$this->load->library('pagination');
        $this->load->library('Common/InvoiceActions');
		//load all common models and libraries here;
		$this->load->model('mdl_reports', 'mdl_reports');
		$this->load->model('mdl_user', 'mdl_user');
		$this->load->model('mdl_est_status', 'mdl_est_status');
		$this->load->helper('business_days_cal');
	}

//*******************************************************************************************************************
//*************
//*************																									Index;
//*************
//*******************************************************************************************************************		 
	public function index()
	{

		if ($this->uri->segment(2) != 'general')
			redirect(base_url('reports/general'));

		$data['title'] = $this->_title . ' - Reports';

		$this->load->model('mdl_clients');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_invoices');

		$data['calls_stat'] = $this->mdl_clients->stat_calls();
		$data['payments_stat'] = $this->mdl_clients->stat_payments();
		$data['clients_stat'] = $this->mdl_clients->stat_clients();
		$data['estimates_stat'] = $this->mdl_estimates->stat_estimates();
		$data['invoices_like'] = $this->mdl_invoices->record_count(array(), array('invoice_like' => 1));
		$data['invoices_dislike'] = $this->mdl_invoices->record_count(array(), array('invoice_like' => 0));

		$data['avarage_payments_day'] = $this->mdl_clients->stat_payments_by_day();
		$data['avarage_payments_month'] = $this->mdl_clients->stat_payments_by_month();

		$data['com'] = $this->mdl_reports->com_overflow();

		$this->load->view("index", $data);

	}


//*******************************************************************************************************************
//*************
//*************																								Estimates;
//*************
//*******************************************************************************************************************	
	
// HELPERS:

	function ajax_load_estimates()
	{
		$limit = 100;
		$offset = $this->input->post('offset');
		$estimator_id = $this->input->post('estimator_id');
		if(!$offset)
			die(json_encode(array('status' => 'error')));
		$data['status'] = 'ok';
		$estimators_files = $this->mdl_reports->get_estimators_files($estimator_id, $limit, $offset);
		$data['estimators_files'] = $estimators_files ? $estimators_files->result() : array();
		die(json_encode($data));
	}

//*********************************************************************************************************************
//*************
//*************																								Workorders;
//*************
//*********************************************************************************************************************
	
//*********************************************************************************************************************
//*************
//*************																								  Invoices;
//*************
//*********************************************************************************************************************

	
	//*********************************************************************************************************************
	//*************
	//*************																							  Payroll;
	//*************
	//*********************************************************************************************************************

	public function payroll_old($id = NULL, $date = NULL, $week = NULL)
	{
		show_404();die;
		if($this->session->userdata('user_type') != "admin")
			show_404();
		$this->load->model('mdl_schedule', 'mdl_schedule'); 
		$this->load->helper('utilities');
		$data['title'] = $this->_title . " - Reports Payroll";
		$data['page_title'] = "Payroll Report";
		$data['page'] = "reports/payroll";

		$data['cdate'] = date("m/Y");

		/*****************    getting employees data *********/
		$this->load->model('mdl_employees', 'employee_model');
		$employees = $this->employee_model->get_employee('', '', 'emp_status ASC');
		if (!empty($employees)) {
			$data["employees"] = $employees->result_array();
		}
		//var_dump($_POST); die;
		$data['get_data_by_date'] = $curdate = $this->input->post("get_data_by_date");
		$data["which_week"] = $this->input->post("which_week");

		if ($date == true)
			$data['get_data_by_date'] = $curdate = $date;
		
		elseif(empty($curdate) && $date == NULL)
			$data['get_data_by_date'] = $curdate = $curdate = date("Y-m-d");

		/***************    getting total week days as per current date  ********/
		$data["weekdays"] = get_week_days($curdate);
		$data["next_weekdays"] = get_week_days($data["weekdays"]["next_week_date"]);
		$data["curdate"] = $curdate;
		if(empty($id))
			$id = $this->input->post("selEmmployee");
		$monthyear = $this->input->post("monthyear");
		$generate_pdf = $this->input->post("generate_pdf");
		$data["generate_pdf_block"] = false;
		$data["c_year"] = date("Y");
		$data["c_month"] = date("m");
//		$data["cal"] = cal($data["c_month"],$data["c_year"]);


		if (!empty($id)) {
			if (!empty($data["employees"])) {
				foreach ($data["employees"] as $ekk => $evv) {
					if ($evv["employee_id"] == $id) {
						$data["employee_row"] = @$evv;
					}
				}
			}

			//list($month,$year) = explode("/",$monthyear);
			//$data["c_year"] = $month;
			//$data["c_month"] = $year;
			$this->load->model("mdl_employee", "emp_login");
			//$emp_data = $this->emp_login->get_data_by_month($month,$year,$id);
			$get_data = array();
			$get_data["start_date"] = $data["weekdays"]["total_week_days"][0];
			$get_data["end_date"] = $data["next_weekdays"]["total_week_days"][6];
			$get_data["id"] = $id;

			$this->load->model('mdl_schedule');
			$whereBonus['team_date >='] = strtotime($get_data['start_date']);
			$whereBonus['team_date <='] = strtotime($get_data['end_date'] . ' 23:59:59');
			$whereBonus['employee_id'] = $get_data['id'];
			$bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);
			$bonuses = array();

			foreach($bonusesRows as $bonus)
				$bonuses[date('Y-m-d', $bonus['team_date'])] = isset($bonuses[date('Y-m-d', $bonus['team_date'])]) ? ($bonuses[date('Y-m-d', $bonus['team_date'])] + $bonus['bonus_amount']) : $bonus['bonus_amount'];
			foreach($bonuses as $key => $val)
				$bonuses[$key] = $val >= 0 ? $val : 0;

			$data['collectedBonuses'] = $this->mdl_schedule->get_collected_bonuses_sum($id, $get_data['end_date']);

			$data['bonusesDates'] = $this->mdl_schedule->get_collected_bonuses_dates($get_data['end_date']);

			$emp_data = $this->emp_login->get_emp_login_data_biweekly($get_data);

			$return_data = array();
			if (!empty($emp_data)) {
				foreach ($emp_data as $ek => $ev) {
					if (is_file(UPLOAD_EMPLOYEE_PIC . $ev["logout_image"])) {
						$ev["logout_image"] = _EMPLOYEE_PIC . $ev["logout_image"];
					} else {
						$ev["logout_image"] = "";
					}

					if (is_file(UPLOAD_EMPLOYEE_PIC . $ev["login_image"])) {
						$ev["login_image"] = _EMPLOYEE_PIC . $ev["login_image"];
					} else {
						$ev["login_image"] = "";
					}

					if (empty($ev["time_diff"])) {
						$ev["time_diff"] = "00.00";
					}
					$loginTime = $ev["login_time"];
					/*if (strtotime($ev["login_time"])) {
						$ev["login_time"] = date("H:i", strtotime($ev["login_time"]));
					} else {
						$ev["login_time"] = "00:00";
					}

					if (strtotime($ev["logout_time"])) {
						$ev["logout_time"] = date("H:i", strtotime($ev["logout_time"]));
					} else {
						$ev["logout_time"] = "00:00";
					}*/
					$date_day = date("d", strtotime($loginTime));
					$return_data[date("Y-m-d", strtotime($loginTime))][] = $ev;
				}

			}
			
			$data["emp_data"] = $return_data;
			$data["bonuses"] = $bonuses;
			$this->load->model('mdl_schedule');
			$wdata['schedule_absence.absence_employee_id'] = $data['employee_row']['employee_id'];
			$wdata['schedule_absence.absence_date >='] = strtotime($get_data['start_date']);
			$wdata['schedule_absence.absence_date <='] = strtotime($get_data['end_date']);
			$absences = $this->mdl_schedule->get_absence($wdata);
			
			$teams = $this->mdl_schedule->get_teams(array('team_date >=' => strtotime($get_data["start_date"]), 'team_date <=' => strtotime($get_data["end_date"])));
			
			foreach($teams as $key=>$val)
			{
				$members = $this->mdl_schedule->get_team_members(array('employee_team_id = ' => $val->team_id));
				foreach($members as $jkey=>$member)
				{
					if($member['employee_id'] == $id)
						$data['teams'][date('Y-m-d', $member['team_date'])] = $members;
				}
			}
			foreach($absences as $key=>$val)
				$data['absences'][$absences[$key]['absence_date']] = $val;
			
            
			if ($generate_pdf == true) {

				ini_set('memory_limit', '32M'); // boost the memory limit if it's low
				$this->load->library('mpdf');
				$data["generate_pdf_block"] = true;
				$html = $this->load->view('payroll_pdf', $data, TRUE);
				/*$this->mpdf->AddPage('L', // L - landscape, P - portrait
					'', '', '', '',
					30, // margin_left
					30, // margin right
					30, // margin top
					30, // margin bottom
					18, // margin header
					12); // margin footer*/
				$this->mpdf->WriteHTML($html);
				$file = "payroll_employee_id_" . $id . '.pdf';
				$this->mpdf->Output($file, 'I');
				exit;
			}
		}
		//echo '<pre>'; var_dump($data['teams']); die;
		
		$this->load->view('payroll', $data);
	}

	//*********************************************************************************************************************
	//*************
	//*************																							  Payroll overview;
	//*************
	//*********************************************************************************************************************

	public function payroll_overview_old()
	{
		show_404();die;
		if($this->session->userdata('user_type') != "admin")
			show_404();
		$this->load->helper("utilities");
		$data['title'] = $this->_title . " - Reports Payroll Overview";
		$data['page_title'] = "Reports Payroll Overview";
		$data['page'] = "reports/payroll_overview";
		$data['cdate'] = date("m/Y");
		$monthyear = $this->input->post("monthyear");
		$generate_pdf = $this->input->post("generate_pdf");
		$data["generate_pdf_block"] = false;

		$curdate = $this->input->post("curdate");
		if (empty($curdate)) {
			$curdate = date("Y-m-d");
		}
		$data["weekdays"] = get_week_days($curdate);
		$data["next_weekdays"] = get_week_days($data["weekdays"]["next_week_date"]);
		$data["curdate"] = $curdate;

		/*
		$year = date("Y");
		$month = date("m");

		if(!empty($monthyear)) {
			list($month,$year) = explode("/",$monthyear);
		}
		$data["c_year"] = $month;
		$data["c_month"] = $year;*/
		$this->load->model("mdl_employee", "emp_login");
		$this->load->model('mdl_employees', 'employee_model');
		$get_data = array();
		$get_data["login_time >="] = $data["weekdays"]["total_week_days"][0] . ' 00:00:00';
		$get_data["login_time <="] = $data["next_weekdays"]["total_week_days"][6] . ' 23:59:59';

		//$emp_data = $this->emp_login->get_overview_report_biweekly($get_data, 'employees.emp_type');
		$users = $this->employee_model->find_All(array('emp_status' => 'current'), 'employees.emp_type');
		foreach($users as $key=>$val)
			$data['users'][$val->employee_id] = $val;

		$emp_data = $this->emp_login->get_overview_report_biweekly($get_data, 'employees.emp_type');
		//echo $this->db->last_query();
		$date = NULL;
		$emp_id = NULL;
		foreach($emp_data as $key => $workData)
		{
			if($workData['logout_time'] == '0000-00-00 00:00:00')
				continue;
			//echo $workData['emp_type'] . '; ';
			if($workData['employee_id'] != $emp_id)
			{
				$emp_id = $workData['employee_id'];
				//$workedAtDay = array();
			}
			$date = date('Y-m-d', strtotime($workData['login_time']));
			$data['emp_data'][$workData['emp_type']][$workData['employee_id']]['emp_name'] = $workData['emp_name'];
			$data['emp_data'][$workData['emp_type']][$workData['employee_id']]['worked'] = isset($data['emp_data'][$workData['emp_type']][$workData['employee_id']]['worked']) ? $data['emp_data'][$workData['emp_type']][$workData['employee_id']]['worked'] + round((strtotime($workData['logout_time']) - strtotime($workData['login_time'])) / 3600, 2) : round((strtotime($workData['logout_time']) - strtotime($workData['login_time'])) / 3600, 2);
			if(!isset($workedAtDay[$emp_id][$date]) || (isset($workedAtDay[$emp_id][$date]) && $workedAtDay[$emp_id][$date] !== FALSE))
				$workedAtDay[$emp_id][$date] = isset($workedAtDay[$emp_id][$date]) ? $workedAtDay[$emp_id][$date] + round((strtotime($workData['logout_time']) - strtotime($workData['login_time'])) / 3600, 2) : round((strtotime($workData['logout_time']) - strtotime($workData['login_time'])) / 3600, 2);
			if($workedAtDay[$emp_id][$date] !== FALSE && $workedAtDay[$emp_id][$date] >= 5 && !$workData['no_lunch'])
			{
				$data['emp_data'][$workData['emp_type']][$workData['employee_id']]['worked'] -= 0.5;
				$workedAtDay[$emp_id][$date] = FALSE;
			}
			$data['emp_data'][$workData['emp_type']][$workData['employee_id']]['to_paid'] = round(($data['emp_data'][$workData['emp_type']][$workData['employee_id']]['worked'] * $workData['employee_hourly_rate']), 2);
			$data['emp_data'][$workData['emp_type']][$workData['employee_id']]['emp_hourly_rate'] = $workData['emp_hourly_rate'];
			$data['emp_data'][$workData['emp_type']][$workData['employee_id']]['emp_type'] = $workData['emp_type'];
		}
		

		if ($generate_pdf == true) {
			$this->load->library('mpdf');
			$data["generate_pdf_block"] = true;
			$html = $this->load->view('payroll_overview_pdf', $data, TRUE);

			$this->mpdf->WriteHTML($html);
			$file = "payroll_employee_id_" . isset($id) ? $id : 0 . '.pdf';
			$this->mpdf->Output($file, 'I');
			exit;
		}
		$this->load->view('payroll_overview', $data);
	}

	

	public function expenses()
	{
		
		$this->load->model('mdl_expense');
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_payroll');
		$data['title'] = $this->_title . ' - Reports - Expenses';
		
		$wdata['expense_date >='] = strtotime(date('Y-m-01 00:00:00'));
		$wdata['expense_date <='] = strtotime(date('Y-m-t 00:00:00'));
        $from = null;
        $to = null;
		if ($this->input->post('from')){
//            $wdata['expense_date >='] = strtotime($this->input->post('from'));
            $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            $wdata['expense_date >='] = strtotime($from->format('Y-m-d') . " 00:00:00");
        }
		if ($this->input->post('to')){
//            $wdata['expense_date <='] = strtotime($this->input->post('to'));
            $to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
            $wdata['expense_date <='] = strtotime($to->format('Y-m-d')  . " 00:00:00");
        }
        $data['from'] = date('Y-m-d', $wdata['expense_date >=']);
        $data['to'] =  date('Y-m-d', $wdata['expense_date <=']);
		
		//Payrolls from every two weeks
		/*
		$friday = array();
		
		$i = (date('N', $wdata['expense_date >=']) > 5) ? ($wdata['expense_date >='] + (86400 * 2)) : $wdata['expense_date >='];
		
		for($i; $i < $wdata['expense_date <=']; $i += 604800)
		{
			if(strtotime("Friday this week", $i) > time() || strtotime("Friday this week", $i) > strtotime($data['to']))
				break;
			if(round($i / 604800) % 2 == 1)
				$friday[] = date('Y-m-d', strtotime("Friday this week", $i));
		}
		$dates = array();
		$data['payrolls'] = array();
		foreach($friday as $key=>$val)
		{
			$dates['expense_date <='] = strtotime($val) - (86400 * 4) - 1;
			$dates['expense_date >='] = strtotime($val) - (86400 * 18); //First payroll from last two weeks
			$payrollExp = $this->mdl_reports->get_payroll_expenses($dates);
			
			$data['payrolls'][$key] = isset($payrollExp[0]) ? $payrollExp[0] : 0;
			if(isset($data['payrolls'][$key]) && $data['payrolls'][$key])
				$data['payrolls'][$key] += array('expense_date' => strtotime($friday[$key]), 'expense_create_date' => strtotime($friday[$key]));
		}
		
		$data['payrolls'] = array_reverse($data['payrolls']);
		*/
		//////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		$payDay = $this->mdl_payroll->get_many_by(array('payroll_day >=' => $data['from'], 'payroll_day <=' => $data['to']));
		$payrollSum = array();
		if($payDay)
		{
			//$payrollSum[]['expense_amount'] = 0;
			//$payrollSum[]['expense_hst_amount'] = 0;
			foreach($payDay as $key=>$val)
			{
				if(strtotime($val->payroll_day) <= strtotime(date('Y-m-d')))
				{
					$payrollSum[$key]['expense_type_id'] = 0;
					$payrollSum[$key]['expense_name'] = 'Payroll';
					$payrollSum[$key]['expense_status'] = 1;
					$payrollSum[$key]['expense_amount'] = $payrollSum[$key]['sum'] = $this->mdl_reports->get_payroll_sum(array('worked_payroll_id' => $val->payroll_id))['sum'];
					$payrollSum[$key]['expense_hst_amount'] = 0;
					$payrollSum[$key]['expense_create_date'] = $payrollSum[$key]['expense_date'] = strtotime($val->payroll_day);
				}
			}
		}
		$data['payrolls'] = $payrollSum;
		
		//!!!!!!!!!!!!!!!!!!!!!!
		//echo '<pre>'; var_dump($data['payrolls'], $payrollSum); die;
		// END two weeks payroll
		$data['expense_id'] = null; 
		$data['expenses'] = $this->mdl_reports->get_expenses($wdata);
		
		$data['expenses'] = array_merge($data['payrolls'], $data['expenses']);
		$dateCreate = array();
		
		foreach ($data['expenses'] as $key => $row) {
			$dateCreate[$key]  = $row['expense_date'];
		}
		array_multisort($dateCreate, SORT_DESC, $data['expenses']);
		
		$data['donut'] = $this->mdl_reports->get_expenses_type_group($wdata);
		//$data['donut'] = array_merge($data['donut'], $data['payrolls']); // Add payroll to donut
		if($data['donut'])
		{
			foreach ($data['donut'] as $key => $row) {
				$sum[]  = $row['sum'];
			}		
			array_multisort($sum, SORT_DESC, $data['donut']);
		}
		
		$data['expense_types'] = $this->mdl_expense->find_all(array('expense_status' => 1), 'expense_status DESC, expense_name ASC');
		//$data['employees'] = $this->mdl_employees->find_all(array('emp_status' => 'current'), 'emp_name');
		$users = $this->mdl_user->get_usermeta(array('emp_status' => 'current'), 'firstname');
		$data['employees'] = $data['users'] = $users ? $users->result() : [];
		
		$data['users'] = $this->mdl_user->find_all(array('active_status' => 'yes'), 'firstname');
		//echo '<pre>'; var_dump(count($data['users']), count($data['employees'])); die;
		$itemsGroups = $this->mdl_expense->get_selected_groups();
		$data['items'] = array();
		if(!empty($itemsGroups))
		{
			foreach($itemsGroups as $itemsGroup)
			{
				foreach($itemsGroup as $key => $group)
				{
					$items = $this->mdl_equipments->get_group_items($group['expense_type_group_id']);
					if($items && !empty($items))
					{
						foreach($items as $item)
							$data['items'][$group['expense_type_id']][] = $item;
					}
				}
			}
		}

		$key = count($data['donut']);
		foreach($data['payrolls'] as $payroll)
		{
			$data['donut'][$key]['expense_amount'] = isset($data['donut'][$key]['expense_amount']) ? ($data['donut'][$key]['expense_amount'] + $payroll['expense_amount']) : $payroll['expense_amount'];
			$data['donut'][$key]['sum'] = isset($data['donut'][$key]['sum']) ? ($data['donut'][$key]['sum'] + $payroll['sum']) : $payroll['sum'];
			$data['donut'][$key]['expense_hst_amount'] = $payroll['expense_hst_amount'];
			$data['donut'][$key]['expense_name'] = $payroll['expense_name'];
			$data['donut'][$key]['expense_status'] = $payroll['expense_status'];
			$data['donut'][$key]['expense_type_id'] = $payroll['expense_type_id'];
		}
		$data['expenses_data'] = $this->mdl_expense->find_all(array('expense_status' => 1), 'expense_status DESC, expense_name ASC');
		$employeesData = $this->mdl_user->get_payroll_user(array('user_active_employee' => 1, 'emp_status' => 'current'), 'emp_status ASC, emp_name ASC');
		//$data['employees'] = $this->mdl_employees->find_all(array('emp_status' => 'current'), 'emp_name');
		if($employeesData)
			$data['employees_data'] = $employeesData->result();
		
		if(!empty($data['donut']))
		{
			foreach ($data['donut'] as $key => $row) {
				$sums[]  = $row['sum'];
			}
			array_multisort($sums, SORT_DESC, $data['donut']);
		}
		//echo '<pre>'; var_dump($data['payrolls']); die;
		
		 //echo '<pre>'; var_dump($data); die;
        $data['defaultTax'] = getDefaultTax();
        $data['allTaxes'] = all_taxes();
		$this->load->view("index_expenses", $data);
	}

	public function ajax_get_edit_expense_form() {
        $this->load->model('mdl_expense');
        $expenses = $this->mdl_reports->get_expenses(['expense_id' => $this->input->post('expense_id')]);
        if($expenses && !empty($expenses))
            $data['expense'] = $expenses[0];
        else
            die(json_encode(['status' => 'error']));
        $users = $this->mdl_user->get_usermeta(array('emp_status' => 'current'), 'firstname');
		$data['employees'] = $data['users'] = $users ? $users->result() : [];
        $data['expense_types'] = $this->mdl_expense->find_all(array('expense_status' => 1), 'expense_status DESC, expense_name ASC');
        $allTaxes = all_taxes();
        $expenseTax = json_decode($data['expense']['expense_tax'], true);
        $taxText = $expenseTax['name'] . ' (' . $expenseTax['value'] . '%)';
        $expenseTax['text'] = $taxText;
        $data['expenseTax'] = $expenseTax;
        $checkTax = checkTaxInAllTaxes($taxText);
        if(!$checkTax)
            $allTaxes[] = ['text' => $taxText, 'name' => $expenseTax['name']];
        $data['allTaxes'] = $allTaxes;
        $result['html'] = $this->load->view('edit_expense_modal', $data, TRUE);
        $result['status'] = 'ok';
        die(json_encode($result));
    }
	
	public function ajax_get_expense()
	{
		$this->load->model('mdl_expense');
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_user');
		$this->load->model('mdl_employee', 'emp_login');
		$this->load->model('mdl_payroll');
		$wdata['expense_date >='] = strtotime(date('Y-m-01 00:00:00'));
		$wdata['expense_date <='] = strtotime(date('Y-m-t 00:00:00'));

        if ($this->input->post('from')){
            $from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
            $wdata['expense_date >='] = strtotime($from->format('Y-m-d') . " 00:00:00");
        }
        if ($this->input->post('to')){
            $to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
            $wdata['expense_date <='] = strtotime($to->format('Y-m-d')  . " 00:00:00");
        }
//
//		if ($this->input->post('from'))
//			$wdata['expense_date >='] = strtotime($this->input->post('from'));
//		if ($this->input->post('to'))
//			$wdata['expense_date <='] = strtotime($this->input->post('to'));
		$data['from'] = date('Y-m-d', $wdata['expense_date >=']);
		$data['to'] = date('Y-m-d', $wdata['expense_date <=']);
		$users = $this->mdl_user->get_usermeta(array('emp_status' => 'current'), 'firstname');
		$data['employees'] = $data['users'] = $users ? $users->result() : [];
		
		$data['users'] = $this->mdl_user->find_all(array('active_status' => 'yes'), 'firstname');
		$data['payrolls'] = array();
		if($this->input->post('user_id'))
			$wdata['expense_created_by'] = $this->input->post('user_id');

		if($this->input->post('emp_id'))
			$wdata['expense_user_id'] = $this->input->post('emp_id');
			//$wdata['expense_employee_id'] = $this->input->post('emp_id');
		if(!$this->input->post('emp_id') && !$this->input->post('user_id'))
		{
			
			/*
			$friday = array();
			$i = (date('N', $wdata['expense_date >=']) > 5) ? ($wdata['expense_date >='] + (86400 * 2)) : $wdata['expense_date >='];
			
			for($i; $i <= $wdata['expense_date <=']; $i += 604800)
			{
				if(strtotime("Friday this week", $i) > time() ||  strtotime("Friday this week", $i) > strtotime($data['to']))
					break;
				if(round($i / 604800) % 2 == 1)
				{
					$friDay = date('Y-m-d', strtotime("Friday this week", $i));
					if(date('Y-m-d', $wdata['expense_date <=']) >= $friDay)
						$friday[] = $friDay;
				}
			}
			$dates = array();
			foreach($friday as $key=>$val)
			{
				$dates['expense_date <='] = strtotime($val) - (86400 * 4) - 1;
				$dates['expense_date >='] = strtotime($val) - (86400 * 18); //First payroll from last two weeks
				$payrollExp = $this->mdl_reports->get_payroll_expenses($dates);
				
				$data['payrolls'][$key] = isset($payrollExp[0]) ? $payrollExp[0] : 0;
				if(isset($data['payrolls'][$key]) && $data['payrolls'][$key])
					$data['payrolls'][$key] += array('expense_date' => strtotime($friday[$key]), 'expense_create_date' => strtotime($friday[$key]));
			}
			
			$data['payrolls'] = array_reverse($data['payrolls']);
			*/
			
			$payDay = $this->mdl_payroll->get_many_by(array('payroll_day >=' => $data['from'], 'payroll_day <=' => $data['to']));
			$payrollSum = array();
			if($payDay)
			{
				//$payrollSum[]['expense_amount'] = 0;
				//$payrollSum[]['expense_hst_amount'] = 0;
				foreach($payDay as $key=>$val)
				{
					if(strtotime($val->payroll_day) <= strtotime(date('Y-m-d')))
					{
						$payrollSum[$key]['expense_type_id'] = 0;
						$payrollSum[$key]['expense_name'] = 'Payroll';
						$payrollSum[$key]['expense_status'] = 1;
						$payrollSum[$key]['expense_amount'] = $payrollSum[$key]['sum'] = $this->mdl_reports->get_payroll_sum(array('worked_payroll_id' => $val->payroll_id))['sum'];
						$payrollSum[$key]['expense_hst_amount'] = 0;
						$payrollSum[$key]['expense_create_date'] = $payrollSum[$key]['expense_date'] = strtotime($val->payroll_day);
					}
				}
			}
			
			$data['payrolls'] = $payrollSum;
			
		}	
		
		$result['donut'] = $this->mdl_reports->get_expenses_type_group($wdata);
		
		if($this->input->post('expense_id') && $this->input->post('expense_id') !== '')
		{
			$result['groups'] = $this->mdl_expense->get_sum_selected_groups($this->input->post('expense_id'), $wdata);
			$result['expense_id'] = $wdata['expenses.expense_type_id'] = $this->input->post('expense_id');
			
		}
		
		elseif($this->input->post('expense_id') === '0' && !empty($data['payrolls']))
		{
			$this->load->model('mdl_worked');
			
			foreach($data['payrolls'] as $key=>$val)
			{
				//$get_data["login_time >="] = date('Y-m-d 00:00:00', ($val['expense_date'] - (86400 * 18)));
				//$get_data["login_time <="] = date('Y-m-d 23:59:59', ($val['expense_date'] - (86400 * 4) - 1));
				
				//$emp_data = $this->emp_login->get_overview_report_biweekly($get_data, 'employees.emp_type');
				$emp_data = $this->mdl_worked->get_workeds(array('worked_date >=' => date('Y-m-d', $val['expense_date'] - (86400 * 18)), 'worked_date <=' => date('Y-m-d', $val['expense_date'] - (86400 * 4) - 1)));
				
				$date = NULL;
				$emp_id = NULL;

				foreach($emp_data as $key => $workData)
				{ //var_dump($workData, $a); die;
					if($workData->worked_user_id != $emp_id)
					{
						$emp_id = $workData->worked_user_id;
						$workedAtDay = array();
					}
					/*$date = date('Y-m-d', strtotime($workData['login_time']));
					
					$empData[$workData['employee_id']][$date]['worked'] = isset($empData[$workData['employee_id']][$date]['worked']) ? $empData[$workData['employee_id']][$date]['worked'] + $workData['seconds'] : $workData['seconds'];
					$workedAtDay[$date] = isset($workedAtDay[$date]) ? $workedAtDay[$date] + $workData['seconds'] : $workData['seconds'];
					
					if($workedAtDay[$date] !== FALSE && $workedAtDay[$date] >= 5 && !$workData['no_lunch'])
					{
						$workData['seconds'] -= 0.5;
						$workedAtDay[$date] = FALSE;
					}
					*/
					$workData->worked_hours -= $workData->worked_lunch; 
					//var_dump($workData); die;
					if(!isset($result['groups'][$workData->worked_user_id]['summ']))
						$result['groups'][$workData->worked_user_id]['summ'] = round(($workData->worked_hours * $workData->worked_hourly_rate), 2);
					else
						$result['groups'][$workData->worked_user_id]['summ'] += round(($workData->worked_hours * $workData->worked_hourly_rate), 2);
					
					$result['groups'][$workData->worked_user_id]['summ'] = number_format($result['groups'][$workData->worked_user_id]['summ'], 2, '.', '');
					
					$result['groups'][$emp_id]['group_name'] = $workData->emp_name;
					$result['groups'][$emp_id]['expense_type_group_id'] = 0;
					$result['groups'][$emp_id]['expense_type_id'] = 0;
					$result['groups'][$emp_id]['group_id'] = 0;
				}	
				
			}
			//var_dump($result); die;
			$result['groups'] = array_values($result['groups']);
			foreach ($result['groups'] as $key => $row) {
				$summ[]  = $row['summ'];
			}
			array_multisort($summ, SORT_DESC, $result['groups']);
		}
		$data['expense_id'] = $this->input->post('expense_id');
		
		
		if($this->input->post('group_id'))
		{
			$wdata['equipment_groups.group_id'] = $this->input->post('group_id');
			$wdata['expense_types_groups.expense_type_id'] = $this->input->post('expense_id');
			$result['items'] = $this->mdl_expense->get_sum_selected_items($this->input->post('group_id'), $wdata);
			unset($wdata['expense_types_groups.expense_type_id']);
		}
		
		if($this->input->post('item_id'))
		{
			$wdata['equipment_items.item_id'] = $this->input->post('item_id');
		}
		
		$data['expenses'] = $this->mdl_reports->get_expenses($wdata);
		
		if(!$this->input->post('emp_id') && !$this->input->post('user_id'))
			$data['expenses'] = array_merge($data['payrolls'], $data['expenses']);
		
		$dateCreate = array();
		
		foreach ($data['expenses'] as $key => $row) {
			$dateCreate[]  = $row['expense_date'];
		}
		array_multisort($dateCreate, SORT_DESC, $data['expenses']);
		
		$data['expense_types'] = $this->mdl_expense->find_all(array('expense_status' => 1), 'expense_status DESC, expense_name ASC');
		
		$itemsGroups = $this->mdl_expense->get_selected_groups();
		$data['items'] = array();
		if(!empty($itemsGroups))
		{
			foreach($itemsGroups as $itemsGroup)
			{
				foreach($itemsGroup as $key => $group)
				{
					$items = $this->mdl_equipments->get_group_items($group['expense_type_group_id']);
					if($items && !empty($items))
					{
						foreach($items as $item)
							$data['items'][$group['expense_type_id']][] = $item;
					}
				}
			}
		}
		
		$key = count($result['donut']);
		foreach($data['payrolls'] as $payroll)
		{
			$result['donut'][$key]['expense_amount'] = isset($result['donut'][$key]['expense_amount']) ? ($result['donut'][$key]['expense_amount'] + $payroll['expense_amount']) : $payroll['expense_amount'];
			$result['donut'][$key]['sum'] = isset($result['donut'][$key]['sum']) ? ($result['donut'][$key]['sum'] + $payroll['sum']) : $payroll['sum'];
			$result['donut'][$key]['expense_hst_amount'] = $payroll['expense_hst_amount'];
			$result['donut'][$key]['expense_name'] = $payroll['expense_name'];
			$result['donut'][$key]['expense_status'] = $payroll['expense_status'];
			$result['donut'][$key]['expense_type_id'] = $payroll['expense_type_id'];
		}
		
		if(!empty($result['donut']))
		{
			foreach ($result['donut'] as $key => $row) {
				$sum[]  = $row['sum'];
			}
			array_multisort($sum, SORT_DESC, $result['donut']);
		}
		
		$data['defaultTax'] = getDefaultTax();
		$result['html'] = $this->load->view('expenses_table', $data, TRUE);
		//echo '<pre>'; var_dump($result['html']); die;
		//$result['status'] = 'ok';
		$this->successResponse($result);
	}

    public function ajax_delete_expense_file()
    {
        if (bucket_unlink($this->input->post('file_path')))
            die(json_encode(array('status' => 'ok')));

        die(json_encode(array('status' => 'error')));
	}

	function update_lunch()
	{
		//var_dump($_POST); die;
		$result['status'] = 'error';
		$this->load->model("mdl_employee", "emp_login");
		$data['created_date >='] = $this->input->post('date') . ' 00:00:00';
		$data['created_date <='] = $this->input->post('date') . ' 23:59:59';
		$data['employee_id'] = $this->input->post('emp_id');
		$lunch = $this->input->post('lunch');
		if($this->emp_login->update(array('no_lunch' => $lunch), $data))
		{
			$this->load->model('mdl_worked');

			$wdata['worked_date'] = $this->input->post('date');
			$wdata['worked_user_id'] = $this->input->post('emp_id');
			//$wdata['worked_employee_id'] = $this->input->post('emp_id');
			$update['worked_lunch'] = $lunch ? 0 : 0.5;
			$this->mdl_worked->update_by($wdata, $update);

			$result['status'] = 'ok';
			$result['msg'] = 'Done!';
		}
		else
			$result['msg'] = 'Something wrong. Please try again later';
		die(json_encode($result));
	}
	
	

	public function expense_types()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_expense');
		$this->load->model('mdl_equipments');
		$data['title'] = 'Expense Types';
		$data['items_groups'] = $this->mdl_equipments->find_all(array(), 'group_name');
		$data['expenses'] = $this->mdl_expense->find_all(array(), 'expense_status DESC, expense_name ASC');
		$data['expenses_groups'] = $this->mdl_expense->get_selected_groups();
		$this->load->view('index_expenses_types', $data);
	}
	
	function ajax_save_expense()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_letter');
		$this->load->model('mdl_expense');
		$id = $this->input->post('expense_id');
		
		$groups = $this->mdl_expense->get_selected_groups(array('expense_type_id' => $id));
		if($this->input->post('expense_name'))
			$data['expense_name'] = strip_tags($this->input->post('expense_name', TRUE));
		
		if($this->input->post('expense_groups'))
			$data['expense_groups'] = $this->input->post('expense_groups', TRUE);

		if ($id != '') {
			$this->mdl_expense->update($id, $data);
			if(!empty($groups) && !$data['expense_groups'])
				$this->mdl_expense->reset_expense_groups($id, array());
			die(json_encode(array('status' => 'ok')));
		}
		$this->mdl_expense->insert($data);

        $expenses = $this->mdl_reports->get_expenses(['expense_id' => $this->input->post('expense_id')]);
		$result['html'] = $this->load->view('expenses_table_tbody', ['expenses' => $expenses]);
		$result['status'] = 'ok';
		die(json_encode($result));
	}

	function add_expense_amount($worked_id, $slug = NULL, $extra = 0){
	    $worked = EmployeeWorked::find($worked_id);

	    if(!$worked)
	        $this->response("400", ['status'=>'error', 'message'=>'No worked data']);

        $expenseType = ExpenseType::whereSlug($slug)->first();
        $Expense = new Expense();

        $amount = floatval(request()->input('value'))/config_item('tax_rate');
        $hst = floatval($amount) * (config_item('tax_perc') / 100);

        $data = [
            'expense_type_id' => $expenseType->expense_type_id??0,
            'expense_user_id' => $worked->worked_user_id,
            'expense_date' => strtotime($worked->worked_date),
            'expense_is_extra' => $extra,
            'expense_amount' => $amount,
            'expense_hst_amount' => $hst
        ];

        $Expense->fill($data);
        $Expense->save();

        return $this->response(['status' => 'ok', 'value'=>floatval($this->input->post('value'))], 200);
    }

	function update_expense_amount()
	{
	    $this->load->model('mdl_expense');
		
		if(!$this->input->post('pk')){
			$this->output->set_status_header(400);
			return $this->output->set_output(json_encode(['status' => 'error', 'message'=>'Not valid expense']));	
		}
		
		if(!is_numeric($this->input->post('value'))){
			$this->output->set_status_header(400);
			return $this->output->set_output(json_encode(['status' => 'error', 'message'=>'Not valid amount']));	
		}

		if($this->input->post('name'))
			$data[$this->input->post('name')] = floatval($this->input->post('value'));

		if(isset($data['expense_amount']))
		{
			$expense_amount = $data['expense_amount'];
			$data['expense_amount'] = $expense_amount / config_item('tax_rate');
			$data['expense_hst_amount'] = $data['expense_amount'] * (config_item('tax_perc') / 100);
		}
		
		$this->mdl_expense->update_expense($this->input->post('pk'), $data);
		
		die(json_encode(['status' => 'ok', 'value'=>floatval($this->input->post('value'))]));
	}

	function ajax_delete_expense()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_expense');
		$id = $this->input->post('expense_id');
		$status = intval($this->input->post('status'));
		if($status && $status != 1)
			$status = 1;

		$expense_type = $this->mdl_expense->get_expenses(['expense_type_id'=>$id], TRUE);
		if($expense_type && $expense_type['protected']==1)
			die(json_encode(array('status' => 'error')));

		$this->mdl_expense->update($id, array('expense_status' => $status));
		die(json_encode(array('status' => 'ok')));
	}

	/*function report_crews()
	{
		$this->load->model('mdl_employees');
		$this->load->model('mdl_schedule');
		$from_date = date('Y-m-') . '01';
		$to_date = date('Y-m-t');
		$to_date .= ' 23:59:59';
		$employees = $this->mdl_employees->find_all(array('emp_status' => 'current', 'emp_feild_worker' => 1), 'emp_name');
		foreach($employees as &$employee)
		{
			$employee->teams = $this->mdl_schedule->find_team_by(array('team_date >= ' => strtotime($from_date), 'team_date <=' => strtotime($to_date), 'employee_id' => $employee->employee_id));
			echo $this->db->last_query();die;
		}
		echo "<pre>";
		var_dump($employees);
	}*/

	function performance()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_emp_productivity');
		$this->load->model('mdl_worked_likes');
		
		$data['title'] = 'Perfomance';
		
		$data['from'] = date('Y-m-01');
		$data['to'] = date('Y-m-t');
		
		if ($this->input->post('from'))
		{
			$data['from'] = $this->input->post('from') . " 00:00:00" ;
			//$wdata['expense_date >='] = strtotime($data['from']);
		}
		if ($this->input->post('to'))
		{
			$data['to'] = $this->input->post('to')  . " 23:59:59";
			//$wdata['expense_date <='] = strtotime($data['to']);
		}
		$data['employees'] = $this->mdl_emp_productivity->get_producivity(array('worked_date >=' => $data['from'], 'worked_date <=' => $data['to'], 'worked_hours >' => 0));
		$data['likes'] = $this->mdl_worked_likes->get_likes_by(array('likes_date >=' => $data['from'], 'likes_date <=' => $data['to']));
	
		$this->load->view('index_perfomance', $data);
	
	}
	
		
	/***********************SALES**************************/
	
	function sales_targets($year = NULL)
	{ 
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$data['title'] = "Sales";
		if(!$year)
			$year = date('Y');
		$data['year'] = $year;
		$data['minYear'] = 2013;
		$this->load->model('mdl_sale');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_clients');
		$data['sales'] = $this->mdl_sale->get_all(array('sale_date >=' => $data['year'] . '-01-01', 'sale_date <=' => $data['year'] . '-12-31'));
		$statuses = $this->mdl_est_status->get_many_by(array('est_status_confirmed' => 1));
		$company_estimates = array();

		$invoicesData = $this->mdl_invoices->find_all(['date_created >=' => $year . '-01-01', 'date_created <=' => $year . '-12-31', 'invoice_statuses.completed' => 1], '', ['invoice_statuses', 'invoice_statuses.invoice_status_id = invoices.in_status']);

		foreach($invoicesData as $val)
			$invoices[date('m', strtotime($val->date_created))][] = $val;
		$data['paymentsSum'] = 0;
		foreach($data['sales'] as $key=>$sale)
		{
			$total_company = 0;
			$total_created_invoices_company = 0;
			$total_invoices_company = 0;
			$month = date('m', strtotime($sale['sale_date']));
			$data['sales'][$key]['received_payments'] = 0;
			
			$payments = $this->mdl_clients->payment_made(array('payment_date >' => strtotime($sale['sale_date']), 'payment_date <' => strtotime(date('Y-m-t H:i:s', strtotime($sale['sale_date'] . ' 23:59:59'))), 'payment_checked' => 1));
			
			
			if(!empty($payments) && $payments)
			{
				foreach($payments as $k=>$v)
				{
					$data['paymentsSum'] += $v['sum'];
					$data['sales'][$key]['received_payments'] += $v['sum'];
				
				}
			}
			
			$wdata['estimates.date_created >'] = strtotime($sale['sale_date']);
			$wdata['estimates.date_created <'] = strtotime(date('Y-m-t', strtotime($sale['sale_date'])));
			foreach($statuses as $jkey=>$status)
			{
				$wdata['estimates.status'] = $status->est_status_id;
				
				$comObj = $this->mdl_estimates->get_estimates('', '', '', '', 'date_created', '', $wdata);
				
				if($comObj)
					$company_estimates = $comObj->result_array();
				if($company_estimates && !empty($company_estimates))
				{
					foreach($company_estimates as $akey=>$estimate)
					{
						$total_company += $this->mdl_estimates->get_total_for_estimate($estimate['estimate_id'])['sum'];
						if($estimate['invoice_date_created'])
							$total_invoices_company += $this->mdl_estimates->get_total_for_estimate($estimate['estimate_id'], array('service_status' => 2))['sum'];
					}
				}
                $sale_amount = $sale['sale_amount'] ? $sale['sale_amount'] : 1;
				$data['sales'][$key]['complete_company'] = round(($total_company / $sale_amount) * 100, 2);
				$data['sales'][$key]['total_company'] = round($total_company, 2);
				$data['sales'][$key]['total_invoices_company'] = round($total_invoices_company, 2);
			}
			if(isset($invoices[$month]))
			{
				foreach($invoices[$month] as $val)
				{
					$invoice = $this->mdl_estimates->estimate_sum_and_hst($val->estimate_id);
					$total_created_invoices_company += $invoice['total'] + $invoice['hst'];
				}
			}
			$data['sales'][$key]['total_created_invoices_company'] = $total_created_invoices_company;
		}

        $data['paymentsSum'] = money($data['paymentsSum']);
		$this->load->view('index_sales', $data);
	}

	function sales() {
		$data['title'] = $this->_title . ' | Sales Report';
		$data['menu_clients'] = "active";

		$this->load->model('mdl_est_status');
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_services');
        $this->load->model('mdl_user');
        $this->load->model('mdl_categories');
        $this->load->model('mdl_workorders');
        $this->load->model('mdl_letter');
        $this->load->model('mdl_user');
        $this->load->model('mdl_invoice_status');
        $this->load->model('mdl_leads_status');
        $this->load->model('mdl_payroll');
        $this->load->model('mdl_services');

        $date = date('Y-m-d');

		$payroll = $this->mdl_payroll->get_by(array('payroll_start_date <=' => $date, 'payroll_end_date >=' => $date));
		$quarters = get_quarter_date();


        $dates['last_payroll'][0] = date(getDateFormat(), strtotime($payroll->payroll_start_date . '-14 days'));
        $dates['last_payroll'][1] = date(getDateFormat(), strtotime($payroll->payroll_start_date . '-1 days'));
        $dates['this_payroll'][0] = date(getDateFormat(), strtotime($payroll->payroll_start_date));
        $dates['this_payroll'][1] = date(getDateFormat(), strtotime($payroll->payroll_end_date));
        $dates['last_30_days'][0] = date(getDateFormat(), strtotime(date('Y-m-d') . '-30 days'));
        $dates['last_30_days'][1] = date(getDateFormat());
        $dates['last_month'][0] = date(getDateFormat(), strtotime("first day of previous month"));
        $dates['last_month'][1] = date(getDateFormat(), strtotime("last day of previous month"));
        $dates['this_month'][0] = date(getDateFormat(), strtotime(date('Y-m-01')));
        $dates['this_month'][1] = date(getDateFormat(), strtotime(date('Y-m-t')));
        $dates['last_quarter'][0] = $quarters['last_start_date'];
        $dates['last_quarter'][1] = $quarters['last_end_date'];
        $dates['this_quarter'][0] = $quarters['start_date'];
        $dates['this_quarter'][1] = $quarters['end_date'];
        $dates['last_year'][0] = date(getDateFormat(), strtotime(date("Y-01-01", strtotime("-1 year"))));
        $dates['last_year'][1] = date(getDateFormat(), strtotime(date("Y-12-31", strtotime("-1 year"))));
        $dates['this_year'][0] = date(getDateFormat(), strtotime(date("Y-01-01")));
        $dates['this_year'][1] = date(getDateFormat(), strtotime(date("Y-12-31")));
		$data['dates'] = $dates;
        $data['invoices_statuses'] = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);
        $data['wo_statuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
        $data['estimate_statuses'] = $this->mdl_est_status->with('mdl_est_reason')->get_many_by(array('est_status_active' => 1));

        $data['estimators'] = [];
        $estimators = $this->mdl_estimates->get_active_estimators();
        if ($estimators) {
            $data['estimators'] = $estimators;
        }
        $data['services'] = $this->mdl_services->find_all([
            'service_parent_id' => NULL,
            'service_status' => 1,
            'is_product' => 0,
            'is_bundle' => 0
        ], 'service_priority');

        $data['products'] = $this->mdl_services->find_all([
            'service_parent_id' => NULL,
            'service_status' => 1,
            'is_product' => 1,
            'is_bundle' => 0
        ], 'service_priority');
	    $this->load->view('index_sales_report', $data);
    }

    function ajax_get_sales_data() {

	    if(!$this->input->post())
	        show_404();

	    $this->load->model('mdl_estimates_orm');

	    $result = [
	        'status' => true,
	        'data' => [],
        ];


        $limit = 50;
        $offset = (int) $this->input->post('offset');
	    $where = $this->_set_sales_report_where_array();

	    $rows = $this->mdl_reports->getSalesReportRows($where['conditions'], $where['conditionsWhereIn'], $limit, $offset);

        $sum_all = $this->mdl_reports->getSalesReportRows($where['conditions'], $where['conditionsWhereIn'], 0, 0, TRUE);

        $result['data'] = [
            'count_all' => $sum_all && !empty($sum_all->total) ? $sum_all->total : 0,
            'sum' => $sum_all && !empty($sum_all->sum) ? $sum_all->sum : 0,
            /*'sum_estimates' => $sum_all && !empty($sum_all->total_estimates) ? $sum_all->total_estimates : 0,*/
            'count' => $rows && !empty($rows) ? count($rows) : 0,
            'limit' => $limit,
            'offset' => $offset,
            'stats' => $offset ? false : $this->mdl_reports->getSalesReportStats($where['conditions'], $where['conditionsWhereIn']),
            'html' => $offset ? $this->load->view('_partials/sales_report_rows_each', [
                    'rows' => $rows
                ], true) : $this->load->view('_partials/sales_report_rows', [
                    'rows' => $rows
                ], true)
        ];

	    $this->response($result);
    }

    private function _set_sales_report_where_array(){
	    $this->load->model('mdl_est_status');
	    $this->load->model('mdl_workorders');
	    $this->load->model('mdl_invoice_status');
	    $this->load->model('mdl_user');
	    $this->load->model('mdl_services');
        $data['filters'] = $data['conditions'] = $data['conditionsWhereIn'] = [];
        $models['search_status']['model'] = (string) 'est_status';
        $models['search_status']['name'] = (string) 'est_status_name';
        $models['search_workorder_status']['model'] = (string) 'workorders';
        $models['search_workorder_status']['name'] = (string) 'wo_status_name';
        $models['search_invoice_status']['model'] = (string) 'invoice_status';
        $models['search_invoice_status']['name'] = (string) 'invoice_status_name';

        $client_types[1] = 'Residential';
        $client_types[2] = 'Corporate';
        $client_types[3] = 'Municipal';


        $post = $this->input->post();

        $filters = [
            'search_client_type' => 'clients.client_type',
            'search_estimator' => 'estimates.user_id',
            'search_workorder_estimator' => 'estimates.user_id',
            'search_status' => 'estimates.status',
            'search_service_type' => 'estimates_services.service_id',
            'search_workorder_status' => 'workorders.wo_status',
            'search_invoice_status' => 'invoices.in_status',
            'search_client_tags' => 'client_tags.tag_id'
        ];

        foreach ($post as $key => $val) {

            if ($key == 'search_client_tags') {
                $val = $val ? explode('|', $val) : null;
            }
            if(isset($filters[$key]) && !empty($val)) {
                if(!is_array($val)) {
                    $data['conditions'][$filters[$key]] = $val;

                    if(($key == 'search_estimator' && !empty($post['search_estimator'])) || ($key == 'search_workorder_estimator' && !empty($post['search_estimator']))) {
                        $user = $this->mdl_user->get_user_name($val)->row_array();
                        $userName = ($user != '') ? $user['firstname'] .' ' . $user['lastname']: '';
                        $data['filters']['Estimator'] = $userName;
                    }
                    elseif($key == 'search_client_type') {
                        $data['filters']['Client Type'] = $client_types[$val];
                    }
                } else {
                    $data['conditionsWhereIn'][$filters[$key]] = $val;
                    if(isset($models[$key])){
                        $module = 'mdl_' . $models[$key]['model'];
                        $name = $models[$key]['name'];
                        $statuses = $this->$module->get_many($val);
                        $filterName = (string) ucwords(str_replace(['_', '.'], [' ', ' '], $filters[$key]));
                        $data['filters'][$filterName] = [];

                        foreach ($statuses as $jkey => $jval) {
                            $data['filters'][$filterName][$jkey] = $jval->$name;
                        }
                    }
                }
            }
            if($key == 'search_by') {
                $data['filters']['Search By'] = ucwords($val);
                if($val == 'estimates') {
                    if(isset($post['search_estimate_date']) && !empty($post['search_estimate_date'])) {
                        $dates = explode(' - ', $post['search_estimate_date']);
                        $data['filters']['Estimates Created From'] = $dates[0];
                        $data['filters']['Estimates Created To'] = $dates[1];
                        $data['conditions']['estimates.date_created >='] = strtotime(DateTime::createFromFormat(getDateFormat(), $dates[0])->format('Y-m-d') . " 00:00:00");
                        $data['conditions']['estimates.date_created <='] = strtotime( DateTime::createFromFormat(getDateFormat(), $dates[1])->format('Y-m-d') . " 23:59:59");
                        unset($post['search_estimate_date']);
                    }
                } elseif($val == 'workorders') {
                    $data['conditions']['workorders.id IS NOT NULL'] = NULL;
                    if(isset($post['search_workorder_date']) && !empty($post['search_workorder_date'])) {
                        $dates = explode(' - ', $post['search_workorder_date']);
                        $data['filters']['Workorders Created From'] = $dates[0];
                        $data['filters']['Workorders Created To'] = $dates[1];
                        $data['conditions']['workorders.date_created >='] = DateTime::createFromFormat(getDateFormat(), $dates[0])->format('Y-m-d');
                        $data['conditions']['workorders.date_created <='] = DateTime::createFromFormat(getDateFormat(), $dates[1])->format('Y-m-d');
                        unset($post['search_workorder_date']);
                    }
                } elseif($val == 'invoices') {
                    $data['conditions']['invoices.id IS NOT NULL'] = NULL;
                    if(isset($post['search_invoice_date']) && !empty($post['search_invoice_date'])) {
                        $dates = explode(' - ', $post['search_invoice_date']);
                        $data['filters']['Invoices Created From'] = $dates[0];
                        $data['filters']['Invoices Created To'] = $dates[1];
                        $data['conditions']['invoices.date_created >='] = DateTime::createFromFormat(getDateFormat(), $dates[0])->format('Y-m-d');
                        $data['conditions']['invoices.date_created <='] = DateTime::createFromFormat(getDateFormat(), $dates[1])->format('Y-m-d');
                        unset($post['search_invoice_date']);
                    }
                }
            }
        }
        return $data;
    }

    function get_sales_pdf()
    {
        $this->load->library('mpdf');
        $this->load->model('mdl_estimates_orm');
        $data['title'] = 'SALES REPORT';

        $where = $this->_set_sales_report_where_array();
        $data['filters'] = $where['filters'];
        $data['services'] = $this->mdl_reports->getSalesReportStats($where['conditions'], $where['conditionsWhereIn']);
        $data['all_estimates'] = $this->mdl_reports->getSalesReportRows($where['conditions'], $where['conditionsWhereIn'], 0, 0, TRUE);

        $html = $this->load->view('sales_pdf', $data, TRUE);

        $this->mpdf->WriteHTML($html);
        $file = "sales_report.pdf";
        $this->mpdf->Output($file, 'I');
    }

	function payments_pdf($id, $from, $to)
	{
		$this->load->library('mpdf');
		$this->load->model('mdl_clients');
		$data['title'] = 'PAYMENTS RECEIVED ' . $id;
		
		
		$wdata['payment_account'] = $id;
		$wdata['payment_date >='] = strtotime($from);
		$wdata['payment_date <='] = strtotime($to . ' 23:59:59');
		$data['from'] = $from;
		$data['to'] = $to;
		$data['client_payments'] = $this->mdl_clients->get_payments($wdata, '', '', 'payment_date ASC'); 
		
		$data['pdf'] = TRUE;
        $data['defaultTax'] = getDefaultTax();
		$html = $this->load->view('payments_pdf', $data, TRUE);

		$this->mpdf->WriteHTML($html);
		$file = "payments_" . $id . '.pdf';
		$this->mpdf->Output($file, 'I');
	}
	
	
	function ajax_save_sale()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_sale');
		
		$id = $this->input->post('id');
		$data['sale_date'] = $this->input->post('year') . '-' .$this->input->post('date') . '-' . date('01') ;
		$data['sale_amount'] = strip_tags($this->input->post('amount', TRUE));
		
		if ($id != '') {
			$this->mdl_sale->update_sale($id, $data);
			die(json_encode(array('status' => 'ok')));
		}
		$this->mdl_sale->insert_sale($data);	
		die(json_encode(array('status' => 'ok')));
	}
	function ajax_delete_sale()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		$this->load->model('mdl_sale');
		$id = $this->input->post('id');
		if ($id != '')
		{
			$this->mdl_sale->delete_sale($id);
			die(json_encode(array('status' => 'ok')));
		}
		die(json_encode(array('status' => 'error')));
	}
	
	function ajax_add_compliment()
	{
		$deleted = $this->input->post('deleted');
		$dates = $this->input->post('dates');
		$id = $this->input->post('id');
		$type = $this->input->post('type');
		$result['status'] = 'error';
		$this->load->model('mdl_worked_likes');
		//var_dump($_POST); die;
		if(!empty($deleted) && $deleted)
		{
			foreach($deleted as $key=>$val)
			{
				$this->mdl_worked_likes->delete($val);
				$result['status'] = 'ok';
			}
		}
		if(!empty($dates) && $dates)
		{
			foreach($dates as $key=>$val)
			{
				//var_dump($val); die;
				$this->mdl_worked_likes->insert(array('likes_user_id' => $id, 'likes_type' => $type, 'likes_date' => $val));
				$result['ids'][] = $this->db->insert_id();
				$result['status'] = 'ok';
			}
		}
		die(json_encode($result));
		
	}
	
	function ajax_change_payments()
	{
		//var_dump($_POST); die;
		$result['status'] = 'error';
		$this->load->model('mdl_clients');
		$id = $this->input->post('id');
		if($id)
		{
			$data['payment_checked'] = intval($this->input->post('checked'));
			$this->mdl_clients->update_payment($id, $data);
			$result['status'] = 'ok';
		}
		die(json_encode($result));
	}

	function ajax_change_account()
	{
		$result['status'] = 'error';
		$this->load->model('mdl_clients');
		$id = $this->input->post('id');
		$payment_id = $this->input->post('data_id');
		if($id)
		{
			$this->mdl_clients->update_payment($payment_id, array('payment_account' => $id));
			$result['status'] = 'ok';
		}
		die(json_encode($result));
	}
	/***********************END SALES**********************/

    public function invoices($page = 1){
        $filter = [];
        $date = new DateTime();
        $date->modify('first day of this month');
        $filter['date_from'] = $date->format('Y-m-d');
        $date->modify('last day of this month');
        $filter['date_to'] = $date->format('Y-m-d');

        $addFields['total_by_classes'] = [
            'all_classes' => true,
            'no_class' => true
        ];
        $addFields['invoice_paid_date'] = true;
        $addFields['invoice_sent_date'] = true;
        $addFields['discount_total'] = true;
        $invoices = Invoice::getInvoices(1, $filter, 0, true, $addFields);
        $classes = QBClass::all()->toArray();
        $estimators = User::estimator()->active()->baseFields()->get()->toArray();
        $references = Reference::getAllActive()->toArray();

        $data = [
            'title' => 'Report',
            'links' => $this->pagination->create_links(),
            'invoices' => $invoices,
            'classes' => $classes,
            'showClasses' => [],
            'estimators' => $estimators,
            'references' => $references
        ];
        $this->load->view('index_invoices_report', $data);
    }

    public function getInvoiceReportTable(){
        $filter = [];
        $addFields['total_by_classes'] = [
            'all_classes' => true,
            'no_class' => true
        ];
        $addFields['invoice_paid_date'] = true;
        $addFields['invoice_sent_date'] = true;
        $addFields['discount_total'] = true;
        $showClasses = [];
        $page = $this->input->post('page') ?? 1;
        $dateFrom = $this->input->post('date_from');
        $dateTo = $this->input->post('date_to');
        $estimators = $this->input->post('estimator');
        $references = $this->input->post('reference');
        $classes = $this->input->post('classes');
        if(!empty($dateFrom)){
            $filter['date_from'] = DateTime::createFromFormat(getDateFormat(), $dateFrom)->format('Y-m-d');
        }
        if(!empty($dateTo)){
            $filter['date_to'] = DateTime::createFromFormat(getDateFormat(), $dateTo)->format('Y-m-d');
        }
        if(!empty($estimators)){
            $filter['estimators_id'] = $estimators;
        }
        if(!empty($references)){
            $filter['references_id'] = $references;
        }
        if(!empty($classes)){
            $filter['classes'] = $classes;
            $showClasses = QBClass::whereIn(QBClass::ATTR_CLASS_ID, $classes)->get()->toArray();;
        }
        $invoices = Invoice::getInvoices($page, $filter, 0, true, $addFields);
        $table =  $this->load->view('_partials/invoice_report_table', ['invoices' => $invoices ?? [], 'showClasses' => $showClasses], true);
        $this->successResponse(['table' => $table]);
        return;
    }
	
	
}
//end of file reports.php
