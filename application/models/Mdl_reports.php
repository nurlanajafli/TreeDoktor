<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

//*******************************************************************************************************************
//*************
//*************																							mdl_reports:
//************* 													Contailns Business Analitics, Statistic and KPI:
//*******************************************************************************************************************	

class Mdl_reports extends MY_Model
{

	function __construct()
	{
		parent::__construct();

	}

//*******************************************************************************************************************
//*************
//*************																		Count Records for total files:
//************* 													
//*******************************************************************************************************************	

	function getTotalClients()
	{
		$result = $this->db->select('COUNT(client_id) as count')->get('clients')->row();
		if($result)
			return $result->count;
		return 0;
	}

	// /getTotalClients

	function getTotalLeads()
	{
		$result = $this->db->select('COUNT(lead_id) as count')->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id')->where('lead_statuses.lead_status_default', 1)->where('client_id <>', '0')->get('leads')->row();
		if($result)
			return $result->count;
		return 0;
	}

	// /getTotalLeads

	function getTotalEstimates()
	{
		$query = $this->db->select('COUNT(estimate_id) as count');
        if(is_cl_permission_owner()) {
            $query = $query->where('estimates.user_id', request()->user()->id);
        } elseif (is_cl_permission_none()) {
            $query = $query->where('estimates.user_id', -1);
        }
        $result = $query->get('estimates')->row();
		if($result)
			return $result->count;
		return 0;
	}

	// /getTotalEstimates

	function getTotalWorkorders()
	{
		$result = $this->db->select('COUNT(id) as count')->get('workorders')->row();
		if($result)
			return $result->count;
		return 0;
	}

	// /getTotalWorkorders

	function getTotalInvoices()
	{
		$result = $this->db->select('COUNT(invoices.id) as count')
            ->join('invoice_statuses', 'invoices.in_status=invoice_statuses.invoice_status_id')
            /*->where('invoice_statuses.completed', 0)*/->get('invoices')->row();
		if($result)
			return $result->count;
		return 0;
	}// /getTotalInvoices

//*******************************************************************************************************************
//*************
//*************																		Count Records for specific dates:
//************* 													
//*******************************************************************************************************************	

	function getTodayTotalClients($date_now)
	{
		$sql_query = "SELECT COUNT(client_id) as count FROM clients WHERE clients.client_date_created = '$date_now'";
		$result = $this->db->query($sql_query)->row();
		if($result)
			return $result->count;
		return 0;
	}

	// /getTodayTotalClients

	function getTodayTotalLeads($date_now = false, $where = [])
	{
		$this->db->select('users.*, COUNT(DISTINCT leads.lead_id) as count');
		if($date_now) {
            $this->db->where('leads.lead_date_created >=', $date_now . ' 00:00:00');
            $this->db->where('leads.lead_date_created <=', $date_now . ' 23:59:59');
        }
		if($where && count($where)){
            $this->db->where($where);
        }
		$this->db->join('users', 'users.id = leads.lead_author_id', 'LEFT');
		/*$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'lead_statuses.lead_status_id = lead_reason_status.reason_lead_status_id', 'LEFT');
		*/
		$this->db->group_by('leads.lead_author_id');
		$this->db->order_by('count', 'DESC');
		$query = $this->db->get('leads');
		return $query->result_array();
		
		//$sql_query = "SELECT * FROM leads WHERE leads.lead_date_created = '$date_now'";
		//$query = $this->db->query($sql_query);
		//return $query->num_rows();
	}

	// /getTodayTotalLeads

	function getTodayTotalEstimates($date_now = false, $where = [])
	{
        $this->load->model('mdl_estimates_orm');
        if($date_now) {
            $sub_q = $this->mdl_estimates_orm->calcQuery(['estimates.date_created >=' => strtotime($date_now), 'estimates.date_created <' => strtotime($date_now) + 86400]);
            $this->db->select('users.*, COUNT(DISTINCT estimates.estimate_id) as count, SUM(totals.sum_without_tax) as estimated_sum');
            $this->db->where('estimates.date_created >=', strtotime($date_now));
            $this->db->where('estimates.date_created <', strtotime($date_now) + 86400);
        }
        elseif($where && count($where)) {
            $sub_q = $this->mdl_estimates_orm->calcQuery($where);
            $this->db->select('users.*, COUNT(DISTINCT estimates.estimate_id) as count, SUM(totals.sum_without_tax) as estimated_sum');
            $this->db->where($where);
        }
		$this->db->join('users', 'users.id = estimates.user_id', 'LEFT');
        $this->db->join('('.$sub_q.') totals', 'estimates.estimate_id = totals.estimate_id');
		$this->db->group_by('estimates.user_id');
		$this->db->order_by('count', 'DESC');
		$query = $this->db->get('estimates');
		$result = $query->result_array();
		return $result;
	}

	// /getTodayTotalEstimates

	function getTodayTotalWorkorders($date_now = false, $where = [])
	{
        $this->load->model('mdl_estimates_orm');
        //echo '<pre>'; var_dump($where); die;
        if($date_now) {
            $sub_q = $this->mdl_estimates_orm->calcQuery(['workorders.date_created' => $date_now]);
            $this->db->select('users.*, COUNT(DISTINCT workorders.id) as count, SUM(totals.sum_without_tax) as estimated_sum');
            $this->db->where('workorders.date_created =', $date_now);
        }
        elseif($where && count($where)){
            $sub_q = $this->mdl_estimates_orm->calcQuery($where);
            $this->db->select('users.*, COUNT(DISTINCT workorders.id) as count, SUM(totals.sum_without_tax) as estimated_sum');
            $this->db->where($where);
        }
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id', 'LEFT');
		$this->db->join('users', 'users.id = estimates.user_id', 'LEFT');
        $this->db->join('('.$sub_q.') totals', 'estimates.estimate_id = totals.estimate_id');
		$this->db->group_by('estimates.user_id');
		$this->db->order_by('count', 'DESC');
		$query = $this->db->get('workorders');
		return $query->result_array();
		
		
		//$sql_query = "SELECT * FROM workorders WHERE workorders.date_created = '$date_now'";
		//$query = $this->db->query($sql_query);
		//return $query->num_rows();
	}

