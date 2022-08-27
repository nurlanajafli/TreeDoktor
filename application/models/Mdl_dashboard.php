<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * dashboard model
 * created by: gisterpages team
 * created on: august - 2012
 */

class Mdl_dashboard extends CI_Model
{

	function __construct()
	{
		parent::__construct();

		//$this->_table = 'gp_dashboards';
		//$this->_table2 = 'gp_dashboardmeta';
	}

	function search($search_keyword = NULL, $price = NULL) {
		//echo '<pre>'; var_dump($search_keyword, $price); die;
        $search_keyword = addslashes($search_keyword);
        /*$search_keyword = explode(' ', $search_keyword);
        array_walk($search_keyword, function(&$value, $key) { $value = '+' . $value . '*'; } );
        $search_keyword = trim(implode(' ', $search_keyword));*/
		$subqueryClients = $subqueryClientsContacts = $subqueryEstimates = $subqueryWorkorders = NULL; //TOTAL
		if(!$price) //TOTAL
		{ //TOTAL
		    //, MATCH (`client_name`,`client_address`,`client_city`,`client_country`,`client_state`,`client_zip`) AGAINST ('{$search_keyword}' IN BOOLEAN MODE) as score
			$this->db->select("client_address as item_address, client_name as item_name, cc_phone as item_phone, cc_name as item_cc_name, CONCAT('clients') as item_module_name, CONCAT('details') as item_action_name, clients.client_id as item_id, clients.client_id as item_no, client_name as item_title, CONCAT('1') as item_position, CONCAT(NULL) as total", FALSE);
			
			$this->db->from('clients');
			$this->db->join('clients_contacts', 'cc_client_id = clients.client_id AND cc_print = 1', 'left');
			$this->db->join('leads', 'clients.client_id = leads.client_id');
			//$this->db->where("MATCH (`client_name`,`client_address`,`client_city`,`client_country`,`client_state`,`client_zip`) AGAINST ('{$search_keyword}'  IN BOOLEAN MODE)", NULL, FALSE);
			$this->db->like('client_name', $search_keyword);
			$this->db->or_like('client_address', $search_keyword);
			$this->db->or_like('lead_address', $search_keyword);
			//$this->db->order_by('client_id', 'DESC');

			$subqueryClients = $this->db->_compile_select();
			$this->db->_reset_select();

			//, MATCH (cc_name,cc_email,cc_phone) AGAINST ('{$search_keyword}' IN BOOLEAN MODE) as score
			$this->db->select("client_address as item_address, client_name as item_name, cc_phone as item_phone, cc_name as item_cc_name, CONCAT('clients') as item_module_name, CONCAT('details') as item_action_name, client_id as item_id, client_id as item_no, client_name as item_title, CONCAT('1') as item_position, CONCAT(NULL) as total", FALSE);
			
			$this->db->from('clients_contacts');
			$this->db->join('clients', 'cc_client_id = client_id');
            //$this->db->where("MATCH (cc_name,cc_email,cc_phone) AGAINST ('{$search_keyword}' IN BOOLEAN MODE)", NULL, FALSE);
			$this->db->like('cc_phone_clean', $search_keyword);
			$this->db->or_like('cc_name', $search_keyword);
			
			//if(strpos($search_keyword, '@'))
			$this->db->or_like('cc_email', $search_keyword);
			//$this->db->order_by('client_id', 'ASC');

			$subqueryClientsContacts = $this->db->_compile_select();
			$this->db->_reset_select();

            //, 1 as score
			$this->db->select("lead_address as item_address, client_name as item_name, cc_phone as item_phone, cc_name as item_cc_name, CONCAT('workorders') as item_module_name, CONCAT('profile') as item_action_name, workorders.id as item_id, workorder_no as item_no, workorder_no as item_title, CONCAT('4') as item_position, CONCAT(NULL) as total", FALSE);
			
			$this->db->from('workorders');
			$this->db->join('estimates', 'workorders.estimate_id = estimates.estimate_id');
			$this->db->join('clients', 'estimates.client_id = clients.client_id');
			$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
			$this->db->join('clients_contacts', 'cc_client_id = clients.client_id AND cc_print = 1', 'left');
			$this->db->where('workorders.workorder_no', trim($search_keyword));
			//$this->db->or_like('workorders.id', $search_keyword);
			//$this->db->order_by('workorders.id', 'DESC');

			$subqueryWorkorders = $this->db->_compile_select();
			$this->db->_reset_select();
		

			//, MATCH (lead_no,lead_body,lead_address,lead_city,lead_country,lead_state,lead_zip) AGAINST ('{$search_keyword}' IN BOOLEAN MODE)
			$this->db->select("lead_address as item_address, client_name as item_name, cc_phone as item_phone, cc_name as item_cc_name, CONCAT('estimates') as item_module_name, CONCAT('profile') as item_action_name, estimates.estimate_id as item_id, estimate_no as item_no, estimate_no as item_title, CONCAT('3') as item_position , NULL as total", FALSE);
			
			$this->db->from('estimates');
			$this->db->join('clients', 'estimates.client_id = clients.client_id');
			$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
			$this->db->join('clients_contacts', 'cc_client_id = clients.client_id AND cc_print = 1', 'left');
			$this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id  ', 'left');
			$this->db->join('discounts', 'discounts.estimate_id=estimates.estimate_id', 'left');
			$this->db->join('client_payments', 'client_payments.estimate_id=estimates.estimate_id', 'left');
            //$this->db->where("MATCH (lead_no,lead_body,lead_address,lead_city,lead_country,lead_state,lead_zip) AGAINST ('{$search_keyword}' IN BOOLEAN MODE)", NULL, FALSE);
            $this->db->where('estimates.estimate_no', trim($search_keyword));
			//$this->db->where('1' , 1, FALSE);
			$this->db->group_by('estimates.estimate_id');
		
		//TOTAL
		//if(!$price) { 
			
			/*$this->db->like('lead_address', $search_keyword);
			$this->db->or_like('estimate_no', $search_keyword);*/

			//$this->db->or_like("estimates.estimate_balance", $search_keyword); // TOTAL
		//}
		//else {
		//	$this->db->like("estimates.estimate_balance", $price);
	//	}
		//TOTAL
		
		
		//$this->db->or_like('estimate_id', $search_keyword);
		//$this->db->order_by('estimate_id', 'DESC');

			$subqueryEstimates = $this->db->_compile_select();
			$this->db->_reset_select();
	
		} //TOTAL
        //, 1 as score
		$this->db->select("lead_address as item_address, client_name as item_name, cc_phone as item_phone, cc_name as item_cc_name, CONCAT('invoices') as item_module_name, CONCAT('profile') as item_action_name, invoices.id as item_id, invoice_no as item_no, invoice_no as item_title, CONCAT('5') as item_position, 
		    ROUND(CAST((SUM(IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) /
                   IF(COUNT(DISTINCT (client_payments.payment_id)), COUNT(DISTINCT (client_payments.payment_id)), 1) /
                   IF(COUNT(DISTINCT (invoice_interest.id)), COUNT(DISTINCT (invoice_interest.id)), 1) - IF(estimate_hst_disabled <> 2, IFNULL(
                           IF(discounts.discount_percents = 0, discounts.discount_amount,
                              (discounts.discount_amount * SUM(
                                      IF(estimates_services.service_status <> 1, estimates_services.service_price, 0)) /
                   IF(COUNT(DISTINCT (client_payments.payment_id)), COUNT(DISTINCT (client_payments.payment_id)), 1) /
                   IF(COUNT(DISTINCT (invoice_interest.id)), COUNT(DISTINCT (invoice_interest.id)), 1) /
                               100)), 0), 0)) /
                  IF(estimate_hst_disabled = 2, " . config_item('tax_rate') . ", 1) AS DECIMAL(10, 3)), 2) as total
		", FALSE);
        
        $this->db->from('invoices');
        $this->db->join('estimates', 'invoices.estimate_id = estimates.estimate_id');
        $this->db->join('invoice_interest', 'invoices.id = invoice_interest.invoice_id', 'left');
        $this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
		$this->db->join('clients', 'estimates.client_id = clients.client_id');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
		$this->db->join('clients_contacts', 'cc_client_id = clients.client_id AND cc_print = 1', 'left');
		
		$this->db->join('estimates_services', 'estimates_services.estimate_id = invoices.estimate_id   AND service_status = 2', 'left');
		$this->db->join('discounts', 'discounts.estimate_id=invoices.estimate_id', 'left');
		$this->db->join('client_payments', 'client_payments.estimate_id=invoices.estimate_id', 'left');
		//$this->db->where('1' , 1, FALSE);
		$this->db->group_by('invoices.estimate_id');
		//TOTAL
		if(!$price) {
			/*$this->db->like('invoices.invoice_no', $search_keyword);
			 
			$this->db->or_like("estimates.estimate_balance", $search_keyword);*/
            $this->db->where('invoices.invoice_no', trim($search_keyword));
		}
		else {
			$this->db->like("estimates.estimate_balance",  $price);
		}
		//TOTAL
		
		//$this->db->or_like('invoices.id', $search_keyword);
		//$this->db->order_by('invoices.id', 'DESC');

        $subqueryInvoices = $this->db->_compile_select();
       
		$this->db->_reset_select();
		/*
		$this->db->select("leads.lead_address as item_address, client_name as item_name, cc_phone as item_phone, cc_name as item_cc_name, CONCAT('invoices') as item_module_name, CONCAT('profile') as item_action_name, invoices.id as item_id, invoice_no as item_no, invoice_no as item_title, CONCAT('6') as item_position, estimates.estimate_balance as total", FALSE);
        
        $this->db->from('invoices');
		$this->db->join('estimates', 'invoices.estimate_id = estimates.estimate_id');
		
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
		$this->db->join('clients', 'estimates.client_id = clients.client_id');
		
		$this->db->join('clients_contacts', 'cc_client_id = clients.client_id AND cc_print = 1', 'left');
		
		
        
		
		if(!$price) {
			$this->db->or_like('invoices.id', $search_keyword);
			 
			$this->db->or_like("estimates.estimate_balance", $search_keyword);
		}
		else {
			$this->db->like("estimates.estimate_balance",  $price);
		}
		$this->db->order_by('invoices.id', 'DESC');
		
        $subqueryInvoicesTotal = $this->db->_compile_select();
        */
      //  echo '<pre>'; var_dump($subqueryInvoicesTotal); die;
		//$this->db->_reset_select();
		
		$sql = "SELECT * FROM (";
		
		if($subqueryClients)
			$sql .= "($subqueryClients) UNION ";
		if($subqueryClientsContacts)
			$sql .= "($subqueryClientsContacts) UNION ";
		if($subqueryEstimates)
			$sql .= "($subqueryEstimates) UNION ";
		
		if($subqueryWorkorders)
			$sql .= "($subqueryWorkorders) UNION ";
		
		$sql .= "($subqueryInvoices)";
		//$sql .= "UNION ($subqueryInvoicesTotal)";
		
		//, `score` DESC
		$sql .= ") as items GROUP BY `item_module_name`, `item_id` ORDER BY `item_position` ASC, `item_id` DESC LIMIT 100";

        $result = $this->db->query($sql)->result();
		return $result;
	}

	function globalSearch($search_keyword, $limit = '', $start = '', $sort_opt = '', $sort_order = '')
	{

		if ($search_keyword == '') {
			return FALSE;
			exit;
		}

		$array = array(
			'clients.client_id' => $search_keyword,
			'clients.client_name' => $search_keyword,
			'clients_contacts.cc_phone' => $search_keyword,
			'clients_contacts.cc_name' => $search_keyword,
			'clients.client_address' => $search_keyword,
			'clients.client_address2' => $search_keyword,
			'clients_contacts.cc_email' => $search_keyword,
			'clients.client_promo_code' => $search_keyword,
			'leads.lead_address' => $search_keyword,
			//'clients.client_main_intersection' => $search_keyword,
			//'clients.client_address2' 		=> $search_keyword,
			//'clients.client_main_intersection2' => $search_keyword,
			'estimates.estimate_no' => $search_keyword,
			'estimates.estimate_balance' => $search_keyword,
			'workorders.workorder_no' => $search_keyword,
			'invoices.invoice_no' => $search_keyword
		);

		$this->db->select(' clients.client_id,
							TRIM(clients.client_name) as client_name,
							clients.client_contact,
							clients.client_address,
							clients.client_city,
							clients.client_zip,
							clients_contacts.cc_email,
							clients_contacts.cc_phone,
							clients_contacts.cc_name,
							leads.lead_address,
							leads.lead_city,
							leads.lead_zip,
							estimates.estimate_id,
                            estimates.estimate_no,
							estimates.date_created,
							estimates.status,
							estimate_statuses.est_status_name as status,
							estimates.estimate_balance,
							workorders.id AS workorder_id,
							workorders.workorder_no,
							workorders.wo_status,
							workorder_status.wo_status_name,
							invoices.id AS invoice_id,
							invoices.invoice_no,
							invoices.in_status,
							invoice_statuses.invoice_status_name');

		$this->db->from('clients', 'estimates', 'leads');
		$this->db->join('clients_contacts', 'clients.client_id=clients_contacts.cc_client_id', 'left');
		$this->db->join('estimates', 'clients.client_id=estimates.client_id', 'left');
		
		$this->db->join('estimate_statuses', 'estimates.status=estimate_statuses.est_status_id', 'left');
		$this->db->join('leads', 'clients.client_id=leads.client_id', 'left');
		$this->db->join('workorders', 'workorders.estimate_id=estimates.estimate_id', 'left');
		$this->db->join('workorder_status', 'workorders.wo_status=workorder_status.wo_status_id', 'left');
		$this->db->join('invoices', 'invoices.workorder_id=workorders.id', 'left');
		$this->db->join('invoice_statuses', 'invoices.in_status = invoice_statuses.invoice_status_id', 'left');
		$this->db->or_like($array);
		$this->db->group_by('clients.client_id');
		if (!empty($limit)) {
			$this->db->limit($limit, $start);
		}
		if (empty($sort_order)) {
			$sort_order = 'ASC';
		}

		if (!empty($sort_opt)) {
			$order = explode(',', $sort_opt);
			foreach ($order as $row) {
				$this->db->order_by('TRIM(' . $row . ')', $sort_order);
			}
		} else {
			$this->db->order_by("TRIM(`clients`.`client_name`)", $sort_order);
		}
		$result = $this->db->get()->result();
		return $result;
	}
//print($this->db->last_query());


}

//end of file dashboard_model.php
