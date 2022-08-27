<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Mdl_workorders extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'workorders';
		$this->primary_key = "workorders.id";
        $this->load->model('mdl_estimates_orm');
        $this->load->model('mdl_clients');
	}

//*******************************************************************************************************************
//*************
//*************													Insert Workorders Function; Returns insert id or false; 
//*************
//*******************************************************************************************************************	

	function insert_workorders($data)
	{

		if ($data) {

			$insert = $this->db->insert($this->table, $data);

			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}

//*********************************************************************************************************************
//*************
//*************															Get Workorder Function; Returns row() or false; 
//*************
//**********************************************************************************************************************	

	function get_workorders($search_keyword, $status=null, $limit, $start, $wdata = array())
	{
		// MK changed the query to pull dinamic data from the tables.
		$subWhere = 'workorders.id IS NOT NULL';
        $this->load->model('mdl_estimates_orm');
        if(is_array($wdata)) {
            foreach ($wdata as $key => $val) {
                $subWhere .= " AND " . $key . " = '" . $val ."' ";
            }
        } elseif(is_string($wdata)) {
            $subWhere .= ' AND ' . $wdata;
        }
        if($status != "" && $status !== null)
		{
		    $subWhere .= ' AND workorders.wo_status = ' . $status;
		}

		$extraJoin[0]['table'] =  'clients';
		$extraJoin[0]['condition'] = 'clients.client_id = estimates.client_id';
		$extraJoin[1]['table'] = 'leads';
		$extraJoin[1]['condition'] = 'leads.lead_id = estimates.lead_id';

        if (isset($search_keyword) && $search_keyword != "") {

            $subWhere .= " AND (clients.client_name LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_name LIKE '%" . $search_keyword . "%'
								OR clients.client_address LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_email LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_phone LIKE '%" . $search_keyword . "%'
								OR leads.lead_address LIKE '%" . $search_keyword . "%'
								OR workorders.workorder_no LIKE '%" . $search_keyword . "%')";
            $extraJoin[2]['table'] =  'clients_contacts';
            $extraJoin[2]['condition'] = 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1';

        }
        $extraJoin[3]['table'] = 'client_tags';
        $extraJoin[3]['condition'] = 'clients.client_id = client_tags.client_id';
        $extraJoin[3]['type'] = 'left';

        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($subWhere, $extraJoin);
		$sql_query = "SELECT 					clients.client_id,
												clients.client_name,
												clients.client_contact,
												clients.client_address,
												clients.client_city,
												clients.client_country,
												clients.client_zip,
												clients_contacts.cc_phone,
												
												estimates.estimate_no,
												estimates_crews.estimate_crew_id,
												
												leads.lead_id,
												leads.lead_address as client_address,
												leads.lead_city as client_city,
												leads.lead_state as client_state,
												leads.lead_zip as client_zip,
												leads.latitude as lat,
												leads.longitude as lon,
												
												workorders.id,
												workorders.workorder_no,
												workorders.estimate_id,
												workorders.date_created,
												workorders.wo_status,
												workorders.wo_priority,
												workorders.wo_estimator,
												workorders.wo_office_notes,
                                                
												users.firstname,
												users.lastname,
												users.id as estimator_id,
												users.emailid,
												
												workorder_status.wo_status_name,
												workorder_status.wo_status_color,
												
												/***УМНОЖЕНИЕ НА КОЛИЧЕСТВО ТЕГОВ***/
												totals.sum_without_tax / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as total,
                                                ROUND(SUM(estimates_services.service_time + estimates_services.service_travel_time +
                                                         estimates_services.service_disposal_time) / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1), 2) as total_time,
                                                totals.sum_taxable / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as sum_taxable,
                                                totals.sum_non_taxable / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as sum_non_taxable,
                                                totals.sum_for_services / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as sum_for_services,
                                                totals.sum_services_without_discount / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as sum_services_without_discount,
                                                totals.sum_without_tax / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as sum_without_tax,
                                                totals.total_tax / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as total_tax,
                                                totals.total_with_tax / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as total_with_tax,
                                                totals.total_due / IF(COUNT(DISTINCT client_tags.tag_id), COUNT(DISTINCT client_tags.tag_id), 1) as total_due

									FROM 		clients


									LEFT JOIN 	clients_contacts

									ON 			clients.client_id = clients_contacts.cc_client_id/* AND clients_contacts.cc_print = 1*/
									
									LEFT JOIN   client_tags
									ON          clients.client_id = client_tags.client_id 
									
									INNER JOIN 	leads
									
									ON 			clients.client_id=leads.client_id
									
									INNER JOIN 	estimates
									
									ON 			leads.lead_id=estimates.lead_id 
												
									INNER JOIN 	workorders
									
									ON 			estimates.estimate_id=workorders.estimate_id
									
									INNER JOIN 	estimates_services
									
									ON			estimates_services.estimate_id=workorders.estimate_id
									
									LEFT JOIN 	estimates_crews
									
									ON			estimates.estimate_id=estimates_crews.estimate_id
											
									INNER JOIN workorder_status
									
									ON			workorders.wo_status=workorder_status.wo_status_id

									LEFT JOIN users

									ON			estimates.user_id=users.id
									
									LEFT JOIN ($totalsSubQuery) totals ON estimates.estimate_id = totals.estimate_id
									
															
									WHERE 1=1 ";

		if (isset($search_keyword) && $search_keyword != "") {
			$sql_query .= " AND (clients.client_name LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_name LIKE '%" . $search_keyword . "%'
								OR clients.client_address LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_email LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_phone LIKE '%" . $search_keyword . "%'
								OR leads.lead_address LIKE '%" . $search_keyword . "%'
								OR workorders.workorder_no LIKE '%" . $search_keyword . "%')";
		}

		if (isset($status) && $status != "") {
			//AND workorder_status.wo_status_name
			$sql_query .= " AND workorder_status.wo_status_id = '" . $status . "' AND workorders.wo_status = '" . $status . "'";
		}

        if(is_cl_permission_owner()) {
            $sql_query .= " AND estimates.user_id = " . request()->user()->id;
        }

        if(is_cl_permission_none()) {
            $sql_query .= " AND estimates.user_id = -1";
        }

		if($wdata || !empty($wdata))
		{
			$sql_query .= " AND ";
			if(is_array($wdata))
				foreach($wdata as $key => $val)
					$sql_query .= $key . "='".$val."' ";
			else
				$sql_query .= $wdata;
		}

		$sql_query .= ' GROUP BY estimates_services.estimate_id';
		$sql_query .= ' ORDER BY workorders.date_created ASC';

		if ($limit != '') {
			$sql_query .= " LIMIT " . $limit;
		}

		if ($start != '') {
			$sql_query .= ", " . $start;
		}

		$query = $this->db->query($sql_query);

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/

	} // End get_leads();

