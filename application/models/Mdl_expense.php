<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * estimate_services model
 * created by: Ruslan Gleba
 * created on: december - 2014
 */

class Mdl_expense extends MY_Model
{

	function __construct()
	{
		parent::__construct();
		$this->table = 'expense_types';
		$this->table1 = 'expenses';
		$this->table2 = 'expense_types_groups';
		$this->primary_key = "expense_types.expense_type_id";
	}

	function get_expenses($wdata = array(), $limit = FALSE)
	{
		$this->db->where($wdata);
		$query = $this->db->get($this->table);
		if(!$limit)
			$result = $query->result_array();
		else
			$result = $query->row_array();
		return $result;
	}

	public function update($id, $data)
	{
		$groups = array();
		if(isset($data['expense_groups']))
		{
			$groups = $data['expense_groups'];
			unset($data['expense_groups']);
		}
		if($groups && !empty($groups))
			$this->reset_expense_groups($id, $groups);
		$this->db->where($this->primary_key, $id);
		if ($this->db->update($this->table, $data))
			return TRUE;
		return FALSE;
	}

	public function insert($data)
	{
		$groups = isset($data['expense_groups']) ? $data['expense_groups'] : [];
		unset($data['expense_groups']);
		if ($this->db->insert($this->table, $data))
		{
			$id = $this->db->insert_id();
			if($groups && !empty($groups))
				$this->reset_expense_groups($id, $groups);
			return TRUE;
		}
		return FALSE;
	}

	function get_selected_groups($wdata = array())
	{
		$result = array();
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->order_by('expense_type_id');
		$this->db->join('equipment_groups', 'group_id = expense_type_group_id');
		$groups = $this->db->get($this->table2)->result_array();
		foreach($groups as $group)
			$result[$group['expense_type_id']][] = $group;
		return $result;
	}
	
	function get_sum_selected_groups($id, $wdata = array())
	{
		$groups = array();
		/******КОСТЫЛЬ ДЛЯ ПЕЙРОЛА В ЗАТРАТАХ*****/
		if($id == 0 && isset($wdata['expense_date >=']) && isset($wdata['expense_date <=']))
		{
			$this->load->model("employee/mdl_employee", "emp_login");
			$get_data = array();
			$totalPayrollSum = 0;
			$get_data["login_time >="] = date('Y-m-d H:i:s', $wdata['expense_date >=']);
			$get_data["login_time <="] = date('Y-m-d 23:59:59', $wdata['expense_date <=']);
			$emp_data = $this->emp_login->get_overview_report_biweekly($get_data);
			//var_dump($this->db->last_query()); die;
			/*
				SELECT employees.emp_name, employees.employee_id, employees.emp_type, employees.emp_hourly_rate, ROUND((((DATE_FORMAT(time_diff, '%i')/60)*100)/100), 2) + DATE_FORMAT(time_diff, '%H') AS seconds, total_pay, login_time, employee_hourly_rate, no_lunch
				FROM (`employee_login`)
				RIGHT JOIN `employees` ON `employees`.`employee_id` = `employee_login`.`employee_id`
				WHERE `login_time` >= '2015-04-01 00:00:00'
				AND `login_time` <= '2015-04-30 23:59:59'
				 ORDER BY `employees`.`employee_id` ASC 
			 * 
			 * 
			 * 
			 * */
			$emp_id = NULL;
			$date = NULL;
			$braked = array();
			//var_dump($get_data); die;
			foreach($emp_data as $emp)
			{
				if($emp_id != $emp['employee_id'])
				{
					$a[] = $emp_id = $emp['employee_id'];
					$date = date('Y-m-d', strtotime($emp['login_time']));
					$hours[$emp_id][$date] = isset($hours[$emp_id][$date]) ? $hours[$emp_id][$date] : 0;
				}
				if($date != date('Y-m-d', strtotime($emp['login_time'])))
				{
					$date = date('Y-m-d', strtotime($emp['login_time']));
					$hours[$emp_id][$date] = isset($hours[$emp_id][$date]) ? $hours[$emp_id][$date] : 0;
				}
				$hours[$emp_id][$date] += $emp['seconds'];
				if($hours[$emp_id][$date] > 5 && !isset($braked[$emp_id][$date]) && !$emp['no_lunch'])
				{
					$emp['seconds'] -= 0.5;
					$braked[$emp_id][$date] = TRUE;
				}
				
				
				$groups[$emp['employee_id']]['group_name'] = $emp['emp_name'];
				$groups[$emp['employee_id']]['summ'] = isset($groups[$emp['employee_id']]['summ']) ? ($groups[$emp['employee_id']]['summ'] + ($emp['seconds'] * $emp['employee_hourly_rate'])) : ($emp['seconds'] * $emp['employee_hourly_rate']);
				$groups[$emp['employee_id']]['group_id'] = $groups[$emp['employee_id']]['group_id'] = $groups[$emp['employee_id']]['expense_type_group_id'] = 0;
				
			}
		}
		
		
		/******КОСТЫЛЬ ДЛЯ ПЕЙРОЛА В ЗАТРАТАХ*****/
		else
		{
			$this->db->select('equipment_groups.group_name, equipment_items.group_id, expense_types_groups.*, (SUM(expenses.expense_amount) + SUM(expenses.expense_hst_amount)) as summ');
			if($wdata && !empty($wdata))
				$this->db->where($wdata);
			$this->db->where('expense_types_groups.expense_type_id', $id);
			$this->db->where('expenses.expense_type_id', $id);
			$this->db->join('equipment_items', 'equipment_items.group_id = expense_types_groups.expense_type_group_id', 'left');
			$this->db->join('equipment_groups', 'equipment_groups.group_id = expense_types_groups.expense_type_group_id');
			$this->db->join('expenses', 'equipment_items.item_id = expenses.expense_item_id');
			$this->db->group_by('expense_type_group_id');
			$groups = $this->db->get('expense_types_groups')->result_array();
		}
		//var_dump($a); die;
		$sums = array();
		foreach($groups as $key => $val)
			$sums[$key] = $val['summ'];
		array_multisort($sums, SORT_DESC, $groups);
		
		return $groups;
	}
	