	// /getTodayTotalWorkorders

	function getTodayWorkordersSum($date_now = false, $where = [])
	{
        $this->load->model('mdl_estimates_orm');
        if($date_now)
            $sub_q = $this->mdl_estimates_orm->calcQuery(['workorders.date_created' => $date_now]);
        elseif($where && count($where))
            $sub_q = $this->mdl_estimates_orm->calcQuery($where);
        $this->db->select('IFNULL(SUM(totals.sum_without_tax), 0) as sum');
		$this->db->join('estimates', 'estimates.estimate_id = workorders.estimate_id');
		if($date_now)
    		$this->db->where('workorders.date_created', $date_now);
		elseif($where && count($where))
            $this->db->where($where);
        $this->db->join('('.$sub_q.') totals', 'estimates.estimate_id = totals.estimate_id');

        $result = $this->db->get('workorders')->row();
		return $result->sum ?? 0;
	}

	// /getTodayWorkorders


//*******************************************************************************************************************
//*************
//*************																						Estimates stats:
//************* 													
//*******************************************************************************************************************	

	function estimates_statistic($status, $estimator_id, $status_only, $from_date, $to_date, $area = "")
	{

		$this->db->select('COUNT(estimates.estimate_id) as estimates_amount, estimates.status');
		$this->db->from('estimates');
		if (isset($area) && $area != "") {
			$this->db->join('leads', 'estimates.lead_id = leads.lead_id AND leads.lead_neighborhood = '.$area);
		}
		if (isset($estimator_id) && $estimator_id != "") {
			$this->db->where('estimates.user_id', $estimator_id);
		}
		if (isset($status) && $status != "") {
			$this->db->where('estimates.status', $status);
		}
		if (isset($status_only) && $status_only != "") {
			$this->db->where('estimates.status', $status_only);
		}
		if (isset($from_date) && $from_date != "") {
			$this->db->where('estimates.date_created >= ', strtotime($from_date));
			$this->db->where('estimates.date_created <= ', strtotime($to_date) + 86399);
		}
		$this->db->group_by('estimates.status');

		$query = $this->db->get();

		return $query->result();
	}

	function revenue_estimates_sum($status, $estimator_id, $status_only, $from_date, $to_date, $area = "")
	{
		$this->db->select_sum('estimates_services.service_price');
		$this->db->join('estimates', 'estimates.estimate_id = estimates_services.estimate_id');
		if (isset($area) && $area != "") {
			$this->db->join('leads', 'estimates.lead_id = leads.lead_id AND leads.lead_neighborhood = ' . $area);
		}
		
		if (isset($estimator_id) && $estimator_id != "")
			$this->db->where('estimates.user_id', $estimator_id);
		if (isset($status) && $status != "")
			$this->db->where('estimates.status', $status);
		if (isset($status_only) && $status_only != "")
			$this->db->where('estimates.status', $status_only);
		if (isset($from_date) && $from_date != "") {
			$this->db->where('estimates.date_created >=', strtotime($from_date));
			$this->db->where('estimates.date_created <', (strtotime($to_date)/* + 86400*/));
		}

		$row = $this->db->get('estimates_services')->row();
		return $row->service_price;
	}

	function revenue_estimates_sum_new($where = []) {

		$extraJoin = [];
		if (isset($where['area']) && $where['area'] != "") {
			$extraJoin[0]['table'] = 'leads';
			$extraJoin[0]['condition'] = 'estimates.lead_id = leads.lead_id AND leads.lead_neighborhood = ' . $where['area'];
			unset($where['area']);
		}

		$this->load->model('mdl_estimates_orm');
		$sub_q = $this->mdl_estimates_orm->calcQuery($where, $extraJoin);
		$this->db->select('SUM(totals.sum_for_services) as sum_for_services, estimates.status', false);
		$this->db->from('estimates');
		$this->db->join('('.$sub_q.') totals', 'estimates.estimate_id = totals.estimate_id');
		$this->db->group_by('estimates.status');
		return $this->db->get()->result();
	}