//*******************************************************************************************************************
//*************
//*************								Count workorders function
//*************
//*******************************************************************************************************************	

	function workorders_record_count($search_keyword, $status)
	{
		$this->db->select('clients.client_id');
		$this->db->from('clients');
		$this->db->join('leads', 'clients.client_id=leads.client_id');
		$this->db->join('estimates', 'leads.lead_id=estimates.lead_id');
		$this->db->join('workorders', 'estimates.estimate_id=workorders.estimate_id');
		$this->db->join('workorder_status', 'workorders.wo_status=workorder_status.wo_status_id');
		$this->db->join('users', 'estimates.user_id=users.id');

		if($status)
			$this->db->where('workorders.wo_status', $status);

		if($search_keyword)
		{
			$this->db->like('clients.client_name', $search_keyword);
			$this->db->or_like('clients.client_contact', $search_keyword);
			$this->db->or_like('clients.client_address', $search_keyword);
			$this->db->or_like('workorders.workorder_no', $search_keyword);
		}

		return $this->db->count_all_results();
	}

//*******************************************************************************************************************
//*************
//*************																				Update workorder function
//*************
//*******************************************************************************************************************	

	function update_workorder($data, $wdata)
	{
		if (!$data && !$wdata)
			return false;

		$this->db->where($wdata);
		$update = $this->db->update($this->table, $data);
		
		if ($this->db->affected_rows() > 0)
			return TRUE;
		
		return FALSE;
	} // End. update_estimates ();

