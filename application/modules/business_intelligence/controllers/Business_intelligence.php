<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
 
use application\modules\references\models\Reference;
use application\modules\emails\models\Email;
use application\modules\tasks\models\Task;
use application\modules\schedule\models\ScheduleAbsence;
use application\modules\schedule\models\ScheduleTeams;
use application\modules\schedule\models\ScheduleTeamsMember;
class Business_intelligence extends MX_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->_title = SITE_NAME;

		if (!isUserLoggedIn()) {
			redirect('login');
		}
		$this->load->model('mdl_calls');
		$this->load->model('mdl_clients');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_estimates');
		$this->load->model('mdl_est_status');
		$this->load->model('mdl_history_log');
		$this->load->model('mdl_leads');
		$this->load->model('mdl_invoices');
		$this->load->model('mdl_payroll');
		$this->load->model('mdl_reports');
		$this->load->model('mdl_schedule');
		$this->load->model('mdl_tracker');
		$this->load->model('mdl_user');
		$this->load->model('mdl_worked');
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_settings_orm');

		$this->load->library('pagination');
		$this->load->helper('business_days_cal');
	}
	
	/*********************************************START CLIENTS**************************************************/
	
	function lead_statistics()
	{
		$data['title'] = 'Leads | Statistic By Statuses';
		
		$data['from'] = strtotime(date('Y-m-01'));
		$data['to'] = strtotime(date('Y-m-t'));
		
//		if ($this->input->post('from'))
//			$data['from'] = strtotime($this->input->post('from') . " 00:00:00");
//		if ($this->input->post('to'))
//			$data['to'] = strtotime( $this->input->post('to')  . " 23:59:59");
		if ($this->input->post('from')){
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$data['from'] = strtotime($from->format('Y-m-d') . " 00:00:00") ;
		}
		if ($this->input->post('to')){
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$data['to'] = strtotime($to->format('Y-m-d')  . " 23:59:59");
		}
		$leadsObj = $this->mdl_leads->get_leads(['lead_date_created >=' => date('Y-m-d H:i:s', $data['from']) , 'lead_date_created <=' => date('Y-m-d H:i:s', $data['to'])], NULL, 'leads.client_id');
		 
		$leads = []; 
		$data['data'] = [];
		if($leadsObj)
			$leads = $leadsObj->result();
		$data['total'] = count($leads);//countOk
		
		foreach($leads as $k=>$v)
		{ 
			
			if(!isset($data['data'][$v->lead_status_id]['total']))
				$data['data'][$v->lead_status_id]['total'] = 0;
			if($v->lead_status_declined){
				
				$reason = $v->lead_reason_status_id;
				if(!$reason)
					$reason = "Havn't Reason";
				if(!isset($data['data'][$v->lead_status_id]['reasons'][$reason]['total']))
					$data['data'][$v->lead_status_id]['reasons'][$reason]['total'] = 0; 
				$data['data'][$v->lead_status_id]['reasons'][$reason]['total'] += 1;
				$data['data'][$v->lead_status_id]['reasons'][$reason]['status_value'] = ($v->reason_name) ? $v->reason_name : $reason;
			}
			
			$data['data'][$v->lead_status_id]['status_value'] = $v->lead_status_name;  
			$data['data'][$v->lead_status_id]['total'] += 1;  
		}  
		
		$this->load->view('index_lead_stat', $data);
	}
	
	function emails_stat()
	{
		$this->load->helper('email_statistic_helper');
		ini_set('memory_limit', '-1');
		$data['title'] = 'Emails Statistics';

		$data['limit'] = $limit = 100;

		$data['from'] = date('Y-m-01');
		$data['to'] = date('Y-m-t');
		$dateTo = date('Y-m-t 23:59:59');

		if ($this->input->post('from')){
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$data['from'] = $from->format('Y-m-d');
		}
		if ($this->input->post('to')){
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$data['to'] = $to->format('Y-m-d');
			$to->modify('tomorrow');
			$toTimestamp = $to->getTimestamp();
			$to->setTimestamp($toTimestamp - 1);
			$dateTo = $to->format('Y-m-d H:i:s');
		}
		
		$data['statuses'] = $statuses = ['accepted', 'delivered', 'opened', 'clicked', 'unsubscribed', 'error', 'rejected', 'bounce', 'complained'];
		$data['color'] = ['#12E600', '#008000', '#2E9AFE', '#AFD8F8', '#EDC240', '#FF0000', '#EA6A6A', '#B50000', '#FFA500'];

		$data['all'] = 0;
		$data['data'] = [];

		foreach($statuses as $status) {
			$data['data'][$status]['letters'] = Email::getEmailsStatistics($data['from'], $dateTo, $status, $limit);
			$data['all'] += $data['data'][$status]['count'] = Email::getEmailsStatistics($data['from'], $dateTo, $status, 0, 0, true);
			$data['data'][$status]['count_actual'] = Email::getEmailsStatistics($data['from'], $dateTo, $status, 0, 0, false, true);
			$data['data'][$status]['more'] = $data['data'][$status]['count'] <= $limit ? 0 : 1;
		}

		$this->load->view('emails_stat', $data);
	}
	
	function ajax_more_emails()
	{
		$this->load->helper('email_statistic_helper');
		$result['status'] = 'error';
		$limit = intval($this->input->post('limit'));
		$offset = intval($this->input->post('num'));
		$dateFrom = $dateTo = null;

		if ($this->input->post('from')){
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$dateFrom = $from->format('Y-m-d');
		}
		if ($this->input->post('to')){
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$to->modify('tomorrow');
			$toTimestamp = $to->getTimestamp();
			$to->setTimestamp($toTimestamp - 1);
			$dateTo = $to->format('Y-m-d H:i:s');
		}
		$type = $this->input->post('type');

		$data['data'][$type]['letters'] = Email::getEmailsStatistics($dateFrom, $dateTo, $type, $limit, $offset);;
		$data['v'] = $type; 
		$result['offset'] = $offset + $limit;
		if($data['data'][$type]['letters'])
		{
			$result['status'] = 'ok';
			$result['more'] = count($data['data'][$type]['letters']) < $limit ? 0 : 1;//countOk
			$result['blocks'] = $this->load->view('email_row', $data, TRUE);
		}
		
		die(json_encode($result));
	}
	
	function statistic()
	{
		if (isset($this->session->userdata["CL"]) && $this->session->userdata["CL"] == 2) {
			redirect('dashboard');
		}
		$data['title'] = 'Client Statistic';
		$data['from'] = date('Y-m-01');
		$data['to'] = date('Y-m-t');

		if ($this->input->post('from')){
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$data['from'] = $from->format('Y-m-d');
		}
		if ($this->input->post('to')){
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$data['to'] = $to->format('Y-m-d');
		}
		$wdata['lead_date_created >='] = $data['from'] . ' 00:00:00';
		$wdata['lead_date_created <='] = $data['to'] . ' 23:59:59';
		$data['all'] = [];
		$data['client_type'][1] = 'Residential';
		$data['client_type'][2] = 'Corporate';
		$data['client_type'][3] = 'Municipal';
		$all = $this->mdl_leads->get_refferal_stat($wdata, false, true);

		if($all && count($all))
			$data['all'] = $all[0];
		$wdata['reference.slug !='] = 'existing_client';
		$data['data'] = $this->mdl_leads->get_refferal_stat($wdata, 'reference.id', true);
		unset($wdata['reference.slug !=']);

		$wdata['reference.slug'] = 'existing_client';
		$data['data'] = array_merge($this->mdl_leads->get_refferal_stat($wdata, 'reference.id, clients.client_type', true), $data['data']);
		unset($wdata['reference.slug']);

		$wdata['reference.slug IS NULL'] = null;
		$data['data'] = array_filter(array_merge($this->mdl_leads->get_refferal_stat($wdata, false, true), $data['data']), function($v) {
			return (int)$v->count;
        });
		unset($wdata['reference.slug IS NULL']);

		foreach($data['data'] as $k=>$v)
		{
			$where = 'lead_date_created >= "' . $wdata['lead_date_created >='] . '" AND lead_date_created <= "' . $wdata['lead_date_created <='] . '"';
			
			if($v->lead_reffered_by != 'existing_client')
			{
				$wdata['reference.id'] = $v->lead_reffered_by_id;
				$where .= ' AND lead_reffered_by = "' . $wdata['reference.id'] . '"';
				$leads = $this->mdl_leads->get_leads($wdata, '', '');

				if($leads && $leads->num_rows())
				{ 
					$data['data'][$k]->leads = $leads->result();
				}
				$workorders = $this->mdl_workorders->get_workorders('', '', '', '', $where);
				if($workorders && $workorders->num_rows())
					$data['data'][$k]->workorders = $workorders->result();
			}
			else
			{
				$wdata['reference.id'] = $v->lead_reffered_by_id;
				$wdata['clients.client_type'] = $v->client_type;
				$where .= ' AND lead_reffered_by = "' . $wdata['reference.id'] . '"';
				$where .= ' AND clients.client_type = ' .  $wdata['clients.client_type'];
				$leads = $this->mdl_leads->get_leads($wdata, '', '');
				if($leads && $leads->num_rows())
					$data['data'][$k]->leads = $this->mdl_leads->get_leads($wdata, '', '')->result();
				$workorders = $this->mdl_workorders->get_workorders('', '', '', '', $where);
				if($workorders && $workorders->num_rows())
					$data['data'][$k]->workorders = $workorders->result();
				unset($wdata['clients.client_type']);
			}
			unset($wdata['reference.id']);
			$where  = '';
		}

		$sorts  = array_map(function ($v){ return $v->count; }, $data['data']);
		array_multisort($sorts, SORT_DESC, $data['data']);

		$data['statuses'] = Reference::orderBy(Reference::ATTR_NAME)->get()->keyBy(Reference::ATTR_SLUG, 'slug')->toArray();
		$this->load->view('refered_stat', $data);

	}
	
	public function refferals_clients()
	{
		
		$data['title'] = $this->_title . ' - Refferals Clients';
		
		$wdata['lead_date_created >='] = $data['from'] = date('Y-m-01');
		$wdata['lead_date_created <='] = $data['to'] = date('Y-m-t');
		if($this->input->post('from'))
			$wdata['lead_date_created >='] = $data['from'] =  $this->input->post('from');

		if($this->input->post('to'))
			$wdata['lead_date_created <='] = $data['to'] =  $this->input->post('to');
		
		$data['type'] = $type = 'client';
		
		$refferals = $this->mdl_leads->get_refferals($wdata, $type);
		
		$data['refferals'] = array();
		if($refferals && !empty($refferals))
		{
			foreach($refferals as $key=>$val)
			{
				$data['refferals'][$key]['lead_reffered_client'] = $val->lead_reffered_client;
				$wdata['leads.lead_reffered_client'] = $val->lead_reffered_client;
				
				unset($wdata['estimate_statuses.est_status_confirmed']);
				$data['refferals'][$key]['estimates'] = $this->mdl_leads->get_refferal_data($wdata, $type);

				$data['refferals'][$key]['reffered'] = $val->reffered;
				$data['refferals'][$key]['count'] = $val->count;
				$data['refferals'][$key]['sum'] = $val->sum;
				$data['refferals'][$key]['confirmed_count'] = 0;
				$data['refferals'][$key]['confirmed_sum'] = 0;
				$wdata['estimate_statuses.est_status_confirmed'] = 1;
				
				$confirmed = $this->mdl_leads->get_refferals($wdata, $type);
				$confirmed_est = $this->mdl_leads->get_refferal_data($wdata, $type);
				if($confirmed && !empty($confirmed))
				{
					$data['refferals'][$key]['confirmed_count'] = $confirmed[0]->count;
					$data['refferals'][$key]['confirmed_sum'] = $confirmed[0]->sum;
				}
				if($confirmed_est && !empty($confirmed_est))
					$data['refferals'][$key]['confirmed_est'] = $confirmed_est;
			}
		}
		
		$this->load->view("index_refferals", $data);
	}
	
	function refferals_users()
	{
		$data['title'] = $this->_title . ' - Refferals Users';
		$data['type'] = $type = 'user';
		if($this->input->post('type'))
			$type = $this->input->post('type');
		$wdata['lead_date_created >='] = $data['from'] = date('Y-m-01');
		$wdata['lead_date_created <='] = $data['to'] = date('Y-m-t');
		if($this->input->post('from'))
			$wdata['lead_date_created >='] = $data['from'] =  $this->input->post('from');
		if($this->input->post('to'))
			$wdata['lead_date_created <='] = $data['to'] =  $this->input->post('to');
		
		$refferals = $this->mdl_leads->get_refferals($wdata, $type);
		
		$data['refferals'] = array();
		if($refferals && !empty($refferals))
		{
			foreach($refferals as $key=>$val)
			{
				
				$wdata['leads.lead_reffered_user'] = $val->reffered_id;
				unset($wdata['estimate_statuses.est_status_confirmed']);
				$data['refferals'][$key]['estimates'] = $this->mdl_leads->get_refferal_data($wdata, $type);
				
				$data['refferals'][$key]['reffered'] = $val->reffered;
				$data['refferals'][$key]['count'] = $val->count;
				$data['refferals'][$key]['sum'] = $val->sum;
				$data['refferals'][$key]['confirmed_count'] = 0;
				$data['refferals'][$key]['confirmed_sum'] = 0;
				$data['refferals'][$key]['confirmed_count'] = 0;
				$data['refferals'][$key]['confirmed_sum'] = 0;
				$wdata['estimate_statuses.est_status_confirmed'] = 1;
				
				$confirmed = $this->mdl_leads->get_refferals($wdata, $type);
				$confirmed_est = $this->mdl_leads->get_refferal_data($wdata, $type);
				if($confirmed && !empty($confirmed))
				{
					$data['refferals'][$key]['confirmed_count'] = $confirmed[0]->count;
					$data['refferals'][$key]['confirmed_sum'] = $confirmed[0]->sum;
				}
				if($confirmed_est && !empty($confirmed_est))
				{
					$data['refferals'][$key]['confirmed_est'] = $confirmed_est;
				}
			}
		}

		$this->load->view("index_refferals", $data);
	}
	/*********************************************END CLIENTS**************************************************/
	
	/*********************************************ESTIMATES**************************************************/
	public function estimates_report()
	{		
		$data['title'] = $this->_title . ' - Reports - Estimates';
		$data['data_available'] = FALSE;
		$data['dates_available'] = FALSE;

		$usermetadata = $this->mdl_user->get_usermeta();
		$data['users'] = array();
		if (!empty($usermetadata)) {
			//Get list of all active user_id -> estimators:
			$data['users'] = $this->mdl_user->get_usermeta(array('active_status' => 'yes'))->result();
		}
		$data['est_wo_period'] = $this->mdl_reports->count_wo_days_created();

		//
		//	Overall Company Productivity:
		//
		
		$data['corp_total_estimates'] = 0;//$this->mdl_reports->estimates_statistic($status, $estimator_id, $status_only, $from_date, $to_date);
		$data['corp_revenue_total_estimates'] = 0;//$this->mdl_reports->revenue_estimates_sum_new();
 
		$data['statuses'] = $this->mdl_est_status->get_all();
		$all_estimates = $this->mdl_reports->estimates_statistic('', '', '', '', '');
		$all_totals = $this->mdl_reports->revenue_estimates_sum_new([]);

		foreach($data['statuses'] as $estStatus)
		{			
			$data['corp_estimates'][$estStatus->est_status_id] = 0;
			foreach($all_estimates as $est_amount){
				if($estStatus->est_status_id == $est_amount->status){
					$data['corp_estimates'][$estStatus->est_status_id] = $est_amount->estimates_amount;
				}
			}
			$data['corp_revenue_estimates'][$estStatus->est_status_id] = 0;
			foreach($all_totals as $est_total){
				if($estStatus->est_status_id == $est_total->status){
					$data['corp_revenue_estimates'][$estStatus->est_status_id] = $est_total->sum_for_services;
				}
			}
						
			$data['corp_total_estimates'] += $data['corp_estimates'][$estStatus->est_status_id];
			$data['corp_revenue_total_estimates'] += $data['corp_revenue_estimates'][$estStatus->est_status_id];
		}
		
		$this->load->view("estimates", $data);
	}
	
	function ajax_estimates_report_users()
	{
		//
		//	ESTIMATOR ID REQUESTED - DATA RECEVIED:
		//
		$estimator_id = "";
		$status = "";
		$status_only = "";
		$from_date = "";
		$to_date = "";
					
		if ($this->input->post('user_id')) {
		
			$data['statuses'] = $this->mdl_est_status->get_all();
			
			//Presets:
			$data['user_id'] = $this->input->post('user_id');
			$data['data_available'] = TRUE;
			$data['dates_available'] = FALSE;
			$estimator_id = $this->input->post('user_id');
			$data['estimator_id'] = $estimator_id;
			$status_only = "";
			$data['date'] = date('Y-m-d');
			
			$data['estimator_meta'] = $this->mdl_user->get_user_name($estimator_id)->row();
			
			$all_totals = $this->mdl_reports->revenue_estimates_sum_new([]);

			if ($this->input->post('from_date') == '' && $this->input->post('to_date') == '') {
				
				$data['corp_revenue_total_estimates'] = 0;//$this->mdl_reports->revenue_estimates_sum_new();
 						
				foreach($data['statuses'] as $estStatus)
				{
					$data['corp_revenue_estimates'][$estStatus->est_status_id] = 0;
					foreach($all_totals as $est_total){
						if($estStatus->est_status_id == $est_total->status){
							$data['corp_revenue_estimates'][$estStatus->est_status_id] = $est_total->sum_for_services;
						}
					}
							
					$data['corp_revenue_total_estimates'] += $data['corp_revenue_estimates'][$estStatus->est_status_id];
				}
				
				//Data:
				$id = $estimator_id;
				
				$data['estimators_files'] = $this->mdl_reports->get_estimators_files($estimator_id, 100, 0);	
				
				//Productivity:			
				$data['total_estimates'] = 0;//$this->mdl_reports->estimates_statistic($status, $estimator_id, $status_only, $from_date, $to_date);
				$data['revenue_total_estimates'] = 0;//$this->mdl_reports->revenue_estimates_sum_new(['estimates.user_id'=>$estimator_id]);
				$all_estimates_user = $this->mdl_reports->estimates_statistic('', $estimator_id, '', $from_date, $to_date);
				$all_totals_user = $this->mdl_reports->revenue_estimates_sum_new(['estimates.user_id'=>$estimator_id]);
		
				foreach($data['statuses'] as $estStatus)
				{					
					$data['personal_estimates'][$estStatus->est_status_id] = 0;
					$data['revenue_personal_estimates'][$estStatus->est_status_id] = 0;
					
					foreach($all_estimates_user as $est_total){
						if($estStatus->est_status_id == $est_total->status){
							$data['personal_estimates'][$estStatus->est_status_id] = $est_total->estimates_amount;
						}
					}					
					foreach($all_totals_user as $est_total){
						if($estStatus->est_status_id == $est_total->status){
							$data['revenue_personal_estimates'][$estStatus->est_status_id] = $est_total->sum_for_services;
						}
					}
					
					$data['total_estimates'] += $data['personal_estimates'][$estStatus->est_status_id];
					$data['revenue_total_estimates'] += $data['revenue_personal_estimates'][$estStatus->est_status_id];
				}

			}

			//
			//
			//	DATES REQUESTED - DATA RECEVIED:
			//
			//
			else if ($this->input->post('from_date') != '' && $this->input->post('to_date') != '') {
				//Presets:

				//Turn ON Information:
				$data['dates_available'] = TRUE;

				//Get the dates selected by the user:
//				$from_date = $this->input->post('from_date');
//				$to_date = $this->input->post('to_date');
//				die(json_encode($this->input->post('from_date')));
				$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from_date'));
				if(!$from) {
					return $this->response([
						'status' => false,
						'message' => 'Wrong From Date'
					], 400);					
				}
//				$data['from'] = strtotime($from->format('Y-m-d') . " 00:00:00") ;
				$from_date = $from->format('Y-m-d');
				$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to_date'));
				if(!$to) {
					return $this->response([
						'status' => false,
						'message' => 'Wrong To Date'
					], 400);
				}
				$to_date = $to->format('Y-m-d');
				//Make the dates avalilable for future references:
				$data['from_date_val'] = $from_date;
				$data['to_date_val'] = $to_date;

				//Get the number of working days between two dates - business_days_cal helper;
				$startDate = $from_date;
				$endDate = $to_date;
				$holidays = array("");
				$data['period_days_val'] = getWorkingDays($startDate, $endDate, $holidays);
				
				$this->load->model('mdl_estimates');
				$this->load->model('mdl_clients');
				
				$qaWdata['estimates.user_id'] = $estimator_id;
				$qaWdata['estimates.date_created >'] = strtotime($from_date);
				$qaWdata['estimates.date_created <'] = strtotime($to_date)+86400;
				
				//Data:
				$data['total_estimates_date'] = 0;
				$all_estimates_date = $this->mdl_reports->estimates_statistic('', $estimator_id, '', $from_date, $to_date);
				foreach($all_estimates_date as $est){
					$data['total_estimates_date'] += $est->estimates_amount;
				}
				
				$estimates = $this->mdl_estimates->get_estimates('', $status, $data['total_estimates_date'], 0, "estimates.estimate_id", "DESC", $qaWdata);
				
				if($estimates)
				{
					$date['client_note_date >='] = $from_date . ' 00:00:00';
					$date['client_note_date <='] = date('Y-m-d H:i:s'); //?

					$estimates = $estimates->result_array();
					$estimate_contacts = 0;
					$data['estimate_counts_contact'] = 0;
					$data['estimator_counts_contact'] = 0;
					foreach($estimates as $key=>$val)
					{
						$estimate_contacts = $this->mdl_clients->get_notes($val['client_id'], 'contact', $date);
						if($estimate_contacts)
						{
							$data['estimate_counts_contact'] += count($estimate_contacts);//countOk
							foreach($estimate_contacts as $jkey=>$jval)
							{
								if($jval['author'] == $estimator_id)
									$data['estimator_counts_contact'] += 1;
							}
						}
						
					}
				}

				$u_from_date = strtotime($from_date);
				$u_to_date = strtotime($to_date) + 86400;
				$data['revenue_total_estimates_date'] = 0;//$this->mdl_reports->revenue_estimates_sum_new(['estimates.user_id'=>$estimator_id, 'estimates.date_created >='=>$u_from_date,
														//'estimates.date_created <='=>$u_to_date]);

				$data['confirmed_estimates_date'] = 0;
				$data['revenue_confirmed_estimates_date'] = 0; 
				$data['declined_estimates_date'] = 0;
				$data['revenue_declined_estimates_date'] = 0;
				
				$stats_by_date = $this->mdl_reports->estimates_statistic('', $estimator_id, '', $from_date, $to_date);
				$revenue_by_date = $this->mdl_reports->revenue_estimates_sum_new(['estimates.user_id'=>$estimator_id,
																	'estimates.date_created >='=>$u_from_date, 'estimates.date_created <='=>$u_to_date]);
				
				foreach($data['statuses'] as $key=>$status)
				{
					if($status->est_status_confirmed)
					{
						foreach($stats_by_date as $est_total){
							if($status->est_status_id == $est_total->status){
								$data['confirmed_estimates_date'] += $est_total->estimates_amount;
							}
						}
						foreach($revenue_by_date as $est_total){
							if($status->est_status_id == $est_total->status){
								$data['revenue_confirmed_estimates_date'] += $est_total->sum_for_services;
								$data['revenue_total_estimates_date'] += $est_total->sum_for_services;
							}
						}
												
					}
					elseif($status->est_status_declined)
					{
						
						foreach($stats_by_date as $est_total){
							if($status->est_status_id == $est_total->status){
								$data['declined_estimates_date'] += $est_total->estimates_amount;
							}
						}
						foreach($revenue_by_date as $est_total){
							if($status->est_status_id == $est_total->status){
								$data['revenue_declined_estimates_date'] += $est_total->sum_for_services;
								$data['revenue_total_estimates_date'] += $est_total->sum_for_services;
							}
						}
					}
					elseif(!$status->est_status_declined && !$status->est_status_confirmed) {
						foreach($revenue_by_date as $est_total) {
							if($status->est_status_id == $est_total->status) {
								$data['revenue_total_estimates_date'] += $est_total->sum_for_services;
							}
						}
					}
				}
				
			}
			
			if ($this->input->post('from_date') == '' && $this->input->post('to_date') == '') {
				$html = $this->load->view('estimates_personal_statistic', $data, TRUE);
				$htmlf = $this->load->view('estimates_personal_files', ['estimators_files' => $data['estimators_files']], TRUE);
				$htmld = $this->load->view('estimates_personal_statistic_date', $data, TRUE);
	
				return $this->response([
					'status' => TRUE,
					'data' => ['stats_html' => $html, 'stats_html_date' => $htmld, 'files_html' => $htmlf, 'by_dates' => 0]
				], 200);
			} else {
				$html = $this->load->view('estimates_personal_statistic_date', $data, TRUE);
				return $this->response([
					'status' => TRUE,
					'data' => ['stats_html_date' => $html, 'by_dates' => 1]
				], 200);
			}
			
		} else {
			return $this->response([
				'status' => false,
				'message' => 'User ID Is Not Provided'
			], 400);
		}
	}

	function estimates_statistic()
	{
		$data['title'] = $this->_title . ' - Estimates Statistic';
		$data['count'] = is_array($this->input->post('count'))? element('count', $this->input->post('count'), 0): 0;

		$postDates = $this->input->post('dates') ? explode(' - ', $this->input->post('dates')) : '';
		$data['to'] = date('Y-m-d 23:59:59');
		$data['from'] = date('Y-m-d 00:00:00', mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
		$where['event_start >='] = $wdata['estimates.date_created >='] = strtotime($data['from']);
		$where['event_end <='] = $wdata['estimates.date_created <='] = strtotime($data['to']);

		if (isset($postDates[0]) && !empty($postDates[0]))
		{
			$from = DateTime::createFromFormat(getDateFormat(), $postDates[0]);
			if(!$from)
				$from = $postDates[0];
			else
				$from = $from->format('Y-m-d');
			$data['from'] = $from . " 00:00:00" ;
			$where['event_start >='] = $wdata['estimates.date_created >='] = strtotime($data['from']);
		}
		if (isset($postDates[0]) && !empty($postDates[0]))
		{
			$to = DateTime::createFromFormat(getDateFormat(), $postDates[1]);
			if(!$to)
				$to = $postDates[1];
			else
				$to = $to->format('Y-m-d');
			$data['to'] = $to . " 23:59:59";
			$where['event_end <='] = $wdata['estimates.date_created <='] = strtotime($data['to']);
		}

		//echo '<pre>'; var_dump($data, $_POST); die;
		$dates['1_month'][0] = date(getDateFormat(), strtotime(date('Y-m-d') . ' -30 days'));
		$dates['1_month'][1] = date(getDateFormat());
		$dates['3_months'][0] = date(getDateFormat(), strtotime(date('Y-m-d') . ' -3 months'));
		$dates['3_months'][1] = date(getDateFormat());
		$dates['6_months'][0] = date(getDateFormat(), strtotime(date('Y-m-d') . ' -6 months'));
		$dates['6_months'][1] = date(getDateFormat());
		$dates['12_months'][0] = date(getDateFormat(), strtotime(date('Y-m-d') . ' -12 months'));
		$dates['12_months'][1] = date(getDateFormat());

		$data['dates'] = $dates;
		$data['from_date'] = date(getDateFormat(), strtotime($data['from']));
		$data['to_date'] = date(getDateFormat(), strtotime($data['to']));
		//echo '<pre>'; var_dump($_POST, $data); die;
		//************* function confirmed_client ******************
		//************* (array) conditions, (bool) is_new, (bool) is_estimated  ******************
		$confWhere = ['estimates.date_created >=' => strtotime($data['from']), 'estimates.date_created <=' => strtotime($data['to'])];

		/* calculate new confirmed leads percentage */
		$data['estimators_files']['conf_new_client_company']['all'] = $new_leads = $this->mdl_leads->confirmed_client($confWhere, TRUE);
		$data['estimators_files']['conf_new_client_company']['count'] = $new_confirmed_leads = $this->mdl_leads->confirmed_client($confWhere, TRUE, TRUE);
		$data['estimators_files']['conf_new_client_company']['total'] = calculate_percentage($new_leads, $new_confirmed_leads);

		/* calculate old confirmed leads percentage */
		$data['estimators_files']['conf_old_client_company']['all'] = $old_leads = $this->mdl_leads->confirmed_client($confWhere, FALSE);
		$data['estimators_files']['conf_old_client_company']['count'] = $old_confirmed_leads = $this->mdl_leads->confirmed_client($confWhere, FALSE, TRUE);
		$data['estimators_files']['conf_old_client_company']['total'] = calculate_percentage($old_leads, $old_confirmed_leads);

		$this->load->model('mdl_user');
		$users = $this->mdl_user->get_user(null, null, 'id ASC');
		$data['estimators'] = $users->num_rows() ? $users->result_array() : [];
		$data['fields'] = ['Confirmed '.get_currency().' Total', 'Confirmed % '.get_currency(), 'Confirmed % #', 'Confirmed #', 'Confirmed % Medium', 'Medium Confirmed Estimate', 'Medium Estimate', 'Total Estimate #', 'Total Estimate '.get_currency(), "Confirmed New Client %<br><small><strong>(Total / Confirmed)</strong></small>", 'Confirmed Old Client %<br><small><strong>(Total / Confirmed)</strong></small>', 'Invoiced '.get_currency(). ' Total<br><small><strong>(Total / Estimator)</strong></small>', 'Paid '.get_currency(). ' Total<br><small><strong>(Total / Estimator)</strong></small>'];

		$data['fields_keys'] = ['confirmed_sum', 'confirmed_sum_perc', 'confirmed_perc', 'confirmed_count', 'confirmed_perc_medium', 'medium_confirmed_estimate', 'medium_estimate', 'total_count', 'total_sum', 'conf_new_client', 'conf_old_client', 'invoiced_sum', 'paid_sum'];

		$data['fields_signes'] = [get_currency(), '%', '%', '', '%', get_currency(), get_currency(), '', get_currency(), '%', '%', get_currency(),  get_currency()];
		$data['estimators_files']['company_confirmed'] = 0;
		$data['estimators_files']['confirmed_sum_company'] = 0;
		$data['estimators_files']['confirmed_count_company'] = 0;
		$data['estimators_files']['total_sum_company'] = 0;
		$data['estimators_files']['total_count_company'] = 0;
		$data['estimators_files']['invoiced_sum_company']['total'] = 0;
		$data['estimators_files']['invoiced_sum_company']['count'] = 0;

		$data['estimators_files']['paid_sum_company']['total'] = 0;
		$data['estimators_files']['paid_sum_company']['count'] = 0;

		$countEstor = count($data['estimators']) + 1;

		//$data['estimators'][$countEstor]['id'] = $countEstor;
		//$data['estimators'][$countEstor]['firstname'] = 'Other';
		//$data['estimators'][$countEstor]['emp_field_estimator'] = 0;
		$data['count_estimators'] = 0;
		$a = [];
		/*$data['estimators_files']['conf_new_client'][$countEstor]['count'] = $data['estimators_files']['conf_old_client'][$countEstor]['count'] = 0;
		$data['estimators_files']['conf_new_client'][$countEstor]['total'] = $data['estimators_files']['conf_old_client'][$countEstor]['total'] = 0;
		$data['estimators_files']['confirmed_sum_perc'][$countEstor]['total'] = $data['estimators_files']['confirmed_perc'][$countEstor]['total'] = 0;
		$data['estimators_files']['confirmed_perc_medium'][$countEstor]['total'] = $data['estimators_files']['medium_confirmed_estimate'][$countEstor]['total'] = 0;
		$data['estimators_files']['medium_estimate'][$countEstor]['total'] = $data['estimators_files']['medium_confirmed_estimate'][$countEstor]['total'] = 0;
		$data['estimators_files']['total_count'][$countEstor] = $data['estimators_files']['total_sum'][$countEstor] = 0;
		$data['estimators_files']['confirmed_count'][$countEstor] = $data['estimators_files']['confirmed_sum'][$countEstor] = 0;
		$data['estimators_files']['invoiced_sum'][$countEstor] = $data['estimators_files']['invoiced_count'][$countEstor] = 0;
		$data['estimators_files']['paid_sum'][$countEstor] = $data['estimators_files']['paid_count'][$countEstor] = 0;
*/

		$estimate_stats = $this->mdl_estimates->estimates_statistic(false, $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'employees.emp_field_estimator = "1"', true, 'estimates.user_id');

		$data['estimate_stats'] = $estimate_stats->result_array();
		$lastId = 0;
		foreach($data['estimate_stats'] as $key=>$val) {
			$confWhere['estimates.user_id'] = $val['id'];
			$conf_estimate_stats_data = $this->mdl_estimates->estimates_statistic($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'workorders.id IS NOT NULL AND employees.emp_field_estimator = "1"', true, 'estimates.user_id');
			$conf_estimate_stats = $conf_estimate_stats_data->row_array();

			$invoiced_stats_data = $this->mdl_estimates->estimates_statistic($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'invoices.in_status != 4 AND employees.emp_field_estimator = "1"', true, 'estimates.user_id');
			$invoiced_stats = $invoiced_stats_data->row_array();
			$paid_stats_data = $this->mdl_estimates->estimates_statistic($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'invoices.in_status = 4 AND employees.emp_field_estimator = "1"', true, 'estimates.user_id');
			$paid_stats = $paid_stats_data->row_array();
			if ($val['paid_invoices'] == null) {
				unset($data['estimate_stats'][$key]);
				continue;
			}

			$data['estimators_files']['total_count'][$val['id']] = $val['quantity'];
			$data['estimators_files']['total_count_company'] += $val['quantity'];//countOk
			$data['estimators_files']['total_sum'][$val['id']] = round($val['sum_without_tax'], 2);
			$data['estimators_files']['total_sum_company'] += round($val['sum_without_tax'], 2);

			$data['estimators_files']['confirmed_count'][$val['id']] = isset($conf_estimate_stats['quantity']) ? $conf_estimate_stats['quantity'] : 0;
			$data['estimators_files']['confirmed_count_company'] += isset($conf_estimate_stats['quantity']) ? $conf_estimate_stats['quantity'] : 0;//countOk

			$data['estimators_files']['confirmed_sum'][$val['id']] = isset($conf_estimate_stats['sum_without_tax']) ? round($conf_estimate_stats['sum_without_tax'], 2) : 0;
			$data['estimators_files']['confirmed_sum_company'] += isset($conf_estimate_stats['sum_without_tax']) ? round($conf_estimate_stats['sum_without_tax'], 2) : 0;

			$data['estimators_files']['invoiced_sum'][$val['id']]['total'] =  isset($invoiced_stats['sum_without_tax']) ? round($invoiced_stats['sum_without_tax'], 2) : 0;
			$data['estimators_files']['invoiced_sum_company']['total'] += isset($invoiced_stats['sum_without_tax']) ? round($invoiced_stats['sum_without_tax'], 2) : 0;
			$data['estimators_files']['invoiced_sum'][$val['id']]['count'] = isset($invoiced_stats['quantity']) ? $invoiced_stats['quantity'] : 0;
			$data['estimators_files']['invoiced_sum_company']['count'] +=  isset($invoiced_stats['quantity']) ? $invoiced_stats['quantity'] : 0;//countOk


			$data['estimators_files']['paid_sum'][$val['id']]['total'] = isset($paid_stats['sum_without_tax']) ? round($paid_stats['sum_without_tax'], 2) : 0 ;
			$data['estimators_files']['paid_sum_company']['total'] += isset($paid_stats['sum_without_tax']) ? round($paid_stats['sum_without_tax'], 2) : 0 ;
			$data['estimators_files']['paid_sum'][$val['id']]['count'] = isset($paid_stats['quantity']) ? $paid_stats['quantity'] : 0;
			$data['estimators_files']['paid_sum_company']['count'] += isset($paid_stats['quantity']) ? $paid_stats['quantity'] : 0;//countOk




			$data['estimators_files']['confirmed_sum_perc'][$val['id']] = ($data['estimators_files']['total_sum'][$val['id']]) ? round($data['estimators_files']['confirmed_sum'][$val['id']] * 100 / $data['estimators_files']['total_sum'][$val['id']], 2) : 0;
			$data['estimators_files']['confirmed_perc'][$val['id']] = ($data['estimators_files']['total_count'][$val['id']]) ? round($data['estimators_files']['confirmed_count'][$val['id']] * 100 / $data['estimators_files']['total_count'][$val['id']], 2) : 0;
			$data['estimators_files']['confirmed_perc_medium'][$val['id']] = round(($data['estimators_files']['confirmed_sum_perc'][$val['id']] + $data['estimators_files']['confirmed_perc'][$val['id']]) / 2, 2);
			$data['estimators_files']['medium_confirmed_estimate'][$val['id']] = ($data['estimators_files']['confirmed_count'][$val['id']]) ? round($data['estimators_files']['confirmed_sum'][$val['id']] / $data['estimators_files']['confirmed_count'][$val['id']], 2) : 0;
			$data['estimators_files']['medium_estimate'][$val['id']] = ($data['estimators_files']['total_count'][$val['id']]) ? round($data['estimators_files']['total_sum'][$val['id']] / $data['estimators_files']['total_count'][$val['id']], 2) : 0;

			$data['estimators_files']['conf_new_client'][$val['id']]['count'] = $estimator_new_leads = $this->mdl_leads->confirmed_client($confWhere, TRUE, TRUE);
			$data['estimators_files']['conf_old_client'][$val['id']]['count'] = $estimator_old_leads = $this->mdl_leads->confirmed_client($confWhere, FALSE, TRUE);

			$data['estimators_files']['conf_new_client'][$val['id']]['total'] = calculate_percentage($new_confirmed_leads, $estimator_new_leads);
			$data['estimators_files']['conf_old_client'][$val['id']]['total'] = calculate_percentage($old_confirmed_leads, $estimator_old_leads);

			$data['estimators_files']['conf_old_client'][$val['id']] = array_reverse($data['estimators_files']['conf_old_client'][$val['id']]);
			$data['estimators_files']['conf_new_client'][$val['id']] = array_reverse($data['estimators_files']['conf_new_client'][$val['id']]);
			$lastId = $val['id'];
		}
		//echo '<pre>'; var_dump($data); die;
		$allConfWhere = 'estimates.date_created >= ' . strtotime($data['from']) . ' AND estimates.date_created <= ' . strtotime($data['to']) . ' AND (employees.emp_field_estimator != "1" OR employees.emp_field_estimator IS NULL)';

		$lastId++;
		$data['estimate_stats'][$lastId]['firstname'] = 'Other';
		$est_stats_data = $this->mdl_estimates->estimates_statistic(false, $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], '(employees.emp_field_estimator != "1" OR employees.emp_field_estimator IS NULL)', true, false);
		$est_stats = $est_stats_data->row_array();
		//(employees.emp_field_estimator != "1" OR employees.emp_field_estimator IS NULL)'

		$conf_estimate_stats_data = $this->mdl_estimates->estimates_statistic(false, $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'workorders.id IS NOT NULL AND (employees.emp_field_estimator != "1" OR employees.emp_field_estimator IS NULL)', true, false);
		$conf_estimate_stats = $conf_estimate_stats_data->row_array();
		$invoiced_stats_data = $this->mdl_estimates->estimates_statistic(false, $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'invoices.in_status != 4 AND (employees.emp_field_estimator != "1" OR employees.emp_field_estimator IS NULL)', true, false);
		$invoiced_stats = $invoiced_stats_data->row_array();
		$paid_stats_data = $this->mdl_estimates->estimates_statistic(false, $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], 'invoices.in_status = 4 AND (employees.emp_field_estimator != "1" OR employees.emp_field_estimator IS NULL)', true, false);
		$paid_stats = $paid_stats_data->row_array();
		$data['estimators_files']['total_count'][$lastId] = isset($est_stats['quantity']) ? $est_stats['quantity'] : 0;
		$data['estimators_files']['total_count_company'] += isset($est_stats['quantity']) ? $est_stats['quantity'] : 0;//countOk
		$data['estimators_files']['total_sum'][$lastId] = isset($est_stats['sum_without_tax']) ? round($est_stats['sum_without_tax'], 2) : 0;
		$data['estimators_files']['total_sum_company'] += isset($est_stats['sum_without_tax']) ? round($est_stats['sum_without_tax'], 2) : 0;

		$data['estimators_files']['confirmed_count'][$lastId] = isset($conf_estimate_stats['quantity']) ? $conf_estimate_stats['quantity'] : 0;
		$data['estimators_files']['confirmed_count_company'] += isset($conf_estimate_stats['quantity']) ? $conf_estimate_stats['quantity'] : 0;//countOk

		$data['estimators_files']['confirmed_sum'][$lastId] = isset($conf_estimate_stats['sum_without_tax']) ? round($conf_estimate_stats['sum_without_tax'], 2) : 0;
		$data['estimators_files']['confirmed_sum_company'] += isset($conf_estimate_stats['sum_without_tax']) ? round($conf_estimate_stats['sum_without_tax'], 2) : 0;



		$data['estimators_files']['invoiced_sum'][$lastId]['total'] =  isset($invoiced_stats['sum_without_tax']) ? round($invoiced_stats['sum_without_tax'], 2) : 0;
		$data['estimators_files']['invoiced_sum_company']['total'] += isset($invoiced_stats['sum_without_tax']) ? round($invoiced_stats['sum_without_tax'], 2) : 0;
		$data['estimators_files']['invoiced_sum'][$lastId]['count'] = isset($invoiced_stats['quantity']) ? $invoiced_stats['quantity'] : 0;
		$data['estimators_files']['invoiced_sum_company']['count'] +=  isset($invoiced_stats['quantity']) ? $invoiced_stats['quantity'] : 0;//countOk

		$data['estimators_files']['paid_sum'][$lastId]['total'] = isset($paid_stats['sum_without_tax']) ? round($paid_stats['sum_without_tax'], 2) : 0 ;
		$data['estimators_files']['paid_sum_company']['total'] += isset($paid_stats['sum_without_tax']) ? round($paid_stats['sum_without_tax'], 2) : 0 ;
		$data['estimators_files']['paid_sum'][$lastId]['count'] = isset($paid_stats['quantity']) ? $paid_stats['quantity'] : 0;
		$data['estimators_files']['paid_sum_company']['count'] += isset($paid_stats['quantity']) ? $paid_stats['quantity'] : 0;//countOk




		$data['estimators_files']['confirmed_sum_perc'][$lastId] = ($data['estimators_files']['total_sum'][$lastId]) ? round($data['estimators_files']['confirmed_sum'][$lastId] * 100 / $data['estimators_files']['total_sum'][$lastId], 2) : 0;
		$data['estimators_files']['confirmed_perc'][$lastId] = ($data['estimators_files']['total_count'][$lastId]) ? round($data['estimators_files']['confirmed_count'][$lastId] * 100 / $data['estimators_files']['total_count'][$lastId], 2) : 0;
		$data['estimators_files']['confirmed_perc_medium'][$lastId] = round(($data['estimators_files']['confirmed_sum_perc'][$lastId] + $data['estimators_files']['confirmed_perc'][$lastId]) / 2, 2);
		$data['estimators_files']['medium_confirmed_estimate'][$lastId] = ($data['estimators_files']['confirmed_count'][$lastId]) ? round($data['estimators_files']['confirmed_sum'][$lastId] / $data['estimators_files']['confirmed_count'][$lastId], 2) : 0;
		$data['estimators_files']['medium_estimate'][$lastId] = ($data['estimators_files']['total_count'][$lastId]) ? round($data['estimators_files']['total_sum'][$lastId] / $data['estimators_files']['total_count'][$lastId], 2) : 0;

		$data['estimators_files']['conf_new_client'][$lastId]['count'] = $estimator_new_leads = $this->mdl_leads->confirmed_client($allConfWhere, TRUE, TRUE);
		$data['estimators_files']['conf_old_client'][$lastId]['count'] = $estimator_old_leads = $this->mdl_leads->confirmed_client($allConfWhere, FALSE, TRUE);

		$data['estimators_files']['conf_new_client'][$lastId]['total'] = calculate_percentage($new_confirmed_leads, $estimator_new_leads);
		$data['estimators_files']['conf_old_client'][$lastId]['total'] = calculate_percentage($old_confirmed_leads, $estimator_old_leads);

		$data['estimators_files']['conf_old_client'][$lastId] = array_reverse($data['estimators_files']['conf_old_client'][$lastId]);
		$data['estimators_files']['conf_new_client'][$lastId] = array_reverse($data['estimators_files']['conf_new_client'][$lastId]);


		/*foreach($data['estimators'] as $key=>$val) {
			if(intval($val['emp_field_estimator']) && $val['active_status'] === 'yes')
			{
				$confWhere['estimates.user_id'] = $where['estimates.user_id'] = $wdata['estimates.user_id'] = $val['id'];
				$estimate_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], [], true);
				$conf_estimate_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], ['workorders.id IS NOT NULL' => NULL], true);
				$invoiced_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], ['invoices.in_status != 4' =>  NULL], true);
				$paid_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], ['invoices.in_status' =>  4], true);
				if ($estimate_stats['sales'] == NULL && $conf_estimate_stats['sales'] == NULL) {
					unset($data['estimators'][$key]);
					continue;
				}
				$data['estimators_files']['total_count'][$key] = $estimate_stats['quantity'];
				$data['estimators_files']['total_count_company'] += $estimate_stats['quantity'];//countOk
				$data['estimators_files']['total_sum'][$key] = round($estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['total_sum_company'] += round($estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['confirmed_count'][$key] = $conf_estimate_stats['quantity'];
				$data['estimators_files']['confirmed_count_company'] += $conf_estimate_stats['quantity'];//countOk
				$data['estimators_files']['confirmed_sum'][$key] = round($conf_estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['confirmed_sum_company'] += round($conf_estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['invoiced_count'][$key] = $invoiced_stats['quantity'];
				$data['estimators_files']['invoiced_count_company'] += $invoiced_stats['quantity'];//countOk
				$data['estimators_files']['invoiced_sum'][$key] = round($invoiced_stats['sum_without_tax'], 2);
				$data['estimators_files']['invoiced_sum_company'] += round($invoiced_stats['sum_without_tax'], 2);
				$data['estimators_files']['paid_count'][$key] = $paid_stats['quantity'];
				$data['estimators_files']['paid_count_company'] += $paid_stats['quantity'];//countOk
				$data['estimators_files']['paid_sum'][$key] = round($paid_stats['sum_without_tax'], 2);
				$data['estimators_files']['paid_sum_company'] += round($paid_stats['sum_without_tax'], 2);
				$data['estimators_files']['confirmed_sum_perc'][$key] = ($data['estimators_files']['total_sum'][$key]) ? round($data['estimators_files']['confirmed_sum'][$key] * 100 / $data['estimators_files']['total_sum'][$key], 2) : 0;
				$data['estimators_files']['confirmed_perc'][$key] = ($data['estimators_files']['total_count'][$key]) ? round($data['estimators_files']['confirmed_count'][$key] * 100 / $data['estimators_files']['total_count'][$key], 2) : 0;
				$data['estimators_files']['confirmed_perc_medium'][$key] = round(($data['estimators_files']['confirmed_sum_perc'][$key] + $data['estimators_files']['confirmed_perc'][$key]) / 2, 2);
				$data['estimators_files']['medium_confirmed_estimate'][$key] = ($data['estimators_files']['confirmed_count'][$key]) ? round($data['estimators_files']['confirmed_sum'][$key] / $data['estimators_files']['confirmed_count'][$key], 2) : 0;
				$data['estimators_files']['medium_estimate'][$key] = ($data['estimators_files']['total_count'][$key]) ? round($data['estimators_files']['total_sum'][$key] / $data['estimators_files']['total_count'][$key], 2) : 0;
				$data['estimators_files']['conf_new_client'][$key]['count'] = $estimator_new_leads = $this->mdl_leads->confirmed_client($confWhere, TRUE, TRUE);
				$data['estimators_files']['conf_old_client'][$key]['count'] = $estimator_old_leads = $this->mdl_leads->confirmed_client($confWhere, FALSE, TRUE);
				$data['estimators_files']['conf_new_client'][$key]['total'] = calculate_percentage($new_confirmed_leads, $estimator_new_leads);
				$data['estimators_files']['conf_old_client'][$key]['total'] = calculate_percentage($old_confirmed_leads, $estimator_old_leads);
				$data['estimators_files']['conf_old_client'][$key] = array_reverse($data['estimators_files']['conf_old_client'][$key]);
				$data['estimators_files']['conf_new_client'][$key] = array_reverse($data['estimators_files']['conf_new_client'][$key]);
			}
			else {
				if($val['id'] != $key && $key != $countEstor) {
					unset($data['estimators'][$key]);
				}
				if($key == $countEstor)
				{
					continue;
				}
				$confWhere['estimates.user_id'] = $wdata['estimates.user_id'] = $val['id'];
				$estimate_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], [], true);
				$conf_estimate_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], ['workorders.id IS NOT NULL' => NULL], true);
				$invoiced_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], ['invoices.in_status != 4' =>  NULL], true);
				$paid_stats = $this->mdl_estimates->get_estimators_stat($val['id'], $wdata['estimates.date_created >='], $wdata['estimates.date_created <='], ['invoices.in_status' =>  4], true);
				if ($estimate_stats['sales'] == NULL && $conf_estimate_stats['sales'] == NULL) {
					unset($data['estimators'][$key]);
					continue;
				}
				if($val['id'] == 0){
					unset($data['estimators'][$key]);
				}
				$estimate_stats = $estimate_stats ? $estimate_stats : [];
				$data['estimators_files']['total_count'][$countEstor] += $estimate_stats['quantity'];
				$data['estimators_files']['total_count_company'] += $estimate_stats['quantity'];//countOk
				$data['estimators_files']['total_sum'][$countEstor] += round($estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['total_sum_company'] += round($estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['confirmed_count'][$countEstor] += $conf_estimate_stats['quantity'];
				$data['estimators_files']['confirmed_count_company']  += $conf_estimate_stats['quantity'];//countOk
				$data['estimators_files']['confirmed_sum'][$countEstor] += round($conf_estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['confirmed_sum_company'] += round($conf_estimate_stats['sum_without_tax'], 2);
				$data['estimators_files']['invoiced_count'][$countEstor] += $invoiced_stats['quantity'];
				$data['estimators_files']['invoiced_count_company'] += $invoiced_stats['quantity'];//countOk
				$data['estimators_files']['invoiced_sum'][$countEstor] += round($invoiced_stats['sum_without_tax'], 2);
				$data['estimators_files']['invoiced_sum_company'] += round($invoiced_stats['sum_without_tax'], 2);
				$data['estimators_files']['paid_count'][$countEstor] += $paid_stats['quantity'];
				$data['estimators_files']['paid_count_company'] += $paid_stats['quantity'];//countOk
				$data['estimators_files']['paid_sum'][$countEstor] += round($paid_stats['sum_without_tax'], 2);
				$data['estimators_files']['paid_sum_company'] += round($paid_stats['sum_without_tax'], 2);
				$data['estimators_files']['confirmed_sum_perc'][$countEstor] = ($data['estimators_files']['total_sum'][$countEstor]) ? round($data['estimators_files']['confirmed_sum'][$countEstor] * 100 / $data['estimators_files']['total_sum'][$countEstor], 2) : 0;
				$data['estimators_files']['confirmed_perc'][$countEstor] = ($data['estimators_files']['total_count'][$countEstor]) ? round($data['estimators_files']['confirmed_count'][$countEstor] * 100 / $data['estimators_files']['total_count'][$countEstor], 2) : 0;
				$data['estimators_files']['confirmed_perc_medium'][$countEstor] = round(($data['estimators_files']['confirmed_sum_perc'][$countEstor] + $data['estimators_files']['confirmed_perc'][$countEstor]) / 2, 2);
				$data['estimators_files']['medium_confirmed_estimate'][$countEstor] = ($data['estimators_files']['confirmed_count'][$countEstor]) ? round($data['estimators_files']['confirmed_sum'][$countEstor] / $data['estimators_files']['confirmed_count'][$countEstor], 2) : 0;
				$data['estimators_files']['medium_estimate'][$countEstor] = ($data['estimators_files']['total_count'][$countEstor]) ? round($data['estimators_files']['total_sum'][$countEstor] / $data['estimators_files']['total_count'][$countEstor], 2) : 0;
				$data['estimators_files']['conf_new_client'][$countEstor]['count'] += $estimator_new_leads = $this->mdl_leads->confirmed_client($confWhere, TRUE, TRUE);
				$data['estimators_files']['conf_old_client'][$countEstor]['count'] += $estimator_old_leads = $this->mdl_leads->confirmed_client($confWhere, FALSE, TRUE);
				$data['estimators_files']['conf_new_client'][$countEstor]['total'] = calculate_percentage($new_confirmed_leads, $data['estimators_files']['conf_new_client'][$countEstor]['count']);
				$data['estimators_files']['conf_old_client'][$countEstor]['total'] = calculate_percentage($old_confirmed_leads, $data['estimators_files']['conf_old_client'][$countEstor]['count']);
				$data['count_estimators']++;
			}
		}
		$data['estimators_files']['conf_old_client'][$countEstor] = array_reverse($data['estimators_files']['conf_old_client'][$countEstor]);
		$data['estimators_files']['conf_new_client'][$countEstor] = array_reverse($data['estimators_files']['conf_new_client'][$countEstor]);
		foreach($data['estimators_files'] as $k=>$v){
			if(is_array($data['estimators_files'][$k])){
				ksort($data['estimators_files'][$k]);
			}
		}*/

		$wdata = $wdata_est = $wdata_emp = [];
		$wdata['team_date_start >='] = $wdata_est['team_date_start >='] = $wdata_emp['team_date_start >='] = date("Y-m-d", strtotime($data['from']));
		$wdata['team_date_start <='] = $wdata_est['team_date_start <='] = $wdata_emp['team_date_start <='] = date("Y-m-d", strtotime($data['to']));

		if($this->input->post('active_flag')){
			$wdata_emp['active_status'] = 'yes';
		}

		$data['active_flag'] = $this->input->post('active_flag');
		$avgFinishedWo = $this->mdl_workorders->estimator_stats_by_finished_wo(NULL, strtotime($data['from']), strtotime($data['to']));
		$newAvg = $this->mdl_workorders->estimator_stats_without_finished_wo(NULL, strtotime($data['from']), strtotime($data['to']));

		foreach($newAvg as $k=>&$v)
		{
			foreach($avgFinishedWo as $key=>$val)
			{
				if(isset($val->estimator_mhr_return) && $val->id == $v->id) {
					$v->avg_finished = $val->estimator_mhr_return;
					$v->avg_finished2 = $val->estimator_mhr_return2;
				}
			}
			$data['estimators_mhr_rate'][] = $v;
		}
		$data['employees_mhr_return'] = $this->mdl_workorders->employees_mhr_return($wdata, $wdata_emp);
		$data['total_damage_complain'] = $this->mdl_workorders->total_damage_complain($wdata);

		$this->load->view('index_estimates_statistic', $data);
	}

	function new_estimates_statistic()
	{
		$data['title'] = $this->_title . ' - Estimates Statistic';
		$to = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
		$data['to'] = $to->format('Y-m-d');
		$from = DateTime::createFromFormat('Y-m-d', date('Y-m-d', mktime(0, 0, 0, date("m")-1, date("d"),   date("Y"))));
		$data['from'] = $from->format('Y-m-d');
		$where['event_start >='] = $wdata['estimates.date_created >='] = strtotime($data['from'] . " 00:00:00");
		$where['event_end <='] = $wdata['estimates.date_created <='] = strtotime($data['to'] . " 23:59:59");
		$data['estimator_id'] = $this->input->post('estimator') ? $this->input->post('estimator') : false;
		
		
		$data['revenue_total_estimates_date'] = 0;

		$data['confirmed_estimates_date'] = 0;
		$data['revenue_confirmed_estimates_date'] = 0; 
		$data['declined_estimates_date'] = 0;
		$data['revenue_declined_estimates_date'] = 0;
		$data['total_estimates_date'] = 0;
		
		
		if ($this->input->post('from'))
		{
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			if(!$from)
				$from = $this->input->post('from');
			else
				$from = $from->format('Y-m-d');
			$data['from'] = $from;
			$where['event_start >='] = $wdata['estimates.date_created >='] = strtotime($from . " 00:00:00");
		}
		if ($this->input->post('to'))
		{
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			if(!$to)
				$to = $this->input->post('to');
			else
				$to = $to->format('Y-m-d');
			$data['to'] = $to;
			$where['event_end <='] = $wdata['estimates.date_created <='] = strtotime($to . " 23:59:59");
		}
		if($data['estimator_id'])
		{
			$data['estimator_invoiced_statistic'] = $this->mdl_estimates->get_estimators_stat($data['estimator_id'],  false,  false, ['invoices.id IS NOT NULL' => NULL, 'invoices.date_created >= "' . date('Y-m-d', strtotime($from)) . '"' => NULL, 'invoices.date_created <= "' . date('Y-m-d', strtotime($to)) . '"' => NULL], true);
			//echo '<pre>'; var_dump($this->db->last_query()); die;
			unset($data['estimator_invoiced_statistic']['sum_without_tax']);
			//echo '<pre>'; var_dump($data['estimator_invoiced_statistic'], $this->db->last_query()); die;
			$data['total_invoiced_statistic'] = $this->mdl_estimates->get_estimators_stat(false, /*$where['event_start >=']*/ false, /*$where['event_end <=']*/ false, ['invoices.id IS NOT NULL' => NULL, 'invoices.date_created >= "' . date('Y-m-d', strtotime($from)) . '"' => NULL, 'invoices.date_created <= "' . date('Y-m-d', strtotime($to)) . '"' => NULL], true);
			$data['not_invoiced_estimates'] = $this->mdl_estimates->get_estimators_stat($data['estimator_id'], /*$where['event_start >='], $where['event_end <=']*/ false, false, ['invoices.id IS NULL' => NULL, 'estimates.date_created >= '. strtotime($from)  => NULL, 'estimates.date_created <= ' . strtotime($to) => NULL]);
			$data['estimator_not_invoiced_statistic'] = count($data['not_invoiced_estimates']) ? count($data['not_invoiced_estimates']) : 0; //$this->mdl_estimates->get_estimators_stat($data['estimator_id'], /*$where['event_start >='], $where['event_end <=']*/ false, false, ['invoices.id IS NULL' => NULL], true);
			//echo '<pre>'; var_dump($this->db->last_query()); die;
			$estimatorData = $this->mdl_estimates->get_all_estimators(['id' => $data['estimator_id']]);
			$data['estimator'] = array_shift($estimatorData);
			$data['invoiced_estimates'] = $this->mdl_estimates->get_estimators_stat($data['estimator_id'], /*$where['event_start >=']*/ false, /*$where['event_end <=']*/ false, ['invoices.id IS NOT NULL' => NULL, 'invoices.date_created >= "' . date('Y-m-d', strtotime($from)) . '"' => NULL, 'invoices.date_created <= "' . date('Y-m-d', strtotime($to)) . '"' => NULL]);
			
			$data['statuses'] = $this->mdl_est_status->get_all();
			
			$all_estimates_date = $this->mdl_reports->estimates_statistic('', $data['estimator_id'], '', $data['from'], $data['to']);
			foreach($all_estimates_date as $est){
				$data['total_estimates_date'] += $est->estimates_amount;
			}
			
			$stats_by_date = $this->mdl_reports->estimates_statistic('', $data['estimator_id'], '', $data['from'], $data['to']);
			$revenue_by_date = $this->mdl_reports->revenue_estimates_sum_new(['estimates.user_id'=>$data['estimator_id'],
																'estimates.date_created >='=>$where['event_start >='], 'estimates.date_created <='=>$where['event_end <=']]);

			foreach($data['statuses'] as $key=>$status)
			{
				if($status->est_status_confirmed)
				{
					foreach($stats_by_date as $est_total){
						if($status->est_status_id == $est_total->status){
							$data['confirmed_estimates_date'] += $est_total->estimates_amount;
						}
					}
					foreach($revenue_by_date as $est_total){
						if($status->est_status_id == $est_total->status){
							$data['revenue_confirmed_estimates_date'] += $est_total->sum_for_services;
							//$data['revenue_total_estimates_date'] += $est_total->sum_for_services;
						}
					}
											
				}
				elseif($status->est_status_declined)
				{
					
					foreach($stats_by_date as $est_total){
						if($status->est_status_id == $est_total->status){
							$data['declined_estimates_date'] += $est_total->estimates_amount;
						}
					}
					foreach($revenue_by_date as $est_total){
						if($status->est_status_id == $est_total->status){
							$data['revenue_declined_estimates_date'] += $est_total->sum_for_services;
							//$data['revenue_total_estimates_date'] += $est_total->sum_for_services;
						}
					}
				}
			}
			foreach($revenue_by_date as $est_total) {
				$data['revenue_total_estimates_date'] += $est_total->sum_for_services;
			}
		}
		else
			$data['estimator_invoiced_statistic'] = false;
		


		$startDate = $data['from'];
		$endDate = $data['to'];
		$holidays = array("");
		$data['period_days_val'] = getWorkingDays($startDate, $endDate, $holidays);
		//echo '<pre>'; var_dump($data); die;
		//echo '<pre>'; var_dump($data['estimator_statistic'], $data['estimator']); die;
		//************* function confirmed_client ******************
		//************* (array) conditions, (bool) is_new, (bool) is_estimated  ******************	

		$data['estimators'] = $this->mdl_user->get_usermeta(['active_status' => 'yes', 'emp_field_estimator'=>'1', 'system_user' => 0])->result_array();
		
		$this->load->view('new_estimates_statistic', $data);
	}
	
	
	/*********************************************END ESTIMATES**************************************************/
	
	
	/*********************************************WO**************************************************/
	
	public function workorders_report()
	{

		$data['title'] = $this->_title . ' - Reports - Workorders';

		$data['total_workorders'] = $this->mdl_reports->workorders_statistic2('');
		$data['statuses'] = $this->mdl_workorders->get_all_statuses();
		
		$this->load->view("workorders", $data);
	}//End of workorders.
	
	/*********************************************END WO**************************************************/
	
	/*********************************************INVOICES**************************************************/
	
	public function invoices_report($fromDate = NULL, $toDate = NULL)
	{

		$data['title'] = $this->_title . ' - Reports - Invoices';
		$this->load->model('mdl_reports');
		$this->load->model('mdl_payroll');
		$status = "";
		//$data['all_time'] = FALSE;
		$dates['today'][0] = date(getDateFormat(), strtotime(date('Y-m-d 00:00:00')));
		$dates['today'][1] = date(getDateFormat(), strtotime(date('Y-m-d 23:59:59')));
		$dates['yesterday'][0] = date(getDateFormat(), strtotime(date('Y-m-d 00:00:00') .' -1 days'));
		$dates['yesterday'][1] = date(getDateFormat(), strtotime(date('Y-m-d 23:59:59') . '-1 days'));
		$dates['last_7_days'][0] = date(getDateFormat(), strtotime(date('Y-m-d 00:00:00') . ' -7 days'));
		$dates['last_7_days'][1] = date(getDateFormat(), strtotime(date('Y-m-d 23:59:59') . '-7 days'));
		$dates['last_30_days'][0] = date(getDateFormat(), strtotime(date('Y-m-d') . ' -30 days'));
		$dates['last_30_days'][1] = date(getDateFormat());
		$dates['this_month'][0] = date(getDateFormat(), strtotime(date('Y-m-01')));
		$dates['this_month'][1] = date(getDateFormat(), strtotime(date('Y-m-t')));
		$dates['last_month'][0] = date(getDateFormat(), strtotime("first day of previous month"));
		$dates['last_month'][1] = date(getDateFormat(), strtotime("last day of previous month"));


		$dates['all_time'][0] = date(getDateFormat(), strtotime($this->mdl_invoices->getStartInvoicesReportDate()));
		$dates['all_time'][1] = date(getDateFormat(), strtotime($this->mdl_invoices->getEndInvoicesReportDate()));

		if($fromDate) {
			$from_date = $fromDate;
			$to_date = $toDate;
		} elseif($fromDate == $dates['all_time'][0] && $to_date == $dates['all_time'][1]) {
			$from_date = $dates['all_time'][0]; //date('Y-m-01');
			$to_date = $dates['all_time'][1]; //date("Y-m-t");
			//$data['all_time_from'] = TRUE;
		} else {
			$from_date = date('Y-m-01');
			$to_date = date('Y-m-t');
		}
		$data['dates'] = $dates;
		$this->load->model('mdl_invoice_status');
		$completed_status = (int)element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'completed' => 1]), 0);

		$amountNotPaid = $this->mdl_reports->sum_amount_services($status, $from_date, $to_date, ['invoices.in_status !=' => $completed_status]);
		$amount = $this->mdl_reports->sum_amount_services($status, $from_date, $to_date, ['invoices.in_status' => $completed_status]);

		$data['revenue_total_invoices'] = round($amount['service_price'] + $amountNotPaid['service_price'], 2);

		
		$data['invoices_statuses'] = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);
		$data['invoice_by_statuses'] = $this->mdl_reports->invoices_statistic2($status, $from_date, $to_date);

		//$data['invoices'] = json_decode(json_encode($this->mdl_invoices->invoices()), TRUE);
		
		$startDate = strtotime($this->mdl_invoices->getStartInvoicesReportDate());
		$endDate = strtotime(date('Y-m-d'));
		$currDate = $startDate;

		$data['from_date'] = date(getDateFormat(), strtotime($from_date));
		$data['to_date'] = date(getDateFormat(), strtotime($to_date));
		while($currDate < $endDate) {

			$payrolls = $this->mdl_payroll->get_many_by(['payroll_start_date >=' => date('Y-m-d', $currDate), 'payroll_start_date <=' => date('Y-m-d', strtotime(date('Y', $currDate) . '-12-31'))]);

			$payrollNum = 0;

			foreach ($payrolls as $key => $payroll) {

				$result = [];

				$endWeek = strtotime($payroll->payroll_end_date);
				$startWeek = strtotime($payroll->payroll_start_date);

				$from_date = $payroll->payroll_start_date;
				$to_date = $payroll->payroll_end_date;
				$timestamp = strtotime($to_date);
				$result['invoices'] = $this->mdl_reports->invoices_statistic($completed_status, $from_date, $to_date);
				$total = $this->mdl_reports->revenue_invoices_sum($completed_status, $from_date, $to_date);

				$result['revenue_invoices'] = round($total, 2);
				$result['payments'] = $this->mdl_clients->get_payments_sum(array('payment_date >=' => strtotime($from_date), 'payment_date <=' => strtotime($to_date))); 
				$result['invoices_year'] = date('Y', $timestamp);
				$result['invoices_date_start'] = $payroll->payroll_start_date;
				$result['invoices_date_end'] = $payroll->payroll_end_date;

				$data['report'][date('Y', $currDate)][$payrollNum++] = $result;

			}

			$currDate = strtotime(date('Y', $currDate) . '-12-31') + 86400;

		}

		$data['payrollsDates'] = $this->mdl_payroll->get_many_by(['payroll_start_date >=' => date('Y') . '-01-01', 'payroll_start_date <=' => date('Y-m-d', strtotime(date('Y') . '-12-31'))]);

		$this->load->view("invoices", $data);
	}//End of invoices. 
	public function new_invoices_report($fromDate = NULL, $toDate = NULL)
	{

		$data['title'] = $this->_title . ' - Reports - Invoices';
		$this->load->model('mdl_reports');
		$this->load->model('mdl_payroll');
		$status = "";
		$data['all_time'] = FALSE;
		$data['all_time_from'] = $this->mdl_invoices->getStartInvoicesReportDate();
		$data['all_time_to'] = $this->mdl_invoices->getEndInvoicesReportDate();
		if($fromDate) {
			$from_date = $fromDate;
			$to_date = $toDate;
		} elseif($fromDate == $data['all_time_from'] && $to_date == $data['all_time_to']) {
			$from_date = $data['all_time_from']; //date('Y-m-01');
			$to_date = $data['all_time_to']; //date("Y-m-t");
			$data['all_time_from'] = TRUE;
		} else {
			$from_date = $fromDate ? $fromDate : date('Y-m-01');
			$to_date = $toDate ? $toDate : date("Y-m-t");
		}
		$this->load->model('mdl_invoice_status');
		
		
		$data['from_date'] = $from_date;
		$data['to_date'] = $to_date;
		
		$data['invoices_statuses'] = $this->mdl_invoice_status->get_many_by(['invoice_status_active' => 1]);
		$data['total']['sales'] = $data['total']['quantity'] = 0;
		foreach($data['invoices_statuses'] as $key=>$val)
		{
			$data['invoice_by_statuses'][$val->invoice_status_id] = $this->mdl_invoices->get_new_invoices_stat(['invoices.id IS NOT NULL' => NULL, 'invoices.in_status' => $val->invoice_status_id, 'invoices.date_created >=' => $from_date, 'invoices.date_created <=' => $to_date]);
			$data['total']['sales'] += $data['invoice_by_statuses'][$val->invoice_status_id]['sales'];
			$data['total']['quantity'] += $data['invoice_by_statuses'][$val->invoice_status_id]['quantity'];;
		}
		$this->load->view("new_invoices", $data);
	}//End of invoices. 
	
	/*********************************************END INVOICES**************************************************/
	
	/*********************************************EQUIPMENTS**************************************************/
	
	function distance_report()
	{
		$data['title'] = $this->_title . ' | Distance Report';
		$items = $this->mdl_equipments->get_items("item_tracker_name IS NOT NULL AND item_tracker_name != ''", 'item_name');
		$data['from'] = $where['egtd_date >='] = date('Y-m-01');
		$data['to'] = $where['egtd_date <='] = date('Y-m-t');
		if($this->input->post('from'))
			$data['from'] = $where['egtd_date >='] = $this->input->post('from');
		if($this->input->post('to'))
			$data['to'] = $where['egtd_date <='] = $this->input->post('to');
		
		foreach($items as $k=>$v)
		{
			$where['item_id'] = $v->item_id;
			$data['distance'][$k] = (array) $this->mdl_equipments->get_summ_gps_distance($where, 'sum');
			$date = (array) $this->mdl_equipments->get_summ_gps_distance(array('egtd_date <' => $data['from'], 'item_id' => $where['item_id']), 'sum');
			$data['distance'][$k]['start_counter'] = $v->item_gps_start_counter + $date['count'];
			$data['distance'][$k]['item_name'] = $v->item_name;
			$data['distance'][$k]['item_id'] = $v->item_id;
		}
		
		$this->load->view('index_distance_report', $data);
	}
	
	function get_gps_report()
	{
		$info['title'] = $this->_title . ' | GPS Data';
		//$eqs = $this->db->query("SELECT * FROM `equipment_items` WHERE item_code LIKE '%VHC%'")->result_array();
		$eqs = \application\modules\equipment\models\Equipment::query()
			->whereNotNull('eq_gps_id')
			->where('eq_gps_id', '!=', '')
			->get();
		$info['data'] = [];
		$wdata['eq_td_date >='] = date('Y-m-01');
		$wdata['eq_td_date <='] = date('Y-m-t');
		$info['from'] = date('Y-m-01 00:00:00');
		 
		$info['to'] = date('Y-m-t 23:59:59'); 
		
		if($this->input->post('from'))
		{
			$wdata['eq_td_date >='] = $this->input->post('from');
			$info['from'] = $wdata['eq_td_date >='] . " 00:00:00" ; 
		}
		if ($this->input->post('to'))
		{
			$wdata['eq_td_date <='] =  $this->input->post('to');
			$info['to'] = $wdata['eq_td_date <='] . " 23:59:59"; 
		}
		 
		foreach($eqs as $key=>$val)
		{
			$wdata['eq_td_code'] = $val->eq_code;
			$obj = $this->mdl_tracker->get_many_by($wdata);
			$result = [];
			 
			if(!empty($obj))
			{
				 
				$date = date('Y-m-d');
				$data = [];
				$data['distance'] = $data['time'] = 0;
				$dates = [];
				
				foreach($obj as $k=>$v)
				{
					$result = json_decode($v->eq_td_data);
					 
					if(isset($result->parkData) && isset($result->parkData->data) && !empty($result->parkData->data))
					{
						
						$reversePark = array_reverse($result->parkData->data);
						$distanceResponse = $result->distanceData;
						
						$date = date('Y-m-d', strtotime($reversePark[0][1]));
						foreach($reversePark as $a=>$b)
						{
							$dates[$date]['time'] = isset($dates[$date]['time']) ? $dates[$date]['time'] : 0;
							if(isset($reversePark[$a+1]))
								$dates[$date]['time'] += strtotime($b[1]) - strtotime($reversePark[$a+1][2]);
						}
						if($distanceResponse && !empty($distanceResponse))
						{
                            //$dates['kms'] = 0;
							$dates[$date]['kms'] = isset($dates[$date]['kms']) ? $dates[$date]['kms'] : 0;
							foreach($distanceResponse->data as $a=>$b)
								$dates[$date]['kms'] += $b[6];
						}
						
					}
					
				} 
				$data['days'] = count($obj);//countOk
				$data['name'] = $val->eq_name;
				foreach($dates as $ke=>$va)
				{ 
					
					$data['distance'] += round($va['kms'], 2);
					$data['time'] = $data['time'] + $va['time'];
				 
				}
				
				$info['data'][$val->eq_code] = $data;
				$info['data'][$val->eq_code]['schedule_days'] = 0;

                $schDays = $this->db->query("SELECT COUNT(DISTINCT(equipment_team_id)) as schedule_days FROM `schedule_teams_equipment` JOIN equipment ON equipment_id = eq_id JOIN schedule_teams ON equipment_team_id = team_id WHERE team_date >= UNIX_TIMESTAMP('" . $info['from'] . "') AND team_date <= UNIX_TIMESTAMP('" . $info['to'] . "') AND eq_code = '" . $val->eq_code . "' GROUP BY equipment_id ORDER BY schedule_days DESC")->row_array();
				if($schDays && !empty($schDays))
					$info['data'][$val->eq_code]['schedule_days'] = $schDays['schedule_days'];
				
			}
		}
		
		ksort($info['data']);
		$this->load->view('index_gps_report', $info);
	}
	/*********************************************END EQUIPMENTS**************************************************/
	
	
	/*********************************************SCHEDULE**************************************************/
	function schedule_report() { 
		$data['title'] = 'Schedule Report';
		$startDate = strtotime('2015-01-01');
		$endDate = strtotime(date('Y-m-d'));

		$data['report'] = [];

		$currDate = $startDate;

		while($currDate < $endDate) {

			$payrolls = $this->mdl_payroll->get_many_by(['payroll_start_date >=' => date('Y-m-d', $currDate), 'payroll_start_date <=' => date('Y-m-d', strtotime(date('Y', $currDate) . '-12-31'))]);

			$payrollNum = 0;

			foreach ($payrolls as $key => $payroll) {
				$endWeek = strtotime($payroll->payroll_end_date) + 86400;
				$startWeek = strtotime($payroll->payroll_start_date);
				$twoWeekTeams = $this->mdl_schedule->get_teams(array('team_date >=' => $startWeek, 'team_date <' => $endWeek));
				$twoWeekClosedTeams = $this->mdl_schedule->get_teams(array('team_date >=' => $startWeek, 'team_date <' => $endWeek, 'team_closed' => 1));
				$result['two_weekly_amount'] = 0;
				$result['two_weekly_hours'] = 0;
				$result['two_weekly_sum'] = 0;

				$result['two_weekly_closed_amount'] = 0;
				$result['two_weekly_closed_hours'] = 0;
				$result['two_weekly_closed_sum'] = 0;

				foreach($twoWeekTeams as $jkey => $val)
				{
					$result['two_weekly_amount'] += $val->team_amount;
					if($val->team_closed)
						$result['two_weekly_closed_amount'] += $val->team_amount;
					if($val->team_crew_id)
					{
						$team_members = $this->mdl_schedule->get_team_members(array('schedule_teams.team_id' => $val->team_id));
						
						foreach($team_members as $member)
						{
							$worked = $this->mdl_worked->get_by(['worked_user_id' => $member['employee_id'], 'worked_date' => date('Y-m-d', $val->team_date)]);
							if($worked)
							{
								$result['two_weekly_hours'] += $worked->worked_hours;
								if($val->team_closed)
									$result['two_weekly_closed_hours'] += $worked->worked_hours;
							}
						}
					}
				}
				if($result['two_weekly_hours'] != 0)
					$result['two_weekly_sum'] = round($result['two_weekly_amount'] / $result['two_weekly_hours'], 2);
				if($result['two_weekly_closed_hours'] != 0)
					$result['two_weekly_closed_sum'] = round($result['two_weekly_closed_amount'] / $result['two_weekly_closed_hours'], 2);
				
				$data['report'][date('Y', $currDate)][$payrollNum++] = $result;
			}
			$currDate = strtotime(date('Y', $currDate) . '-12-31') + 86400;
		}

		$data['payrollsDates'] = $this->mdl_payroll->get_many_by(['payroll_start_date >=' => date('Y') . '-01-01', 'payroll_start_date <=' => date('Y-m-d', strtotime(date('Y') . '-12-31'))]);
		
		$this->load->view('schedule_report', $data);
	}
	/*********************************************END SCHEDULE**************************************************/
	
	/*********************************************ACCOUNTING**************************************************/
	function general()
	{
		$data['title'] = $this->_title . ' - Income - Calculation';

		$wdataLeads['lead_date_created >='] = date('Y-m-01');
		$wdataLeads['lead_date_created <='] = date('Y-m-t');
		$data['from'] = date('Y-m-01 00:00:00');
		$wdata['expense_date >='] = strtotime($data['from']);
		$data['to'] = date('Y-m-t 23:59:59');
		$wdata['expense_date <='] = strtotime($data['to']);
		
		if ($this->input->post('from'))
		{
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$wdataLeads['lead_date_created >='] = $from->format('Y-m-d');
			$data['from'] = $wdataLeads['lead_date_created >='] . " 00:00:00" ;
			$wdata['expense_date >='] = strtotime($data['from']);
		}
		if ($this->input->post('to'))
		{
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$wdataLeads['lead_date_created <='] =  $to->format('Y-m-d');
			$data['to'] = $wdataLeads['lead_date_created <='] . " 23:59:59";
			$wdata['expense_date <='] = strtotime($data['to']);
		}
		
		$data['calls_stat'] = $this->mdl_clients->stat_calls(array('client_note_date >=' => $data['from'], 'client_note_date <=' => $data['to']));
		$data['payments_stat'] = $this->mdl_clients->stat_payments(array('payment_date >=' => $wdata['expense_date >='], 'payment_date <=' => $wdata['expense_date <='])); 
		$data['clients_stat'] = $this->mdl_clients->stat_clients(array('client_date_created >=' => $data['from'], 'client_date_created <=' => $data['to'] ));
		
		$data['estimates_stat'] = $this->mdl_estimates->stat_estimates(array('date_created >=' => $wdata['expense_date >='], 'date_created <=' => $wdata['expense_date <=']));
		$data['invoices_like'] = $this->mdl_invoices->record_count(array(), array('invoice_like' => 1, 'date_created >=' => $data['from'], 'date_created <=' => $data['to']));
		
		$data['invoices_dislike'] = $this->mdl_invoices->record_count(array(), array('invoice_like' => 0, 'date_created >=' => $data['from'], 'date_created <=' => $data['to']));
		$data['avarage_payments_day'] = $this->mdl_clients->stat_payments_by_day(array('payment_date >=' => $wdata['expense_date >='], 'payment_date <=' => $wdata['expense_date <=']));
		$data['avarage_payments_month'] = $this->mdl_clients->stat_payments_by_month(array('payment_date >=' => $wdata['expense_date >='], 'payment_date <=' => $wdata['expense_date <=']));
		
		$data['received_payments'] = $this->mdl_clients->payment_made(array('payment_date >' => $wdata['expense_date >='], 'payment_date <' => $wdata['expense_date <='], 'payment_checked' => 1));
		
		
		$data['com'] = $this->mdl_reports->com_overflow(array('estimates.date_created >' => $wdata['expense_date >='], 'estimates.date_created <' => $wdata['expense_date <=']));
		
		$estimate_ids = $this->mdl_estimates->get_estimate_total_sum($data);
		$payDay = $this->mdl_payroll->get_many_by(array('payroll_day >=' => $data['from'], 'payroll_day <=' => $data['to']));
		$payrollSum[0]['expense_amount'] = 0;
		$payrollSum[0]['expense_hst_amount'] = 0;
		if($payDay)
		{
			foreach($payDay as $key=>$val)
				$payrollSum[0]['expense_amount'] += $this->mdl_reports->get_payroll_sum(array('worked_payroll_id' => $val->payroll_id))['sum'];
		}

		$expenses = $this->mdl_reports->get_expenses($wdata);//get payroll expenses
		$expenses = array_merge($expenses, $payrollSum);

		$this->load->model('mdl_invoice_status');
		$completed_status = (int)element('invoice_status_id', (array)$this->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'completed' => 1]), 0);

		$invoices = $this->mdl_invoices->find_all(array('date_created >=' => date('Y-m-d', $wdata['expense_date >=']), 'date_created <=' => date('Y-m-d', $wdata['expense_date <=']), 'in_status' => $completed_status));
		
		$data['total_sum_expenses'] = null;
		$data['total_hst_expenses'] = null;
		$data['total_sum_invoices_paid'] = null;
		$data['total_hst_invoices_paid'] = null;
		$data['total_sum_invoices'] = null;
		$data['total_hst_invoices'] = null;

		foreach($estimate_ids as $key=>$val)
		{
			$invoice = $this->mdl_estimates->estimate_sum_and_hst($val['estimate_id']);
			$data['total_sum_invoices_paid'] += $invoice['total'];
			$data['total_hst_invoices_paid'] += $invoice['hst'];
		}

		foreach($invoices as $key=>$val)
		{
			$invoice = $this->mdl_estimates->estimate_sum_and_hst($val->estimate_id);
			$data['total_sum_invoices'] += $invoice['total'];
			$data['total_hst_invoices'] += $invoice['hst'];
		}
		
		foreach($expenses as $key=>$val)
		{
			$data['total_sum_expenses'] += $val['expense_amount'];
			$data['total_hst_expenses'] += $val['expense_hst_amount'];
		}
		
		$data['total_sum_invoices_paid'] = round($data['total_sum_invoices_paid'], 2);
		$data['total_hst_invoices_paid'] = round($data['total_hst_invoices_paid'], 2);
		$data['total_sum_invoices'] = round($data['total_sum_invoices'], 2);
		$data['total_hst_invoices'] = round($data['total_hst_invoices'], 2);
		$data['total_sum_expenses'] = round($data['total_sum_expenses'], 2);
		$data['total_hst_expenses'] = round($data['total_hst_expenses'], 2);
		$blocks = $this->config->item('leads_services');
		foreach ($blocks as $block) {
			$services = [];
			foreach ($block['list'] as $key => $item) {
				$services[] = $item['name'] . " = 'yes'";
			}
			$data['blocks'][$block['name']] = $this->mdl_leads->get_leads_by_services($wdataLeads, $services, TRUE);
		}
		$data['defaultTax'] = getDefaultTax();
		$this->load->view("index_income_calc", $data);
	}//End Income Calculation
	
	function client_payments()
	{
		if ($this->session->userdata('user_type') != "admin") {
			show_404();
		}
		
		$config = array();
		$config['uri_segment'] = 3;
		$config["base_url"] = base_url() . "business_intelligence/client_payments/";
		//To test 2013-05-01
		$wdata = array();
		$data['page'] = $page = $this->uri->segment(3) ? $this->uri->segment(3) : 1;
		$data['pdf'] = FALSE;
		if($this->uri->segment(3) && $this->uri->segment(4))
		{
			$data['from'] = $this->uri->segment(3);
			$data['to'] = $this->uri->segment(4);

			if(stristr(getDateFormat(), '/')) {
				$data['from'] = str_replace('-', '/', $data['from']);
				$data['to'] = str_replace('-', '/', $data['to']);
			}
			$from = DateTime::createFromFormat(getDateFormat(), $data['from']);
			$to = DateTime::createFromFormat(getDateFormat(), $data['to']);
			$data['from'] = $from->format("Y-m-d");
			$data['to'] = $to->format("Y-m-d");
			$config["base_url"] = base_url() . "business_intelligence/client_payments/" . $this->uri->segment(3) . "/" . $this->uri->segment(4);
			$config['uri_segment'] = 5;
			$data['page'] = $page = $this->uri->segment(5) ? $this->uri->segment(5) : 1;
			if($this->uri->segment(5))
			{				
				if(strpos($this->uri->segment(5), 'am') !== FALSE)
				{
					$config['uri_segment'] = 6;
					$data['page'] = $page = $this->uri->segment(6) ? $this->uri->segment(6) : 1;
					
					if($this->uri->segment(5))
						$data['amount'] = $where['payment_amount'] = $wdata['payment_amount'] = str_replace([',', 'am'], ['.', ''], $this->uri->segment(5));
				}
				else
				{
					$config['uri_segment'] = 5;
					$data['page'] = $page = $this->uri->segment(5) ? $this->uri->segment(5) : 1;
					
					if($this->uri->segment(6) && strpos($this->uri->segment(6), 'am') !== FALSE)
						$data['amount'] = $where['payment_amount'] = $wdata['payment_amount'] = str_replace([',', 'am'], ['.', ''], $this->uri->segment(5));
				}
			}
			$where['payment_date >'] = strtotime($data['from']);
			$where['payment_date <'] = strtotime($data['to'] . ' 23:59:59');
			$wdata['payment_date >='] = $where['payment_date >'];
			$wdata['payment_date <='] = $where['payment_date <'];
			$data['from'] .= ' 00:00:00';
			$data['to'] .= ' 23:59:59';
		}
		
		if(empty($wdata))
		{
			$wdata['payment_date >='] = strtotime(date('Y-m-d ' . '00:00:00', (time() - 86400 * 7)));
			$wdata['payment_date <='] = strtotime(date('Y-m-d 23:59:59'));
		}
		//
		$data['title'] = "Client Payments"; 
		$total_rows = $this->mdl_clients->count_payments($wdata);

		$config["total_rows"] = $total_rows;
		$config["per_page"] = 200;
		
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';
		$config['use_page_numbers'] = TRUE;
		$this->pagination->initialize($config);
			
		$start = $page - 1;
		$start = $start * $config["per_page"];
		$limit = $config["per_page"];
		
		$data['client_payments'] = $this->mdl_clients->get_payments($wdata, $limit, $start, 'client_payments.payment_id DESC'); //Get client payments
		
		$data['payment_account'] = $this->mdl_clients->payment_account();
		$where['payment_checked'] = 1;
		
		$data['filter'] = $this->mdl_clients->payment_made($where);
		$data["links"] = $this->pagination->create_links();
		$data['access_token'] = $this->mdl_settings_orm->get_by('stt_key_name', 'accessTokenKey')->stt_key_value;
		$this->load->view('index_payments', $data);
	}
	
	function ajax_change_payments()
	{
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
	
	/*********************************************END ACCOUNTING**************************************************/
	/*********************************************PERSONNEL**************************************************/
	function users_statistics()
	{

		$data['title'] = "Users Statistic";
		$where['call_date >='] = date('Y-m-01') . ' 00:00:00';
		$where['call_date <='] = date('Y-m-t') . ' 23:59:59';
		
		if ($this->input->post('from')){
//			$where['call_date >=']  = $this->input->post('from') . ' 00:00:00';
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$where['call_date >='] = $from->format('Y-m-d') . " 00:00:00" ;
		}
		if ($this->input->post('to')){
//			$where['call_date <='] = $this->input->post('to') . ' 23:59:59';
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$where['call_date <='] = $to->format('Y-m-d')  . " 23:59:59";
		}

		$data['from'] = $where['call_date >='];
		$data['to'] = $where['call_date <='];

		$data['calls'] = [];
		if(config_item('phone'))
			$data['calls'] = $this->mdl_calls->get_calls_for_stats($where);
		$where = array();
		$where['lead_date_created >='] = $data['from'];
		$where['lead_date_created <='] = $data['to'];
		$data['leads'] = $this->mdl_leads->get_count_leads($where);
		
		$where = array();
		$where["FROM_UNIXTIME(date_created) >="] = $data['from'];
		$where["FROM_UNIXTIME(date_created) <="] = $data['to'];
		$data['estimates'] = $this->mdl_estimates->get_count_estimates($where);
		
		$where = array();
		$where["date_created >="] = $data['from'];
		$where["date_created <="] = $data['to'];
		$data['workorders'] = $this->mdl_workorders->get_count_workorders($where);
		
		$where = array();
		$where["date_created >="] = $data['from'];
		$where["date_created <="] = $data['to'];
		$data['invoices'] = $this->mdl_invoices->get_invoices_stat($where);
		
		$where = array();
		$where["client_note_date >="] = $data['from'];
		$where["client_note_date <="] = $data['to'];
		$data['letters'] = $this->mdl_clients->get_count_letters_to_stats($where);
		
		$where = array();
		$where["client_date_created >="] = $data['from'];
		$where["client_date_created <="] = $data['to'];
		$data['clients'] = $this->mdl_clients->get_count_clients_to_stats($where);
		
		$where = array();
		$where["log_date >="] = $data['from'];
		$where["log_date <="] = $data['to'];
		$data['refs'] = $this->mdl_history_log->get_count_links_to_stats($where);
		
		$this->load->view('users_stats', $data);
	}
	
	public function history($user_id = 'all', $page = 0)
	{
		if(!isAdmin())
			show_404();
		$where = NULL;
		if (intval($user_id) || $user_id === '0') {
			$where = array('log_user_id' => intval($user_id));
			$user = $user_id;
		}
		$data['title'] = $this->_title . " - User Activity Hisory";
		$data['page_title'] = "User Management";
		$data['page'] = "business_intelligence/index";
		$this->load->library('pagination');
		$config['uri_segment'] = 4;
		$config['base_url'] = base_url('business_intelligence/history/' . $user_id);
		$config['per_page'] = 100;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';
		$config['total_rows'] = $this->mdl_user->get_users_activity_history($where, FALSE, FALSE, TRUE);
		$this->pagination->initialize($config);
		$data['logs'] = $this->mdl_user->get_users_activity_history($where, $config['per_page'], $page);
		$data['users'] = $this->mdl_user->get_user()->result_array();
		$data['pagging'] = $this->pagination->create_links();
		$data['currentuser'] = $user_id;
		$this->load->view('activity_view_history', $data);
	}
	
	public function activity($user_id = 'all', $page = 0)
	{ 
		$where = NULL;
		if (intval($user_id) || $user_id === '0') {
			$where = array('log_user_id' => intval($user_id));
			$user = $user_id;
		}
		$data['title'] = $this->_title . " - User Login Activity";
		$data['page_title'] = "User Management";
		$data['page'] = "user/index";
		$this->load->library('pagination');
		$config['uri_segment'] = 4;
		$config['base_url'] = base_url('business_intelligence/activity/' . $user_id);
		$config['per_page'] = 100;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';
		$config['total_rows'] = $this->mdl_user->get_users_activity($where, FALSE, FALSE, TRUE);
		$this->pagination->initialize($config);
		$data['logs'] = $this->mdl_user->get_users_activity($where, $config['per_page'], $page);
		$data['users'] = $this->mdl_user->get_user()->result_array();
		$data['pagging'] = $this->pagination->create_links();
		$data['currentuser'] = $user_id;
		$this->load->view('activity_view', $data);
	}
	
	function payroll_statistics()
	{ 
		$startDate = strtotime('2016-01-01');
		$endDate = strtotime(date('Y-m-d'));
		
		$data['title'] = 'Support vs Field Payrol Report';
		$data['report'] = [];

		$currDate = $startDate;
		while($currDate < $endDate) {
			$payrolls = $this->mdl_payroll->get_many_by(['payroll_start_date >=' => date('Y-m-d', $currDate), 'payroll_start_date <=' => date('Y-m-d', strtotime(date('Y', $currDate) . '-12-31'))]);

			$payrollNum = 0;
			foreach($payrolls as $key => $payroll)
			{
				
				$where = [
						'worked_date >= ' => $payroll->payroll_start_date,
						'worked_date <= ' => $payroll->payroll_end_date,
						'worked_payroll_id' => $payroll->payroll_id,
						'active_status' => 'yes',
						'worker_type' => 1
				];
				$workerStat = $this->mdl_worked->get_worked_hours_worker_stats($where);
				$where['worker_type'] = 2;
				$supportStat = $this->mdl_worked->get_worked_hours_worker_stats($where);
				
				$result['two_weekly_support'] = 0;
				$result['two_weekly_worker'] = 0;
				
				if($workerStat && !empty($workerStat))
				{
					foreach($workerStat as $k=>$v)
						$result['two_weekly_worker'] += $v->worked_total_pay;
				}
				if($supportStat && !empty($supportStat))
				{
					foreach($supportStat as $k=>$v)
						$result['two_weekly_support'] += $v->worked_total_pay;
				}
				
				$data['report'][date('Y', $currDate)][$payrollNum++] = $result;
			}
			$currDate = strtotime(date('Y', $currDate) . '-12-31') + 86400;
		}
		$data['payrollsDates'] = $this->mdl_payroll->get_many_by(['payroll_start_date >=' => date('Y') . '-01-01', 'payroll_start_date <=' => date('Y-m-d', strtotime(date('Y') . '-12-31'))]);

		$this->load->view('worker_report', $data);
	}
	function crews_statistic()
	{
		$this->load->model('mdl_schedule');
		$data['title'] = $this->_title . ' - Crews Statistic'; 
		
		$data['to'] = date('Y-m-t 23:59:59');
		$data['from'] = date('Y-m-01 00:00:00');
		$where['schedule.event_start >=']  = strtotime($data['from']);
		$where['schedule.event_end <='] = strtotime($data['to']);
		
		if ($this->input->post('from'))
		{
//			$data['from'] = $this->input->post('from') . " 00:00:00" ;
//			$where['schedule.event_start >='] = strtotime($data['from']);
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$data['from'] = $from->format('Y-m-d') . " 00:00:00";
			$where['schedule.event_start >='] = strtotime($data['from']);
		}
		if ($this->input->post('to'))
		{
//			$data['to'] = $this->input->post('to')  . " 23:59:59";
//			$where['schedule.event_end <='] = strtotime($data['to']);
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$data['to'] =$to->format('Y-m-d')  . " 23:59:59";
			$where['schedule.event_end <='] = strtotime($data['to']);
		}
		$data['data'] = $this->mdl_schedule->crews_avg_statistic($where);
		$this->load->view('index_crews_stat', $data);
	}
	
	function absent_days()
	{
		if($this->session->userdata('user_type') != "admin" && !$this->session->userdata("UHR"))
			show_404();
		$this->load->model('mdl_absence');
		$this->load->model('mdl_reasons');
		$data['title'] = $this->_title . ' - HR';
		$users = $this->mdl_user->get_payroll_user(array('user_active_employee' => 1, 'emp_status' => 'current', 'emp_no_dayoff <>' => 1), 'emp_status ASC, emp_name ASC');
		$data['from'] = date('Y-m-01');
		$data['to'] = date('Y-m-t');
		
		if($this->input->post('from')) {
			$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('from'));
			$data['from'] = $from->format('Y-m-d');
			$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('to'));
			$data['to'] =$to->format('Y-m-d');
		}
			
		$wdata['absence_ymd >='] = $data['from'];
		$wdata['absence_ymd <='] = $data['to'];
		
		$data['users'] = $data['employees'] = [];
		if($users)
			$data['employees'] = $users = $users->result_array();
		$data['reasons'] = $reasons = $this->mdl_reasons->order_by('reason_name', 'asc')->get_many_by(array('reason_status' => 1));
		
		foreach($data['employees'] as $key=>$val)
		{
			$wdata['absence_user_id'] = $val['id'];
			$data['users'][$val['emp_name']]['id'] = $val['id'];
			$wdata['reasons_absence.reason_company'] = 0;
			$data['users'][$val['emp_name']]['total'] = $this->mdl_absence->with('mdl_reasons')->get_count_by_reason($wdata, TRUE);
			$data['users'][$val['emp_name']]['id'] = $val['id'];
			unset($wdata['reasons_absence.reason_company']);
			foreach($reasons as $k=>$v)
			{
				$wdata['absence_reason_id'] = $v->reason_id;
				$data['users'][$val['emp_name']][$v->reason_name]['count'] = $this->mdl_absence->get_count_by_reason($wdata, TRUE);
				$data['users'][$val['emp_name']][$v->reason_name]['id'] = $v->reason_id;
			}
			unset($wdata['absence_reason_id']);
		}
		
		$this->load->view('index_hr', $data);
	}
	
	function ajax_save_absence()
	{
		if($this->session->userdata('user_type') != "admin" && !$this->session->userdata("UHR"))
			show_404();
		$this->load->model('mdl_absence');
		$this->load->model('mdl_user');
		$data['absence_user_id'] = $this->input->post('employee');
		$data['absence_reason_id'] = $this->input->post('reason');
		$from = DateTime::createFromFormat(getDateFormat(), $this->input->post('dayofffrom'));
		$to = DateTime::createFromFormat(getDateFormat(), $this->input->post('dayoffto'));
		$sms = $this->input->post('sms') ? TRUE : FALSE;
		$text = $this->input->post('sms_text') ? $this->input->post('sms_text') : FALSE;

		$diff = date_diff($to, $from)->days;

		$from = $from->format('Y-m-d');
		$to = $to->format('Y-m-d');

		$hasTeam = ScheduleTeamsMember::where('user_id', $data['absence_user_id'])->datesInterval($from, $to)->count();
		if($hasTeam)
			return $this->response(['status'=>'error', 'msg'=>'Member scheduled on team in this dates']);

		$data['absence_ymd'] = $from;
		$row = $this->mdl_absence->get_by(array('absence_user_id' => $data['absence_user_id'], 'absence_ymd' => $from));

		/*----------- office tasks -----------*/
		$this->load->model('mdl_client_tasks', 'mdl_client_tasks');
		$this->mdl_client_tasks->add_absence_task($this->input->post('employee'), $this->input->post('dayofffrom'), $this->input->post('dayoffto'));
		/*----------- office tasks -----------*/

		if(!$data['absence_user_id'] || !$data['absence_reason_id'])
		{
			$msg = (!$data['absence_user_id']) ? 'User not selected' : '';
			$msg .= (!$data['absence_reason_id']) ? '<br> Reason not selected' : '';
			die(json_encode(array('status' => 'error', 'msg' =>  $msg)));
		}
		$count = 0;
		if(empty($row) && date('N', strtotime($from)) != 6 && date('N', strtotime($from)) != 7)
		{
			$count = 1;
			$this->mdl_absence->insert($data);
		}

		if($sms)
		{
			$this->load->model('mdl_user');
			$emp = $this->mdl_user->get_payroll_user(['users.id' => $data['absence_user_id']]);
			$userdata = ($emp->num_rows()) ? $emp->row() : false;
			if($userdata && $userdata->emp_phone && $userdata->emp_phone != '')
			{
				$text = str_replace('[NAME]', $userdata->emp_name, $text);
				$text = str_replace('[FROM]', $from, $text);
				$text = str_replace('[TO]', $to, $text);

				$this->load->driver('messages');
				$this->messages->send(substr($userdata->emp_phone, 0, config_item('phone_clean_length')), $text);
			}
		}
		if(!$diff)
			die(json_encode(array('status' => 'ok', 'count' => $count, 'user_id' => $data['absence_user_id'], 'reason' => $data['absence_reason_id'])));
		for($i=1; $i<=$diff; $i++)
		{
			$data['absence_ymd'] = date('Y-m-d', strtotime($data['absence_ymd'] . "+1 days"));
			$row = $this->mdl_absence->get_by(array('absence_user_id' => $data['absence_user_id'], 'absence_ymd' => $data['absence_ymd']));
			if(!$row && date('N', strtotime($data['absence_ymd'])) != 6 && date('N', strtotime($data['absence_ymd'])) != 7) {
				$count++;
				$this->mdl_absence->insert($data);
			}
		}
		
		die(json_encode(array('status' => 'ok', 'count' => $count, 'user_id' => $data['absence_user_id'], 'reason' => $data['absence_reason_id'])));
	}
	
	function ajax_get_absence()
	{
		if($this->session->userdata('user_type') != "admin" && !$this->session->userdata("UHR"))
			show_404();
		$this->load->model('mdl_absence');
		$data['absence_user_id'] = $this->input->post('user');
		$data['absence_reason_id'] = $this->input->post('reason');
		$data['absence_ymd >='] = json_decode($this->input->post('from'));
		$data['absence_ymd <='] = json_decode($this->input->post('to'));
		$rows['rows'] = $this->mdl_absence->with('mdl_reasons')->get_many_by($data);
		$result['html'] = $this->load->view('table_absence', $rows, TRUE);
		$result['status'] = 'ok';
		die(json_encode($result));
	}
	
	function ajax_delete_absence()
	{
		$result['status'] = 'error';

		$request = request();
		if($this->session->userdata('user_type') != "admin" && !$this->session->userdata("UHR"))
			show_404();

		$task = Task::where(['task_assigned_user'=>$request->input('user'), 'task_date'=>$request->input('date'), 'task_category'=>-1])->first();
		if($task)
			$task->delete();

		$absence = ScheduleAbsence::where(['absence_user_id'=>$request->input('user'), 'absence_reason_id'=>$request->input('reason')])
			->whereDate('absence_ymd', '=', $request->input('date'))->delete();

		if($absence)
			$result['status'] = 'ok';

		return $this->response($result);
	}
	
	/*********************************************END PERSONNEL**************************************************/

	function incidents($page = 1) {
		if(!$page || intval($page) < 0)
			$page = 1;

		$data['title'] = 'Near Miss / Incidents';
		$this->load->model('mdl_incidents');
		$this->load->library('pagination');

		$config['base_url'] = base_url('business_intelligence/incidents');
		$config['total_rows'] = $this->mdl_incidents->count_all();
		$config['per_page'] = 50;

		$config['use_page_numbers'] = TRUE;
		$config['full_tag_open'] = '<ul class="pagination pagination-sm m-t-none m-b-none pull-right">';
		$config['full_tag_close'] = '</ul>';

		if(ceil($config['total_rows'] / $config['per_page']) < $page && $config['total_rows'])
			show_404();

		$this->pagination->initialize($config);

		$data['incidents'] = $this->mdl_incidents->getIncidentsList($config['per_page'], ($page * $config['per_page']) - $config['per_page']);
		$data['links'] = $this->pagination->create_links();
		$this->load->view('index_incidents', $data);
	}

	function incident($id = NULL) {
		$this->load->model('mdl_incidents');
		$this->load->library('mpdf');

		$incident = $this->mdl_incidents->getIncidentData($id);
		if(!$incident)
			show_404();

		$html = $this->load->view('incident_pdf', $incident, TRUE);
		//die($html);
		$this->mpdf->WriteHTML($html);
		$this->mpdf->Output('incident_' . $incident->inc_id . '.pdf', 'I');
	}
	
	function gps_tracking() {
		
		$data['title'] = $this->_title . ' - Tracking Map';
		$this->load->library('googlemaps');
		$this->load->model('mdl_user');
		
		$config['center'] = config_item('map_center');
		$config['zoom'] = 'auto';
		$this->googlemaps->initialize($config);
		$data['map'] = $this->googlemaps->create_map();
		$data['users'] = $this->mdl_user->find_all(array('active_status' => 'yes', 'system_user' => '0'), 'firstname');

		$this->load->view('route_map', $data);
			
	}
	
	function ajax_get_coords() {
		
		$this->load->library('googlemaps');
		$this->load->model('mdl_worked');
		$this->load->model('mdl_emp_login');
		$this->load->model('mdl_users_tracking');
		
		$user_id =  $this->input->post('user_id');
		$date = false;
		if(getDateFormat() !== NULL)
			$date = DateTime::createFromFormat(getDateFormat(), ($this->input->post('date') ?: date(getDateFormat())))->format('Y-m-d');
		$data['status'] = 'error';
		if(!$user_id || !$date)
			return false;
		$data['coords'] = [];
		
		$logins = $this->mdl_worked->with('mdl_emp_login')->get_many_by(['worked_user_id' => $user_id, 'worked_date' => $date]);
		foreach($logins as $k=>$v) {
			foreach($v->mdl_emp_login as $key=>$val) {
				$time = date('H:i:s', time());
				if($val->logout)
					$time = $val->logout;
				$coords = $this->mdl_users_tracking->find_all(['ut_user_id' => $user_id, 'ut_date >=' => $val->login_date . ' ' . $val->login, 'ut_date <=' => $val->login_date . ' '. $time], 'ut_date');
				if(count($coords)) {
					$data['coords'][] = $coords;
					$data['status'] = 'ok';
				}
			}
		}
		return $this->response($data);
	}
}
?>