	function get_estimators_files($estimator_id = '', $limit = FALSE, $offset = FALSE, $wdata = array())
	{
        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.user_id' => $estimator_id]);
		$queryStr = "SELECT 		estimates.estimate_no,
												estimates.date_created,
												estimates.status,
												estimates.estimate_id,
												estimates.estimate_count_contact,
												estimate_statuses.est_status_name as status,
												estimate_statuses.est_status_priority as status_priority,
												clients.client_id,
												clients.client_name,
												clients.client_address,
												clients.client_city,
												clients_contacts.cc_phone,
												SUM(estimates_services.service_price) as total,
												totals.*

									FROM 		estimates

									INNER JOIN 	clients

									ON 			estimates.client_id=clients.client_id

									LEFT JOIN 	clients_contacts

									ON 			clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1

									INNER JOIN  estimates_services

									ON 			estimates.estimate_id=estimates_services.estimate_id

									INNER JOIN 	estimate_statuses

									ON 			estimate_statuses.est_status_id=estimates.status
									
									LEFT JOIN ($totalsSubQuery) totals ON estimates.estimate_id = totals.estimate_id

									WHERE 	1=1	";
		
		if($estimator_id && $estimator_id != '')
			$queryStr .= " AND estimates.user_id = $estimator_id";
		if(!empty($wdata))
		{
			
			$queryStr .= " AND ";
			if(is_array($wdata))
				foreach($wdata as $key => $val)
					$queryStr .= $key . "='".$val."' ";
			else
				$queryStr .= $wdata;
		}
		
		$queryStr .= " GROUP BY estimates_services.estimate_id";
		$queryStr .= " ORDER BY 	estimates.date_created DESC";
									
		
		if($limit && $offset !== FALSE)
			$queryStr .= " LIMIT " . $offset . ", " . $limit;
		
		
		
		
		$query = $this->db->query($queryStr);

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/

	} //* end get_estimators_files()


//*******************************************************************************************************************
//*************
//*************																						Workorders stats:
//************* 													
//*******************************************************************************************************************


	function workorders_statistic($status)
	{

		$sql_query = "	SELECT 	* 
						FROM 	workorders
						
						
						INNER JOIN workorder_status
									
						ON	workorders.wo_status=workorder_status.wo_status_id
						
						";

		if (isset($status) && $status != "") {
			$sql_query .= " WHERE workorder_status.wo_status_id = '" . $status . "' AND workorders.wo_status = '" . $status . "'";
		}

		$query = $this->db->query($sql_query);
		return $query->num_rows();
	}

	function workorders_statistic2($status = '')
	{
		$this->load->model('mdl_estimates_orm');

		$totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['workorders.id IS NOT NULL ' => NULL]);

		$this->db->select('workorders.wo_status, COUNT(workorders.id) as count, workorder_status.wo_status_name as status, SUM(totals.sum_for_services) as sum, SUM(totals.total_due) as due');
        $this->db->join('workorder_status', 'workorders.wo_status=workorder_status.wo_status_id');

        $this->db->join("({$totalsSubQuery}) totals", 'workorders.estimate_id = totals.estimate_id', 'left', FALSE);
        if (isset($status) && $status != "") {
			$this->db->where('workorder_status.wo_status_id', $status);
			$this->db->where('workorders.wo_status', $status);
		}
		$this->db->group_by('workorders.wo_status');
		$this->db->order_by('workorder_status.wo_status_priority');
        $query = $this->db->get('workorders')->result();
        return $query;
	}

	function revenue_workorder_sum($status)
	{
        $this->db->select_sum('estimates_services.service_price');
        $this->db->join('estimates', 'estimates.estimate_id = estimates_services.estimate_id');
        $this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id');
        $this->db->join('workorder_status', 'workorders.wo_status=workorder_status.wo_status_id', 'LEFT');
        if (isset($status) && $status != "") {
            $this->db->where('workorders.wo_status', $status);
            $this->db->where('workorder_status.wo_status_id', $status);
        }

        $row = $this->db->get('estimates_services')->row();
        return $row->service_price;
	}


