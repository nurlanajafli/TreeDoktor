<?php

use application\modules\estimates\models\TreeInventoryEstimateService;
use application\modules\estimates\models\TreeInventoryEstimateServiceWorkTypes;

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
        $this->table6 = 'schedule_teams_bonuses';
        $this->table7 = 'schedule_event_services';
        $this->table8 = 'schedule_estimators_stat';
        $this->table9 = 'schedule_crews_stat';
        $this->table10 = 'schedule_teams_tools';
        $this->primary_key = "schedule.id";
    }

    function insert_event_services($data)
    {
        if ($data) {
            $insert = $this->db->insert($this->table7, $data);
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            }
        }
        return FALSE;
    }


    function get_events_amount($wdata = [])
    {
        $this->db->where($wdata);
        $this->db->select_sum('event_price');
        return $this->db->get('schedule')->row_array();
    }

    public function get_event_services($wdata = [], $whereIn = ['field' => NULL, 'values' => []])
    {
        if ($wdata)
            $this->db->where($wdata);
        if (isset($whereIn['field']) && $whereIn['field'] && isset($whereIn['values']) && is_array($whereIn['values']))
            $this->db->where_in($whereIn['field'], $whereIn['values']);
        $this->db->select('*, schedule_event_services.service_id as event_service_id, ');
        $this->db->join('estimates_services', 'estimates_services.id = schedule_event_services.service_id', 'left');
        $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->db->join('schedule', 'schedule.id = schedule_event_services.event_id', 'left');
        $this->db->join('estimates_services_crews', 'estimates_services.id = estimates_services_crews.crew_service_id', 'left');
        $this->db->join('crews', 'crews.crew_id = estimates_services_crews.crew_user_id', 'left');
        $this->db->join('estimates_services_equipments', 'estimates_services.id = estimates_services_equipments.equipment_service_id', 'left');
        $this->db->join('estimate_equipment', 'estimate_equipment.eq_id = estimates_services_equipments.equipment_item_id', 'left');
        $this->db->group_by('estimates_services.id');
        $result = $this->db->get($this->table7)->result_array();
        if(!empty($result)){
            foreach ($result as $key => $item){
                if(isset($item['is_bundle']) && $item['is_bundle']){
                    $this->db->join('estimates_services', 'estimates_services.id = estimates_bundles.eb_service_id', 'left');
                    $this->db->join('estimates_services_crews', 'estimates_services_crews.crew_service_id = estimates_services.id', 'left');
                    $this->db->join('crews', 'crews.crew_id = estimates_services_crews.crew_user_id', 'left');
                    $this->db->join('estimates_services_equipments', 'estimates_services.id = estimates_services_equipments.equipment_service_id', 'left');
                    $this->db->join('estimate_equipment', 'estimate_equipment.eq_id = estimates_services_equipments.equipment_item_id', 'left');
                    $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
                    $this->db->group_by('estimates_services.id');
                    $this->db->where('estimates_bundles.eb_bundle_id = ' . $item['event_service_id']);
                    $result[$key]['bundle_records'] = $this->db->get('estimates_bundles')->result_array();
                }
            }
        }

        return $result;
    }

    public function delete_event_services($wdata)
    {
        if ($wdata)
            $this->db->where($wdata);
        $this->db->delete($this->table7);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function insert_team_bonus($data)
    {
        if ($data) {
            $insert = $this->db->insert($this->table6, $data);
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            }
        }
        return FALSE;
    }

    function delete_team_bonus($data)
    {
        if ($data) {
            $this->db->where($data);
            $this->db->delete($this->table6);
            if ($this->db->affected_rows() > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    function get_bonuses($wdata)
    {
        if ($wdata)
            $this->db->where($wdata);
        return $this->db->get($this->table6)->result_array();
    }

    function get_employee_bonuses($wdata)
    {
        if ($wdata)
            $this->db->where($wdata);
        $this->db->join('schedule_teams', 'team_id = bonus_team_id');
        $this->db->join('schedule_teams_members', 'employee_team_id = bonus_team_id');
        $this->db->join('bonuses_types', 'bonuses_types.bonus_type_id = schedule_teams_bonuses.bonus_type_id', 'left');
        return $this->db->get($this->table6)->result_array();
    }

    function get_collected_bonuses_dates($toDate = NULL)
    {
        /***/
        return array('from' => '2015-05-01', 'to' => '2015-12-01');
        /***/
        if (!$toDate)
            $toDate = date('Y-m-d');

        $currMonth = date('n', strtotime($toDate));
        if ($currMonth < 5 || $currMonth > 11) {
            $from = ($currMonth > 11) ? date('Y', strtotime($toDate)) . '-11-01' : (date('Y', strtotime($toDate)) - 1) . '-11-01';
            //$to = ($currMonth > 11) ? (date('Y', strtotime($toDate)) + 1) . '-05-01' : date('Y', strtotime($toDate)) . '-05-01';
        } else {
            $from = date('Y', strtotime($toDate)) . '-05-01';
            //$to = date('Y', strtotime($toDate)). '-11-01';
        }
        $to = $toDate;
        if ((strtotime($to) - strtotime($from)) / 86400 < 13) {
            if ($currMonth == 5) {
                $from = (date('Y', strtotime($toDate)) - 1) . '-11-01';
                $to = date('Y', strtotime($toDate)) . '-05-01';
            } elseif ($currMonth == 11) {
                $from = date('Y', strtotime($toDate)) . '-05-01';
                $to = date('Y', strtotime($toDate)) . '-11-01';
            }
        }
        return array('from' => $from, 'to' => $to);
    }

    function get_collected_bonuses_sum($user_id, $toDate = NULL)
    {
        /*if(date('m') ==  12 || date('m') < 5)
            return 0;*/
        /*******30.11.2015 CLOSED BY YURIY INQUIRY*********/
        $this->load->model('employee/mdl_employee', 'emp_login');
        $bonuses = $worked = array();
        $summ = 0;
        $dates = $this->get_collected_bonuses_dates($toDate);
        $whereBonus['team_date >='] = strtotime($dates['from']);
        $whereBonus['team_date <'] = strtotime($dates['to']);
        $whereBonus['user_id'] = $user_id;
        $bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);

        foreach ($bonusesRows as $bonus)
            $bonuses[date('Y-m-d', $bonus['team_date'])] = isset($bonuses[date('Y-m-d', $bonus['team_date'])]) ? intval($bonuses[date('Y-m-d', $bonus['team_date'])] + $bonus['bonus_amount']) : intval($bonus['bonus_amount']);
        foreach ($bonuses as $key => $val)
            $bonuses[$key] = $val >= 0 ? $val : 0;

        $loginWhere["start_date"] = $dates['from'];
        $loginWhere["end_date"] = $dates['to'];
        $loginWhere["id"] = $user_id;

        $loginData = $this->emp_login->get_emp_login_data_biweekly($loginWhere);
        if (!$loginData)
            $loginData = array();

        foreach ($loginData as $login) {
            if ($login['logout_time'] != '0000-00-00 00:00:00') {
                $date = date('Y-m-d', strtotime($login['login_time']));
                $timeDiff = round(((strtotime($login['logout_time']) - strtotime($login['login_time'])) / 3600), 2);
                $worked[$date]['time_diff'] = isset($worked[$date]['time_diff']) ? ($worked[$date]['time_diff'] + $timeDiff) : $timeDiff;
                $worked[$date]['hourly_rate'] = $login['employee_hourly_rate'];
                $worked[$date]['no_lunch'] = $login['no_lunch'];
            }
        }

        foreach ($worked as $key => $workedDaily) {
            /***/
            if (date('m', strtotime($key)) >= 5 && date('m', strtotime($key)) <= 11)/***/ {
                if (isset($bonuses[$key])) {
                    $workedHrs = $workedDaily['no_lunch'] ? $workedDaily['time_diff'] : ($workedDaily['time_diff'] - 0.5);
                    $bonusSum = round($workedHrs * $workedDaily['hourly_rate'] * $bonuses[$key] / 100, 2);
                    $summ += $bonusSum;
                }
                /***/
            }/***/
        }

        return $summ;
    }

    function get_collected_bonuses_sum1($user_id, $toDate = NULL, $fromDate = NULL)
    {
        $this->load->model('reports/mdl_worked');
        $bonuses = $worked = array();
        $summ = 0;
        $dates = $this->get_collected_bonuses_dates($toDate);
        $whereBonus['team_date >='] = $fromDate ? strtotime($fromDate) : strtotime($dates['from']);
        $whereBonus['team_date <'] = strtotime($dates['to']);
        $whereBonus['user_id'] = $user_id;
        $bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);

        if (!$bonusesRows) {
            $whereBonus['employee_id'] = $user_id;
            unset($whereBonus['user_id']);
            $bonusesRows = $this->mdl_schedule->get_employee_bonuses($whereBonus);
        }


        foreach ($bonusesRows as $bonus)
            $bonuses[date('Y-m-d', $bonus['team_date'])] = isset($bonuses[date('Y-m-d', $bonus['team_date'])]) ? intval($bonuses[date('Y-m-d', $bonus['team_date'])] + $bonus['bonus_amount']) : intval($bonus['bonus_amount']);
        foreach ($bonuses as $key => $val)
            $bonuses[$key] = $val >= 0 ? $val : 0;


        $worked = $this->mdl_worked->get_many_by(array('worked_user_id' => $user_id, 'worked_date >=' => $dates['from'], 'worked_date <=' => $dates['to']));

        if (!$worked)
            $worked = $this->mdl_worked->get_many_by(array('worked_employee_id' => $user_id, 'worked_date >=' => $dates['from'], 'worked_date <=' => $dates['to']));

        foreach ($worked as $key => $workedDaily) {
            if (isset($bonuses[$workedDaily->worked_date])) {
                $bonusSum = round(($workedDaily->worked_hours - $workedDaily->worked_lunch) * $workedDaily->worked_hourly_rate * $bonuses[$workedDaily->worked_date] / 100, 2);
                $summ += $bonusSum;
            }
        }

        return $summ;
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

    function update_team_member($wdata, $data)
    {
        $this->db->where($wdata);
        $update = $this->db->update($this->table1, $data);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        }
        return FALSE;
    }

    function delete_team_member($data)
    {
        if ($data) {
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
        $this->db->select("users.*, CONCAT(users.firstname, ' ', users.lastname) as emp_name, reasons_absence.*, users.id as employee_id, employees.emp_feild_worker, absence_ymd", FALSE);
        $this->db->where($data);
        $this->db->join('users', 'users.id = schedule_absence.absence_user_id');
        $this->db->join('employees', 'employees.emp_user_id = users.id');
        $this->db->join('reasons_absence', 'reasons_absence.reason_id = schedule_absence.absence_reason_id');
        return $this->db->get($this->table4)->result_array();
    }

    function update_team_item($data, $wdata)
    {
        $this->db->where($wdata);
        $this->db->update($this->table2, $data);
        return TRUE;
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

    function insert_team_tool($data)
    {
        if ($data) {
            $insert = $this->db->insert($this->table10, $data);
            if ($this->db->affected_rows() > 0) {
                return $this->db->insert_id();
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }

    function delete_team_tool($data)
    {
        if ($data) {
            $this->db->where($data);
            $this->db->delete($this->table10);
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
        $query = 'REPLACE  ' . $this->table5 . " SET note_date='" . $data['note_date'] . "', note_text='" . $data['note_text'] . "'";
        $this->db->query($query);
        return TRUE;
    }

    function get_note($wdata = array())
    {
        $this->db->where($wdata);
        return $this->db->get($this->table5)->row_array();
    }

    function get_team_events($wdata)
    {
        $this->db->where($wdata);
        return $this->db->get($this->table)->result_array();
    }

    function get_team_event_services($eventId = NULL)
    {
        $this->db->where('event_id', $eventId);
        return $this->db->get($this->table7)->result_array();
    }

    function get_team_members($wdata, $limit = FALSE, $group_by = 'users.id', $where_in = [])
    {
        if (!$wdata)
            return FALSE;

        if(!empty($where_in) && !empty($where_in[0]) && !empty($where_in[1]))
            $this->db->where_in($where_in[0], $where_in[1]);

        $this->db->where($wdata);

        $this->db->select("schedule_teams_members.*, schedule_teams.*, crews.*, users.id, users.user_type, users.emailid, 
		    users.firstname, users.lastname, users.active_status, users.picture, users.color, users.color, users.user_email, users.user_signature, 
		    users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, CONCAT(leader.firstname, ' ', leader.lastname) as team_leader_name, 
		    worked_date, worked_hours, worked_lunch, ROUND(worked_hours - IF(worked_lunch IS NULL, 0, worked_lunch), 2) as worked_time, emp_feild_worker, team_expeses_report.*, bld.expense_id as bld_expense_id, extra.expense_id as extra_expense_id", FALSE);

        $this->db->join('users', 'users.id = schedule_teams_members.user_id');
        $this->db->join('employees', 'employees.emp_user_id = users.id', 'LEFT');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'LEFT');
        $this->db->join('schedule_teams tleader', 'tleader.team_id = schedule_teams_members.employee_team_id AND tleader.team_leader_user_id = schedule_teams_members.user_id', 'left');
        $this->db->join('users leader', 'leader.id = schedule_teams.team_leader_user_id', 'left');
        $this->db->join('employee_worked', "schedule_teams.team_date_start = employee_worked.worked_date AND schedule_teams.team_date_end = employee_worked.worked_date AND schedule_teams_members.user_id = employee_worked.worked_user_id", 'left', FALSE);
        $this->db->join('crews', 'schedule_teams.team_crew_id = crews.crew_id', 'left');

        $this->db->join('expenses as bld', 'users.id = bld.expense_user_id AND schedule_teams.team_id = bld.expense_team_id AND bld.expense_is_extra=0', 'left');
        $this->db->join('expenses as extra', 'users.id = extra.expense_user_id AND schedule_teams.team_id = extra.expense_team_id AND extra.expense_is_extra=1', 'left');

        $this->db->join('team_expeses_report', 'users.id = team_expeses_report.ter_user_id AND schedule_teams.team_id = team_expeses_report.ter_team_id', 'left');

        $this->db->order_by('team_id', 'DESC');
        $this->db->order_by('tleader.team_leader_user_id', 'DESC');

        if($group_by)
            $this->db->group_by($group_by);

        //`team_id` DESC, tleader.team_leader_id DESC
        $query = $this->db->get($this->table1);

        if ($limit)
            return $query->row_array();
        return $query->result_array();
    }

    function get_team_items($wdata, $limit = FALSE)
    {
        if (!$wdata)
            return FALSE;
        $this->db->where($wdata);
        $this->db->join('equipment', 'equipment.eq_id = schedule_teams_equipment.equipment_id');
        $this->db->join('equipment_groups', 'equipment.group_id = equipment_groups.group_id');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_equipment.equipment_team_id');
        $query = $this->db->get($this->table2);
        if ($limit)
            return $query->row_array();
        return $query->result_array();
    }

    function get_team_tools($wdata, $limit = FALSE)
    {
        if (!$wdata)
            return FALSE;
        $this->db->select('equipment.eq_name, equipment.eq_name as item_name, schedule_teams_tools.*');
        $this->db->where($wdata);
        $this->db->join('equipment', 'equipment.eq_id = schedule_teams_tools.stt_item_id');
        $this->db->join('equipment_groups', 'equipment.group_id = equipment_groups.group_id');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_tools.stt_team_id');
        $query = $this->db->get($this->table10);
        if ($limit)
            return $query->row_array();
        return $query->result_array();
    }

    function get_free_members($wdata = array(), $notIn = array())
    {
        if ($wdata && count($wdata))
            $this->db->where($wdata);
        if ($notIn && count($notIn))
            $this->db->where_not_in('id', $notIn);
        $this->db->select("users.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name", FALSE);
        $this->db->order_by('emp_name');
        return $this->db->get('users')->result();
    }

    function get_free_items($wdata = array(), $notIn = array())
    {
        $query = \application\modules\equipment\models\Equipment::query()->with(['group']);
        if (!empty($wdata)) {
            $query->where($wdata);
        }
        if (!empty($notIn)) {
            $query->whereNotIn('eq_id', $notIn);
        }
        $query->groupBy('eq_id')->orderBy('group_id')->orderBy('eq_id');
        return $query->get();
//        if ($wdata && count($wdata))
//            $this->db->where($wdata);
//        if ($notIn && count($notIn))
//            $this->db->where_not_in('item_id', $notIn);
//        $this->db->select('equipment_items.item_id, equipment_items.item_name, equipment_items.item_code, equipment_items.group_id, equipment_groups.group_color');
//        $this->db->join('equipment_groups', 'equipment_groups.group_id = equipment_items.group_id');
//        $this->db->order_by('equipment_items.group_id, equipment_items.item_id');
//        return $this->db->get('equipment_items')->result();
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

        $weight = 0;
        $this->db->insert('schedule_teams', $data);
        $teamId = $this->db->insert_id();
        $members = $items = array();
        foreach ($teamMembers as $member) {
            $members[] = array('employee_team_id' => $teamId, 'user_id' => $member, 'weight' => $weight++);
        }
        //$members[] = array('employee_team_id' => $teamId, 'employee_id' => $member);
        foreach ($teamItems as $item)
            $items[] = array('equipment_team_id' => $teamId, 'equipment_id' => $item, 'weight' => $weight++);
        if (count($members))
            $this->db->insert_batch('schedule_teams_members', $members);
        if (count($items))
            $this->db->insert_batch('schedule_teams_equipment', $items);
        return $teamId;
    }

    function update_team($team_id, $data, $wdata = array())
    {
        if ($team_id)
            $this->db->where('team_id', $team_id);
        if ($wdata)
            $this->db->where($wdata);
        if (!$team_id && !count($wdata))
            return FALSE;
        $this->db->update('schedule_teams', $data);

        return TRUE;
    }

    function get_teams($wdata = array(), $limit = FALSE, $group_by = FALSE, $orders = [], $where_in=[])
    {
        /*
        SELECT SUM(expense_amount) as total_expenses FROM expenses
        JOIN schedule ON expense_event_id = schedule.id
        JOIN schedule_teams ON schedule.event_team_id = schedule_teams.team_id
        WHERE schedule_teams.team_id = 9727
        GROUP BY schedule_teams.team_id
        */

        $this->db->select('SUM(expense_amount) as total_expenses, schedule_teams.team_id as expenses_team_id', FALSE);

        $this->db->from('expenses');
        $this->db->join('schedule', 'expense_event_id = schedule.id');
        $this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id');

        if ($wdata)
            $this->db->where($wdata);
        if(!empty($where_in) && !empty($where_in[0]) && !empty($where_in[1]))
            $this->db->where_in($where_in[0], $where_in[1]);

        $this->db->group_by('schedule_teams.team_id');

        $expensesSubQuery = $this->db->_compile_select();
        $this->db->_reset_select();


        if ($wdata && count($wdata))
            $this->db->where($wdata);

        if(!empty($where_in) && !empty($where_in[0]) && !empty($where_in[1]))
            $this->db->where_in($where_in[0], $where_in[1]);

        $this->db->select("schedule_teams.*, crews.*, CONCAT(users.firstname, ' ', users.lastname) as emp_name, users.*, users.id as employee_id, expenses_total.total_expenses", FALSE);
        $this->db->join('users', 'users.id = schedule_teams.team_leader_user_id', 'left');
        $this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');
        $this->db->join("($expensesSubQuery) expenses_total", "schedule_teams.team_id=expenses_total.expenses_team_id", 'left');

        if($group_by)
            $this->db->group_by($group_by);

        if(count($orders)){
            foreach ($orders as $key => $order) {
                $this->db->order_by($order[0], $order[1]);
            }
        }



        if ($limit)
            $result = $this->db->get('schedule_teams')->row();
        else
            $result = $this->db->get('schedule_teams')->result();

        return $result;
    }

    function get_week_teams_members($wdata = array(), $limit = FALSE, $group_by = FALSE, $orders = [])
    {
        if ($wdata && count($wdata))
            $this->db->where($wdata);

        $this->db->select("schedule_teams.*, crews.*, CONCAT(users.firstname, ' ', users.lastname) as emp_name, users.*, users.id as employee_id, stm.user_id", FALSE);
        
        $this->db->join('schedule_teams_members stm', 'stm.employee_team_id = schedule_teams.team_id', 'left');
        $this->db->join('users', 'users.id = stm.user_id', 'left');
        //$this->db->join('users', 'users.id = schedule_teams.team_leader_user_id', 'left');
        $this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');

        if($group_by)
            $this->db->group_by($group_by);

        if(count($orders)){
            foreach ($orders as $key => $order) {
                $this->db->order_by($order[0], $order[1]);
            }
        }
            
            

        if ($limit)
            $result = $this->db->get('schedule_teams')->row();
        else
            $result = $this->db->get('schedule_teams')->result();

        return $result;
    }

    function find_team_by($wdata = array(), $limit = FALSE)
    {
        if ($wdata && count($wdata))
            $this->db->where($wdata);
        $this->db->select('schedule_teams.*, schedule_teams_members.*');
        $this->db->join('schedule_teams_members', 'schedule_teams_members.employee_team_id = schedule_teams.team_id', 'left');
        $this->db->order_by('schedule_teams.team_date_start');
        if ($limit)
            $result = $this->db->get('schedule_teams')->row();
        else
            $result = $this->db->get('schedule_teams')->result();
        return $result;
    }

    function get_team_member_stats()
    {
        /*

        */
    }

    function get_crew_items($wdata, $limit = FALSE)
    {
        if (!$wdata)
            return FALSE;
        $this->db->select('equipment.eq_name, equipment.eq_name as item_name, emp_leader.emp_name as crew_leader_name, crews.crew_leader, crews.crew_name, crews_equipment.*');
        $this->db->where($wdata);
        $this->db->join('equipment', 'equipment.eq_id = crews_equipment.equipment_id');
        $this->db->join('crews', 'crews.crew_id = crews_equipment.eq_crew_id');
        $this->db->join('users as emp_leader', 'crews.crew_leader = emp_leader.emp_user_id', 'left');
        $this->db->order_by('crew_id');
        $query = $this->db->get($this->table2);
        if ($limit)
            return $query->row_array();
        return $query->result_array();
    }

    function get_events($wdata = array(), $limit = FALSE, $order = FALSE)
    {
        foreach ($wdata as $key => $val) {
            if (strpos($key, 'event_') === 0) {
                $wdata['schedule.' . $key] = $val;
                unset($wdata[$key]);
            }
        }

        /* ------------- subquery ----------*/
        $this->db->select('schedule.id as sch_id, ROUND((((schedule.event_end - schedule.event_start)/3600)/((MAX(`e`.`event_end`) - MIN(`e`.`event_start`))/3600))*schedule_teams.team_man_hours, 2) as event_man_hours, schedule.event_price as event_total', FALSE);/*ROUND((((schedule.event_end - schedule.event_start)/3600)/((MAX(`e`.`event_end`) - MIN(`e`.`event_start`))/3600))*schedule_teams.team_amount, 2) OLD event_total val*/

        $this->db->from($this->table);
        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->db->join('estimates_services', "estimates_services.estimate_id=estimates.estimate_id", 'left');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('schedule e', 'e.event_team_id = schedule.event_team_id', 'left');
        $this->db->join('users est', 'est.id = estimates.user_id', 'left');

        if ($wdata)
            $this->db->where($wdata);

        $this->db->group_by('schedule_teams.team_id');

        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();

        /* -------------end subquery ----------*/

        /* subquery 2 */
        $this->db->select('schedule.id as time_sch_id, SUM(estimates_services.service_price) as total, ROUND((SUM(estimates_services.service_time) + SUM(estimates_services.service_travel_time) + SUM(estimates_services.service_disposal_time)), 2) as total_time, 
			ROUND( SUM(  `estimates_services`.`service_time` + `estimates_services`.`service_travel_time` + `estimates_services`.`service_disposal_time` ) , 2 ) AS planned_workorder_time, COUNT(  `estimates_services_crews`.`crew_id` ) AS crews_count', FALSE);

        $this->db->from($this->table);
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('schedule_event_services', 'schedule_event_services.event_id=schedule.id', 'left');
        $this->db->join('estimates_services_crews', "estimates_services_crews.crew_service_id=schedule_event_services.service_id", 'left');
        $this->db->join('estimates_services', "estimates_services.id=schedule_event_services.service_id", 'left');
        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');

        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('users est', 'est.id = estimates.user_id', 'left');
        if ($wdata)
            $this->db->where($wdata);

        $this->db->group_by('schedule.id');
        $subquery2 = $this->db->_compile_select();
        $this->db->_reset_select();
        /* subquery 2 end */

        /* subquery 3 */
        $this->db->select('workorders.id as wo_id, schedule_event_services.event_id, SUM(estimates_services.service_price) AS total', FALSE);

        $this->db->from('workorders');
        $this->db->join('estimates_services', "estimates_services.estimate_id = workorders.estimate_id", 'left');

        $this->db->join('schedule_event_services', 'schedule_event_services.service_id=estimates_services.id', 'left');
        $this->db->join('schedule', 'schedule_event_services.event_id=schedule.id', 'left');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('users est', 'est.id = estimates.user_id', 'left');
        if ($wdata)
            $this->db->where($wdata);

        $this->db->group_by('schedule_event_services.event_id');
        $subquery3 = $this->db->_compile_select();
        $this->db->_reset_select();
        /* end subquery 3 */

        /* subquery 4 */
        $this->db->select("expenses.expense_event_id, ROUND(SUM(expenses.expense_amount)+SUM(expenses.expense_hst_amount), 2) as expense_amount_sum", FALSE);
        $this->db->from('expenses');
        $this->db->where('expenses.expense_event_id IS NOT NULL');
        $this->db->group_by('expense_event_id');
        $subquery4 = $this->db->_compile_select();
        $this->db->_reset_select();
        /* end subquery 4 */


        $this->db->select("COUNT(DISTINCT e.id) as count_events, COUNT(DISTINCT schedule_teams_members.user_id) as count_members, schedule.*, schedule_teams.*, est.emailid, est.firstname, est.lastname, crews.crew_color, crews.crew_leader, crews.crew_name, workorder_status.wo_status_name, workorder_status.wo_status_color, workorders.id as wo_id, workorders.workorder_no, workorders.wo_status, estimates.estimate_id, estimates.user_id, estimates.estimate_no, leads.lead_address, leads.lead_country, leads.lead_state, leads.lead_city, leads.latitude, leads.longitude, CONCAT(users.firstname, ' ', users.lastname) as leader_name, services_total.total, estimates_total.event_total, workorder_time.total_time, estimates_total.event_man_hours, estimates_total.sch_id, workorder_time.planned_workorder_time, workorder_time.crews_count, expenses_sum.expense_amount_sum, expenses_sum.expense_event_id, er.*", FALSE);

		$this->db->join('schedule e', 'e.event_wo_id = schedule.event_wo_id');
		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');

		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');
		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('workorder_status', 'workorders.wo_status = workorder_status.wo_status_id', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');

		$this->db->join('users', 'users.id = schedule_teams.team_leader_user_id', 'left');
		$this->db->join('users est', 'est.id = estimates.user_id', 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id', 'left');
		$this->db->join('clients', 'clients.client_id = leads.client_id', 'left');

		$this->db->join('events_reports er', 'er.er_event_id = schedule.id', 'left');

        $this->db->join("($subquery) estimates_total", "schedule.id=estimates_total.sch_id", 'left');

        $this->db->join("($subquery2) workorder_time", "schedule.id=workorder_time.time_sch_id", 'left');

        $this->db->join("($subquery3) services_total", "schedule.id=services_total.event_id", 'left');

        $this->db->join("($subquery4) expenses_sum", "schedule.id=expenses_sum.expense_event_id", 'left');

		$this->db->where($wdata);

		if($order)
			$this->db->order_by($order);
		else {
			$this->db->order_by('schedule.event_start', 'DESC');
		}

        $this->db->group_by('schedule.id');

		if($limit == 1)
			$result = $this->db->get($this->table)->row_array();
		else
			$result = $this->db->get($this->table)->result_array();

        return $result;
    }

    function get_events_totals($wdata = array(), $limit = FALSE, $order = FALSE)
    {
        $this->db->select('SUM(estimates_services.service_price)/COUNT(DISTINCT estimates_services_crews.crew_id ) as planned_price, ROUND((estimates_services.service_time+estimates_services.service_travel_time+estimates_services.service_disposal_time)*COUNT(DISTINCT estimates_services_crews.crew_id ),2) AS planned_mhrs', FALSE);

        $this->db->from('workorders');

        $this->db->join('estimates', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->db->join('estimates_services', "estimates_services.estimate_id=estimates.estimate_id", 'left');

        $this->db->join('estimates_services_crews', "estimates_services_crews.crew_service_id=estimates_services.id", 'left');

        if ($wdata)
            $this->db->where($wdata);
        $this->db->where('estimates_services.service_status <>', 1);
        $this->db->group_by('estimates_services.id');
        $result = $this->db->get()->result_array();

        return $result;
    }

    function save_event($data, $update = TRUE)
    {
        if ($update) {
            $this->db->where('id', $data['id']);
            $this->db->update($this->table, $data);
            return TRUE;
        } else {
            $this->db->insert($this->table, $data);
            return $this->db->insert_id();
        }
        //return FALSE;
    }

    function delete_event($event_id)
    {
        $this->db->where('id', $event_id);
        $this->db->delete($this->table);
        return TRUE;
    }

    function insert_update($data = array())
    {
        if (!isset($data['update_time']) || !$data['update_time'])
            $data['update_time'] = time();
        $this->db->insert('schedule_updates', $data);
        return $this->db->insert_id();
    }

    function get_update($wdata = array())
    {
        if ($wdata && count($wdata))
            $this->db->where($wdata);
        $this->db->order_by('update_id', 'DESC');
        $this->db->limit(1);
        return $this->db->get('schedule_updates')->row_array();
    }

    function getCountEvents($wdata = [])
    {
        $this->db->where($wdata);
        return $this->db->count_all_results($this->table);
    }

    function get_all_hours($event_id)
    {
        $this->load->model('mdl_services_orm');
        $this->load->model('mdl_crews_orm');

        $this->db->where('id', $event_id);
        $services_data = $this->db->get($this->table)->row_array();

        $this->db->where('employee_team_id', $services_data['event_team_id']);
        $members = $this->db->count_all_results($this->table1);

        $data['estimate_price'] = 0;
        $data['estimate_time'] = 0;

        foreach (json_decode($services_data['event_services']) as $key => $val) {
            $time = 0;
            $estCrews = $this->mdl_crews_orm->get_many_by('crew_service_id', $val);
            $serv = $this->mdl_services_orm->get($val);

            $data['estimate_price'] += $serv->service_price;
            if ($serv->service_time)
                $time += $serv->service_time;
            if ($serv->service_travel_time)
                $time += $serv->service_travel_time;
            if ($serv->service_disposal_time)
                $time += $serv->service_disposal_time;
            $data['estimate_time'] += $time * count($estCrews);
        }
        $this->db->where('team_id', $services_data['event_team_id']);
        $teamData = $this->db->get($this->table3)->row_array();
        $data['team_amount'] = $teamData['team_amount'];
        $sections = $this->mdl_schedule->get_teams(array('team_id' => $services_data['event_team_id']));

        $this->load->model('mdl_employee');
        $this->load->model('mdl_worked');
        foreach ($sections as $key => $section) {
            $data['team_hours'] = 0;
            if ($section->team_crew_id) {
                $team_members = $this->mdl_schedule->get_team_members(array('schedule_teams.team_id' => $section->team_id));

                foreach ($team_members as $member) {
                    $worked = $this->mdl_worked->get_by(array('worked_user_id' => $member['user_id'], 'worked_date' => date('Y-m-d', $section->team_date)));
                    if ($worked)
                        $data['team_hours'] += $worked->worked_hours;
                }
            }
        }
        return $data;
    }

    function get_estimator_stat($where)
    {
        if (!count($where))
            return FALSE;

        $this->db->select('SUM(total ) AS sum');
        $this->db->where($where);
        $query = $this->db->get($this->table8);
        return $query;
    }

    function get_crews_stat($where)
    {
        if (!count($where))
            return FALSE;

        $this->db->where($where);
        $query = $this->db->get($this->table9);
        return $query;
    }

    function insert_estimator_stat($data)
    {
        $this->db->insert($this->table8, $data);
        return TRUE;
    }

    function insert_crews_stat($data)
    {
        $this->db->insert($this->table9, $data);
        return TRUE;
    }

    function delete_estimator_stat($team_id)
    {
        $this->db->where(array('team_id' => $team_id));
        $this->db->delete($this->table8);
        return TRUE;
    }

    function delete_crews_stat($team_id)
    {
        $this->db->where(array('team_id' => $team_id));
        $this->db->delete($this->table9);
        return TRUE;
    }

    function team_count($where)
    {
        $this->db->where($where);
        $this->db->select('COUNT(schedule_teams.team_id) as count_teams');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_estimators_stat.team_id', 'left');
        $result = $this->db->get($this->table8)->row_array();
        return $result;
    }

    function estimators_mhr_sum($where)
    {
        $this->db->where($where);
        //SUM(schedule_estimators_stat.total) /***1***/
        //SUM(schedule_estimators_stat.total) / COUNT(schedule_estimators_stat.team_id) /***3***/
        $this->db->select('ROUND(SUM(team_amount / team_man_hours), 2) as total, ROUND(SUM(team_amount / team_man_hours) / COUNT(schedule_teams.team_id), 2) as mhr_return, COUNT(schedule_teams.team_id) as count, users.*', FALSE);
        $this->db->join('schedule', 'team_id = event_team_id');
        $this->db->join('workorders', 'workorders.id = event_wo_id');
        $this->db->join('estimates', 'workorders.estimate_id = estimates.estimate_id');
        $this->db->join('users', 'users.id = estimates.user_id');
        $this->db->where('team_closed', 1);
        $this->db->group_by('users.id');
        $this->db->order_by('mhr_return', 'DESC');
        $result = $this->db->get('schedule_teams')->result_array();

        return $result;
    }

    function employees_mhr_sum($where)
    {
        $this->db->where($where);
        //SUM(schedule_estimators_stat.total) /***1***/
        //SUM(schedule_estimators_stat.total) / COUNT(schedule_estimators_stat.team_id) /***3***/
        $this->db->select('ROUND(SUM(team_amount / team_man_hours), 2) as total, ROUND(SUM(team_amount / team_man_hours) / COUNT(schedule_teams.team_id), 2) as mhr_return, COUNT(schedule_teams.team_id) as count, employees.*', FALSE);
        $this->db->join('schedule_teams_members', 'team_id = employee_team_id');
        $this->db->join('employees', 'employees.employee_id = schedule_teams_members.employee_id');
        $this->db->where('team_closed', 1);
        $this->db->group_by('employee_id');
        $this->db->order_by('mhr_return', 'DESC');
        $result = $this->db->get('schedule_teams')->result_array();

        return $result;
    }

    function get_events_dashboard($wdata = array(), $oneRow = FALSE, $notArb = FALSE, $where_in=[], $orWhere=[])
    {
        if(count($where_in) && isset($where_in[1]) && count($where_in[1]))
            $this->db->where_in($where_in[0], $where_in[1]);

        if($wdata)
			$this->db->where($wdata);

        if($orWhere){
            $this->db->or_where($orWhere);
        }


		$this->db->select("COUNT(DISTINCT e.id) as count_events, 
			COUNT(DISTINCT schedule_teams_members.user_id) as count_members,
			ROUND((((schedule.event_end - schedule.event_start)/3600)/((MAX(`te`.`event_end`) - MIN(`te`.`event_start`))/3600))*schedule_teams.team_man_hours, 2) as event_man_hours, 
			ROUND( SUM(event_services.service_time + event_services.service_travel_time + event_services.service_disposal_time) / COUNT(DISTINCT e.id) / COUNT(DISTINCT schedule_teams_members.user_id), 2 ) AS planned_workorder_time, 
			ROUND( SUM(event_services.service_time + event_services.service_disposal_time) / COUNT(DISTINCT e.id) / COUNT(DISTINCT schedule_teams_members.user_id), 2 ) AS planned_service_time, 
			ROUND( SUM(event_services.service_travel_time/*/CEILING(event_services.service_time/10)*/) / COUNT(DISTINCT e.id) / COUNT(DISTINCT schedule_teams_members.user_id), 2 ) AS planned_travel_time, 
			est.emailid, est.firstname, est.lastname,  schedule.*, schedule_teams.*, estimates.*, workorders.wo_pdf_files, workorders.workorder_no, workorder_status.wo_status_name, workorder_status.wo_status_color, 
			workorders.id as wo_id, workorders.workorder_no, workorders.wo_status, leads.lead_address,  leads.lead_state,  leads.lead_country, leads.lead_city, leads.lead_zip, leads.latitude, leads.longitude, crews.crew_name, 
			CONCAT(users.firstname, ' ', users.lastname) as leader_name, clients.client_unsubscribe, clients.client_name, clients.client_brand_id, ev_tailgate_safety_form, 
			events_reports.*, schedule_teams_members.user_id", FALSE);
		$this->db->join('schedule e', 'e.event_wo_id = schedule.event_wo_id');
        $this->db->join('schedule te', 'te.event_team_id = schedule.event_team_id', 'left');
		$this->db->join('events', 'events.ev_event_id = schedule.id', "left");
		$this->db->join('events_reports', 'events_reports.er_event_id = schedule.id', "left");
		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
		//COUNT(estimates_services_crews.crew_id) AS crews_count
		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');
		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('workorder_status', 'workorders.wo_status = workorder_status.wo_status_id', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('clients', 'clients.client_id = estimates.client_id', 'left');
		//$this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id', 'left');

		$this->db->join('schedule_event_services','schedule_event_services.event_id=schedule.id', 'left');

		//$this->db->join('estimates_services_crews', 'estimates_services_crews.crew_service_id=schedule_event_services.service_id', 'left');

		$this->db->join('estimates_services event_services', "event_services.id=schedule_event_services.service_id", 'left');

		$this->db->join('users', 'users.id = schedule_teams.team_leader_user_id', 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id', 'left');
		$this->db->join('users est', 'est.id = estimates.user_id', 'left');

		/*if($notArb) {
			$this->db->where_not_in('event_services.service_id', [2, 3, 25, 28, 30, 26, 29, 27, 34, 31, 32, 33, 39]);
		}*/

		$this->db->order_by('schedule.event_start', 'ASC');
		$this->db->group_by('schedule.id');
		$result = $this->db->get('schedule');
		//echo "<pre>" . $this->db->last_query();die;
		if($oneRow)
			return $result->row_array();
		return $result->result_array();

	}

	function get_event_members($wdata)
	{
		if($wdata)
			$this->db->where($wdata);

		$this->db->select("schedule.id, crews.crew_name, crews.crew_leader, DATE_FORMAT(FROM_UNIXTIME(schedule.event_start), '%d-%m-%Y') as team_date, GROUP_CONCAT(CONCAT(users.firstname, ' ', users.lastname) SEPARATOR ', ') as team_members", false);


		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('users est', 'est.id = estimates.user_id', 'left');

		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');

		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');

		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');

		$this->db->join('users','users.id = schedule_teams_members.user_id', 'left');

		$this->db->order_by('schedule.event_start');
		$this->db->group_by('schedule.event_team_id');

		$result = $this->db->get('schedule');
		return $result->result_array();
	}

	function get_estimator_report_event_members($wdata, $row = FALSE)
	{
		if($wdata)
			$this->db->where($wdata);

		$this->db->select("schedule.id, schedule.event_price, schedule_teams.team_id, schedule_teams.team_date, schedule_teams.team_man_hours, schedule_teams.team_amount, schedule_teams.team_closed, crews.crew_name, crews.crew_leader, DATE_FORMAT(FROM_UNIXTIME(schedule.event_start), '%Y-%m-%d') as team_date, GROUP_CONCAT(CONCAT(users.firstname, ' ', users.lastname) SEPARATOR ', ') as team_members", false);


		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('users est', 'est.id = estimates.user_id', 'left');

		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');

		$this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');

		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');

		$this->db->join('users','users.id = schedule_teams_members.user_id', 'left');

		$this->db->order_by('schedule.event_start');
		$this->db->group_by('schedule.id');

		$result = $this->db->get('schedule');

		if($row)
			return $result->row_array();
		return $result->result_array();
	}

	function sum_demage_complain($where = array())
	{
		$this->db->select("SUM(schedule.event_damage) / COUNT(DISTINCT schedule_teams_members.user_id) as event_damage, SUM(schedule.event_complain) / COUNT(DISTINCT schedule_teams_members.user_id) as event_complain", FALSE);



		$this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');


		if(count($where))
			$this->db->where($where);

		//$this->db->group_by('schedule.id');
		$query = $this->db->get($this->table);
		return $query->row_array();
	}

	function avg_dmg_cmp($where = array())
	{

		$this->db->select('team_id, SUM( schedule.event_damage ) day_damage, SUM( schedule.event_complain ) day_complain', FALSE);
        $this->db->from('schedule');

        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('schedule_teams_members', 'schedule_teams_members.employee_team_id = schedule_teams.team_id', 'left');

        if ($where && count($where))
            $this->db->where($where);
        $this->db->group_by('team_id');
        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select("AVG( day_damage ) as avg_demage , AVG( day_complain ) as avg_complain", FALSE);
        $this->db->from('schedule_teams');


        $this->db->join("($subquery) teamStat", 'teamStat.team_id = schedule_teams.team_id');

        $query = $this->db->get();
        return $query->row_array();

    }

    function check_busy_members($where = array(), $whereIn = array())
    {
        $whereIn = $whereIn ? $whereIn : [];
        if ($where && !empty($where))
            $this->db->where($where);
        if ($whereIn && !empty($whereIn))
            $this->db->where_in('users.id', $whereIn);

        $this->db->select("schedule_teams_members.*, schedule_teams.*, crews.*, users.*, users.id as employee_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name, CONCAT(leader.firstname, ' ', leader.lastname) as team_leader_name, worked_date, worked_hours, worked_lunch, ROUND(worked_hours - IF(worked_lunch IS NULL, 0, worked_lunch), 2) as worked_time, emp_feild_worker", FALSE);
        $this->db->join('users', 'users.id = schedule_teams_members.user_id');
        $this->db->join('employees', 'employees.emp_user_id = users.id');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_members.employee_team_id');
        $this->db->join('schedule_teams tleader', 'tleader.team_id = schedule_teams_members.employee_team_id AND tleader.team_leader_user_id = schedule_teams_members.user_id', 'left');
        $this->db->join('users leader', 'leader.id = schedule_teams.team_leader_user_id', 'left');
        $this->db->join('employee_worked', "FROM_UNIXTIME(schedule_teams.team_date + 3600, '%Y-%m-%d') = employee_worked.worked_date AND schedule_teams_members.user_id = employee_worked.worked_user_id", 'left', FALSE);
        $this->db->join('crews', 'schedule_teams.team_crew_id = crews.crew_id', 'left');
        $this->db->order_by('team_id', 'DESC');
        $this->db->order_by('tleader.team_leader_user_id', 'DESC');
        //`team_id` DESC, tleader.team_leader_id DESC
        $query = $this->db->get($this->table1);
        return $query->result_array();
    }

    function check_busy_items($where = array(), $whereIn = array())
    {
        if ($where && !empty($where))
            $this->db->where($where);
        if ($whereIn && !empty($whereIn))
            $this->db->where_in('equipment.eq_id', $whereIn);

        $this->db->join('equipment', 'equipment.eq_id = schedule_teams_equipment.equipment_id');
        $this->db->join('equipment_groups', 'equipment.group_id = equipment_groups.group_id');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_equipment.equipment_team_id');
        $query = $this->db->get($this->table2);

        return $query->result_array();
    }

    function getTeamsMembersWithOrder($date = NULL, $teamId = NULL)
    {

        if (!$date && !$teamId)
            return FALSE;

        $fromDate = $toDate = NULL;

        if ($date) {
            $fromDate = strtotime($date) - 4000;
            $toDate = $fromDate + 86400;
        }

        $teamId = intval($teamId);

        $sql = "
			SELECT 'user' as type, schedule_teams_members.user_id as item_id, employee_team_id as team_id, CONCAT(users.firstname, ' ', users.lastname) as name, equipment.eq_id as group_id, schedule_teams_members.weight, team_leader_user_id, team_color, team_rating, emp_feild_worker as field_worker, users.emailid as emailid, NULL as group_color, equipment.eq_code as driver_id FROM schedule_teams_members
			JOIN schedule_teams ON schedule_teams_members.employee_team_id = schedule_teams.team_id
			JOIN users ON schedule_teams_members.user_id = users.id
			JOIN employees ON users.id = employees.emp_user_id
			LEFT JOIN schedule_teams_equipment ON schedule_teams_equipment.equipment_team_id = schedule_teams.team_id AND schedule_teams_equipment.equipment_driver_id = users.id AND schedule_teams_equipment.equipment_id IN (SELECT eq_id FROM equipment WHERE group_id IN (16))
            LEFT JOIN equipment ON schedule_teams_equipment.equipment_id = equipment.eq_id
			WHERE 
		";

        if ($fromDate && $toDate)
            $sql .= " team_date > $fromDate AND team_date < $toDate AND ";

        if ($teamId)
            $sql .= " schedule_teams.team_id = $teamId AND ";

        $sql .= " 1=1 GROUP BY schedule_teams_members.user_id";

        if (!$teamId) {
            $sql .= "
				UNION

				SELECT 'user' as type, schedule_absence.absence_user_id as item_id, NULL as team_id, CONCAT(users.firstname, ' ', users.lastname) as name, NULL as group_id, '0' as weight, NULL as team_leader_user_id, crews.crew_color as team_color, NULL as team_rating, emp_feild_worker as field_worker, users.emailid as emailid, reason_name as group_color, NULL as driver_id FROM schedule_absence
				JOIN users ON schedule_absence.absence_user_id = users.id
				JOIN employees ON users.id = employees.emp_user_id
				JOIN crews ON crews.crew_id = 0
				JOIN reasons_absence ON reasons_absence.reason_id = schedule_absence.absence_reason_id
				WHERE absence_ymd = '$date'
			";
        }

        $sql .= "
			UNION

			SELECT 'equipment' as type, schedule_teams_equipment.equipment_id as item_id, equipment_team_id as team_id, equipment.eq_name as name, equipment.group_id as group_id, weight, team_leader_user_id, team_color, team_rating, driver.emailid as field_worker, equipment.eq_code as emailid, group_color, driver.id as driver_id FROM schedule_teams_equipment
			JOIN schedule_teams ON schedule_teams_equipment.equipment_team_id = schedule_teams.team_id
			JOIN equipment ON schedule_teams_equipment.equipment_id = equipment.eq_id
			JOIN equipment_groups ON equipment.group_id = equipment_groups.group_id
			LEFT JOIN users as driver ON schedule_teams_equipment.equipment_driver_id = driver.id
			WHERE 
		";

        if ($fromDate && $toDate)
            $sql .= " team_date > $fromDate AND team_date < $toDate AND ";

        if ($teamId)
            $sql .= " schedule_teams.team_id = $teamId AND ";

        $sql .= '1=1 ORDER BY ABS(team_id) ASC, ABS(weight) ASC, type DESC';

        return $this->db->query($sql)->result_array();
    }

    function getTeamsMembersWithOrderWeek($fromDate = NULL, $toDate = NULL, $leaders = NULL)
    {
        $teamLeadersString = '';
        $teamLeaders = [];
        if(is_array($leaders) && count($leaders)){
            $teamLeaders = array_unique(array_diff($leaders, array('', NULL, false)));
            $teamLeadersString = trim(implode(',', $teamLeaders), ',');
        }
        //$fromDate = strtotime($date) - 4000;
        //$toDate = $fromDate + 86400;


        $sql = "
            SELECT 'user' as type, schedule_teams_members.user_id as item_id, employee_team_id as team_id, CONCAT(users.firstname, ' ', users.lastname) as name, equipment_items.item_id as group_id, schedule_teams_members.weight, team_leader_user_id, team_color, team_rating, emp_feild_worker as field_worker, users.emailid as emailid, NULL as group_color, equipment_items.item_code as driver_id FROM schedule_teams_members
            JOIN schedule_teams ON schedule_teams_members.employee_team_id = schedule_teams.team_id
            JOIN users ON schedule_teams_members.user_id = users.id
            JOIN employees ON users.id = employees.emp_user_id
            LEFT JOIN schedule_teams_equipment ON schedule_teams_equipment.equipment_team_id = schedule_teams.team_id AND schedule_teams_equipment.equipment_driver_id = users.id AND schedule_teams_equipment.equipment_id IN (SELECT item_id FROM equipment_items WHERE group_id IN (16))
            LEFT JOIN equipment_items ON schedule_teams_equipment.equipment_id = equipment_items.item_id
            WHERE 
        ";

        if($teamLeaders && is_array($teamLeaders) && count($teamLeaders)){
            $sql .= " schedule_teams.team_leader_user_id IN(".$teamLeadersString.") AND ";
        }

        if ($fromDate && $toDate)
            $sql .= " team_date >= $fromDate AND team_date < $toDate AND ";

        $sql .= " 1=1";


        $sql .= "
            UNION

            SELECT 'user' as type, schedule_absence.absence_user_id as item_id, NULL as team_id, CONCAT(users.firstname, ' ', users.lastname) as name, NULL as group_id, '0' as weight, NULL as team_leader_user_id, crews.crew_color as team_color, NULL as team_rating, emp_feild_worker as field_worker, users.emailid as emailid, reason_name as group_color, NULL as driver_id FROM schedule_absence
            JOIN users ON schedule_absence.absence_user_id = users.id
            JOIN employees ON users.id = employees.emp_user_id
            JOIN crews ON crews.crew_id = 0
            JOIN reasons_absence ON reasons_absence.reason_id = schedule_absence.absence_reason_id
            WHERE absence_date >= $fromDate AND absence_date < $toDate
        ";


        $sql .= "
            UNION

            SELECT 'equipment' as type, schedule_teams_equipment.equipment_id as item_id, equipment_team_id as team_id, equipment_items.item_name as name, equipment_items.group_id as group_id, weight, team_leader_user_id, team_color, team_rating, driver.emailid as field_worker, equipment_items.item_code as emailid, group_color, driver.id as driver_id FROM schedule_teams_equipment
            JOIN schedule_teams ON schedule_teams_equipment.equipment_team_id = schedule_teams.team_id
            JOIN equipment_items ON schedule_teams_equipment.equipment_id = equipment_items.item_id
            JOIN equipment_groups ON equipment_items.group_id = equipment_groups.group_id
            LEFT JOIN users as driver ON schedule_teams_equipment.equipment_driver_id = driver.id
            WHERE 
        ";

        if($teamLeaders && is_array($teamLeaders) && count($teamLeaders))
            $sql .= " schedule_teams.team_leader_user_id IN (".$teamLeadersString.") AND ";

        if ($fromDate && $toDate)
            $sql .= " team_date >= $fromDate AND team_date < $toDate AND ";

        $sql .= '1=1 ORDER BY ABS(team_id) ASC, ABS(weight) ASC, type DESC';

        return $this->db->query($sql)->result_array();
    }

	function updateEquipmentsOrder($teamId, $data) {
		$this->db->update_batch('schedule_teams_equipment', $data, 'equipment_id', 100, ["equipment_team_id" => $teamId]);
        return TRUE;
    }

	function updateMembersOrder($teamId, $data) {
		$this->db->update_batch('schedule_teams_members', $data, 'user_id', 100, ["employee_team_id" => $teamId]);
        return TRUE;
    }

    function getEstimatorsForTeams($date, $teamId = NULL)
    {
        /* ------------- subquery ----------*/
        $this->db->select("users.*");

        $this->db->where("FROM_UNIXTIME(schedule.event_start + 3600, '%Y-%m-%d') = ", $date);
        if ($teamId)
            $this->db->where(array('schedule.event_team_id' => $teamId));

        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');

        $this->db->join('users', 'users.id = estimates.user_id', 'left');
        $this->db->group_by('users.id');
        //`team_id` DESC, tleader.team_leader_id DESC
        $query = $this->db->get($this->table);
        return $query->result_array();

    }

    function getTeamsForEstimator($date, $etimatorId = NULL)
    {
        /* ------------- subquery ----------*/
        $this->db->select("schedule_teams.*, crews.*, CONCAT(users.firstname, ' ', users.lastname) as emp_name, users.*, users.id as employee_id", FALSE);

        $this->db->where("FROM_UNIXTIME(schedule.event_start + 3600, '%Y-%m-%d') = ", $date);
        if ($etimatorId)
            $this->db->where(array('estimates.user_id' => $etimatorId));


        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('users', 'users.id = schedule_teams.team_leader_user_id', 'left');
        $this->db->join('crews', 'crews.crew_id = schedule_teams.team_crew_id', 'left');

        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');

        //$this->db->join('users est', 'est.id = estimates.user_id', 'left');
        $this->db->group_by('schedule_teams.team_id');
        //`team_id` DESC, tleader.team_leader_id DESC
        $query = $this->db->get($this->table);
        return $query->result_array();

    }
    
    function find_by_id($id)
    {
        $this->db->select('schedule.*, workorders.*');
        $this->db->join('workorders', 'workorders.id=schedule.event_wo_id', 'left');

        $this->db->where($this->primary_key, $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    function get_followup($where = [], $statusList = [])
    {
        $followUpConfig = $this->config->item('followup_modules')['schedule'];
        $dbStatuses = [];
        foreach ($statusList as $value) {
            if (isset($followUpConfig['statuses'][$value]))
                $dbStatuses[] = $value;
        }

        $this->db->select("schedule.id, TIMEDIFF(FROM_UNIXTIME(schedule.event_start), '" . date('Y-m-d H:i') . "') as datediff,  schedule.event_start  as this_status_date, clients.client_id, users.id as estimator_id, clients_contacts.*, estimates.*", FALSE);

        $this->db->join('workorders', "workorders.id = schedule.event_wo_id");
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        $this->db->join('leads', 'estimates.lead_id = leads.lead_id');
        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
        $this->db->join('users', 'users.id = estimates.user_id', 'left');

        if ($where)
            $this->db->where($where);
        $this->db->where_in('wo_status', $dbStatuses);
        /*
        if(!$every)
            $this->db->having("FROM_UNIXTIME(this_status_date + 3600 + " . (intval($periodicity) * 86400) . ", '%Y-%m-%d') = '" . date('Y-m-d') . "'");
        else
            $this->db->having("datediff % " . intval($periodicity) . " = 0 AND datediff > 0");
        */
        /*$this->db->limit(1);*/
        $this->db->group_by('schedule.id');

        return $this->db->get($this->table)->result_array();
    }

    function get_followup_variables($id)
    {
        $this->load->model('mdl_estimates');
        $this->load->model('mdl_workorders');

        $event = $this->find_by_id($id);
        $wo = $this->mdl_workorders->wo_find_by_id($event->event_wo_id);

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
        $result['PHONE'] = $wo->cc_phone;
        $result['NAME'] = $wo->cc_name;
        $result['TIME'] = $date;
        $result['TIME_AND_DATE'] = $fullDate . ' ' . $date;
        $result['DATE'] = $fullDate;

        $result['NO'] = $wo->workorder_no;
        $result['LEAD_NO'] = $wo->lead_no;
        $result['ESTIMATE_NO'] = $wo->estimate_no;
        $result['INVOICE_NO'] = NULL;
        $result['ESTIMATOR_NAME'] = $wo->firstname . ' ' . $wo->lastname;

        $totalForEstimate = $this->mdl_estimates->get_total_for_estimate($wo->estimate_id);
        $result['AMOUNT'] = money($totalForEstimate['sum']);
        $result['TOTAL_DUE'] = money($this->mdl_estimates->get_total_estimate_balance($wo->estimate_id));
        $result['CCLINK'] = '<a href="' . $this->config->item('payment_link') . 'payments/' . md5($wo->estimate_no . $wo->client_id) . '">link</a>';

        return $result;
    }

    function crews_avg_statistic($where = [])
    {
        $this->db->select('schedule_event_services.*, estimates_services_crews.crew_user_id, estimates_services_crews.crew_service_id, MAX(crew_return_priority) as return_priority', FALSE);
        $this->db->from('schedule_event_services');

        $this->db->join('estimates_services_crews', 'schedule_event_services.service_id = estimates_services_crews.crew_service_id');
        $this->db->join('crews', 'crews.crew_id = estimates_services_crews.crew_user_id');
        $this->db->join('schedule', 'schedule.id = schedule_event_services.event_id');

        if ($where && count($where))
            $this->db->where($where);

        $this->db->group_by('schedule.event_team_id');
        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select("ROUND(SUM(schedule_teams.team_amount) / SUM(schedule_teams.team_man_hours), 2) as avg, ROUND(SUM(schedule_teams.team_amount), 2) as avg_team_amount, ROUND(SUM(schedule_teams.team_man_hours), 2) as avg_team_mhrs,  schedule.id, schedule.event_price, ses.*, crews.*, schedule_teams.*", FALSE);
        $this->db->from('schedule');


        $this->db->join("($subquery) ses", 'schedule.id = ses.event_id');
        $this->db->join('crews', 'crews.crew_return_priority = ses.return_priority');
        $this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id');
        if ($where && count($where))
            $this->db->where($where);
        $this->db->where(['schedule_teams.team_closed' => 1]);
        $this->db->where(['crews.crew_status' => 1]);
        $this->db->group_by('crews.crew_id');
        $this->db->order_by('ses.return_priority', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    function getJobsIncidentForm($date, $userId)
    {
        $this->db->select("schedule.id, workorders.workorder_no, leads.lead_address", FALSE);
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('leads', 'estimates.lead_id = leads.lead_id', 'left');

        $this->db->where('schedule_teams_members.user_id', $userId);
        $this->db->where("FROM_UNIXTIME(schedule.event_start, '%Y-%m-%d') <=", $date);
        $this->db->where("FROM_UNIXTIME(schedule.event_end, '%Y-%m-%d') >=", $date);

        $this->db->order_by('schedule.event_start', 'ASC');
        $this->db->group_by('schedule.id');
        $result = $this->db->get('schedule');

        return $result->result();
    }

    function getAppAgendaEvents($userId, $date, $toDate = null)
    {
        $toDate = $toDate ?: $date;

        $this->db->select('schedule.id, schedule.event_start, FROM_UNIXTIME(schedule.event_start) AS formatted_event_start, schedule.event_end, 
            FROM_UNIXTIME(schedule.event_end) AS formatted_event_end, schedule.event_state, clients.client_name, clients.client_address, clients.client_contact,
            clients_contacts.cc_phone, clients_contacts.cc_phone_clean, clients_contacts.cc_name, clients_contacts.cc_email, users.firstname, users.lastname,  
            schedule_teams.team_leader_user_id, schedule_teams.team_note, schedule.event_note, events.ev_start_time, wo_pdf_files, workorder_no,  
            leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.latitude, leads.longitude, leads.lead_add_info, estimates.estimate_item_note_crew
        ');

        $this->db->join('workorders', 'event_wo_id = workorders.id');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');
        $this->db->join('leads', 'estimates.lead_id = leads.lead_id');
        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id AND cc_print = 1');

        $this->db->join('events', 'events.ev_event_id = schedule.id', 'left');
        $this->db->join('schedule_teams_members', 'schedule.event_team_id = schedule_teams_members.employee_team_id', 'left');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
        $this->db->join('users', 'users.id = estimates.user_id', 'left');
        $this->db->join('users leader', 'leader.id = schedule_teams.team_leader_user_id', 'left');

        $this->db->where('schedule_teams_members.user_id', $userId);
        $this->db->where("FROM_UNIXTIME(schedule_teams.team_date + 3600, '%Y-%m-%d') >= ", $date);
        $this->db->where("FROM_UNIXTIME(schedule_teams.team_date + 3600, '%Y-%m-%d') <= ", $toDate);
        $this->db->group_by('schedule.id');
        $this->db->order_by('schedule.event_start');

        $data['events'] = $this->db->get('schedule')->result();
        $data['waypoints'] = [];

        foreach ($data['events'] as &$event) {
            $event->files = (isset($event->wo_pdf_files) && $event->wo_pdf_files) ? json_decode($event->wo_pdf_files, true) : [];
            unset($event->wo_pdf_files);
            $data['waypoints'][] = $event->latitude ? $event->latitude . ',' . $event->longitude : urlencode($event->lead_address) . ',' . urlencode($event->lead_city) . ',' . urlencode($event->lead_state) . ',' . urlencode($event->lead_zip);
            $event->estimate_services = $this->getAppEventServices($event->id);
            foreach ($event->estimate_services as &$service) {
                if($service->is_bundle) {
                    $service->items = $this->getAppEventBundleServices($service->id);
                }
            }
        }

        return $data;
    }

    function getAppEvent($eventId)
    {
        $this->db->select('schedule.id, schedule.event_start, FROM_UNIXTIME(schedule.event_start) AS formatted_event_start, 
            schedule.event_end, FROM_UNIXTIME(schedule.event_end) AS formatted_event_end, schedule.event_state, schedule.event_wo_id, schedule.event_team_id, clients.client_name, clients.client_id,
            clients.client_address, clients.client_contact, clients_contacts.cc_phone, clients_contacts.cc_phone_clean, clients_contacts.cc_name, clients_contacts.cc_email,
            users.firstname, users.lastname, schedule_teams.team_leader_user_id, schedule_teams.team_note, schedule.event_note, events.ev_start_time, events.ev_start_travel, wo_pdf_files, workorder_no, 
            TRIM(TRAILING "\r\n" FROM workorders.wo_office_notes) as wo_office_notes, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.latitude, leads.longitude, leads.lead_id, leads.lead_add_info, TRIM(TRAILING "\r\n" FROM estimates.estimate_crew_notes) as estimate_crew_notes, estimates.estimate_id
        ');

        $this->db->join('workorders', 'event_wo_id = workorders.id');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');
        $this->db->join('leads', 'estimates.lead_id = leads.lead_id');
        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('clients_contacts', 'clients_contacts.cc_client_id = clients.client_id AND cc_print = 1');
        $this->db->join('events', 'events.ev_event_id = schedule.id', 'left');
        $this->db->join('schedule_teams_members', 'schedule.event_team_id = schedule_teams_members.employee_team_id', 'left');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
        $this->db->join('users', 'users.id = estimates.user_id', 'left');
        $this->db->join('users leader', 'leader.id = schedule_teams.team_leader_user_id', 'left');

        $this->db->where('schedule.id', $eventId);
        $this->db->group_by('schedule.id');

        $event = $this->db->get('schedule')->row();

        if (!$event)
            return FALSE;

        $event->files = (isset($event->wo_pdf_files) && $event->wo_pdf_files) ? json_decode($event->wo_pdf_files, true) : [];

        // add tree inventory map
        $treeInventoryMapPath = inventory_screen_path($event->client_id, $event->lead_id . '_tree_inventory_map.png');
        if(is_bucket_file($treeInventoryMapPath))
            array_unshift($event->files, $treeInventoryMapPath);
        $treeInventoryMapPath = inventory_screen_path($event->client_id, $event->lead_id . '.png');
        if(is_bucket_file($treeInventoryMapPath))
            array_unshift($event->files, $treeInventoryMapPath);

        unset($event->wo_pdf_files);
        $data['waypoints'][] = $event->latitude ? $event->latitude . ',' . $event->longitude : urlencode($event->lead_address) . ',' . urlencode($event->lead_city) . ',' . urlencode($event->lead_state) . ',' . urlencode($event->lead_zip);
        $event->estimate_services = $this->getAppEventServices($event->id);
        foreach ($event->estimate_services as &$service) {
            if($service->is_bundle) {
                $service->items = $this->getAppEventBundleServices($service->id);
            }
            else{
                $service->tree_inventory = TreeInventoryEstimateService::where('ties_estimate_service_id', $service->id)->with(['tree'])->first();
                if(!empty($service->tree_inventory)){
                    $tree_inventory = $service->tree_inventory;
                    $service->job_estimate_service_ti_title = $service->estimate_service_ti_title;
                    $service->job_service_description = $service->service_description;
                    if(!empty($tree_inventory->ties_priority) && !empty($service->estimate_service_ti_title))
                        $service->job_estimate_service_ti_title .=  ', Priority: ' . $tree_inventory->ties_priority;
                    $workTypes = TreeInventoryEstimateServiceWorkTypes::where('tieswt_ties_id', $tree_inventory->ties_id)->with('work_type')->get()->pluck('work_type')->pluck('ip_name')->toArray();
                    if(!empty($workTypes)  && is_array($workTypes)) {
                        $workTypesDescription = 'Work Types: ' . implode(', ', $workTypes) . '<br>';
                        $service->job_service_description = $workTypesDescription . $service->service_description;
                    }
                }
            }
        }

        return $event;
    }

    public function getAppEventServices($eventId)
    {
        $this->db->select('estimates_services.id, estimates_services.service_description, estimates_services.service_time, estimates_services.service_travel_time, estimates_services.service_disposal_time, estimates_services.quantity, services.service_name, services.is_bundle, services.is_product, estimates_services.estimate_service_ti_title');
        $this->db->join('estimates_services', 'estimates_services.id = schedule_event_services.service_id', 'left');
        $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->db->where('schedule_event_services.event_id', $eventId);
        /*$this->db->where('estimates_services.service_status', 0);*/
        return $this->db->get('schedule_event_services')->result();
    }

    public function getAppEventBundleServices($bundleServiceId)
    {
        $this->db->select('estimates_services.id, estimates_services.service_description, estimates_services.service_time, estimates_services.service_travel_time, estimates_services.service_disposal_time, estimates_services.quantity, services.service_name, services.is_bundle, services.is_product, estimates_services.estimate_service_ti_title');
        $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->db->join('estimates_bundles', 'estimates_services.id = estimates_bundles.eb_service_id');
        $this->db->where('estimates_bundles.eb_bundle_id', $bundleServiceId);
        $this->db->where('estimates_services.service_status', 0);
        return $this->db->get('estimates_services')->result();
    }

    function getTeamForDashboard($userId, $date)
    {
        $this->db->select('schedule_teams.team_note, schedule_teams.team_leader_user_id, schedule_teams.team_id');
        $this->db->join('schedule_teams_members', 'schedule_teams_members.employee_team_id = schedule_teams.team_id');
        $this->db->where([
            'schedule_teams_members.user_id' => $userId,
            'team_date' => strtotime($date)
        ]);
        $result = $this->db->get('schedule_teams')->row();
        if($result){
            $teamId = $result->team_id;
            unset($result->team_id);
        }
        return ['team' => $result, 'teamId' => $teamId ?? null];
    }

    function getEquipmentForDashboard($teamId, $date)
    {
        $this->db->select('equipment.eq_name as name');
        $this->db->join('schedule_teams_equipment', 'schedule_teams_equipment.equipment_id = equipment.eq_id');
        $this->db->join('schedule_teams', 'schedule_teams_equipment.equipment_team_id = schedule_teams.team_id');
        $this->db->where([
            'schedule_teams.team_id' => $teamId,
            'team_date' => strtotime($date)
        ]);
        $this->db->order_by('ABS(schedule_teams_equipment.weight)', 'ASC');
        $result = $this->db->get('equipment')->result();
        return $result;
    }

    function getMembersForDashboard($teamId)
    {
        $this->db->select("users.id, CONCAT((users.firstname), (' '), (users.lastname))  as name, users.picture");
        $this->db->join('users', 'schedule_teams_members.user_id = users.id');
        $this->db->join('employees', 'employees.emp_user_id = users.id');
        $this->db->where([
            'schedule_teams_members.employee_team_id' => $teamId
        ]);
        $this->db->order_by('ABS(weight)', 'ASC');
        $result = $this->db->get('schedule_teams_members')->result();
        return $result;
    }

    function getToolsForDashboard($teamId)
    {
        $this->db->select('equipment.eq_name as name');
        $this->db->join('equipment', 'equipment.eq_id = schedule_teams_tools.stt_item_id');
        $this->db->join('equipment_groups', 'equipment.group_id = equipment_groups.group_id');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule_teams_tools.stt_team_id');
        $this->db->where([
            'schedule_teams.team_id' => $teamId
        ]);
        $result = $this->db->get($this->table10)->result();
        return $result;
    }
    function getSuggestedToolsForDashboard($teamId)
    {
        $this->db->select('equipment_tools_option');

        $this->db->join('schedule', 'schedule_teams.team_id = schedule.event_team_id');
        $this->db->join('schedule_event_services', 'schedule.id = schedule_event_services.event_id');
        $this->db->join('estimates_services_equipments', 'schedule_event_services.service_id = estimates_services_equipments.equipment_service_id');
        $this->db->where([
            'schedule_teams.team_id' => $teamId,
            'equipment_tools_option IS NOT NULL' => null
        ]);
        $rows = $this->db->get($this->table3)->result();
        $result = $uniqueTools = [];

        if(!empty($rows)) {
            foreach ($rows as $row) {
                $items = $row->equipment_tools_option ? json_decode($row->equipment_tools_option, true) : [];
                foreach ($items as $tools) {
                    foreach ($tools as $tool) {
                        if(!in_array($tool, $uniqueTools)) {
                            $uniqueTools[] = $tool;
                            $result[] = ['name' => $tool];
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param $data
     * @return array|null
     */
    public function getEventMemberInTeam($data)
    {
        return $this->db
            ->select('s.event_team_id as team_id, st.team_leader_user_id as teamlead_id, stm.user_id as member_id')
            ->join('schedule_teams as st', 'st.team_id=s.event_team_id AND st.team_leader_user_id=' . (int) $data['loggedUser'])
            ->join('schedule_teams_members as stm', 'stm.employee_team_id=s.event_team_id')
            ->where('s.event_start BETWEEN ' . $data['teamWorkDayStart'] . ' AND ' . $data['teamWorkDayEnd'])
            ->where('stm.user_id', $data['teamMemberId'])
            ->get('schedule as s')
            ->row_array();
    }


    /**
     * @param $team_id
     * @param $user_id
     * @return array|null
     */
    /*
    public function isTeamLead($team_id, $user_id): ?array
    {
        return $this->db
            ->select('team_leader_user_id')
            ->where('team_id', $team_id)
            ->where('team_leader_user_id', $user_id)
            ->get('schedule_teams')
            ->row_array();
    }
    */
}

//end of file user_model.php
