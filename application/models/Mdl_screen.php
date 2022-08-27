<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * schedule model
 * created by: Ruslan Gleba
 * created on: Nov - 2014
 */

class Mdl_schedule extends MY_Model
{

	function __construct()
	{
		parent::__construct();


		$this->table = 'schedule';
		$this->table1 = 'schedule_teams_members';
		$this->table2 = 'schedule_teams_equipment';
		$this->table3 = 'schedule_teams';
		$this->table4 = 'schedule_absence';
		$this->table5 = 'schedule_days_note';
		$this->primary_key = "schedule.id";
	}

	function insert_team_member($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->table1, $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}

	function delete_team_member($data)
	{
		if ($data) {
			//$this->db->join($this->table3, 'schedule_teams.team_id = schedule_teams_members.employee_team_id');
			$this->db->where($data);
			$this->db->delete($this->table1);
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}

	function insert_member_absence($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->table4, $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}

	function delete_member_absence($data)
	{
		if ($data) {
			$this->db->where($data);
			$this->db->delete($this->table4);
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}

	function get_absence($data)
	{
		$this->db->where($data);
		$this->db->join('employees', 'employees.employee_id = schedule_absence.absence_employee_id');
		$this->db->join('reasons_absence', 'reasons_absence.reason_id = schedule_absence.absence_reason_id');
		return $this->db->get($this->table4)->result_array();
	}

	function insert_team_item($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->table2, $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}

	function delete_team_item($data)
	{
		if ($data) {
			$this->db->where($data);
			$this->db->delete($this->table2);
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
		return FALSE;
	}

	function update_date_note($data)
	{
		$query = 'REPLACE  '. $this->table5 . " SET note_date='" . $data['note_date'] . "', note_text='" . $data['note_text'] . "'";
		$this->db->query($query);
		return TRUE;
	}

	function get_note($wdata = array())
	{
		$this->db->where($wdata);
		return $this->db->get($this->table5)->row_array();
	}

	function get_team_members($wdata, $limit = FALSE)
	{
		if(!$wdata)
			return FALSE;
		$this->db->where($wdata);
		$this->db->select('schedule_teams_members.*, schedule_teams.*, crews.*, employees.emp_name, leader.emp_name as team_leader_name');
		$this->db->join('employees', 'employees.employee_id = schedule_teams_members.employee_id');
		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_members.employee_team_id');
		$this->db->join('employees leader', 'leader.employee_id = schedule_teams.team_leader_id', 'left');
		$this->db->join('crews', 'schedule_teams.team_crew_id = crews.crew_id');
		$query = $this->db->get($this->table1);
		if($limit)
			return $query->row_array();
		return $query->result_array();
	}

	function get_team_items($wdata, $limit = FALSE)
	{
		if(!$wdata)
			return FALSE;
		$this->db->where($wdata);
		$this->db->join('equipment_items', 'equipment_items.item_id = schedule_teams_equipment.equipment_id');
		$this->db->join('equipment_groups', 'equipment_items.group_id = equipment_groups.group_id');
		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_equipment.equipment_team_id');
		$query = $this->db->get($this->table2);
		if($limit)
			return $query->row_array();
		return $query->result_array();
	}

	function get_free_members($wdata = array(), $notIn = array())
	{
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		if($notIn && !empty($notIn))
			$this->db->where_not_in('employee_id', $notIn);
		$this->db->select('employees.employee_id, employees.emp_name');
		$this->db->order_by('emp_name');
		return $this->db->get('employees')->result();
	}

	function get_free_items($wdata = array(), $notIn = array())
	{
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		if($notIn && !empty($notIn))
			$this->db->where_not_in('item_id', $notIn);
		$this->db->select('equipment_items.item_id, equipment_items.item_name, equipment_groups.group_color');
		$this->db->join('equipment_groups', 'equipment_groups.group_id = equipment_items.group_id');
		$this->db->order_by('equipment_items.group_id, equipment_items.item_id');
		return $this->db->get('equipment_items')->result();
	}

	function delete_team($team_id)
	{
		$this->db->where('team_id', $team_id);
		$this->db->delete('schedule_teams');
		$this->db->where('equipment_team_id', $team_id);
		$this->db->delete('schedule_teams_equipment');
		$this->db->where('employee_team_id', $team_id);
		$this->db->delete('schedule_teams_members');
		return TRUE;
	}

	function insert_team($data)
	{
		$teamMembers = isset($data['team_members']) && $data['team_members'] ? $data['team_members'] : array();
		$teamItems = isset($data['team_items']) && $data['team_items'] ? $data['team_items'] : array();
		unset($data['team_members'], $data['team_items']);
		$this->db->insert('schedule_teams', $data);
		$teamId = $this->db->insert_id();
		$members = $items = array();
		foreach($teamMembers as $member)
			$members[] = array('employee_team_id' => $teamId, 'employee_id' => $member);
		foreach($teamItems as $item)
			$items[] = array('equipment_team_id' => $teamId, 'equipment_id' => $item);
		if(!empty($members))
			$this->db->insert_batch('schedule_teams_members', $members);
		if(!empty($items))
			$this->db->insert_batch('schedule_teams_equipment', $items);
		return $teamId;
	}

	function update_team($team_id, $data, $wdata = array())
	{
		if($team_id)
			$this->db->where('team_id', $team_id);
		if($wdata)
			$this->db->where($wdata);
		if(!$team_id && empty($wdata))
			return FALSE;
		$this->db->update('schedule_teams', $data);
		return TRUE;
	}

	function get_teams($wdata = array(), $limit = FALSE)
	{
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->select('schedule_teams.*, crews.*, employees.emp_name');
		$this->db->join('employees', 'employees.employee_id = schedule_teams.team_leader_id', 'left');
		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');
		if($limit)
			$result = $this->db->get('schedule_teams')->row();
		else
			$result = $this->db->get('schedule_teams')->result();
		return $result;
	}

	function get_crew_items($wdata, $limit = FALSE)
	{
		if(!$wdata)
			return FALSE;
		$this->db->select('equipment_items.item_name, emp_leader.emp_name as crew_leader_name, crews.crew_leader, crews.crew_name, crews_equipment.*');
		$this->db->where($wdata);
		$this->db->join('equipment_items', 'equipment_items.item_id = crews_equipment.equipment_id');
		$this->db->join('crews', 'crews.crew_id = crews_equipment.eq_crew_id');
		$this->db->join('employees as emp_leader', 'crews.crew_leader = emp_leader.employee_id', 'left');
		$this->db->order_by('crew_id');
		$query = $this->db->get($this->table2);
		if($limit)
			return $query->row_array();
		return $query->result_array();
	}

	function get_events($wdata = array(), $limit = FALSE)
	{
		if($wdata)
			$this->db->where($wdata);
		$this->db->select('schedule.*, schedule_teams.*, crews.crew_color, crews.crew_leader, crews.crew_name, workorders.id as wo_id, workorders.workorder_no, workorders.wo_status, estimates.estimate_id, leads.lead_address, SUM(estimates_services.service_price) as total, SUM(estimates_services.service_time) as total_time');
		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');
		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id', 'left');
		$this->db->group_by('schedule.id');
		$this->db->order_by('schedule.event_start', 'DESC');
		if($limit == 1)
			$result = $this->db->get($this->table)->row_array();
		else
			$result = $this->db->get($this->table)->result_array();
		return $result;
	}

	function save_event($data, $update = TRUE)
	{
		if($update)
		{
			$this->db->where('id', $data['id']);
			$this->db->update($this->table, $data);
			return TRUE;
		}
		else
		{
			$this->db->insert($this->table, $data);
			return $this->db->insert_id();
		}
		return FALSE;
	}

	function delete_event($event_id)
	{
		$this->db->where('id', $event_id);
		$this->db->delete($this->table);
		return TRUE;
	}

}

//end of file user_model.php
