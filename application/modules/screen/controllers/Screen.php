<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Screen extends MX_Controller
{

	function __construct()
	{

		parent::__construct();

		//Checking if user is logged in;
		if (!isUserLoggedIn()) {
			if($this->input->is_ajax_request())
				die(json_encode(array('result' => 'login')));
			redirect('login');

		}
		$this->_title = SITE_NAME;
		$this->load->model('mdl_schedule', 'mdl_schedule');
		$this->load->model('mdl_crews', 'mdl_crews');
		$this->load->model('mdl_estimates', 'mdl_estimates');
	}


	public function index()
	{
		$this->load->model('mdl_workorders');
		$this->load->model('mdl_employees');
		$this->load->model('mdl_equipments');
		$this->load->model('mdl_reasons');

		$data['title'] = "Tree Doctors - Schedule";
		$data['crews'] = $this->mdl_crews->find_all(array('crew_status' => 1, 'crew_id >' => 0));
		$data['reasons'] = $this->mdl_reasons->get_many_by(array('reason_status' => 1));
		//$data['reasons'] = $this->mdl_reasons->find_all(array('reason_status' => 1));
		$data['dayOffCrew'] = $this->mdl_crews->find_by_id(0);
		$data['sections'] = $this->mdl_schedule->get_teams(array('team_date' => strtotime(date('Y-m-d'))));

		$finishedStatusId = $this->mdl_workorders->getFinishedStatusId();
		$statuses = $this->mdl_workorders->get_all_statuses(array('wo_status_id !=' => $finishedStatusId));
		
		$data['wostatuses'] = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
		
		/* ------not used -----------
		$data['workorders_tpl'] = json_encode(array('tpl' => $this->load->view('workorders_popup_tpl', $data, TRUE)));
		------not used ----------- */
		
		$data['employees'] = $this->mdl_employees->find_all(array('emp_status' => 'current', 'emp_feild_worker' => 1), 'emp_name');
		$data['equipment'] = $this->mdl_equipments->get_items(array('item_schedule' => 1, 'item_repair' => 0));
		$data['employees_tpl'] = json_encode(array('tpl' => $this->load->view('wo_employees_tpl', $data, TRUE)));
		$this->load->view("index", $data);
	}

	function data()
	{
		$data = array();

		$wdata = array();
		if($this->input->get('from') && $this->input->get('to'))
		{
			$wdata['schedule.event_start >='] = strtotime($this->input->get('from'));
			$wdata['schedule.event_end <'] = strtotime($this->input->get('to'));
		}

		$events = $this->mdl_schedule->get_events($wdata);
		$this->load->model('mdl_workorders');
		$woStatuses = $this->mdl_workorders->get_all_statuses(array('wo_status_active' => 1));
		
		$this->load->model('mdl_estimates_orm');
		$this->load->model('mdl_services_orm');
		$this->load->model('mdl_crews_orm');
		$this->load->model('mdl_equipment_orm');
		foreach($events as $key => $event)
		{
			$event['event_services'] = array();
			$eventServices = $this->mdl_schedule->get_event_services(array('event_id' => $event['id']));
			if(!empty($eventServices))
			{
				foreach($eventServices as $jkey=>$val)
					$event['event_services'][$val['event_service_id']] = $val['event_service_id'];
				$event['event_services'] = json_encode($event['event_services']);
			}
	
			$service_ids = $event['event_services'] ? json_decode($event['event_services']) : array();
			$event['total_for_services'] = 0;
			$event['total_service_time'] = 0;
			$event['total_event_time'] = 0;
			/*
			*/
			foreach($service_ids as $jkey=>$val)
			{
				$serv = $this->mdl_estimates_orm->get_full_service_data($val);
				
				if(!$serv)
					continue;
				$event['total_for_services'] += $serv->service_price;
				
				if($serv->service_time)
				{
					$event['total_event_time'] += $serv->service_time;
					$event['total_service_time'] += $serv->service_time * count($serv->crew);
				}
				if($serv->service_travel_time)
				{
					$event['total_event_time'] += $serv->service_travel_time;
					$event['total_service_time'] += $serv->service_travel_time * count($serv->crew);
				}
				if($serv->service_disposal_time)
				{
					$event['total_event_time'] += $serv->service_disposal_time;
					$event['total_service_time'] += $serv->service_disposal_time * count($serv->crew);
				}
			}
			
			
			$data[$key]['id'] = $event['id'];
			$data[$key]['section_id'] = $event['event_team_id'];
			$data[$key]['crew_id'] = $event['event_team_id'];
			$data[$key]['start_date'] = date('Y-m-d H:i:s', $event['event_start']);
			$data[$key]['end_date'] = date('Y-m-d H:i:s', $event['event_end']);
			$data[$key]['date'] = date('Y-m-d', $event['event_start']);
			$data[$key]['wo_id'] = $event['event_wo_id'];
			$data[$key]['wo_status'] = $event['wo_status'];
			$data[$key]['estimator'] = $event['emailid'];
			$data[$key]['color'] = $event['wo_status_color'] ? $event['wo_status_color'] : $event['crew_color'];
			$stickerData['event'] = $event;
			$stickerData['statuses'] = $woStatuses;
			$details = $this->load->view('event_sticker_tpl', $stickerData, TRUE);
			$data[$key]['text'] = $data[$key]['details'] = $details;
		}
		die(json_encode(array('data' => $data)));
	}

	private function scheduleResponse($action, $id)
	{
		$this->session->set_userdata(array('id' => $id, 'action' => $action));
		header('Content-type: text/xml');
		die("<?xml version='1.0' ?><data><action type='" . $action . "' sid='" . $id . "' tid='" . $id . "' ></action></data>");
	}

	function ajax_check_any_updates()
	{
		$date = strtotime($this->input->post('date'));
		$lastUpdate = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		die(json_encode(array($lastUpdate)));
	}


	function get_users_statistic(){
		
		$this->load->model('mdl_workorders');
		
		$response = [
			'data' => $this->mdl_workorders->employees_mhr_return_sctreen(),
			'status'=>'success'
		];
		return $this->output->set_output(json_encode($response));
	}

	function ajax_crews_members()
	{
		$dayOffCrew = $this->mdl_crews->find_by_id(0);
		$date = strtotime($this->input->post('date'));
		$dateYmd = $this->input->post('date');
		$absences = $this->mdl_schedule->get_absence(array('absence_date' => $date));
		$members = $this->mdl_schedule->get_team_members(array('schedule_teams.team_date' => $date));

		foreach($absences as $absence)
		{
			$members[] = array(
				'employee_id' => $absence['employee_id'],
				'team_leader_id' => 0,
				'team_id' => 0,
				'emp_name' => $absence['emp_name'],
				'emp_reason' => $absence['reason_name'],
				'crew_name' => $dayOffCrew->crew_name,
				'team_color' => $dayOffCrew->crew_color
			);
		}
		$sorted_teams = $this->mdl_schedule->getTeamsMembersWithOrder($dateYmd);
		$items = $this->mdl_schedule->get_team_items(array('team_date' => $date));
		$sections = $this->mdl_schedule->get_teams(array('team_date' => $date));
		$note = $this->mdl_schedule->get_note(array('note_date' => $date));
		$update = $this->mdl_schedule->get_update(array('update_time >= ' => $date, 'update_time <' => $date + 86400));
		if(!$update)
			$update['update_time'] = time();
		die(json_encode(array('status' => 'ok', 'update' => $update, 'members' => $members, 'items' => $items, 'sections' => $sections, 'note' => $note, 'sorted_teams' => $sorted_teams)));
	}

	function ajax_showed_sections()
	{
		$startDate = strtotime($this->input->post('start_date'));
		$endDate = strtotime($this->input->post('end_date'));
		$sections = $this->mdl_schedule->get_teams(array('team_date >=' => $startDate, 'team_date <' => $endDate));
		$note = $this->mdl_schedule->get_note(array('note_date' => strtotime(date('Y-m-d', $startDate + 1))));
		die(json_encode(array('status' => 'ok', 'sections' => $sections, 'note' => $note)));
	}

	function ajax_get_free_members()
	{
		$date = strtotime($this->input->post('date'));
		$busyMembers = $this->mdl_schedule->get_team_members(array('schedule_teams.team_date' => $date));
		$absences = $this->mdl_schedule->get_absence(array('absence_date' => $date));
		$busyM = array();
		foreach($busyMembers as $member)
			$busyM[] = $member['employee_id'];
		foreach($absences as $member)
			$busyM[] = $member['employee_id'];
		$busyItems = $this->mdl_schedule->get_team_items(array('team_date' => $date));
		$busyI = array();
		foreach($busyItems as $item)
			$busyI[] = $item['item_id'];
		$data['employees'] = $this->mdl_schedule->get_free_members(array('emp_status' => 'current', 'emp_feild_worker' => 1), $busyM);
		$data['equipment'] = $this->mdl_schedule->get_free_items(array('item_schedule' => 1, 'item_repair' => 0), $busyI);
		$result['status'] = 'ok';
		$result['membersHtml'] = $this->load->view('free_employees_label', $data, TRUE);
		$result['itemsHtml'] = $this->load->view('free_items_label', $data, TRUE);
		die(json_encode($result));
	}

	function ajax_add_equipment()
	{
		$this->load->model('mdl_crews');
		$team_id = intval($this->input->post('crew_id'));
		$item_id = intval($this->input->post('item_id'));
		$date = strtotime($this->input->post('date'));

		$this->mdl_schedule->insert_team_item(array('equipment_id' => $item_id, 'equipment_team_id' => $team_id));
		$result['status'] = 'ok';
		die(json_encode($result));
	}

	function ajax_delete_equipment()
	{
		$this->load->model('mdl_crews');
		$team_id = intval($this->input->post('crew_id'));
		$item_id = intval($this->input->post('item_id'));
		$date = strtotime($this->input->post('date'));

		$this->mdl_schedule->delete_team_item(array('equipment_id' => $item_id, 'equipment_team_id' => $team_id));
		$result['status'] = 'ok';
		die(json_encode($result));
	}

	function ajax_add_member()
	{
		$this->load->model('mdl_crews');
		$team_id = intval($this->input->post('crew_id'));
		$employee_id = intval($this->input->post('employee_id'));
		$date = strtotime($this->input->post('date'));
		$this->mdl_schedule->insert_team_member(array('employee_id' => $employee_id, 'employee_team_id' => $team_id));
		$result['status'] = 'ok';
		die(json_encode($result));
	}

	function ajax_delete_member()
	{
		$team_id = intval($this->input->post('crew_id'));
		$employee_id = intval($this->input->post('employee_id'));
		$date = strtotime($this->input->post('date'));
		$this->mdl_schedule->delete_team_member(array('employee_id' => $employee_id, 'employee_team_id' => $team_id));
		$result['status'] = 'ok';
		die(json_encode($result));
	}

	function ajax_add_member_absence()
	{
		$employee_id = intval($this->input->post('employee_id'));
		$reason_id = intval($this->input->post('reason_id'));
		$date = strtotime($this->input->post('date'));
		$this->mdl_schedule->insert_member_absence(array('absence_reason_id' => $reason_id, 'absence_employee_id' => $employee_id, 'absence_date' => $date));
		$result['status'] = 'ok';
		die(json_encode($result));
	}

	function ajax_delete_member_absence()
	{
		$employee_id = intval($this->input->post('employee_id'));
		$date = strtotime($this->input->post('date'));
		$this->mdl_schedule->delete_member_absence(array('absence_employee_id' => $employee_id, 'absence_date' => $date));
		$result['status'] = 'ok';
		die(json_encode($result));
	}
}