//*******************************************************************************************************************
//*************
//*************																						  Invoices stats:
//************* 													
//*******************************************************************************************************************

	function invoices_statistic($status, $from_date, $to_date)
	{
		$sql_query = "	SELECT COUNT(DISTINCT(invoices.id)) as count, invoices.id,
						(ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	* discounts.discount_amount / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/* / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) *// IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, " . config_item('tax_rate') . ", 1) - (SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1))  as due
						FROM 	invoices  JOIN invoice_statuses ON invoices.in_status = invoice_statuses.invoice_status_id
						
						INNER JOIN 	estimates
									
						ON 			invoices.estimate_id= estimates.estimate_id
						
						LEFT JOIN invoice_interest
						
						ON 			invoices.id= invoice_interest.invoice_id
						
						LEFT JOIN 	estimates_services
						
						ON			estimates_services.estimate_id=invoices.estimate_id 
						
						LEFT JOIN 	discounts
						
						ON			discounts.estimate_id=invoices.estimate_id
						
						LEFT JOIN client_payments
						
						ON			client_payments.estimate_id=invoices.estimate_id
						
						
						
						";
		if (isset($status) && $status != "") {
			$sql_query .= " WHERE invoices.in_status = '" . $status . "'";
		}

		if (isset($from_date) && $from_date != "") {
			$sql_query .= " AND invoices.date_created >= '" . $from_date . "' AND invoices.date_created <= '" . $to_date . "'";
		}
		$sql_query .= ' GROUP BY invoices.id';
		$query = $this->db->query($sql_query);
		return $query->result_array();
	}
	
	
	function invoices_statistic2($status, $from_date, $to_date)
	{
        $this->load->model('mdl_estimates_orm');
        $wdata = [];

        if (isset($status) && $status != "") {
            $wdata['invoices.in_status'] = $status;
        }
        if (isset($from_date) && $from_date != "") {
            $wdata['invoices.date_created >='] = $from_date;
        }
        if (isset($to_date) && $to_date != "") {
            $wdata['invoices.date_created <='] = $to_date;
        }

        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($wdata, [
            [
                'table' => 'invoices inv1',
                'condition' => 'estimates.estimate_id = inv1.estimate_id'
            ]
        ]);

        $select = "COUNT(invoices.id) as count, invoice_statuses.invoice_status_name, in_status, invoice_statuses.default, invoice_statuses.is_hold_backs, invoice_statuses.is_sent, 
            invoice_statuses.is_overdue, invoice_statuses.completed, SUM(totals.total_with_tax) as sum_total, SUM(totals.total_due) as sum_due, SUM(totals.sum_without_tax) as sum_service, 
            SUM(totals.discount_total) as sum_discount, SUM(totals.total_tax) as sum_hst, SUM(totals.payments_total) as sum_payments";
        $this->db->from('invoices');
        $this->db->select($select, FALSE);
        $this->db->group_by('invoices.in_status');
        $this->db->where($wdata);
        $this->db->join("invoice_statuses", 'invoices.in_status = invoice_statuses.invoice_status_id');
        $this->db->join("($totalsSubQuery) totals", 'invoices.estimate_id=totals.estimate_id');
        $query = $this->db->get();
        return $query->result();

		$sql_query = "SELECT COUNT(inv.id) as count, inv.invoice_status_name, inv.in_status, inv.default, inv.is_hold_backs, inv.is_sent, inv.is_overdue, inv.completed, SUM(inv.total) as sum_total, SUM(inv.due) as sum_due, SUM(inv.service_sum) as sum_service, SUM(inv.discount) as sum_discount, SUM(inv.hst) as sum_hst, SUM(inv.payments) as sum_payments FROM (
					SELECT invoices.id, invoices.in_status, invoice_statuses.invoice_status_name, invoice_statuses.default, invoice_statuses.is_hold_backs, invoice_statuses.is_sent, invoice_statuses.is_overdue, invoice_statuses.completed,
						
    					ROUND((ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/* / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) *// IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, " . config_item('tax_rate') . ", 1), 2)
                         as total,
                        
                         ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) as service_sum,
                        
                         ROUND(IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0)), 2) as discount,
                         
                         ROUND((ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/* / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) *// IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, " . config_item('tax_rate') . ", 1) - (ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/* / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) *// IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2)), 2) as hst,
						 
						 
						 ROUND(SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 2) as payments,
    
						ROUND((ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2)
						- IF(discounts.discount_percents, ROUND(IF(estimate_hst_disabled = 2, ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2) / " . config_item('tax_rate') . ",	ROUND(SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) / IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) , 2))	*  IF(estimate_hst_disabled <> 2, discounts.discount_amount, 0) / 100, 2), IF(estimate_hst_disabled != 2, IFNULL(discounts.discount_amount, 0), 0))
						 + ROUND(IF(invoices.interest_status = 'No' , SUM(IFNULL(invoice_interest.interes_cost, 0))/* / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) *// IF(COUNT(DISTINCT(client_payments.payment_id)), COUNT(DISTINCT(client_payments.payment_id)), 1)  / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1), 0), 2))
						* IF(estimate_hst_disabled = 0, " . config_item('tax_rate') . ", 1) - (SUM(IF(client_payments.payment_amount, client_payments.payment_amount, 0)) / IF(COUNT(DISTINCT(invoice_interest.id)), COUNT(DISTINCT(invoice_interest.id)), 1) / IF(COUNT(DISTINCT(estimates_services.id)), COUNT(DISTINCT(estimates_services.id)), 1)), 2)
                         as due
						FROM 	invoices  JOIN invoice_statuses ON invoices.in_status = invoice_statuses.invoice_status_id
						
						INNER JOIN 	estimates
									
						ON 			invoices.estimate_id= estimates.estimate_id
						
						INNER JOIN users

                        ON			estimates.user_id=users.id
						
						INNER JOIN 	leads
									
                        ON 			estimates.lead_id= leads.lead_id 
                        
                        INNER JOIN 	clients
                        
                        ON 			estimates.client_id = clients.client_id
						
						LEFT JOIN invoice_interest
						
						ON 			invoices.id= invoice_interest.invoice_id
						
						LEFT JOIN 	estimates_services
						
						ON			estimates_services.estimate_id=invoices.estimate_id 
						
						LEFT JOIN 	discounts
						
						ON			discounts.estimate_id=invoices.estimate_id
						
						LEFT JOIN client_payments
						
						ON			client_payments.estimate_id=invoices.estimate_id";
		if (isset($status) && $status != "") {
			$sql_query .= " WHERE invoices.in_status = '" . $status . "'";
		}

		if (isset($from_date) && $from_date != "") {
			$sql_query .= " AND invoices.date_created >= '" . $from_date . "' AND invoices.date_created < '" . $to_date . "'";
		}
		$sql_query .=  " GROUP BY invoices.id) as inv	GROUP BY inv.in_status";
		$query = $this->db->query($sql_query);
		return $query->result();
	}

    function revenue_invoices_sum($status, $from_date, $to_date)
    {
        $this->db->select_sum('estimates_services.service_price');
        $this->db->join('estimates', 'estimates.estimate_id = estimates_services.estimate_id');
        $this->db->join('invoices', 'estimates.estimate_id = invoices.estimate_id');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'LEFT');
        if (isset($status) && $status != "")
            $this->db->where('invoices.in_status', $status);
        if (isset($from_date) && $from_date != "") {
            $this->db->where('invoices.date_created >=', $from_date);
            $this->db->where('invoices.date_created <', $to_date);
        }
        $row = $this->db->get('estimates_services')->row();
        return $row->service_price;
    }

	function com_overflow($where = array())
	{

		$sql_query = "SELECT 	estimates.estimate_id,
								
              					 
								estimates.date_created as estimate_date,
								estimates.estimate_no, 
								workorders.date_created as workorder_date 
					FROM estimates
					
					INNER JOIN workorders WHERE workorders.estimate_id = estimates.estimate_id
					and workorders.date_created > estimates.date_created + interval 21 day and estimates.status = 'Confirmed'
					and estimates.user_id = 15";
		if(!empty($where))
		{
			foreach($where as $k=>$v)
				$sql_query .= " AND " . $k . "=" . $v;
		}

		$query = $this->db->query($sql_query);
		return $query->result();

	}