	function get_sum_selected_items($id, $wdata = array())
	{
		$items = array();
		$this->db->select('equipment_items.item_code, equipment_items.item_id, expense_types_groups.*, (SUM(expenses.expense_amount) + SUM(expenses.expense_hst_amount)) as summ');
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->where('equipment_items.group_id', $id);
		$this->db->join('equipment_items', 'equipment_items.group_id = expense_types_groups.expense_type_group_id', 'left');
		$this->db->join('equipment_groups', 'equipment_groups.group_id = expense_types_groups.expense_type_group_id');
		$this->db->join('expenses', 'equipment_items.item_id = expenses.expense_item_id');
		$this->db->group_by('equipment_items.item_id');
		$items = $this->db->get('expense_types_groups')->result_array();
		$sums = array();
		foreach($items as $key => $val)
			$sums[$key] = $val['summ'];
		array_multisort($sums, SORT_DESC, $items);

		return $items;
	}

	function reset_expense_groups($id, $groupsArray)
	{
		//if(!$groupsArray)
			//return FALSE;
		$data = array();
		$this->db->where('expense_type_id', $id);
		$this->db->delete($this->table2);
		foreach($groupsArray as $groupId)
		{
			$data[] = array('expense_type_id' => $id, 'expense_type_group_id' => $groupId);
		}
		if(!$data || empty($data))
			return FALSE;
		$this->db->insert_batch($this->table2, $data);
		return TRUE;
	}

	function insert_expense($data)
	{
		$this->db->insert($this->table1, $data);
		return $this->db->insert_id();
	}

	function update_expense($id, $data)
	{
		$this->db->where('expense_id', $id);
		$this->db->update($this->table1, $data);
		return TRUE;
	}

	function delete_expense($id)
	{
		$this->db->where('expense_id', $id);
		$this->db->delete($this->table1);
		return TRUE;
	}
}