//*********************************************************************************************************************
//*************
//*************															Get_client_Workorders; Returns row(); or FALSE;
//*************
//*********************************************************************************************************************

	function get_client_workorders($id)
	{

		$query = $this->db->query("	SELECT 		workorders.workorder_no,
												workorders.date_created,
												workorders.wo_status,
												workorders.id,
												workorders.estimate_id,
												workorder_status.*,
												CONCAT(users.firstname, ' ', users.lastname) as emp_name
												
									FROM 		workorders
									 
									INNER JOIN 	estimates
									
									ON 			workorders.estimate_id=estimates.estimate_id
									
									LEFT JOIN 	users
									
									ON 			estimates.user_id=users.id
									
									INNER JOIN 	clients
									
									ON 			workorders.client_id=clients.client_id

									INNER JOIN workorder_status

									ON 			workorders.wo_status = workorder_status.wo_status_id
									
									WHERE 		workorders.client_id = $id ");

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/

	} //* end get_client_workorder($id)
	
	function get_client_workorders_app($id) {
		$this->db->select('workorders.id, workorders.wo_status, workorders.workorder_no, CONCAT(users.firstname, " ", users.lastname) as emp_name, workorders.date_created, workorder_status.wo_status_name, workorder_status.wo_status_id, estimates.estimate_id', FALSE);
		$this->db->from('workorders');
		$this->db->join('estimates', 'workorders.estimate_id=estimates.estimate_id');
        $this->db->join('leads', 'leads.lead_id=estimates.lead_id');
		$this->db->join('users', 'estimates.user_id=users.id');
		$this->db->join('workorder_status', 'workorders.wo_status = workorder_status.wo_status_id');
		$this->db->where('workorders.client_id', $id);
		$query = $this->db->get();
		return $query;
	}

	function delete_workorder($id)
	{
		if (!$id)
			return false;

		$this->db->where('estimate_id', $id);
		$this->db->delete($this->table);

		if ($this->db->affected_rows() > 0)
			return true;
		
		return false;
	}

	function get_workorder_workers($wdata, $order = 'id DESC')
	{
		$this->db->where($wdata);
		$this->db->join('employees', 'employees.employee_id = workorder_employees.employee_id');
		$this->db->order_by($order);
		return $this->db->get('workorder_employees')->result_array();
	}

	function insert_workorder_workers($data)
	{
		$this->db->insert('workorder_employees', $data);
		return $this->db->insert_id();
	}

	function delete_workorder_workers($wdata)
	{
		$this->db->where($wdata);
		$this->db->delete('workorder_employees');
		return TRUE;
	}

	function get_all_statuses($where = null, $limit = null)
	{
		if($where)
			$this->db->where($where);
        $this->db->order_by('wo_status_active', 'DESC');
        $this->db->order_by('wo_status_priority');
		$query = $this->db->get('workorder_status');
		
		if($limit)
			return $query->row_array();
		
		return $query->result_array();
	}
	
	function getDefaultStatusId(){
		$statusDefault = 1;
		$status = $this->get_all_statuses(['is_default'=>1], true);
    	if(is_array($status) && count($status) && isset($status['wo_status_id']))
        	$statusDefault = $status['wo_status_id'];

        return $statusDefault;
	}

	function getFinishedStatusId(){
		$statusFinished = 0;
		$status = $this->get_all_statuses(['is_finished'=>1], true);
    	if(is_array($status) && count($status) && isset($status['wo_status_id']))
        	$statusFinished = $status['wo_status_id'];

        return $statusFinished;
	}

	function getConfirmByClientId(){
		$statusConfirmByClient = $this->getDefaultStatusId();
		$status = $this->get_all_statuses(['is_confirm_by_client'=>1], true);
    	if(is_array($status) && count($status) && isset($status['wo_status_id']))
        	$statusConfirmByClient = $status['wo_status_id'];

        return $statusConfirmByClient;
	}

	function getFinishedByField(){
		$statusFinishedByField = FALSE;
		$status = $this->get_all_statuses(['is_finished_by_field'=>1], true);
    	if(is_array($status) && count($status) && isset($status['wo_status_id']))
        	$statusFinishedByField = $status['wo_status_id'];

        return $statusFinishedByField;
	}

	function getDeleteInvoiceStatusId(){
		$statusDeleteInvoice = 15;
		$status = $this->get_all_statuses(['is_delete_invoice'=>1], true);
    	if(is_array($status) && count($status) && isset($status['wo_status_id']))
        	$statusDeleteInvoice = $status['wo_status_id'];

        return $statusDeleteInvoice;
	}

	function getPendingStatus() {
		$status = $this->get_all_statuses(['is_scheduled_pending'=>1], true);
        return $status?:false;
	}


	function wo_find_by_id($id, $permissions = true)
	{
		$totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['workorders.id'=>$id]);
		$clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();

		$this->db->select('workorder_status.*, workorders.*, users.emailid, users.firstname, users.lastname, users.id as user_id, estimates.*, leads.*, clients_contacts.*, clients.*, totals.*');
		$this->db->from('workorders');
		$this->db->join('workorder_status', 'workorder_status.wo_status_id = workorders.wo_status', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('leads', 'leads.lead_id = estimates.lead_id', 'left');
		$this->db->join('users', 'estimates.user_id = users.id', 'left');
		$this->db->join('clients', 'estimates.client_id=clients.client_id', 'left');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
		$this->db->join("($totalsSubQuery) totals", 'estimates.estimate_id = totals.estimate_id', 'left');

		if(is_cl_permission_owner() && $permissions && $clientPermissionsSubQuery) {
            $this->db->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = workorders.client_id', 'left');
            $this->db->where('perm.client_id IS NOT NULL');
        }

		$this->db->where('workorders.id', $id);
		$query = $this->db->get();
		return $query->row();
	}


	function wo_find_by_lead_id($id)
	{
		$totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.lead_id'=>$id]);
        $clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();

		$this->db->select('workorder_status.*, workorders.*, users.emailid, totals.*, estimates.estimate_crew_notes');
		$this->db->from('workorders');
		$this->db->join('workorder_status', 'workorder_status.wo_status_id = workorders.wo_status', 'left');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('leads', 'leads.lead_id = estimates.lead_id', 'left');
        $this->db->join('clients', 'estimates.client_id=clients.client_id', 'left');
		$this->db->join('users', 'estimates.user_id = users.id', 'left');
		$this->db->join("($totalsSubQuery) totals", 'estimates.estimate_id = totals.estimate_id', 'left');

        if(is_cl_permission_owner() && $clientPermissionsSubQuery) {
            $this->db->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = workorders.client_id', 'left');
            $this->db->where('perm.client_id IS NOT NULL');
        } elseif (is_cl_permission_none()) {
            $this->db->where('estimates.user_id', -1);
        }

        $this->db->where('estimates.lead_id', $id);
		$query = $this->db->get();
		return $query->row();
	}

	function update_priority($updateBatch)
	{
		$this->db->update_batch('workorder_status', $updateBatch, 'wo_status_id');
		return TRUE;
	}

	function get_wo_status_log($where = array(), $limit = null)
	{
		$this->db->from('status_log');
		$this->db->join('workorder_status', 'workorder_status.wo_status_id = status_log.status_value', 'left');
		if($where && !empty($where))
			$this->db->where($where);
		$this->db->order_by('status_date');
		$query = $this->db->get();
		
		if($limit)
			return $query->row_array();
		
		return $query->result_array();
	}

	function get_scheduled_workorders_by_status_date($where = [])
	{
		$this->db->select("MAX(status_log.status_id) as max_status_id, COUNT(DISTINCT multi_finished.status_id) as count_other_finished, GROUP_CONCAT(DISTINCT FROM_UNIXTIME(multi_finished.status_date, '%Y-%m-%d') ORDER BY multi_finished.status_date ASC SEPARATOR ', ') AS other_finished_dates, estimates_services.service_price as scheduled_price, (estimates_services.service_time + estimates_services.service_travel_time + estimates_services.service_disposal_time) * IF(COUNT(DISTINCT estimates_services_crews.crew_id), COUNT(DISTINCT estimates_services_crews.crew_id), 1) as scheduled_time, schedule.event_wo_id, status_log.status_id", FALSE);
		$this->db->from('schedule');
		$this->db->join('workorders', 'schedule.event_wo_id = workorders.id');
		$this->db->join('status_log', 'workorders.id = status_log.status_item_id');
		$this->db->join('estimates', 'workorders.estimate_id = estimates.estimate_id');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
		$this->db->join('schedule_event_services', 'schedule_event_services.event_id = schedule.id');
		$this->db->join('estimates_services', 'schedule_event_services.service_id = estimates_services.id');
		$this->db->join('estimates_services_status', 'estimates_services.service_status = estimates_services_status.services_status_id AND estimates_services_status.services_status_id = 2');
		$this->db->join('estimates_services_crews', 'estimates_services_crews.crew_service_id = estimates_services.id', 'left');
		$this->db->join('status_log multi_finished', "status_log.status_type = multi_finished.status_type AND status_log.status_item_id = multi_finished.status_item_id AND status_log.status_value = multi_finished.status_value AND status_log.status_id <> multi_finished.status_id  AND FROM_UNIXTIME(status_log.status_date, '%Y-%m-%d') <> FROM_UNIXTIME(multi_finished.status_date, '%Y-%m-%d')", 'left');
		$this->db->where('status_log.status_type', 'workorder');
		$this->db->where_not_in('estimates_services.service_id', [2, 3, 25, 28, 30, 26, 29, 27, 34, 31, 32, 33, 39]);//TODO: check it where not in !!!
		if($where && !empty($where))
			$this->db->where($where);
		$this->db->group_by('estimates_services.id');
		$subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		$this->db->select("SUM(event_damage) as event_damage, event_wo_id", FALSE);
		$this->db->from('schedule');
        $this->db->join('workorders', 'schedule.event_wo_id = workorders.id');
        $this->db->join('estimates', 'workorders.estimate_id = estimates.estimate_id');
        if(isset($where['user_id']))
            $this->db->where('user_id', $where['user_id']);
        if(isset($where['workorders.wo_status']))
            $this->db->where('workorders.wo_status', $where['workorders.wo_status']);
		$this->db->group_by('schedule.event_wo_id');
		$subquery2 = $this->db->_compile_select();
		$this->db->_reset_select();
		
		
		
		$this->db->select("scheduled_services_prices.count_other_finished, 
			scheduled_services_prices.other_finished_dates, 
			IF(
				estimate_hst_disabled <> 2,
				ROUND(SUM(scheduled_services_prices.scheduled_price) - IF(discount_percents, SUM(scheduled_services_prices.scheduled_price) * IFNULL(discounts.discount_amount, 0) / 100, IFNULL(discounts.discount_amount, 0)) - event_damage, 2), 
				ROUND((SUM(scheduled_services_prices.scheduled_price) - IF(discount_percents, SUM(scheduled_services_prices.scheduled_price) * IFNULL(discounts.discount_amount, 0) / 100, IFNULL(discounts.discount_amount, 0))) / " . config_item('tax_rate') . " - event_damage, 2)
			) as scheduled_wo_price, 
			ROUND(SUM(scheduled_services_prices.scheduled_time), 2) as scheduled_wo_time, 
			discounts.discount_amount, discounts.discount_percents, workorders.*, estimates.*, leads.*, status_log.*", FALSE);
		$this->db->from('status_log');
		$this->db->join('workorders', 'workorders.id = status_log.status_item_id');
		$this->db->join("($subquery) scheduled_services_prices", 'scheduled_services_prices.event_wo_id = workorders.id AND status_log.status_id = scheduled_services_prices.max_status_id');
		$this->db->join('estimates', 'workorders.estimate_id = estimates.estimate_id');
		$this->db->join('discounts', 'estimates.estimate_id = discounts.estimate_id', 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
		$this->db->join("($subquery2) sch", 'sch.event_wo_id = workorders.id');
		
		$this->db->where('status_log.status_type', 'workorder');
		
		$this->db->group_by('workorders.id');
		if($where && !empty($where))
			$this->db->where($where);
		$this->db->order_by('status_log.status_date');
		$query = $this->db->get();
		
		return $query->result_array();
	}
	
	function get_status_date($id, $type='workorder', $limit = 1)
	{
		$this->db->select('status_date');
		$this->db->from('status_log');
		$this->db->where('status_item_id', $id);
		$this->db->where('status_type', $type);
		$this->db->limit($limit);
		$this->db->order_by('status_date', 'DESC');
		$query = $this->db->get();
		return $result = $query->row_array();
	}

	function estimators_mhr_rate($wdata = [], $wQueryData = [], $oneRow = FALSE)
	{
        $this->db->select('users.id, team_id, team_amount, team_date_start as team_date, ROUND(team_amount/team_man_hours, 2) as team_mhrs_return, ROUND(team_man_hours, 2) as total_mhrs, ROUND(SUM(schedule.event_end - schedule.event_start) / 3600 / COUNT(distinct schedule_teams_members.user_id) / COUNT(distinct other_schedule.id), 2) as worked_at_schedule, schedule_teams_members.user_id as emp_user_id, ROUND(SUM((other_schedule.event_end - other_schedule.event_start) / 3600) / COUNT(distinct schedule_teams_members.user_id) / COUNT(distinct schedule.id), 2) as hours_at_schedule, ROUND((SUM(schedule.event_end - schedule.event_start) / 3600 / COUNT(distinct schedule_teams_members.user_id) / COUNT(distinct other_schedule.id)) / (SUM((other_schedule.event_end - other_schedule.event_start) / 3600)  / COUNT(distinct schedule_teams_members.user_id)), 2) as perc, ROUND(team_man_hours * ((SUM(schedule.event_end - schedule.event_start) / 3600 / COUNT(distinct schedule_teams_members.user_id) / COUNT(distinct other_schedule.id)) / (SUM((other_schedule.event_end - other_schedule.event_start) / 3600)  / COUNT(distinct schedule_teams_members.user_id))), 2) as mhrs_user', FALSE);
        $this->db->from('schedule_teams');
        $this->db->join('schedule', 'event_team_id = team_id');
        $this->db->join('workorders', 'event_wo_id = workorders.id');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');
        $this->db->join('users', 'estimates.user_id = users.id');
        $this->db->join('schedule_teams_members', 'employee_team_id = team_id');
        $this->db->join('schedule other_schedule', 'team_id = other_schedule.event_team_id');

        $this->db->join('users emp_users', 'schedule_teams_members.user_id = emp_users.id');
        $this->db->join('employee_worked', "emp_users.id = worked_user_id AND worked_date = team_date_start", 'left');
        
        $this->db->where('team_closed', 1);
        if($wdata && !empty($wdata))
        	$this->db->where($wdata);
        $this->db->group_by('team_id, users.id');

		$subquery = $this->db->_compile_select();
		$this->db->_reset_select();

		/*---------------------Damage-----------------------*/
		$wDamageData = $wdata;
		if(isset($wDamageData['worked_date >='])) {
			$wDamageData['team_date_start >='] = date("Y-m-d", strtotime($wDamageData['worked_date >=']));
			unset($wDamageData['worked_date >=']);
		}
		if(isset($wDamageData['worked_date <='])) {
			$wDamageData['team_date_start <='] = date("Y-m-d", strtotime($wDamageData['worked_date <='] . ' 22:59:59'));
			unset($wDamageData['worked_date <=']);
		}
		if(isset($wDamageData['users.id'])) {
			unset($wDamageData['users.id']);
		}
		$damage_subquery = $this->get_estimators_damage_subquery($wDamageData);
		$this->db->_reset_select();
		/*--------------------------------------------------*/

		/*---------------------Complain-----------------------*/
		$complain_subquery = $this->get_estimators_complain_subquery($wDamageData);
		$this->db->_reset_select();
		/*--------------------------------------------------*/

		$this->db->select('ROUND(SUM(event_price), 2) as total, SUM(mhrs_user) as total_mhrs, ROUND(SUM(event_price) / SUM(mhrs_user), 2) as mhrs_return, COUNT(teams_total.team_id) as count_teams, users.firstname, users.lastname, users.id, IFNULL(damage.damage_sum, 0) as damage_sum, damage.damage_count, IFNULL(complain.complain_sum, 0) as complain_sum, complain.complain_count', FALSE);
		
		$this->db->from('users');
        $this->db->join('estimates', 'estimates.user_id = users.id');
        $this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id');

        $this->db->join('schedule', 'workorders.id = schedule.event_wo_id');

        $this->db->join("($subquery) teams_total", 'schedule.event_team_id = teams_total.team_id AND users.id = teams_total.id');

        $this->db->join("($damage_subquery) damage", 'estimates.user_id = damage.e_user_id', 'left');
        $this->db->join("($complain_subquery) complain", 'estimates.user_id = complain.e_user_id', 'left');

        $this->db->where($wQueryData);
		$this->db->group_by('users.id');
		$this->db->order_by('mhrs_return', 'DESC');
		$query = $this->db->get();

		if($oneRow)
			return $query->row_array();

		return $query->result_array();
	}

	function employees_mhr_return($wdata = [], $wQueryData = [], $oneRow = FALSE, $order_field=['mhrs_return2', 'DESC'])
	{
		
		$this->db->select('team_id, team_amount, IF(team_closed, ROUND((team_amount/schedule_teams.team_man_hours), 2), 0) as mhrs_return, IF(team_closed, ROUND((team_amount/(SUM(worked_hours) - SUM(IFNULL(worked_lunch, 0)))), 2), 0) as mhrs_return_login, IF(team_closed, ROUND(team_amount/COUNT(distinct schedule_teams_members.user_id), 2), 0) as total, ROUND(schedule_teams.team_man_hours, 2) as team_hours, ROUND(SUM(worked_hours) - SUM(IFNULL(worked_lunch, 0)), 2) as team_hours_login, schedule_teams.team_closed as isclosed, ROUND(schedule_teams.team_man_hours / COUNT(schedule_teams_members.user_id), 2) as personal_in_team_hours, COUNT(distinct schedule_teams_members.user_id) as count_members', FALSE);
        $this->db->from('schedule_teams');

        $this->db->join('schedule_teams_members', 'employee_team_id = team_id');
        $this->db->join('employee_worked', "user_id = worked_user_id AND worked_date = team_date_start", "left");
        $this->db->join('users', "users.id = schedule_teams_members.user_id");
        
        //$this->db->where('team_closed', 1);
        
        if($wdata && !empty($wdata))
        	$this->db->where($wdata);
        $this->db->group_by('team_id');
        $subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		

		/*---------------------Damage-----------------------*/
        $damage_subquery = $this->get_damage_subquery($wdata);
		$this->db->_reset_select();
		/*--------------------------------------------------*/

		/*---------------------Complain-----------------------*/
		$complain_subquery = $this->get_complain_subquery($wdata);
		$this->db->_reset_select();
		/*--------------------------------------------------*/

		$this->db->select("CONCAT(users.firstname, ' ', users.lastname) as emp_name, users.id,
			ROUND((SUM(IF(teams_total.isclosed, (schedule_teams.team_man_hours)/teams_total.team_hours*teams_total.team_amount, 0))-IFNULL(damage.damage_sum, 0))/SUM(IF(teams_total.isclosed, schedule_teams.team_man_hours, 0)), 2) as new_avg,
			ROUND(SUM(IF(teams_total.isclosed, (schedule_teams.team_man_hours)/teams_total.team_hours*teams_total.team_amount, 0)) / (SUM(IF(teams_total.isclosed, schedule_teams.team_man_hours, 0))), 2) as mhrs_return2,
			ROUND(SUM(IF(teams_total.isclosed, (worked_hours - IFNULL(worked_lunch, 0))/teams_total.team_hours_login*teams_total.team_amount, 0)) / (SUM(IF(teams_total.isclosed, worked_hours - IFNULL(worked_lunch, 0), 0))), 2) as mhrs_return2_login, 
			IFNULL(ROUND(SUM(teams_total.mhrs_return) / SUM(teams_total.isclosed), 2), 0) as mhrs_return, 
			IFNULL(ROUND(SUM(teams_total.mhrs_return_login) / SUM(teams_total.isclosed), 2), 0) as mhrs_return_login, 
			ROUND((IF(teams_total.isclosed, SUM((schedule_teams.team_man_hours)/teams_total.team_hours*teams_total.team_amount), 0)/schedule_teams.team_man_hours + (SUM(teams_total.mhrs_return) / SUM(teams_total.isclosed))) / 2, 2) as avg_mhrs_return, 
			ROUND((IF(teams_total.isclosed, SUM((worked_hours - IFNULL(worked_lunch, 0))/teams_total.team_hours_login*teams_total.team_amount), 0)/(SUM(worked_hours) - SUM(IFNULL(worked_lunch, 0))) + (SUM(teams_total.mhrs_return_login) / SUM(teams_total.isclosed))) / 2, 2) as avg_mhrs_return_login,
			SUM(teams_total.isclosed) as count_teams, 
			ROUND(SUM(IF(teams_total.isclosed, teams_total.team_hours / teams_total.count_members, 0)), 2) as total_mhrs, 
			ROUND(SUM(IF(teams_total.isclosed, worked_hours - IFNULL(worked_lunch, 0), 0)), 2) as total_mhrs_login,
			ROUND(SUM(total), 2) as total, 
			IFNULL(damage.damage_sum, 0) as damage_sum, 
			damage.damage_count, 
			IFNULL(complain.complain_sum, 0) as complain_sum, 
			complain.complain_count, 
			teams_total.team_amount, 
			ROUND(SUM(IF(teams_total.isclosed, (schedule_teams.team_man_hours)/teams_total.team_hours*teams_total.team_amount, 0)), 2) as total2,
			ROUND(SUM(IF(teams_total.isclosed, (worked_hours - IFNULL(worked_lunch, 0))/teams_total.team_hours_login*teams_total.team_amount, 0)), 2) as total2_login,
			team_hours_login, count_members
			", FALSE);
		$this->db->from('users');
		$this->db->join('schedule_teams_members', 'users.id = schedule_teams_members.user_id','left');
		$this->db->join('schedule_teams', 'employee_team_id = schedule_teams.team_id', 'left');
        
        $this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
        
        $this->db->join('employee_worked', "users.id = worked_user_id AND worked_date = team_date_start", "left");
        
        $this->db->join("($subquery) teams_total", 'teams_total.team_id = schedule_teams_members.employee_team_id', 'left');
		
		$this->db->join("($damage_subquery) damage", 'users.id = damage.uid', 'left');
		$this->db->join("($complain_subquery) complain", 'users.id = complain.uid', 'left');

        $this->db->where($wQueryData);
		$this->db->group_by('users.id');
		
		$this->db->order_by($order_field[0], $order_field[1]);
		$this->db->order_by('lastname', 'ASC');
		
		//$this->db->where('active_status', 'yes');
        //$this->db->where('emp_feild_worker', 1);
        //$this->db->where('emp_status', 'current');

		$query = $this->db->get();
		if($oneRow)
			return $query->row_array();

		return $query->result_array();
	}

	private function get_damage_subquery($wdata=[])
	{
		$this->db->select('IFNULL(SUM(event_damage), 0) as damage_sum, estimates.user_id as e_user_id, COUNT(schedule.id) as damage_count, users.id as uid', FALSE);
		$this->db->from('schedule');
		$this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id');
		$this->db->join('schedule_teams_members', 'employee_team_id = team_id', 'left');
		$this->db->join('users', 'users.id = schedule_teams_members.user_id');
		$this->db->join('employee_worked', "users.id = worked_user_id AND worked_date = team_date_start");

        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');

        $this->db->where('event_damage IS NOT NULL', null, false);
        $this->db->where('event_damage <>', 0);

        if($wdata && !empty($wdata))
        	$this->db->where($wdata);

        $this->db->group_by('uid');
        return $this->db->_compile_select();
	}

	private function get_complain_subquery($wdata=[])
	{
		$this->db->select('IFNULL(SUM(event_complain),0) as complain_sum, estimates.user_id as e_user_id, COUNT(schedule.id) as complain_count, users.id as uid', FALSE);
		$this->db->from('schedule');
		$this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id');
		$this->db->join('schedule_teams_members', 'employee_team_id = team_id', 'left');
		$this->db->join('users', 'users.id = schedule_teams_members.user_id');
		$this->db->join('employee_worked', "users.id = worked_user_id AND worked_date = team_date_start");

		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');

        $this->db->where('event_complain IS NOT NULL', null, false);
        $this->db->where('event_complain <>', 0);

        if($wdata && !empty($wdata))
        	$this->db->where($wdata);

        $this->db->group_by('uid');
        return $this->db->_compile_select();
	}

	private function get_estimators_damage_subquery($wdata=[])
	{
		$this->db->select('IFNULL(SUM(event_damage), 0) as damage_sum, estimates.user_id as e_user_id, COUNT(schedule.id) as damage_count', FALSE);
		$this->db->from('schedule');
		$this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id');
		//$this->db->join('schedule_teams_members', 'employee_team_id = team_id', 'left');
		//$this->db->join('users', 'users.id = schedule_teams_members.user_id');
		//$this->db->join('employee_worked', "users.id = worked_user_id AND worked_date = DATE_FORMAT(from_unixtime(team_date + 3600), '%Y-%m-%d')");

        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id');
        $this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');

        $this->db->where('event_damage IS NOT NULL', null, false);
        $this->db->where('event_damage <>', 0);

        if($wdata && !empty($wdata))
        	$this->db->where($wdata);

        $this->db->group_by('estimates.user_id');
        return $this->db->_compile_select();
	}

	private function get_estimators_complain_subquery($wdata=[])
	{
		$this->db->select('IFNULL(SUM(event_complain),0) as complain_sum, estimates.user_id as e_user_id, COUNT(schedule.id) as complain_count', FALSE);
		$this->db->from('schedule');
		$this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id');
		//$this->db->join('schedule_teams_members', 'employee_team_id = team_id', 'left');
		//$this->db->join('users', 'users.id = schedule_teams_members.user_id');
		//$this->db->join('employee_worked', "users.id = worked_user_id AND worked_date = DATE_FORMAT(from_unixtime(team_date + 3600), '%Y-%m-%d')");

		$this->db->join('workorders', 'workorders.id = schedule.event_wo_id');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');

        $this->db->where('event_complain IS NOT NULL', null, false);
        $this->db->where('event_complain <>', 0);

        if($wdata && !empty($wdata))
        	$this->db->where($wdata);

        $this->db->group_by('estimates.user_id');
        return $this->db->_compile_select();
	}


	function total_damage_complain($wdata){

		$this->db->select('SUM(event_damage) as damage_total, SUM(event_complain) as complain_total', FALSE)
		;
        $this->db->from('schedule');
        $this->db->join('schedule_teams', 'event_team_id = team_id');


        if($wdata && !empty($wdata))
        	$this->db->where($wdata);

        $query = $this->db->get();

        if(!$query)
        	return [];
       	
        return $query->row_array();
	}

	function employees_mhr_return_sctreen($wdata = [], $wQueryData = [], $oneRow = FALSE)
	{
		$now = strtotime(date('Y-m-d 23:59:59'));

		$where_mnth = $where_mnth1 = ['team_date_start <='=>date("Y-m-d", $now), 'team_date_start >='=>date("Y-m-d", strtotime(date('Y-m-d') . '-1 month'))];
		//$now-86400*get_count_days(date('Y-m-d', strtotime('-1 month')))];
		$where_3mnth = $where_3mnth1 = ['team_date_start <='=>date("Y-m-d", $now), 'team_date_start >='=>date("Y-m-d", strtotime(date('Y-m-d') . '-3 month'))];
		//$now-86400*get_count_days(date('Y-m-d', strtotime('-3 month')))];
		$where_6mnth = $where_6mnth1 = ['team_date_start <='=>date("Y-m-d", $now), 'team_date_start >='=>date("Y-m-d", strtotime(date('Y-m-d') . '-6 month'))];
		//$now-86400*get_count_days(date('Y-m-d', strtotime('-6 month')))];

		$where_mnth1['emp_status'] = $where_3mnth1['emp_status'] = $where_6mnth1['emp_status'] = 'current';
		$where_mnth1['active_status'] = $where_3mnth1['active_status'] = $where_6mnth1['active_status'] = 'yes';

		$all_time = $this->employees_mhr_return([], ['emp_status' => 'current', 'active_status' => 'yes'], FALSE, ['users.firstname', 'ASC']);
		$mnth = $this->employees_mhr_return($where_mnth, $where_mnth1, FALSE, ['users.firstname', 'ASC']);
		$mnth3 = $this->employees_mhr_return($where_3mnth, $where_3mnth1, FALSE, ['users.firstname', 'ASC']);
		$mnth6 = $this->employees_mhr_return($where_6mnth, $where_6mnth1, FALSE, ['users.firstname', 'ASC']);
		
		return ['all_time'=>$all_time, 'mnth'=>$mnth, 'mnth3'=>$mnth3, 'mnth6'=>$mnth6];
		
	}
	
	function delete_workorder_new($id)
	{

		$sql = 'DELETE workorders, schedule, schedule_event_services FROM workorders ';
		$sql .= 'LEFT JOIN schedule ON workorders.id = schedule.event_wo_id ';
		$sql .= 'LEFT JOIN schedule_event_services ON schedule.id = schedule_event_services.event_id ';
		$sql .= 'WHERE workorders.id = ' . intval($id);
		$this->db->query($sql);
		return TRUE;
	}
	
	function get_count_workorders($wdata = array())
	{
		$this->db->select("COUNT(workorders.id) as count_workorders, CONCAT(users.firstname, ' ', users.lastname) as username", FALSE);
		$this->db->join('status_log', "status_log.status_item_id = workorders.estimate_id AND status_value = 6 AND status_type = 'estimate'");
		$this->db->join('users', "users.id = status_log.status_user_id");
		if(!empty($wdata))
			$this->db->where($wdata);
		$this->db->group_by('users.id');
		$this->db->order_by('count_workorders', 'DESC');
		$query = $this->db->get($this->table);
		return $query->result_array();
	}
	
	function estimator_stats_by_finished_wo($user_id = NULL, $startDate, $endDate)
	{
		$finishedStatusId = (int)$this->getFinishedStatusId();

		//$this->db->_protect_identifiers = false;
		$this->db->select("COUNT(DISTINCT schedule.event_team_id) as count_teams, SUM(schedule.event_damage) as team_damages, team_amount, team_man_hours, event_team_id", FALSE);
        $this->db->from('schedule');

        $this->db->join('schedule_teams', 'event_team_id = team_id');
        $this->db->join('workorders', "workorders.id = schedule.event_wo_id");
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        $this->db->join('invoices', "invoices.estimate_id = estimates.estimate_id");
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id');
        
        $this->db->group_by('schedule.event_team_id');
        $teamsQuery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		//$this->db->_protect_identifiers = false;
		/*$this->db->select("COUNT(status_id) replay_finished, status_item_id", FALSE);
        $this->db->from('status_log');

		$this->db->join('workorders', "workorders.id = status_log.status_item_id");
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        $this->db->where('status_value = 0 AND status_type = "workorder" AND (status_date < ' . $startDate . ' OR status_date > ' . $endDate . ')');
        if($user_id)
			$this->db->where(['estimates.user_id' => $user_id]);
        $this->db->group_by('status_item_id, status_type ');*/
        $this->db->select("MAX(status_date) as last_finish, status_item_id", FALSE);
        $this->db->from('status_log');

		$this->db->join('workorders', "workorders.id = status_log.status_item_id");
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        $this->db->where('status_value = '.$finishedStatusId.' AND status_type = "workorder"');
        if($user_id)
			$this->db->where(['estimates.user_id' => $user_id]);
        $this->db->group_by('status_item_id');

        $confQuery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		//$this->db->_protect_identifiers = false;
		$this->db->select("SUM(schedule.event_price * teams.team_man_hours / teams.team_amount) as workorder_hours, ROUND(SUM((schedule.event_price - schedule.event_damage) * ((teams.team_amount - teams.team_damages) / teams.team_man_hours )) / SUM(schedule.event_price), 2) as mhr_return, SUM(schedule.event_price) as work_price, SUM(schedule.event_price - schedule.event_damage) as total_without_damages, SUM(schedule.event_damage) as work_damages, SUM(schedule.event_complain) as work_complains, SUM(schedule.event_damage > 0) as count_team_damages, SUM(schedule.event_complain > 0) as count_team_complains, SUM(count_teams) as count_teams, estimates.user_id", FALSE);
        $this->db->from('workorders');

       
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        
        
        $this->db->join("($confQuery) as max_finish_date", 'workorders.id = max_finish_date.status_item_id AND max_finish_date.last_finish >= ' .  $startDate . ' AND max_finish_date.last_finish <= ' . $endDate);
        $this->db->join('users', "estimates.user_id = users.id");
        $this->db->join('schedule', "schedule.event_wo_id = workorders.id");
        $this->db->join('schedule_teams', "schedule.event_team_id = schedule_teams.team_id");
        $this->db->join("($teamsQuery) teams", 'schedule_teams.team_id = teams.event_team_id');
        
		$this->db->where('wo_status',  $finishedStatusId);
		$this->db->where('team_closed', 1);
		if($user_id)
			$this->db->where(['estimates.user_id' => $user_id]);
        $this->db->group_by('workorders.id');
        $returnDataQuery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		
		//$this->db->_protect_identifiers = false;
		$this->db->select("users.id, users.firstname, users.lastname, ROUND(SUM(return_data.mhr_return * return_data.work_price) / SUM(return_data.work_price), 2) as estimator_mhr_return, ROUND(SUM(total_without_damages) / SUM(workorder_hours), 2) as estimator_mhr_return2, SUM(return_data.work_price) as total, SUM(work_damages) as total_damages, SUM(work_complains) as total_complains, SUM(count_team_damages) as count_team_damages, SUM(count_team_complains) as count_team_complains, SUM(count_teams) as count_teams, ROUND(SUM(workorder_hours), 2) as workorders_hours", FALSE);
		$this->db->from('users');
		$this->db->join("($returnDataQuery) return_data", 'users.id = return_data.user_id');
		$this->db->group_by('users.id');
		$dataQuery  = $this->db->_compile_select();
		$this->db->_reset_select();
		
		
		$this->db->from("($dataQuery) as data");
		$this->db->order_by('estimator_mhr_return', 'DESC');
		$query = $this->db->get();
		//echo "<pre>"; var_dump($query->result());die;
		return $query->result();
		/*
		 SELECT * FROM (
			SELECT users.id, users.firstname, users.lastname, ROUND(SUM(return_data.mhr_return * return_data.work_price) / SUM(return_data.work_price), 2) as estimator_mhr_return FROM users
			JOIN (
				SELECT ROUND(SUM(schedule.event_price * ((teams.team_amount - teams.team_damages) / teams.team_man_hours )) / SUM(schedule.event_price), 2) as mhr_return, SUM(schedule.event_price) as work_price, estimates.user_id
				FROM (`workorders`)
				JOIN `status_log` ON `status_log`.`status_item_id` = `workorders`.`id` AND status_type = "workorder"
				JOIN `estimates` ON `workorders`.`estimate_id` = `estimates`.`estimate_id`
				LEFT JOIN (SELECT COUNT(status_id) replay_finished, status_item_id
				FROM (`status_log`)
				JOIN `workorders` ON `workorders`.`id` = `status_log`.`status_item_id`
				JOIN `estimates` ON `workorders`.`estimate_id` = `estimates`.`estimate_id`
				WHERE `status_value` = 0
				AND `status_type` = 'workorder'
				AND (`status_date` < 1537761600 OR status_date > 1538884800)
				
				
				AND `estimates`.`user_id` = '6'
				
				
				GROUP BY `status_item_id`, `status_type`) conf ON `workorders`.`id` = `conf`.`status_item_id`
				JOIN users ON estimates.user_id = users.id
				JOIN schedule ON schedule.event_wo_id = workorders.id
				JOIN schedule_teams ON schedule.event_team_id = schedule_teams.team_id
				JOIN (
					SELECT SUM(schedule.event_damage) as team_damages, team_amount, team_man_hours, event_team_id  FROM schedule
					JOIN `schedule_teams` ON `event_team_id` = `team_id`
					JOIN `workorders` ON `workorders`.`id` = `schedule`.`event_wo_id`
					JOIN `estimates` ON `workorders`.`estimate_id` = `estimates`.`estimate_id`
					JOIN `invoices` ON `invoices`.`estimate_id` = `estimates`.`estimate_id` 
					GROUP BY schedule.event_team_id
				) teams ON schedule_teams.team_id = teams.event_team_id
				
				WHERE `wo_status` =  0
				AND `estimates`.`user_id` =  '6'
				AND `status_date` >= 1537761600
				AND `status_date` <= 1538884800
				AND replay_finished IS NULL
				GROUP BY workorders.id
			) as return_data ON users.id = return_data.user_id
			GROUP BY users.id
		) as data
		ORDER BY estimator_mhr_return DESC
		 
		 */
	}
	
	function estimator_stats_without_finished_wo($user_id = NULL, $startDate, $endDate)
	{
		//$this->db->_protect_identifiers = false;
		
		$this->db->select("COUNT(DISTINCT schedule.event_team_id) as count_teams, SUM(schedule.event_damage) as team_damages, team_amount, team_man_hours, event_team_id", FALSE);
        $this->db->from('schedule');

        $this->db->join('schedule_teams', 'event_team_id = team_id');
        $this->db->join('workorders', "workorders.id = schedule.event_wo_id");
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        $this->db->where(['team_date_start >= ' => date("Y-m-d", $startDate), 'team_date_start <= ' => date("Y-m-d", $endDate)]);
        $this->db->group_by('schedule.event_team_id');
        $teamsQuery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		
		//$this->db->_protect_identifiers = false;
		
		$this->db->select("SUM(schedule.event_price * teams.team_man_hours / teams.team_amount) as workorder_hours, ROUND(SUM((schedule.event_price - schedule.event_damage) * ((teams.team_amount - teams.team_damages) / teams.team_man_hours )) / SUM(schedule.event_price), 2) as mhr_return, SUM(schedule.event_price) as work_price, SUM(schedule.event_price - schedule.event_damage) as total_without_damages, SUM(schedule.event_damage) as work_damages, SUM(schedule.event_complain) as work_complains, SUM(schedule.event_damage > 0) as count_team_damages, SUM(schedule.event_complain > 0) as count_team_complains, SUM(count_teams) as count_teams, estimates.user_id", FALSE);
        $this->db->from('workorders');

       
        $this->db->join('estimates', "workorders.estimate_id = estimates.estimate_id");
        $this->db->join('users', "estimates.user_id = users.id");
        $this->db->join('schedule', "schedule.event_wo_id = workorders.id");
        $this->db->join('schedule_teams', "schedule.event_team_id = schedule_teams.team_id");
        $this->db->join("($teamsQuery) teams", 'schedule_teams.team_id = teams.event_team_id');
        if($user_id)
			$this->db->where('estimates.user_id', $user_id);
		$this->db->where(['team_date_start >= ' => date("Y-m-d", $startDate), 'team_date_start <= ' => date("Y-m-d", $endDate)]);
		$this->db->where('team_closed', 1);
        
        $this->db->group_by('workorders.id');
        $returnDataQuery = $this->db->_compile_select();
		$this->db->_reset_select();
		
		//$this->db->_protect_identifiers = false;
		$this->db->select("users.id, users.firstname, users.lastname, ROUND(SUM(return_data.mhr_return * return_data.work_price) / SUM(return_data.work_price), 2) as estimator_mhr_return, ROUND(SUM(total_without_damages) / SUM(workorder_hours), 2) as estimator_mhr_return2, SUM(return_data.work_price) as total, SUM(work_damages) as total_damages, SUM(work_complains) as total_complains, SUM(count_team_damages) as count_team_damages, SUM(count_team_complains) as count_team_complains, SUM(count_teams) as count_teams, ROUND(SUM(workorder_hours), 2) as workorders_hours", FALSE);
		$this->db->from('users');
		$this->db->join("($returnDataQuery) return_data", 'users.id = return_data.user_id');
		$this->db->group_by('users.id');
		$dataQuery  = $this->db->_compile_select();
		$this->db->_reset_select();
		
		
		$this->db->from("($dataQuery) as data");
		$this->db->order_by('estimator_mhr_return2', 'DESC');
		$query = $this->db->get();
		//echo "<pre>" . $this->db->last_query();die;
		return $query->result();
		/*
		SELECT * FROM (
			SELECT users.id, users.firstname, users.lastname, ROUND(SUM(return_data.mhr_return * return_data.work_price) / SUM(return_data.work_price), 2) as estimator_mhr_return FROM users
			JOIN (
				SELECT ROUND(SUM(schedule.event_price * ((teams.team_amount - teams.team_damages) / teams.team_man_hours )) / SUM(schedule.event_price), 2) as mhr_return, SUM(schedule.event_price) as work_price, estimates.user_id
				FROM (`workorders`)
				JOIN `status_log` ON `status_log`.`status_item_id` = `workorders`.`id` AND status_type = "workorder"
				JOIN `estimates` ON `workorders`.`estimate_id` = `estimates`.`estimate_id`
				
				JOIN users ON estimates.user_id = users.id
				JOIN schedule ON schedule.event_wo_id = workorders.id
				JOIN schedule_teams ON schedule.event_team_id = schedule_teams.team_id
				JOIN (
					SELECT SUM(schedule.event_damage) as team_damages, team_amount, team_man_hours, event_team_id  FROM schedule
					JOIN `schedule_teams` ON `event_team_id` = `team_id`
					JOIN `workorders` ON `workorders`.`id` = `schedule`.`event_wo_id`
					JOIN `estimates` ON `workorders`.`estimate_id` = `estimates`.`estimate_id`
					JOIN `invoices` ON `invoices`.`estimate_id` = `estimates`.`estimate_id` 
					GROUP BY schedule.event_team_id
				) teams ON schedule_teams.team_id = teams.event_team_id
				
				WHERE 
				/*AND `estimates`.`user_id` =  '6'*/
				/*
				 `team_date` >= 1537761600
				AND `team_date` <= 1538884800
				
				GROUP BY workorders.id
			) as return_data ON users.id = return_data.user_id
			GROUP BY users.id
		) as data
		ORDER BY estimator_mhr_return DESC
		*/
	}
	
	function calcSiteTime($wdata = [], $group_by = 'workorders.estimate_id' )
	{
		$this->db->select("ROUND(SUM(IFNULL(events_reports.er_on_site_time, 0)) / 3600 , 2) sum_mhrs, workorders.estimate_id", FALSE);
        $this->db->from('workorders');

        $this->db->join('estimates', "estimates.estimate_id = workorders.estimate_id");
        $this->db->join('invoices', "estimates.estimate_id = invoices.estimate_id", "left");
        $this->db->join('schedule', "schedule.event_wo_id = workorders.id", "left");
        $this->db->join('events_reports', "events_reports.er_event_id = schedule.id", "left");
        if(count($wdata))
			$this->db->where($wdata);
        $this->db->group_by($group_by);
        $query = $this->db->_compile_select();
		return $query;
		/*SELECT ROUND(SUM(IFNULL(events.ev_on_site_time, 0)) , 2) sum_mhrs, workorders.estimate_id
		FROM (`workorders`)
		JOIN `estimates` ON `estimates`.`estimate_id` = `workorders`.`estimate_id`
		LEFT JOIN `invoices` ON `estimates`.`estimate_id` = `invoices`.`estimate_id`
		LEFT JOIN `schedule` ON `schedule`.`event_wo_id` = `workorders`.`id`
		LEFT JOIN `events` ON `events`.`ev_event_id` = `schedule`.`id`
		WHERE     `estimates`.`date_created` >= '1601510400' 
		AND       `estimates`.`date_created` <= '1604102400'
		group by   workorders.estimate_id*/

	}
    function get_active_statuses($where = null)
    {
        if($where)
            $this->db->where($where);
        $this->db->where('wo_status_active', 1);
        $this->db->order_by('wo_status_priority');
        $query = $this->db->get('workorder_status');

        return $query->result();
    }
	function get_status_by_id($id){
	    $this->db->where('wo_status_id', $id);
	    return $this->db->get('workorder_status');
    }
    function get_many($values)
    {
        $this->db->where_in('wo_status_id', $values);
        return $this->get_active_statuses();
    }
    function getConfirmedByClientStatusId(){
        $statusDefault = 1;
        $status = $this->get_all_statuses(['is_confirm_by_client'=>1], true);
        if(is_array($status) && count($status) && isset($status['wo_status_id']))
            $statusDefault = $status['wo_status_id'];

        return $statusDefault;
    }
}