//getDateTotalLeadsByCat starts here
	function getDateTotalLeadsByCat($date_now = false, $where = [])
	{
		$this->db->select('COUNT(*) as count');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		if($date_now)
    		$this->db->where("leads.lead_date_created >= '$date_now 00:00:00' AND leads.lead_date_created <= '$date_now 23:59:59'");
		if(!empty($where))
			$this->db->where($where);
		$this->db->group_by('lead_statuses.lead_status_id');
		$query = $this->db->get('leads');
		//if ($query->num_rows()) {
			return $query->row_array();
		/*} else {
			return array();
		}*/
	}

	// /getDateTotalLeadsByCat

	function client_communications($wdata)
	{
		$this->db->select('users.*, COUNT(client_notes.client_note_id) as count');
		$this->db->join('client_notes', 'users.id = client_notes.author', 'left');
		$this->db->where($wdata);
		$this->db->group_by('users.id');
		$this->db->order_by('count', 'DESC');
		$notes = $this->db->get('users')->result_array();
		//if ($notes)
			return $notes;
		//return FALSE;
	}

	function get_expense($wdata = array())
	{
		if(count($wdata))
			$this->db->where($wdata);

		return $this->db->get('expenses')->row_array();
	}
	function get_expenses($wdata = array(), $limit = FALSE, $offset = FALSE, $numRows = FALSE)
	{
		$this->db->select("expenses.*, expense_types.*, equipment_items.*, equipment_groups.*, CONCAT(emp.firstname, ' ', emp.lastname) as emp_name, emp.id as employee_id, users.*", FALSE);

		$this->db->group_by('expenses.expense_id');

		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		if($numRows)
			return $this->db->count_all_results('expenses');
		$this->db->order_by('expense_date DESC');
		$this->db->join('expense_types', 'expense_types.expense_type_id = expenses.expense_type_id', 'LEFT');
		$this->db->join('equipment_items', 'equipment_items.item_id = expenses.expense_item_id', 'LEFT');
		$this->db->join('equipment_groups', 'equipment_groups.group_id = equipment_items.group_id', 'LEFT');
		$this->db->join('users as emp', 'emp.id = expenses.expense_user_id', 'LEFT');
		//$this->db->join('employees', 'employees.employee_id = expenses.expense_employee_id', 'LEFT');
		$this->db->join('users', 'users.id = expenses.expense_created_by', 'LEFT');
		if($offset !== FALSE && $limit  !== FALSE)
			$this->db->limit($limit, $offset);
        $this->db->where('expense_tax IS NOT NULL AND expense_tax <> "" AND JSON_VALID(expense_tax)');
		return $this->db->get('expenses')->result_array();
	}
	
	function get_expenses_sum($wdata = array())
	{
		$this->db->select("ROUND(SUM(expense_amount)+SUM(expenses.expense_hst_amount), 2) as expense_amount_sum", FALSE);

		if($wdata && count($wdata))
			$this->db->where($wdata);

		$result = $this->db->get('expenses')->row_array();
		return money(isset($result['expense_amount_sum'])?$result['expense_amount_sum']:0);
	}


	function get_expenses_type_group($wdata = array())
	{
		$expenses = array();
		/******КОСТЫЛЬ ДЛЯ ПЕЙРОЛА В ЗАТРАТАХ*****/
		//if(isset($wdata['expense_date >=']) && isset($wdata['expense_date <=']))
			//$expenses = $this->get_payroll_expenses($wdata);
		/******КОСТЫЛЬ ДЛЯ ПЕЙРОЛА В ЗАТРАТАХ*****/
		
		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->select('expense_types.*, (SUM(expenses.expense_amount) + SUM(expenses.expense_hst_amount)) AS sum, expenses.expense_amount, expenses.expense_hst_amount');
		$this->db->join('expenses', 'expense_types.expense_type_id = expenses.expense_type_id', 'left');
        $this->db->where('expense_tax IS NOT NULL AND expense_tax <> "" AND JSON_VALID(expense_tax)');
		$this->db->group_by('expense_types.expense_type_id');
		$expenses = array_merge($expenses, $this->db->get('expense_types')->result_array());
		
		$sums = array();
		foreach($expenses as $key => $val)
			$sums[$key] = $val['sum'];
		array_multisort($sums, SORT_DESC, $expenses);

		return $expenses;
	}
	
	function get_payroll_expenses($wdata)
	{
		$this->load->model("employee/mdl_employee", "emp_login");
		$get_data = array();
		$totalPayrollSum = 0;
		if(isset($wdata['employee_id']) && $wdata['employee_id'] == true)
			$get_data['employees.employee_id'] = $wdata['employee_id'];
		$get_data["login_time >="] = date('Y-m-d H:i:s', $wdata['expense_date >=']);
		$get_data["login_time <="] = date('Y-m-d 23:59:59', $wdata['expense_date <=']);
		$emp_data = $this->emp_login->get_overview_report_biweekly($get_data);
		
		$emp_id = NULL;
		$date = NULL;
		$expenses = array();
		$braked = array();
		//echo $this->db->last_query();
		foreach($emp_data as $emp)
		{
			
			if($emp_id != $emp['employee_id'])
			{
				$employee_ids[] = $emp_id = $emp['employee_id'];
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
			$totalPayrollSum += ($emp['seconds'] * $emp['employee_hourly_rate']);
		}
		
		if($totalPayrollSum)
			$expenses[] = array('expense_type_id' => 0,  'expense_name' => 'Payroll', 'expense_status' => 1, 'sum' => money($totalPayrollSum), 'expense_amount' => money($totalPayrollSum), 'expense_hst_amount' => 0, 'employee_ids' => $employee_ids);
		if($totalPayrollSum && isset($get_data['employees.employee_id']))
			$expenses[0]['emp_name'] = $emp_data[0]['emp_name'];
		
		return $expenses;
	}
	
	function count_wo_days_created()
	{
		
		//SELECT COUNT(`workorders`.`id`) as count, DATEDIFF(`workorders`.`date_created`, FROM_UNIXTIME(`estimates`.`date_created`, '%Y-%m-%d')) AS count_days
		//FROM workorders
		//JOIN `estimates` ON `estimates`.`estimate_id` = `workorders`.`estimate_id`
		//GROUP BY count_days
		
		$this->db->select("COUNT(workorders.id) AS count, DATEDIFF(workorders.date_created, FROM_UNIXTIME(estimates.date_created, '%Y-%m-%d')) AS count_days", FALSE);
		$this->db->join("estimates", "workorders.estimate_id = estimates.estimate_id");
		$this->db->group_by("count_days");
		$query = $this->db->get("workorders");
		//var_dump($query); die;
		return $query->result_array();
	}
	
	function get_payroll_sum($wdata)
	{
		//SELECT (SUM( ROUND( worked_hours * worked_hourly_rate, 2 ) ) - SUM( ROUND( worked_lunch * worked_hourly_rate, 2 ))) AS lunch
		//FROM  `employee_worked` 
		//WHERE worked_payroll_id =67
		$select_string = '(SUM( ROUND( IF(worked_hours, worked_hours, 0) * worked_hourly_rate, 2 ) ) - SUM( ROUND( IF(worked_lunch, worked_lunch, 0) * worked_hourly_rate, 2 ))) AS sum, ROUND( SUM( IF(worked_hours, worked_hours, 0) ) - SUM( IF(worked_lunch, worked_lunch, 0) ), 2 ) as sum_hours';
		$this->db->select($select_string, FALSE);
		$this->db->where($wdata);
		return $this->db->get('employee_worked')->row_array();
	}
	
	function sum_amount_services($status, $from_date, $to_date, $where = array())
	{
	    $this->load->model('mdl_estimates_orm');
	    $wdata = [];

        if (isset($status) && $status != "") {
            $wdata['invoices.in_status'] = $status;
        }
        if (isset($from_date) && $from_date != "") {
            $wdata['invoices.date_created >='] = $from_date;
            $wdata['invoices.date_created <='] = $to_date;
        }
        if(isset($where) && count($where)) {
            $wdata = array_merge($wdata, $where);
        }

        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($wdata);

	    $this->db->from('invoices');
        $this->db->select("SUM(ROUND(totals.total_with_tax, 2)) as service_price", FALSE);
        $this->db->join("($totalsSubQuery) totals", 'invoices.estimate_id=totals.estimate_id');
        $this->db->where($wdata);
        $query = $this->db->get();

        return $query->row_array();
		/*$this->db->select('SUM(estimates_services.service_price) as price, invoices.id as invoice_id', FALSE);
		$this->db->from('estimates_services');
		$this->db->join('invoices', 'estimates_services.estimate_id = invoices.estimate_id');
		$this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id');
		if (isset($status) && $status != "")
			$this->db->where('invoices.in_status', $status);
		if (isset($from_date) && $from_date != "") {
			$this->db->where('invoices.date_created >=', $from_date);
			$this->db->where('invoices.date_created <=', $to_date);
		}
		if(isset($where) && !empty($where))
			$this->db->where($where);
		$this->db->group_by('invoices.id');
		$subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		$die = false;

		if(isset($where['service_status']) || isset($where['service_status =']))
			unset($where['service_status'], $where['service_status =']);

		$this->db->select("SUM(ROUND((service_price.price - IF(discount_percents, IFNULL(service_price.price * discount_amount / (100 + discount_amount), 0), IFNULL(discount_amount, 0))) * IF(estimate_hst_disabled, 1, " . config_item('tax_rate') . "), 2)) as service_price", FALSE);
		
		$this->db->join('invoices', 'estimates.estimate_id = invoices.estimate_id');
		$this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id');
		
		$this->db->join('discounts', 'discounts.estimate_id=invoices.estimate_id', 'LEFT');
		
		$this->db->join("($subquery) service_price", 'invoices.id = service_price.invoice_id', 'left');
		
		if (isset($status) && $status != "")
			$this->db->where('invoices.in_status', $status);
		if (isset($from_date) && $from_date != "") {
			$this->db->where('invoices.date_created >=', $from_date);
			$this->db->where('invoices.date_created <=', $to_date);
		}
		if(isset($where) && !empty($where))
			$this->db->where($where);
		$query = $this->db->get('estimates');
		return $query->row_array();*/
	}
	
	function sum_amount_payments($status, $from_date, $to_date)
	{
		$this->db->select_sum('client_payments.payment_amount');
		//$this->db->join('estimates', 'estimates.estimate_id = estimates_services.estimate_id');
		$this->db->join('invoices', 'client_payments.estimate_id = invoices.estimate_id');
		$this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id');
		
		//$this->db->join('discounts', 'discounts.estimate_id = invoices.estimate_id', 'left');
		//$this->db->join('client_payments', 'client_payments.estimate_id = invoices.estimate_id', 'left');
		
		if ($status != "")
			$this->db->where('invoices.in_status', $status);
		if (isset($from_date) && $from_date != "") {
			$this->db->where('invoices.date_created >=', $from_date);
			$this->db->where('invoices.date_created <=', $to_date);
		}
		$query = $this->db->get('client_payments');
		return $query->row_array();
	}
	
	function getEstimatorKPI($userId, $fromDate = NULL, $toDate = NULL) {
	    if(!intval($userId))
	        return FALSE;
        $this->load->model('mdl_est_status');

        $this->db->select('COUNT(DISTINCT estimates.estimate_id) as count, IFNULL(SUM(estimates_services.service_price), 0) as revenue', FALSE);
        $this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id');
        $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->db->where('user_id', intval($userId));
        $this->db->where('services.is_bundle <>', 1);
        if ($fromDate && $toDate) {
            $this->db->where('estimates.date_created >=', strtotime($fromDate));
            $this->db->where('estimates.date_created <=', (strtotime($toDate . ' 23:59:59')));
        }
        $data = $this->db->get('estimates')->row();
        $confimedData = $this->getEstimatorKPIConfirmed($userId, $fromDate, $toDate);

        $result = [
            'count' => isset($data->count) && $data->count ? $data->count : 0,
            'revenue' => isset($data->revenue) && $data->revenue ? $data->revenue : 0,
            'count_confirmed' => isset($confimedData->count_confirmed) && $confimedData->count_confirmed ? $confimedData->count_confirmed : 0,
            'revenue_confirmed' => isset($confimedData->revenue_confirmed) && $confimedData->revenue_confirmed ? $confimedData->revenue_confirmed : 0
        ];

        $rateByCount = $result['count'] ? $result['count_confirmed'] * 100 / $result['count'] : 0;
        $rateByRevenue = $result['revenue'] ? $result['revenue_confirmed'] * 100 / $result['revenue'] : 0;

        $result['confirmation_rate'] = round(($rateByCount + $rateByRevenue) / 2, 2);

        return $result;
    }

    function getEstimatorKPIConfirmed($userId, $fromDate = NULL, $toDate = NULL) {
        if(!intval($userId))
            return FALSE;
        $this->load->model('mdl_est_status');

        $confimedEstimateStatus = $this->mdl_est_status->get_by(['est_status_confirmed' => 1]);
        if(!$confimedEstimateStatus)
            return FALSE;
        $statusId = $confimedEstimateStatus->est_status_id;

        $this->db->select('COUNT(DISTINCT estimates.estimate_id) as count_confirmed, IFNULL(SUM(estimates_services.service_price), 0) as revenue_confirmed', FALSE);
        $this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id');
        $this->db->join('services', 'estimates_services.service_id = services.service_id', 'left');
        $this->db->where('user_id', intval($userId));
        $this->db->where('estimates.status', $statusId);
        $this->db->where('estimates_services.service_status <>', 1);
        $this->db->where('services.is_bundle <>', 1);
        if ($fromDate && $toDate) {
            $this->db->where('estimates.date_created >=', strtotime($fromDate));
            $this->db->where('estimates.date_created <=', (strtotime($toDate . ' 23:59:59')));
        }
        return $this->db->get('estimates')->row();
    }

    function getDailyTotalInvoicesRevenue($date = false, $where = []) {
        $this->load->model('mdl_estimates_orm');
        $wdata = [];
        if($date)
            $wdata['invoices.date_created'] = $date;
        elseif($where && count($where))
            $wdata = $where;
        else
            return false;
        $this->db->select("IFNULL(SUM(totals.sum_without_tax), 0) as total_revenue, COUNT(totals.estimate_id) as count_invoices FROM (" . $this->mdl_estimates_orm->calcQuery($wdata) . ") as totals", FALSE);
        return $this->db->get()->row();
    }
    function getDailyInvoicesByStatus($date = false, $where = []) {
        //$subquery = $this->_getEstimatesTotalWithDiscountSubquery($date);
        $wdata = [];
        if($date)
            $wdata['invoices.date_created'] = $date;
        elseif($where && count($where))
            $wdata = $where;
        else
            return false;
        $this->load->model('mdl_estimates_orm');
        $subquery = $this->mdl_estimates_orm->calcQuery($wdata);
        $this->db->select('IFNULL(SUM(totals.sum_without_tax), 0) as total_revenue, COUNT(totals.estimate_id) as count_invoices, invoice_statuses.invoice_status_id, invoice_statuses.invoice_status_name', FALSE);
        $this->db->from('invoice_statuses');
        $this->db->group_by('invoice_statuses.invoice_status_id');
        $this->db->order_by('invoice_statuses.invoice_status_id');
        $this->db->join("({$subquery}) as totals", 'totals.invoice_status_id = invoice_statuses.invoice_status_id', 'left');
        $this->db->where('invoice_statuses.invoice_status_active', 1);
        return $this->db->get()->result();
    }
    function getDailyInvoicesByEstimator($date = false, $where = []) {
        //$subquery = $this->_getEstimatesTotalWithDiscountSubquery($date);
        $wdata = [];
        if($date)
            $wdata['invoices.date_created'] = $date;
        elseif($where && count($where))
            $wdata = $where;
        else
            return false;
        $this->load->model('mdl_estimates_orm');
        $subquery = $this->mdl_estimates_orm->calcQuery($wdata);
        $this->db->select('IFNULL(SUM(totals.sum_without_tax), 0) as total_revenue, COUNT(totals.estimate_id) as count_invoices, users.firstname, users.lastname, users.emailid', FALSE);
        $this->db->from('users');
        $this->db->group_by('users.id');
        $this->db->order_by('users.id');
        $this->db->join("({$subquery}) as totals", 'totals.estimate_user_id = users.id');
        return $this->db->get()->result();
    }
    function getDailyInvoices($date = false, $where = []) {
        //$subquery = $this->_getEstimatesTotalWithDiscountSubquery($date);
        $this->load->model('mdl_estimates_orm');
        if($date)
            $subquery = $this->mdl_estimates_orm->calcQuery(['invoices.date_created' => $date]);
        elseif($where && count($where))
            $subquery = $this->mdl_estimates_orm->calcQuery($where);
        $this->db->select('totals.*, users.emailid, users.firstname, users.lastname', FALSE);
        $this->db->from('users');
        $this->db->order_by('users.id');
        $this->db->join("({$subquery}) as totals", 'totals.estimate_user_id = users.id');
        return $this->db->get()->result();
    }

    function getSalesReportRows($wdata = [], $wInData = [], $limit = 1, $offset = 0, $count = FALSE) {

        $extraJoin = [
            [
                'table' => 'clients',
                'condition' => 'clients.client_id = estimates.client_id',
            ]
        ];
	    if (isset($wInData['client_tags.tag_id'])) {
            $val = array_map('intval', $wInData['client_tags.tag_id']);
            $extraJoin[] = [
                'table' => '(SELECT client_id, tag_id FROM client_tags WHERE tag_id IN(' . implode(',', $wInData['client_tags.tag_id']) . ')) as client_tags',
                'condition' => 'clients.client_id = client_tags.client_id'
            ];
        }

	    $subQuery = $this->mdl_estimates_orm->calcQuery($wdata + $wInData, $extraJoin);
	    if($count) {
            $this->db->select('totals.estimate_id, totals.sum_without_tax, totals.sum_services_without_discount');
        } else {
            $this->db->select('estimates.*, estimates.date_created as estimate_date_created, clients.*, users.firstname, users.lastname, invoice_statuses.completed, totals.*');
        }
        $this->db->from('estimates');

        $this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id');
        $this->db->join('services', 'estimates_services.service_id = services.service_id');

        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('invoices', 'estimates.estimate_id = invoices.estimate_id', 'left');
        $this->db->join('invoice_statuses', 'invoice_statuses.invoice_status_id = invoices.in_status', 'left');
        $this->db->join('users', 'estimates.user_id = users.id', 'left');

        $this->db->where($wdata);
        $this->db->where('services.is_bundle' , 0);
        if($wInData) {
            foreach ($wInData as $key => $val) {
                if($key === 'client_tags.tag_id') {
                    $val = array_map('intval', $val);
                    $this->db->join(
                        '(SELECT client_id FROM client_tags WHERE tag_id IN(' . implode(',', $val) . ')) as ct',
                        'clients.client_id = ct.client_id'
                    );
                } else {
                    $this->db->where_in($key, $val);
                }
            }
        }

        $this->db->join("({$subQuery}) as totals", 'totals.estimate_id = estimates.estimate_id');

        $this->db->group_by('estimates.estimate_id');

        $this->db->order_by('estimates.date_created', 'desc');

        if(!$count) {
            $this->db->limit($limit, $offset);
            return $this->db->get()->result();
        } else {
            $mainSubQuery = $this->db->_compile_select();
            $this->db->_reset_select();
            $this->db->select("COUNT(DISTINCT(total.estimate_id)) as total, SUM(total.sum_services_without_discount) as sum, SUM(total.sum_without_tax) as total_estimates");
            $this->db->from("({$mainSubQuery}) as total");
            return $this->db->get()->row();
        }


    }

    function getSalesReportStats($wdata = [], $wInData = []) {
        $this->db->select('services.service_name, services.service_id, ROUND(SUM(estimates_services.service_price), 2) as total, COUNT(DISTINCT(estimates.estimate_id), 2) as count');

        $this->db->join('estimates_services', 'estimates_services.service_id = services.service_id');
        $this->db->join('estimates', 'estimates.estimate_id = estimates_services.estimate_id');
        $this->db->join('clients', 'estimates.client_id = clients.client_id');
        $this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('invoices', 'estimates.estimate_id = invoices.estimate_id', 'left');

        $this->db->group_by('services.service_id');
        $this->db->from('services');

        $this->db->where($wdata);
        $this->db->where('estimates_services.service_status <>' ,1);
        $this->db->where('services.is_bundle' , 0);
        if($wInData) {
            foreach ($wInData as $key => $val) {
                if($key === 'client_tags.tag_id') {
                    $val = array_map('intval', $val);
                    $this->db->join(
                        '(SELECT client_id FROM client_tags WHERE tag_id IN(' . implode(',', $val) . ')) as ct',
                        'clients.client_id = ct.client_id'
                    );
                } else {
                    $this->db->where_in($key, $val);
                }
            }
        }

        $subQuery = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select('*');
        $this->db->from('(' . $subQuery . ') as totals');
        $this->db->where('totals.total > ', 0);
        $this->db->order_by('totals.total', 'desc');
        return $this->db->get()->result();
    }
}
//end of file mdl_reports.php
