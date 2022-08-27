<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use application\modules\clients\models\Client;
use application\modules\estimates\models\TreeInventoryEstimateService;

/*
 * estimates model
 * created by: gisterpages team
 * created on: august - 2012
 */

class Mdl_estimates extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		$this->table = 'estimates';
		$this->primary_key = "estimates.estimate_id";
		$this->load->model('mdl_clients');
	}

//*******************************************************************************************************************
//*************
//*************													Insert Estimate Function; Returns insert id or false; 
//*************
//*******************************************************************************************************************	

	function insert_estimates($data)
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

	/* 
	 * function insert_teams
	 * 
	 * param assoc array()
	 * returns insert id or false
	 * 
	 */
	function insert_teams($data)
	{
		if ($data) {
			$insert = $this->db->insert('rel_estimate_teams', $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}

	/* 
	 * function insert_equipments
	 * 
	 * param assoc array()
	 * returns insert id or false
	 * 
	 */
	function insert_equipments($data)
	{
		if ($data) {
			$insert = $this->db->insert('rel_estimate_equips', $data);
			if ($this->db->affected_rows() > 0) {
				return $this->db->insert_id();
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}


//*******************************************************************************************************************
//*************
//*************																Get_client_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	

	function get_client_estimates($id)
	{
		
        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.client_id' => $id]);
        
        $query = $this->db->query("	SELECT 		
	                                            FROM_UNIXTIME(estimates.date_created, '%Y-%m-%d') as formatted_date_created,
												estimates.date_created,
												estimates.status,
												estimates.estimate_brand_id,
												estimates.estimate_balance,
												estimates.estimate_hst_disabled,
												estimates.user_id,
												estimates.estimate_tax_name,
												estimates.estimate_tax_rate,
												estimates.estimate_tax_value,
												estimate_statuses.est_status_name as status,
												estimate_statuses.est_status_declined as est_status_declined,
												users.user_email,
												CONCAT(users.firstname, ' ', users.lastname) as emp_name,
												leads.*,

												nodeclined.total,
                                                totalForTax.totalForTax,
                                                totals.*,
                                                estimates.estimate_id,
                                                estimates.estimate_no,
                                                invoices.invoice_no,
												(IFNULL(SUM(estimates_services.service_time), 0) + IFNULL(SUM(estimates_services.service_travel_time), 0)) as total_time
												
									FROM 		estimates
									
									LEFT JOIN 	users
									
									ON 			estimates.user_id=users.id
									 
									INNER JOIN 	clients
									
									ON 			estimates.client_id=clients.client_id

									INNER JOIN 	leads

									ON 			leads.lead_id=estimates.lead_id
									
									LEFT JOIN 	invoices

									ON 			invoices.estimate_id=estimates.estimate_id

									LEFT JOIN   (
										SELECT SUM(service_price) as total, estimates_services.estimate_id FROM estimates_services 
										JOIN estimates ON estimates.estimate_id = estimates_services.estimate_id
										WHERE service_status <> 1 AND estimates.client_id = $id GROUP BY estimates_services.estimate_id
									) nodeclined 
									
									ON 			estimates.estimate_id=nodeclined.estimate_id
									
									LEFT JOIN   (
										SELECT SUM(service_price) as totalForTax, estimates_services.estimate_id FROM estimates_services 
										JOIN estimates ON estimates.estimate_id = estimates_services.estimate_id
										WHERE service_status <> 1 AND estimates.client_id = $id AND non_taxable = 0 GROUP BY estimates_services.estimate_id
									) totalForTax

                                    ON          estimates.estimate_id=totalForTax.estimate_id
                                    
                                    LEFT JOIN ($totalsSubQuery) totals ON estimates.estimate_id = totals.estimate_id

									LEFT JOIN estimates_services

									ON 			estimates.estimate_id=estimates_services.estimate_id

									INNER JOIN 	estimate_statuses

									ON 			estimate_statuses.est_status_id=estimates.status
									
									WHERE 		estimates.client_id = $id  GROUP BY nodeclined.estimate_id ORDER BY estimates.estimate_no DESC");


			return $query;
	} //* end get_leads()

	function get_client_estimates_app($id)
	{
		$this->load->model('mdl_estimates_orm');
		$totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.client_id' => $id]);
		$this->db->select('es.lead_id, es.estimate_id, es.estimate_no, es.estimate_brand_id, es.date_created, st.est_status_name as status, CONCAT(us.firstname, " ", us.lastname) as emp_name, ld.lead_address,
            ld.lead_city, ld.lead_zip, ld.lead_country, es.estimate_balance, (IFNULL(SUM(sv.service_time), 0) + IFNULL(SUM(sv.service_travel_time), 0)) as total_time, totals.sum_without_tax as total, totals.total_due, 
            es.estimate_tax_name, es.estimate_tax_rate, es.estimate_tax_value, es.estimate_reason_decline, st.est_status_id, st.est_status_declined, st.est_status_default, st.est_status_confirmed, st.est_status_sent, es.user_id', FALSE);
		$this->db->from('estimates es');
		$this->db->join('leads ld', 'ld.lead_id=es.lead_id');
		$this->db->join('users us', 'es.user_id=us.id');
		$this->db->join('estimates_services sv', 'es.estimate_id=sv.estimate_id');
		$this->db->join('estimate_statuses st', 'st.est_status_id=es.status', 'left');
		$this->db->join("($totalsSubQuery) totals", 'es.estimate_id = totals.estimate_id', 'left');
		$this->db->where('es.client_id', $id);
		$this->db->group_by('totals.estimate_id');
		$query = $this->db->get();
		return $query;
	}

//*******************************************************************************************************************
//*************
//*************																Get_own_client_estimates; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	

	function get_own_client_estimates($id, $user_id)
	{
        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.client_id' => $id, 'estimates.user_id' => $user_id]);
		$query = $this->db->query("	SELECT 		estimates.estimate_no,
	                                            FROM_UNIXTIME(estimates.date_created, '%Y-%m-%d') as formatted_date_created,
												estimates.date_created,
												estimates.status,
												estimates.estimate_id,
												estimates.estimate_balance,
												estimates.estimate_hst_disabled,
												estimates.estimate_brand_id,
												estimate_statuses.est_status_name as status,
												estimate_statuses.est_status_declined as est_status_declined,
												users.user_email,
												CONCAT(users.firstname, ' ', users.lastname) as emp_name,
												leads.*,
												totals.*,
												nodeclined.total,

												(IFNULL(SUM(estimates_services.service_time), 0) + IFNULL(SUM(estimates_services.service_travel_time), 0)) as total_time
												
									FROM 		estimates
									
									INNER JOIN 	users
									
									ON 			estimates.user_id=users.id
									
									INNER JOIN 	clients
									
									ON 			estimates.client_id=clients.client_id

									INNER JOIN 	estimate_statuses

									ON 			estimate_statuses.est_status_id=estimates.status

									INNER JOIN 	leads

									ON 			leads.lead_id=estimates.lead_id

									LEFT JOIN   (
										SELECT SUM(service_price) as total, estimates_services.estimate_id FROM estimates_services 
										JOIN estimates ON estimates.estimate_id = estimates_services.estimate_id
										WHERE service_status <> 1 AND estimates.client_id = $id GROUP BY estimates_services.estimate_id
									) nodeclined 

									ON 			estimates.estimate_id=nodeclined.estimate_id
									
									LEFT JOIN ($totalsSubQuery) totals ON estimates.estimate_id = totals.estimate_id

									INNER JOIN estimates_services

									ON 			estimates.estimate_id=estimates_services.estimate_id
									
									WHERE 		estimates.client_id = $id 
									
									AND			estimates.user_id = $user_id   ORDER BY estimates.estimate_no DESC");

		return $query;
	} //* end get_leads()

//*******************************************************************************************************************
//*************
//*************															Get Estimates Function; Returns row() or false; 
//*************
//*******************************************************************************************************************	

	function get_estimates($search_keyword, $status, $limit, $start, $order_field = "estimates.estimate_id", $order_type = "DESC", $wdata = array())
	{
		// MK changed the query to pull dinamic data from the tables.
		$this->load->model('mdl_estimates_orm');
		$contactsJoin = " AND clients_contacts.cc_print = 1";
		if($search_keyword)
			$contactsJoin = NULL;
        if($status && $status != '') {
            $totalsSubQuery = $this->mdl_estimates_orm->calcQuery(['estimates.status' => (int)$status]);
        } else {
            $totalsSubQuery = $this->mdl_estimates_orm->calcQuery();
        }



		$sql_query = "SELECT 					clients.client_id,
												clients.client_brand_id,
												clients.client_name,
												clients.client_contact,
												clients.client_address,
												clients.client_city,
												clients.client_zip,
												clients_contacts.*, 
												
												leads.lead_address,
												leads.lead_city,
												leads.lead_state,
												leads.lead_zip,
												leads.lead_reffered_user,
												leads.lead_reffered_client,
												leads.latitude,
												leads.longitude,
												estimate_statuses.*,
												estimate_reason_status.*, 
												users.firstname,
												users.lastname,
												totals.*,
												totals.sum_without_tax as total,
												
												estimates.lead_id,
												estimates.estimate_brand_id,
												estimates.estimate_id,
												estimates.estimate_no,
												estimates.estimate_last_contact,
												estimates.estimate_count_contact,
												estimates.date_created,
												FROM_UNIXTIME(estimates.date_created) as formatted_date_created,
												estimates.status,
												estimates_qa.qa_id,
												estimate_statuses.est_status_name as status,
												workorders.wo_status,
												invoices.date_created as invoice_date_created,
												
												ROUND(totals.payments_total, 2) AS payments_total,
												emails.email_id, 
												emails.email_status,
												emails.email_created_at
												
									FROM 		clients


									LEFT JOIN 	clients_contacts

									ON 			clients.client_id = clients_contacts.cc_client_id $contactsJoin
									 
									INNER JOIN 	estimates
									
									ON 			clients.client_id=estimates.client_id
									
									LEFT JOIN ($totalsSubQuery) totals ON estimates.estimate_id = totals.estimate_id
									
									INNER JOIN 	estimate_statuses

									ON 			estimate_statuses.est_status_id=estimates.status
									
									LEFT JOIN 	estimate_reason_status

									ON 			estimate_reason_status.reason_id=estimates.estimate_reason_decline
									
									LEFT JOIN 	discounts

									ON 			discounts.estimate_id=estimates.estimate_id
									
									LEFT JOIN 	estimates_qa
									
									ON 			estimates_qa.estimate_id=estimates.estimate_id 
									
									LEFT JOIN 	workorders
									
									ON 			workorders.estimate_id=estimates.estimate_id 
									
									LEFT JOIN 	invoices
									
									ON 			invoices.estimate_id=estimates.estimate_id 
									
									LEFT JOIN leads
									
									ON estimates.lead_id=leads.lead_id
									
									LEFT JOIN users
									
									ON estimates.user_id=users.id
									
									LEFT JOIN emails on emails.email_id = (
                                        SELECT MAX(emailables.email_email_id) as last_estimate_email_id FROM emailables
                                        WHERE emailables.emailable_type = 'application\\\\modules\\\\estimates\\\\models\\\\Estimate' AND emailables.emailable_id = estimates.estimate_id
                                        ORDER BY emailables.email_email_id LIMIT 1
                                    )
									
									";

        if(is_cl_permission_owner()) {
            //$sql_query .= ' LEFT JOIN (' . $this->mdl_clients->getPermissionsSubQuery() . ') perm ON perm.client_id = estimates.client_id WHERE perm.client_id IS NOT NULL ';
            $sql_query .= ' WHERE estimates.user_id = ' . request()->user()->id . ' ';
        } elseif (is_cl_permission_none()) {
            $sql_query .= ' WHERE estimates.user_id = -1 ';
        } else {
            $sql_query .= " WHERE 1=1 ";
        }

		if (!empty($wdata)) {
			foreach ($wdata as $key => $val)
				$sql_query .= ' AND ' . $key . "='" . $val . "'";
		}

		if (isset($search_keyword) && $search_keyword != "") {
			$sql_query .= " AND (clients.client_name LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_name LIKE '%" . $search_keyword . "%'
								OR clients_contacts.cc_email LIKE '%" . $search_keyword . "%'
								OR leads.lead_address LIKE '%" . $search_keyword . "%'
								OR estimates.estimate_no LIKE '%" . $search_keyword . "%')";
		}

		if (isset($status) && $status != "") {
			$sql_query .= " AND estimates.status = '" . $status . "'";
		}

		$sql_query .= " GROUP BY estimates.estimate_id ORDER BY $order_field $order_type ";

		if ($limit !== '') {
			$sql_query .= " LIMIT " . $start;
		}

		if ($start !== '') {
			$sql_query .= ", " . $limit;
		}

		$query = $this->db->query($sql_query);

		return $query;
	} // End get_leads();

//*******************************************************************************************************************
//*************
//*************														Update Estimates Function; Returns bool or false; 
//*************
//*******************************************************************************************************************	

	function update_estimates($data, $wdata)
	{
		if ($data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table, $data);
			//echo $this->db->last_query();
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}// End. update_estimates ();

//*******************************************************************************************************************
//*************
//*************																						Search_estimates;
//*************
//*******************************************************************************************************************

	function search_estimates()
	{

		$search_keyword = strip_tags($this->input->post('search_value'));

		$array = array(
			'clients.client_name' => $search_keyword,
			'clients.client_contact' => $search_keyword,
			'clients.client_address' => $search_keyword,
			'estimates.estimate_no' => $search_keyword
		);

		$this->db->select('clients.client_id,
							clients.client_name,
							clients.client_contact,
							clients.client_address,
							clients.client_city,
							clients.client_zip,
							estimates.estimate_id,
							estimates.date_created,
							estimates.status,
							estimate_statuses.est_status_id as status');

		$this->db->from('clients', 'estimates');
		$this->db->join('estimates', 'clients.client_id=estimates.client_id');
		$this->db->join('estimate_statuses', 'estimate_statuses.est_status_id=estimates.status');
		$this->db->or_like($array);
		$result = $this->db->get();
		return $result;
		return FALSE;
	}


	function estimate_record_count($search_keyword, $status)
	{

		$sql_query = "SELECT 		COUNT(estimates.estimate_id) as count
									FROM 		clients
									 
									INNER JOIN 	estimates
									
									ON 			clients.client_id=estimates.client_id 
									
									INNER JOIN 	leads
									
									ON 			leads.lead_id=estimates.lead_id 
									
									";

        if(is_cl_permission_owner()) {
            //$sql_query .= ' LEFT JOIN (' . $this->mdl_clients->getPermissionsSubQuery() . ') perm ON perm.client_id = estimates.client_id WHERE perm.client_id IS NOT NULL ';
            $sql_query .= ' WHERE estimates.user_id = ' . request()->user()->id . ' ';
        } elseif (is_cl_permission_none()) {
            $sql_query .= ' WHERE estimates.user_id = -1 ';
        } else {
            $sql_query .= " WHERE 1=1 ";
        }

		if (isset($search_keyword) && $search_keyword != "") {
			$sql_query .= " AND (clients.client_name LIKE '%" . $search_keyword . "%'
								OR clients.client_contact LIKE '%" . $search_keyword . "%'
								OR clients.client_address LIKE '%" . $search_keyword . "%'
								OR estimates.estimate_no LIKE '%" . $search_keyword . "%')";
		}

		if (isset($status) && $status != "") {
			$sql_query .= " AND estimates.status = '" . $status . "'";
		}

		$query = $this->db->query($sql_query)->row();
		return $query->count ?? 0;
	}

//*******************************************************************************************************************
//*************
//*************																				Get Total Estimated Cost
//*************
//*******************************************************************************************************************	
	function getTotalEstimatedCost($estimate_id)
	{
		$select_arr = array('arborist_report_price', 'trimming_service_price', 'tree_removal_price',
			'stump_grinding_price', 'wood_removal_price', 'site_cleanup_price',
			'extra_option_price'
		);
		$this->db->select($select_arr);

		$this->db->where('estimate_id', $estimate_id);

		$this->db->from($this->table);

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			$data = $query->result();

			$totalcost = 0;

			foreach ($data[0] as $item_cost) {
				$totalcost += $item_cost;
			}

			return $totalcost;

		} else {
			return FALSE;
		}

	}//end of getTotalEstimatedCost function


	//*******************************************************************************************************************
//*************
//*************																Get_estimate by id
//*************
//*******************************************************************************************************************	

	function get_estimate($id)
	{

		$query = $this->db->query("	SELECT 		*
												
									FROM 		estimates
									
									WHERE 			estimate_id=$id");

		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/

	} //* end get_leads()

	function get_estimates_with_estimator($wdata, $oneRow = FALSE)
	{
        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($wdata);

		$this->db->select('estimates.*, users.*, totals.sum_without_tax as estimate_price, lead_address, totals.*');
		$this->db->where($wdata);
		$this->db->join('users', 'estimates.user_id = users.id', 'left');
		$this->db->join('leads', 'leads.lead_id = estimates.lead_id', 'left');
		$this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id', 'left');
        $this->db->join("({$totalsSubQuery}) as totals", 'totals.estimate_id = estimates.estimate_id');
		$this->db->order_by('user_id');
		$this->db->group_by('estimates.estimate_id');

		if($oneRow)
			$result = $this->db->get('estimates')->row();
		else
			$result = $this->db->get('estimates')->result();
		
		return $result;
	}

	function find_by_id($id)
	{

		$this->db->select('estimates.*, clients.*, estimate_statuses.est_status_name as status, estimate_statuses.est_status_id as status_id, estimate_statuses.est_status_default, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.lead_country, leads.lead_no, users.*, clients_contacts.*, reference.name as reference_name, CONCAT(estimator.firstname, " ", estimator.lastname) as estimator_name');
		$this->db->join('leads', 'estimates.lead_id=leads.lead_id', 'left');
		$this->db->join('clients', 'estimates.client_id=clients.client_id', 'left');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
		$this->db->join('estimate_statuses', 'estimate_statuses.est_status_id=estimates.status', 'left');
		$this->db->join('users', 'estimates.user_id=users.id', 'left');
		$this->db->join('users as estimator', 'estimator.id=leads.lead_estimator', 'left');
		$this->db->join('reference', 'reference.id=leads.lead_reffered_by', 'left');

		$this->db->where('estimate_id', $id);
		$query = $this->db->get('estimates');
		return $query->row();
	}

	function find_all($wdata = [], $order = NULL)
	{
		$this->db->select('estimates.*, estimate_statuses.*');
		$this->db->join('estimate_statuses', 'estimate_statuses.est_status_id=estimates.status', 'left');
		if($wdata)
			$this->db->where($wdata);
		if($order)
			$this->db->order_by($order);
		$query = $this->db->get('estimates');
		return $query->result();
	}

	function update_estimate_balance($estimate_id)
	{
		$this->load->model('mdl_clients');
		$totalBalance = $this->get_total_estimate_balance($estimate_id);
		
		$this->db->where('estimate_id', $estimate_id);
		$this->db->update($this->table, array('estimate_balance' => $totalBalance));
		return $totalBalance;
	}

	function get_total_estimate_balance($id)
	{
		$this->load->model('mdl_estimates_orm');
		$this->load->model('mdl_invoices');
		$estimate = $this->mdl_estimates_orm->get($id);
		return $estimate->total_due;
	}

	function get_completed_estimate_balance($id)
	{
		$this->load->model('mdl_estimates_orm');
		$this->load->model('mdl_invoices');
		$estimate = $this->mdl_estimates_orm->getCompletedOnly($id);
		return $estimate->total_due;
	}

	function get_total_for_estimate($id, $wdata = array())
	{
		$this->load->model('mdl_estimates_orm');
		$estimate = $this->mdl_estimates_orm->get($id);
        $result['sum'] = isset($estimate->sum_without_tax) ? $estimate->sum_without_tax : 0;
		return $result;
	}

	function find_estimate_services($id = NULL, $where_in = array()) : array
	{
		$this->db->select('services.service_is_collapsed, services.service_name, services.is_product, services.is_bundle, services.service_markup, services.service_qb_id, estimates_services.*, COUNT(crew_service_id) as count_members, tree_inventory_estimate_services.*, CONCAT(trees.trees_name_eng, " (", trees.trees_name_lat, ")") as ties_type');
		$this->db->where('estimate_id', $id);
		if(!empty($where_in))
			$this->db->where($where_in);
		$this->db->join('services', 'services.service_id = estimates_services.service_id');
		$this->db->join('estimates_services_crews', 'estimates_services_crews.crew_service_id = estimates_services.id', 'LEFT');
		$this->db->join('tree_inventory_estimate_services', 'tree_inventory_estimate_services.ties_estimate_service_id = estimates_services.id', 'LEFT');
		$this->db->join('trees', 'trees.trees_id = tree_inventory_estimate_services.ties_type', 'LEFT');

		//$this->db->order_by('services.service_priority');
		$this->db->order_by('estimates_services.service_priority');
		
		$this->db->group_by('estimates_services.id');
		return $this->db->get('estimates_services')->result_array();
	}

	function find_estimate_service($id)
	{
		$this->db->select('estimates_services.*, services.service_name');
		$this->db->where('id', $id);
		$this->db->join('services', 'estimates_services.service_id = services.service_id');
		return $this->db->get('estimates_services')->row_array();
	}

	function insert_estimate_service($data)
	{
		$this->db->insert('estimates_services', $data);
		return $this->db->insert_id();
	}

	function delete_estimate_service($wdata)
	{
		$this->db->where($wdata);
		$this->db->delete('estimates_services');
		return TRUE;
	}

	function update_estimate_service($wdata, $data)
	{
		$this->db->where($wdata);
		$this->db->update('estimates_services', $data);
		return TRUE;
	}

	function update_priority($updateBatch)
	{
		$this->db->update_batch('estimates_services', $updateBatch, 'id');
		return TRUE;
	}

	function find_estimate_qa($id)
	{
		$this->db->where('estimate_id', $id);
		$this->db->join('qa', 'qa.qa_id = estimates_qa.qa_id');
		$this->db->order_by('qa_date', 'DESC');
		return $this->db->get('estimates_qa')->result_array();
	}

	function insert_estimate_qa($data)
	{
		$this->db->insert('estimates_qa', $data);
		return $this->db->insert_id();
	}

	function find_contacts($estimate_id)
	{
		$this->db->where('call_estimate_id', $estimate_id);
		$this->db->order_by('call_time', 'DESC');
		return $this->db->get('estimates_calls')->result_array();
	}

	function insert_contact($data)
	{
		$this->db->insert('estimates_calls', $data);
		return TRUE;
	}

	function stat_estimates($where = array())
	{
		$this->db->select('COUNT( WEEKDAY ( date_created ) ) AS count, DAYNAME( date_created ) AS weekday');
		if(!empty($where))
			$this->db->where($where);
		$this->db->group_by('weekday');
		$this->db->order_by('count', 'DESC');
		$query = $this->db->get('estimates');
		return $query->result_array();
	}

	function find_estimate_equipments($id, $wdata = array())
	{
		$this->db->group_by('equipment_item_id');
		$this->db->where('equipment_estimate_id', $id);
		$result = $this->db->get('estimates_services_equipments')->result_array();
		return $result;
	}

	function find_estimate_crews($id, $wdata = array())
	{

		

		/*-------------dima --------------
		$this->db->select("crews.crew_name, crews.crew_leader, estimates_services_crews.*, users.id as crew_leader, users.id as crew_user_id, CONCAT(users.firstname, ' ', users.lastname) as emp_name", FALSE);
		$this->db->where('crew_estimate_id', $id);
		if ($wdata)
			$this->db->where($wdata);
		$this->db->join('crews', 'crews.crew_id = estimates_services_crews.crew_user_id');
		$this->db->join('employees', 'crews.crew_leader = employees.employee_id', 'left');
		$this->db->join('users', 'employees.emp_user_id = users.id', 'left');
		$this->db->order_by('estimates_services_crews.crew_user_id DESC');
		$this->db->group_by('estimates_services_crews.crew_user_id');
		return $this->db->get('estimates_services_crews')->result_array();
		/*-------------dima --------------*/

		/*-------------------ruslan--------------*/
		/*$this->db->select("GROUP_CONCAT(crew_name SEPARATOR ', ') as crew_name, crews.crew_leader, estimates_services_crews.*, employees.emp_name", FALSE);
		$this->db->from('estimates_services_crews');
		$this->db->where('crew_estimate_id', $id);
		if ($wdata)
			$this->db->where($wdata);
		$this->db->join('crews', 'crews.crew_id = estimates_services_crews.crew_user_id');
		$this->db->join('employees', 'crews.crew_leader = employees.employee_id', 'left');
		$this->db->group_by('crew_service_id');

		$subquery = $this->db->_compile_select();
		$this->db->_reset_select();

		$this->db->from($subquery . ' as crew_name', FALSE);
		$this->db->group_by('crew_name.crew_name');
		return $this->db->get();


		return $this->db->get('estimates_services_crews')->result_array();*/
		/*-------------------ruslan--------------*/



		$query = "SELECT * FROM(
		    SELECT GROUP_CONCAT(crew_name ORDER BY crew_name DESC SEPARATOR ',') as crew_name, crews.crew_leader, estimates_services_crews.*, NULL as emp_name
			FROM (`estimates_services_crews`)
			JOIN `crews` ON `crews`.`crew_id` = `estimates_services_crews`.`crew_user_id`
			WHERE `crew_estimate_id` = $id ";

		foreach ($wdata as $key => $value) {
			$query .= " AND $key = '$value'";
		}

		$query .= "GROUP BY crew_service_id) crew_name GROUP BY crew_name.crew_name";
		return $this->db->query($query)->result_array();
	}

	function find_estimate_crews_app($id, $wdata = array()) {
		$query = "SELECT * FROM(
		    SELECT GROUP_CONCAT(crew_name ORDER BY crew_name DESC SEPARATOR ', ') as crew_name, estimates_services_crews.crew_estimate_id
			FROM (`estimates_services_crews`)
			JOIN `crews` ON `crews`.`crew_id` = `estimates_services_crews`.`crew_user_id`
			WHERE `crew_estimate_id` = $id ";

		foreach ($wdata as $key => $value) {
			$query .= " AND $key = '$value'";
		}

		$query .= "GROUP BY crew_service_id) crew_name GROUP BY crew_name.crew_name";

		return $this->db->query($query)->result_array();
	}

	function delete_estimate_crews($wdata)
	{
		$this->db->where($wdata);
		$this->db->delete('estimates_crews');
		return TRUE;
	}

	function replace_estimate_crew($data)
	{
		$this->db->set('estimate_id', $data['estimate_id']);
		$this->db->set('crew_id', $data['crew_id']);
		$this->db->set('estimate_crew_team', $data['estimate_crew_team']);
		if (!isset($data['estimate_crew_id'])) {
			$this->db->insert('estimates_crews');
			return $this->db->insert_id();
		} else {
			$this->db->where('estimate_crew_id', $data['estimate_crew_id']);
			$this->db->update('estimates_crews');
			return TRUE;
		}
		return FALSE;
	}

	function update_estimate_crew($e_crew_id, $data)
	{
		$this->db->where('estimate_crew_id', $e_crew_id);
		$this->db->update('estimates_crews', $data);
		return TRUE;
	}
	
	function get_estimate_total_sum($wdata)
	{
        $this->db->select('MAX(status_log.status_id) as status_id');
        $this->db->from('status_log');
        $this->db->join('invoice_statuses', 'status_log.status_value = invoice_statuses.invoice_status_id AND invoice_statuses.completed=1');
        $this->db->where('status_log.status_type', 'invoice');
        $this->db->where('status_log.status_date >=', strtotime($wdata['from']));
        if(isset($wdata['to']))
            $this->db->where('status_log.status_date <=', strtotime($wdata['to']));
        $this->db->group_by('status_log.status_item_id');
        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();


		$this->db->select('invoices.estimate_id');
		$this->db->where('status_log.status_type', 'invoice');
		$this->db->where('status_log.status_date >=', strtotime($wdata['from']));
		if(isset($wdata['to']))
			$this->db->where('status_log.status_date <=', strtotime($wdata['to']));
		
		$this->db->join('invoices', 'invoices.id = status_log.status_item_id');
		$this->db->join('invoice_statuses `is`', 'invoices.in_status = is.invoice_status_id AND is.completed=1');
        $this->db->join('invoice_statuses `is2`', 'status_log.status_value = is2.invoice_status_id AND is2.completed=1');

        $this->db->join("($subquery) max_status", "max_status.status_id = status_log.status_id");
		$this->db->group_by('invoices.estimate_id');
		$query = $this->db->get('status_log');
		return $query->result_array();
	}
	
	function estimate_sum_and_hst($id)
	{
		$this->load->model('mdl_estimates_orm');
		$estimate = $this->mdl_estimates_orm->get($id);

		$balance['total'] = $estimate->sum_without_tax ?: 0;
		$balance['hst'] = $estimate->total_tax ?: 0;
		$balance['payments'] = $estimate->payments_total ?: 0;

		return $balance;
	}

    function estimate_completed_sum_and_hst($id)
    {
        $this->load->model('mdl_estimates_orm');
        $estimate = $this->mdl_estimates_orm->getCompletedOnly($id);

        $balance['total'] = $estimate->sum_without_tax;
        $balance['hst'] = $estimate->total_tax;
        $balance['payments'] = $estimate->payments_total;

        return $balance;
    }
	
	function get_full_estimate_data($wdata = array(), $sum = FALSE)
	{
		if($sum)
			$this->db->select("COUNT(estimate_id) AS count_estimates", FALSE);
		$this->db->join('clients', 'clients.client_id = estimates.client_id');
		$this->db->join('leads', 'leads.lead_id = estimates.lead_id');
		if(!empty($wdata))
			$this->db->where($wdata);
		
		if($sum)
			return $this->db->get('estimates')->row_array();
		return $this->db->get('estimates')->result_array();
	}
	
	function get_estimate_statistic($where = array())
	{
		$this->db->select('COUNT(*) as count, SUM( estimates_services.service_price ) AS sum');
		$this->db->join('estimate_statuses', 'estimates.status = estimate_statuses.est_status_id', 'left');
		$this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id', 'left');
		$this->db->join('users', 'estimates.user_id = users.id', 'left');
		$this->db->join('employees', 'users.id = employees.emp_user_id', 'left');
		
		if(!empty($where))
			$this->db->where($where);
		//SELECT COUNT(*) as count, SUM( estimates_services.service_price ) AS SUM FROM estimates
		//JOIN estimate_statuses ON estimates.status = estimate_statuses.est_status_id
		//JOIN estimates_services ON estimates.estimate_id = estimates_services.estimate_id
		//JOIN users ON estimates.user_id = users.id
		//JOIN employees ON users.emailid = employees.emp_username
		//WHERE employees.emp_status = 'current'
		//GROUP BY users.id
		$this->db->group_by('estimates.estimate_id');
		$query = $this->db->get('estimates');
		return $query->result_array();
	}

    function get_dashboard_estimate_statistic($where = array())
    {
        $this->db->select('SUM( estimates_services.service_price ) AS sum');
        $this->db->join('estimate_statuses', 'estimates.status = estimate_statuses.est_status_id', 'left');
        $this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id', 'left');
        $this->db->join('users', 'estimates.user_id = users.id', 'left');
        $this->db->join('employees', 'users.id = employees.emp_user_id', 'left');

        if(!empty($where))
            $this->db->where($where);
        $query = $this->db->get('estimates');
        if($query->num_rows() === 0)
            return 0;
        $res = $query->row();
        return round($res->sum,2);
    }

	function get_active_estimators()
	{
		$this->db->select('users.id, users.firstname, users.lastname, users.user_email');
		$this->db->join('employees', 'users.id = employees.emp_user_id');
		$this->db->where('employees.emp_field_estimator = "1"');
		$this->db->where('employees.emp_status = "current"');
		$query = $this->db->get('users');
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/
		//SELECT users.id, users.firstname, users.lastname, employees . * 
		//FROM users
		//JOIN employees ON users.emailid = employees.emp_username
		//WHERE employees.emp_field_estimator =1
		//AND employees.emp_status =  'current'
	}
	
	function get_all_estimators($where = [])
	{
		$this->db->select('users.id, users.firstname, users.lastname, users.user_email');
		$this->db->join('employees', 'users.id = employees.emp_user_id');
		$this->db->where('employees.emp_field_estimator = "1"');
		if($where && count($where))
			$this->db->where($where);
		$query = $this->db->get('users');
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/
		//SELECT users.id, users.firstname, users.lastname, employees . * 
		//FROM users
		//JOIN employees ON users.emailid = employees.emp_username
		//WHERE employees.emp_field_estimator =1
		//AND employees.emp_status =  'current'
	}

	function delete_estimate($estimate_id)
	{
		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('client_payments');
		
		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('discounts');
		
		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('project');

        $this->db->where('estimate_id', $estimate_id);
        $estimateServices = $this->db->get('estimates_services')->result_array();
        if(!empty($estimateServices)){
            foreach ($estimateServices as $service){
                TreeInventoryEstimateService::where('ties_estimate_service_id', $service['id'])->delete();
                $this->db->where('eb_service_id', $service['id']);
                $this->db->delete('estimates_bundles');
            }
        }

		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('estimates_services');
		
		$this->db->where('crew_estimate_id', $estimate_id);
		$this->db->delete('estimates_services_crews');
		
		$this->db->where('equipment_estimate_id', $estimate_id);
		$this->db->delete('estimates_services_equipments');
		
		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('estimates_crews');
		
		$this->db->where('call_estimate_id', $estimate_id);
		$this->db->delete('estimates_calls');
		
		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('estimates_qa');
		
		$this->db->where('estimate_id', $estimate_id);
		$this->db->delete('estimates');
		//$sql = 'DELETE estimates, client_payments, discounts, project, estimates_services, estimates_services_crews, estimates_services_equipments, estimates_crews, estimates_calls, estimates_qa  FROM estimates ';
		//$sql .= 'LEFT JOIN client_payments ON estimates.estimate_id = client_payments.estimate_id ';
		//$sql .= 'LEFT JOIN discounts ON estimates.estimate_id = discounts.estimate_id ';
		//$sql .= 'LEFT JOIN project ON estimates.estimate_id = project.estimate_id ';
		//$sql .= 'LEFT JOIN estimates_services ON estimates.estimate_id = estimates_services.estimate_id ';
		//$sql .= 'LEFT JOIN estimates_services_crews ON estimates.estimate_id = estimates_services_crews.crew_estimate_id ';
		//$sql .= 'LEFT JOIN estimates_services_equipments ON estimates.estimate_id = estimates_services_equipments.equipment_estimate_id ';
		//$sql .= 'LEFT JOIN estimates_crews ON estimates.estimate_id =  estimates_crews.estimate_id ';
		//$sql .= 'LEFT JOIN estimates_calls ON estimates.estimate_id = estimates_calls.call_estimate_id ';
		//$sql .= 'LEFT JOIN estimates_qa ON estimates.estimate_id = estimates_qa.estimate_id ';
		//$sql .= 'WHERE estimates.estimate_id = ' . intval($estimate_id);
		//$this->db->query($sql);
		return true;
	}
	
	function get_count_estimates($wdata = array())
	{
		
		$this->db->select("COUNT(estimate_id) AS count_estimates, CONCAT(firstname, ' ', lastname) AS username", FALSE);
		$this->db->join('users', 'users.id = estimates.user_id');
		if(!empty($wdata))
			$this->db->where($wdata);
		$this->db->group_by('user_id');
		$this->db->order_by('count_estimates', 'DESC');
		$query = $this->db->get($this->table);
		return $query->result_array();
	}
	
	function global_search_estimates($date = [], $services = [], $estimate_price = [], $service_price = [], $estimator = '', $andWorkers = [], $orWorkers = [],  $status = '', $note = '', $limit = '', $start = '')
	{
		/*
			
			SELECT estimates.*, estimate_statuses.est_status_name, CONCAT(estimator.firstname, ' ', estimator.lastname) AS estimator, clients.client_name, clients_contacts.cc_phone, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, 
			(
				SUM(estimates_services.service_price) 
				/ IF(COUNT(DISTINCT(users.id)), COUNT(DISTINCT(users.id)), 1)
				/ IF(COUNT(DISTINCT(schedule_teams.team_id)), COUNT(DISTINCT(schedule_teams.team_id)), 1)
			)
				as estimate_price

			FROM (`estimates`)
			JOIN `leads` ON `estimates`.`lead_id` = `leads`.`lead_id`
			JOIN `estimates_services` ON `estimates`.`estimate_id` = `estimates_services`.`estimate_id`
			JOIN `services` ON `estimates_services`.`service_id` = `services`.`service_id`
			JOIN `estimate_statuses` ON `estimates`.`status` = `estimate_statuses`.`est_status_id`
			JOIN `users` estimator ON `estimates`.`user_id` = `estimator`.`id`
			JOIN `clients` ON `estimates`.`client_id` = `clients`.`client_id`
			LEFT JOIN `clients_contacts` ON `clients`.`client_id` = `clients_contacts`.`cc_client_id` AND cc_print = 1
			LEFT JOIN `workorders` ON `estimates`.`estimate_id` = `workorders`.`estimate_id`
			LEFT JOIN `schedule` ON `workorders`.`id` = `schedule`.`event_wo_id`
			LEFT JOIN `schedule_teams` ON `schedule`.`event_team_id` = `schedule_teams`.`team_id`
			LEFT JOIN `schedule_teams_members` ON `schedule_teams`.`team_id` = `schedule_teams_members`.`employee_team_id`
			LEFT JOIN `users` ON `schedule_teams_members`.`user_id` = `users`.`id`
			WHERE (estimates.date_created >= 1583211600 AND estimates.date_created <= 1583297999)
			GROUP BY `estimates`.`estimate_id`
			ORDER BY `estimates`.`estimate_id` ASC
			LIMIT 1000

		*/


		if(is_array($andWorkers) && !empty($andWorkers)) {
			$this->db->select('schedule.id');
			$this->db->from('schedule');
			$this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id', 'left');
			$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
			$this->db->join('users', 'schedule_teams_members.user_id = users.id', 'left');
			$this->db->where_in('schedule_teams_members.user_id', $andWorkers);
			$this->db->group_by('schedule.id');
			$this->db->having('COUNT(schedule.id) >= ' . count($andWorkers));//countOk
			$subquery = $this->db->_compile_select();
			$this->db->_reset_select();
		}

        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery();
        $clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();

		$this->db->select("estimates.*, totals.sum_without_tax, estimate_statuses.est_status_name, CONCAT(estimator.firstname, ' ', estimator.lastname) AS estimator, clients.client_name, clients_contacts.cc_phone, leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, (SUM(estimates_services.service_price) / IF(COUNT(DISTINCT(users.id)), COUNT(DISTINCT(users.id)), 1)/* / IF(COUNT(DISTINCT(schedule_teams.team_id)), COUNT(DISTINCT(schedule_teams.team_id)), 1)*/) as estimate_price", FALSE);


        $this->db->join("({$totalsSubQuery}) totals", 'estimates.estimate_id = totals.estimate_id', 'left', FALSE);

		//$this->db->join('users', 'estimates.user_id = users.id', 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
		$this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id');
		$this->db->join('services', 'estimates_services.service_id = services.service_id');
		$this->db->join('estimate_statuses', 'estimates.status = estimate_statuses.est_status_id');
		$this->db->join('users estimator', 'estimates.user_id = estimator.id');
		$this->db->join('clients', 'estimates.client_id = clients.client_id');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
		
		
		$this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
		$this->db->join('schedule', 'workorders.id = schedule.event_wo_id', 'left');
		$this->db->join('schedule_teams', 'schedule.event_team_id = schedule_teams.team_id', 'left');
		$this->db->join('schedule_teams_members', 'schedule_teams.team_id = schedule_teams_members.employee_team_id', 'left');
		$this->db->join('users', 'schedule_teams_members.user_id = users.id', 'left');

		if(!empty($andWorkers)) {
			$this->db->join("($subquery) event_workers", "schedule.id = event_workers.id");
		}

        if (is_array($date) && !empty($date)) {
            if (count($date) > 1)//countOk
				$queryDate = '(estimates.date_created >= ' . $date['date_created >='] . ' AND estimates.date_created <= ' . $date['date_created <='] . ')' ;
			else
			{
				foreach($date as $k=>$v)
					$queryDate = '(estimates.'. $k .' ' .$v.')';
			}
			$this->db->where($queryDate);
		}

        if (is_array($services) && !empty($services)) {
            if (count($services) > 1)//countOk
			{
				$queryServices = '(';
				
				foreach($services as $k=>$v)
				{
					$queryServices .= 'estimates_services.service_id = ' . $v;
					if(isset($services[$k+1]))
						$queryServices .= ' OR ';
				}
				$queryServices .= ')';
			}
			else
				$queryServices = '(estimates_services.service_id = ' . $services . ')';
			$this->db->where($queryServices);
		} else if(intval($services))
            $this->db->where('estimates_services.service_id', $services);

        if (is_array($service_price) && !empty($service_price)) {
            if (count($service_price) > 1)//countOk
                $querySerPrice = '(estimates_services.service_price >= "' . $service_price['service_price >='] . '" AND estimates_services.service_price <= "' . $service_price['service_price <='] . '")';
            else {
                foreach ($service_price as $k => $v)
                    $querySerPrice = '(' . $k . ' "' . $v . '")';
            }
            $this->db->where($querySerPrice);
        }
		if($estimator != '')
			$this->db->where('estimates.user_id', $estimator);
        if (is_array($orWorkers) && !empty($orWorkers)) {
            if (count($orWorkers) > 1)//countOk
			{
				$queryMembers = '(';
				
				foreach($orWorkers as $k=>$v)
				{
					$queryMembers .= 'schedule_teams_members.user_id = ' . $v;
					if(isset($orWorkers[$k+1]))
						$queryMembers .= ' OR ';
				}
				$queryMembers .= ')';
			}
			else
				$queryMembers = '(schedule_teams_members.user_id = ' . $orWorkers[0] . ')';
			$this->db->where($queryMembers);
		}
		if($status != '')
			$this->db->where('estimates.status', $status);
		if($note != '')
		{
			$this->db->like('estimates.estimate_item_team', $note); 
			$this->db->or_like('estimates.estimate_item_estimated_time', $note); 
			$this->db->or_like('estimates.estimate_item_note_crew', $note); 
			$this->db->or_like('estimates.estimate_item_note_estimate', $note); 
			$this->db->or_like('estimates.estimate_item_note_payment', $note); 
			$this->db->or_like('estimates_services.service_description', $note); 
			$this->db->or_like('services.service_description', $note); 
			$this->db->or_like('schedule.event_note', $note); 
			$this->db->or_like('schedule_teams.team_note', $note); 
			$this->db->or_like('schedule_teams.team_hidden_note', $note); 
		}
		$this->db->group_by('estimates.estimate_id');
		$this->db->order_by('estimates.estimate_id', 'ASC');

        if (is_array($estimate_price) && !empty($estimate_price)) {
            if (count($estimate_price) > 1)//countOk
                $this->db->having('(SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ >= "' . $estimate_price['>='] . '" AND SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ <= "' . $estimate_price['<='] . '")');
            else {
                foreach ($estimate_price as $k => $v)
                    $this->db->having('(SUM(estimates_services.service_price) /* / IF(COUNT(DISTINCT(client_tasks.task_id)), COUNT(DISTINCT(client_tasks.task_id)), 1) */ ' . $k . ' "' . $v . '")');
            }
        }

        if ($limit != '') {
			$this->db->limit($limit, $start);
		}

        if(is_cl_permission_owner() && $clientPermissionsSubQuery) {
            $this->db->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = estimates.client_id', 'left');
            $this->db->where('perm.client_id IS NOT NULL');
        } elseif (is_cl_permission_none()) {
            $this->db->where('estimates.user_id', -1);
        }

		$result = $this->db->get('estimates');

		
		return $result;
	}
	
	function get_total_for_estimate_by($wdata = [], $extraJoin = [])
	{
        if (empty($wdata) || !is_array($wdata)) {
            return FALSE;
        }
	    $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($wdata, $extraJoin);

        $this->db->select_sum('sum_without_tax', 'sum');
        $this->db->join("({$totalsSubQuery}) totals", 'estimates.estimate_id = totals.estimate_id', 'left', FALSE);
        $this->db->join("leads", 'estimates.lead_id = leads.lead_id');
        $this->db->where($wdata);
        $this->db->group_by('estimates.client_id');

        return $this->db->get('estimates')->row_array();
	}

    function get_total_for_estimate_by_lead_cc($wdata = array())
    {
        if (empty($wdata) || !is_array($wdata)) {
            return FALSE;
        }
        $this->load->model('mdl_estimates_orm');
        $totalsSubQuery = $this->mdl_estimates_orm->calcQuery($wdata, [['table' => 'leads', 'condition' => 'estimates.lead_id = leads.lead_id']]);

        $this->db->select_sum('sum_without_tax', 'sum');
        $this->db->join("({$totalsSubQuery}) totals", 'estimates.estimate_id = totals.estimate_id', 'left', FALSE);
        $this->db->join("leads", 'estimates.lead_id = leads.lead_id');
        $this->db->where($wdata);
        $this->db->group_by('estimates.client_id');

        return $this->db->get('estimates')->row_array();
    }

	function get_three_days_estimates($date = FALSE, $wdata = array())
	{
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id', 'left');
		if($date)
			$this->db->where("DATEDIFF( '" . $date . "', FROM_UNIXTIME(date_created, '%Y-%m-%d')) = 3");
		$this->db->where('(status = 1 OR status = 2)');
		if(!empty($wdata))
			$this->db->where($wdata);
		$result = $this->db->get('estimates')->result_array();
		return $result;
	}
	
	function get_estimates_by_service($where)
	{
		$this->db->join('estimates', 'estimates_services.estimate_id = estimates.estimate_id');
		$this->db->where($where);
		$result = $this->db->get('estimates_services')->result_array();
		return $result;
	}


	function get_followup($statusList = [], $periodicity = NULL, $every = FALSE, $clientTypes = FALSE)
	{
		$followUpConfig = $this->config->item('followup_modules')['estimates'];		
		$dbStatuses = [];
		foreach ($statusList as $value) {
			//if(isset($followUpConfig['statuses'][$value]))
				$dbStatuses[] = $value;
		}

		$this->db->select("DATEDIFF('" . date('Y-m-d') . "', FROM_UNIXTIME(IFNULL(MAX(status_log.status_date), date_created))) as datediff, IFNULL(MAX(status_log.status_date), date_created) as this_status_date, clients.client_id, users.id as estimator_id, clients_contacts.*, estimates.*", FALSE);

		$this->db->join('status_log', "status_item_id = estimate_id AND status = status_value AND status_type = 'estimate'", 'left');
		$this->db->join('leads', 'estimates.lead_id = leads.lead_id');
		$this->db->join('clients', 'estimates.client_id = clients.client_id');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
        $this->db->join('client_tags', 'clients.client_id = client_tags.client_id', 'left');
        $this->db->join('followup_settings_tags', 'followup_settings_tags.tag_id = client_tags.tag_id', 'left');
		$this->db->join('users', 'users.id = estimates.user_id', 'left');

		$this->db->where_in('status', $dbStatuses);
		if($clientTypes)
			$this->db->where_in('client_type', $clientTypes);

		if(!$every)
			$this->db->having("FROM_UNIXTIME(this_status_date + 3600 + " . (intval($periodicity) * 86400) . ", '%Y-%m-%d') = '" . date('Y-m-d') . "'");
		else
			$this->db->having("(datediff % " . intval($periodicity) . ") = 0 AND datediff > 0");
		/*$this->db->limit(1);*/
		$this->db->group_by('estimate_id');

		return $this->db->get($this->table)->result_array();
	}

	function get_followup_variables($id)
	{
		$estimate = $this->find_by_id($id);

		$result['JOB_ADDRESS'] = $estimate->lead_address;
		$result['ADDRESS'] = $estimate->client_address;
		$result['EMAIL'] = $estimate->cc_email;
		$result['PHONE'] = $estimate->cc_phone;
		$result['NAME'] = $estimate->cc_name;
		$result['NO'] = $estimate->estimate_no;
		$result['LEAD_NO'] = $estimate->lead_no;
		$result['ESTIMATE_NO'] = $estimate->estimate_no;
		$result['INVOICE_NO'] = NULL;
		$result['ESTIMATOR_NAME'] = $estimate->firstname . ' ' . $estimate->lastname;
		
		$totalForEstimate = $this->get_total_for_estimate($id);
        $result['AMOUNT'] = money($totalForEstimate['sum']);
        $result['TOTAL_DUE'] = money($this->get_total_estimate_balance($id));
		$result['CCLINK'] = '<a href="' . $this->config->item('payment_link') . 'payments/' . md5($estimate->estimate_no . $estimate->client_id) . '">link</a>';
		$result['SIGNATURELINK'] = '<a href="' . $this->config->item('payment_link') . 'payments/estimate_signature/' . md5($estimate->estimate_id) . '">link</a>';

		return $result;
	}
	
	function get_estimates_with_status($where = [])
	{
		
		//$this->db->join('status_log', "estimates.estimate_id = status_log.status_item_id AND status_type = 'estimate'");
		$this->db->select('status_item_id, max(status_log.status_id) as  last_id', FALSE);
        $this->db->from('status_log');

		$this->db->group_by('status_item_id');

        $subquery = $this->db->_compile_select();
		$this->db->_reset_select();
		$this->db->select('estimates.*, status_log.*, estimate_statuses.*');
		$this->db->join("($subquery) as stat_info", "estimates.estimate_id=stat_info.status_item_id", 'left');
		$this->db->join("status_log", "stat_info.last_id = status_log.status_id");
		$this->db->join("estimate_statuses", "estimate_statuses.est_status_id = estimates.status");
		
		
		if($where && !empty($where))
			$this->db->where($where);
		//$this->db->limit(10, 0);
		$result = $this->db->get($this->table);
		//if ($result->num_rows() > 0) {
			return $result->result_array();
		/*} else {
			return FALSE;
		}*/
	}

	function get_last_client_estimate($clientId)
	{
		$this->db->where('client_id', $clientId);
		$this->db->join('users', 'users.id = estimates.user_id');
		$this->db->order_by('date_created', 'DESC');
		$this->db->limit(1);
		return $this->db->get('estimates')->row();
	}

    function get_same_client_estimates($estimate_id)
    {
        $this->db->select('client_id',FALSE);
        $this->db->from('estimates');
        $this->db->where('estimate_id',$estimate_id);
        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();
        $this->db->select('estimates.estimate_id, estimates.estimate_no', FALSE);
        $this->db->join("estimate_statuses", "estimates.status = estimate_statuses.est_status_id",'left');
        $this->db->join("invoices", "estimates.estimate_id = invoices.estimate_id",'left');
        $this->db->join("invoice_statuses", "invoices.in_status = invoice_statuses.invoice_status_id",'left');
        $this->db->where("estimates.client_id = ($subquery)");
        $this->db->where("(estimate_statuses.est_status_declined IS NULL OR estimate_statuses.est_status_declined <> 1 OR estimates.estimate_id = $estimate_id)");
        $this->db->where("(invoice_statuses.completed IS NULL OR invoice_statuses.completed <> 1 OR estimates.estimate_id = $estimate_id)");
        $result = $this->db->get($this->table);
        return $result->result_array();
    }


    function get_estimators_stat($estimator_id = false, $from = false, $to = false, $where = [], $sum = false)
    {
        $wdata = [];
        $wo_group = 'workorders.estimate_id';
        $calc_group = 'estimates.estimate_id';
        $sub_group = 'workorders.id';
        if ($sum) {
            $wo_group = 'estimates.user_id';
            $sub_group = '';
        }
        $this->load->model('mdl_estimates_orm');
        $this->load->model('mdl_workorders');
        if ($from && $to)
            $wdata = ['estimates.date_created >= ' => $from, 'estimates.date_created <= ' => $to];
        else
            $wdata = $where;
        if ($estimator_id !== false)
            $wdata['estimates.user_id'] = $estimator_id;

        $this->db->select('schedule.event_wo_id, ROUND((((schedule.event_end - schedule.event_start)/3600)/((MAX(`e`.`event_end`) - MIN(`e`.`event_start`))/3600))*schedule_teams.team_man_hours, 2) as event_man_hours, schedule.event_price as event_total', FALSE);/*ROUND((((schedule.event_end - schedule.event_start)/3600)/((MAX(`e`.`event_end`) - MIN(`e`.`event_start`))/3600))*schedule_teams.team_amount, 2) OLD event_total val*/

        $this->db->from('schedule');
        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->db->join('invoices', 'estimates.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('estimates_services', "estimates_services.estimate_id=estimates.estimate_id", 'left');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('schedule e', 'e.event_team_id = schedule.event_team_id', 'left');
        $this->db->join('users est', 'est.id = estimates.user_id', 'left');

        if ($where)
            $this->db->where($where);
        if ($estimator_id !== false)
            $this->db->where(['estimates.user_id' => $estimator_id]);
        // $this->db->where(['schedule.event_start >= ' => $from, 'schedule.event_end <= ' => $to]);
        $this->db->group_by('schedule_teams.team_id');

        $estimate_totals = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select('workorders.id, workorders.workorder_no, SUM(IFNULL(event_man_hours, 0)) as event_man_hours, COUNT(DISTINCT(workorders.id)) as quantity');
        $this->db->from('workorders');
        $this->db->join('estimates', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->db->join("($estimate_totals) t", "workorders.id = t.event_wo_id");

        $this->db->group_by($sub_group);
        $subquery = $this->db->_compile_select();
        $this->db->_reset_select();


        $calcQuery = $this->mdl_estimates_orm->calcQuery($wdata, [], $calc_group);
        //echo '<pre>'; var_dump($calcQuery); die;
        if ($sum) {

            $this->db->select('SUM(sum_services_without_discount) as sum_services_without_discount, SUM(discount_total) as discounts, a.estimate_user_id', false);
            $this->db->from("($calcQuery) as a", false);
            $this->db->group_by('a.estimate_user_id');
            $aQuery = $this->db->get();
            $calQuery = $aQuery->row_array();
        }
        $this->db->_reset_select();

        $calcSiteTime = $this->mdl_workorders->calcSiteTime($wdata, $wo_group);
        $this->db->_reset_select();
        $select = "ROUND(SUM(IFNULL(expenses.expense_amount, 0)), 2) as expenses_total, estimates.estimate_id";
        $this->db->select($select, FALSE);
        $this->db->from('estimates');
        $this->db->where($where);
        $this->db->where($wdata);
        $this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('invoices', 'estimates.estimate_id = invoices.estimate_id', 'left');
        $this->db->join('schedule', 'schedule.event_wo_id = workorders.id', 'left');
        $this->db->join('expenses', 'expenses.expense_event_id = schedule.id', 'left');
        $this->db->join('employees', 'employees.emp_user_id = estimates.user_id', 'left');

        if ($sum)
            $this->db->group_by('estimates.user_id');
        else
            $this->db->group_by('estimates.estimate_id');

        $expensesSubQuery = $this->db->_compile_select();
        $this->db->_reset_select();

        if($sum)
            $this->db->select("SUM(IFNULL(`totals`.sum_services_without_discount, 0)) as sales, SUM(IFNULL(`estimates_total`.event_man_hours, 0)) as event_man_hours, 
			SUM(IFNULL(`totals`.discount_total, 0)) as discounts, 
			SUM(IFNULL(expenses.expenses_total, 0)) as direct_expenses, COUNT(DISTINCT(totals.estimate_id)) as quantity, SUM(IFNULL(`mhrs`.sum_mhrs, 0)) as crew_hrs");
        else
        {
            $this->db->select("estimate_reason_status.reason_name, expenses.*, estimate_statuses.est_status_name, estimates.date_created, clients_contacts.cc_phone, totals.*, mhrs.*, clients.*, estimates_total.*");
        }

        //$this->db->join("clients", "clients.client_id = estimates.client_id", "left");
        //if (!$sum)
            $this->db->join("($calcQuery) totals", "totals.estimate_id = estimates.estimate_id");
        $this->db->join("($calcSiteTime) mhrs", "mhrs.estimate_id = estimates.estimate_id", "left");
        $this->db->join("workorders", "workorders.estimate_id = estimates.estimate_id", 'left');
        $this->db->join("invoices", "estimates.estimate_id = invoices.estimate_id", 'left');
        $this->db->join('schedule', 'schedule.event_wo_id = workorders.id', 'left');
        $this->db->join("clients", "clients.client_id = estimates.client_id", 'left');
        $this->db->join('employees', 'employees.emp_user_id = estimates.user_id', 'left');
        if (!$sum) {
            $this->db->join("estimate_reason_status", "estimate_reason_status.reason_id = estimates.estimate_reason_decline", 'left');
            $this->db->join("estimate_statuses", "estimate_statuses.est_status_id = estimates.status", 'left');
            $this->db->join("clients_contacts", "clients_contacts.cc_client_id = clients.client_id AND clients_contacts.cc_print = 1", 'left');
        }

        /*else
        {

        }*/
        $this->db->join("($subquery) estimates_total", "estimates_total.id=workorders.id", 'left');
        $this->db->join("({$expensesSubQuery}) expenses", 'expenses.estimate_id = estimates.estimate_id', 'left', FALSE);
        if ($sum)
            $this->db->group_by('estimates.user_id');
        else
            $this->db->group_by('estimates.estimate_id');
        //$this->db->join("estimate_statuses", "estimate_statuses.est_status_id = estimates.status");
        //$this->db->join("estimate_reason_status", "estimate_reason_status.reason_id = estimates.estimate_reason_decline", "left");
        $this->db->where($wdata);

        if ($where && count($where))
            $this->db->where($where);

        $query = $this->db->get('estimates');

        if ($sum) {
            $result = $query->row_array();
            if(isset($calQuery['discounts'])) {
                $result['discounts'] = $calQuery['discounts'];
            }
            if(isset($calQuery['sum_services_without_discount'])) {
                $result['sales'] = $calQuery['sum_services_without_discount'];
            }

        }
        else {
            $result = $query->result_array();
        }
        return $result;

    }
    function estimates_statistic($estimator_id = false, $from = false, $to = false, $where = '', $sum = false, $group_by = false)
    {
        if(!$from || !$to)
            return false;
        $this->load->model('mdl_estimates_orm');
        $this->load->model('mdl_workorders');

        $wdata = ['estimates.date_created >= ' => $from, 'estimates.date_created <= ' => $to];
        if($estimator_id !== false)
            $wdata['estimates.user_id'] = $estimator_id;

        $calcQuery = $this->mdl_estimates_orm->calcQuery($wdata);
        $this->db->_reset_select();
        $calcSiteTime = $this->mdl_workorders->calcSiteTime($wdata);
        $this->db->_reset_select();



        $this->db->select("SUM(IFNULL(`totals`.sum_services_without_discount, 0)) as paid_invoices, SUM(IFNULL(`totals`.sum_without_tax, 0)) as sum_without_tax, 
        SUM(IFNULL(`totals`.discount_total, 0)) as discounts,  COUNT(DISTINCT(totals.estimate_id)) as quantity, SUM(IFNULL(`mhrs`.sum_mhrs, 0)) as crew_hrs, employees.emp_field_estimator, users.active_status, users.id, users.firstname, users.lastname");

        $this->db->join("($calcQuery) totals", "totals.estimate_id = estimates.estimate_id");
        $this->db->join("($calcSiteTime) mhrs", "mhrs.estimate_id = estimates.estimate_id", "left");
        $this->db->join("workorders", "workorders.estimate_id = estimates.estimate_id",'left');
        $this->db->join("invoices", "estimates.estimate_id = invoices.estimate_id",'left');
        $this->db->join("clients", "clients.client_id = estimates.client_id",'left');

        $this->db->join('employees', 'employees.emp_user_id = estimates.user_id', 'left');
        $this->db->join('users', 'estimates.user_id = users.id', 'left');
        $this->db->where($wdata);
        if($where && !empty($where))
            $this->db->where($where);
        if($group_by && !empty($group_by)) {
            $this->db->group_by($group_by);
        }
        $query = $this->db->get('estimates');

        return $query;

    }

    /*function get_estimates_statistic($estimator_id = false, $from = false, $to = false, $where = '', $sum = false, $group_by = false)
    {
        if(!$from || !$to)
            return false;
        $this->load->model('mdl_estimates_orm');
        $this->load->model('mdl_workorders');

        $wdata = ['estimates.date_created >= ' => $from, 'estimates.date_created <= ' => $to];
        if($estimator_id !== false)
            $wdata['estimates.user_id'] = $estimator_id;

        $calcQuery = $this->mdl_estimates_orm->calcQuery($wdata);
        $this->db->_reset_select();
        $calcSiteTime = $this->mdl_workorders->calcSiteTime($wdata);
        $this->db->_reset_select();

        $this->db->select('schedule.event_wo_id, ROUND((((schedule.event_end - schedule.event_start)/3600)/((MAX(`e`.`event_end`) - MIN(`e`.`event_start`))/3600))*schedule_teams.team_man_hours, 2) as event_man_hours, schedule.event_price as event_total', FALSE);

        $this->db->from('schedule');
        $this->db->join('workorders', 'workorders.id = schedule.event_wo_id', 'left');
        $this->db->join('estimates', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->db->join('invoices', 'estimates.estimate_id=invoices.estimate_id', 'left');
        $this->db->join('estimates_services', "estimates_services.estimate_id=estimates.estimate_id", 'left');
        $this->db->join('schedule_teams', 'schedule_teams.team_id = schedule.event_team_id', 'left');
        $this->db->join('schedule e', 'e.event_team_id = schedule.event_team_id', 'left');
        $this->db->join('users est', 'est.id = estimates.user_id', 'left');

        if ($where)
            $this->db->where($where);
        if($estimator_id !== false)
            $this->db->where(['estimates.user_id' => $estimator_id]);
        // $this->db->where(['schedule.event_start >= ' => $from, 'schedule.event_end <= ' => $to]);
        $this->db->group_by('schedule_teams.team_id');

        $estimate_totals = $this->db->_compile_select();
        $this->db->_reset_select();

        $this->db->select('workorders.id, workorders.workorder_no, SUM(IFNULL(event_man_hours, 0)) as event_man_hours, COUNT(DISTINCT(workorders.id)) as quantity');
        $this->db->from('workorders');
        $this->db->join('estimates', 'estimates.estimate_id=workorders.estimate_id', 'left');
        $this->db->join("($estimate_totals) t", "workorders.id = t.event_wo_id");

        $this->db->group_by('workorders.id');
        $subquery = $this->db->_compile_select();


        $this->db->select("SUM(IFNULL(`totals`.sum_services_without_discount, 0)) as sales, SUM(IFNULL(`totals`.sum_without_tax, 0)) as sum_without_tax,
        SUM(IFNULL(`totals`.discount_total, 0)) as discounts,  COUNT(DISTINCT(totals.estimate_id)) as quantity, SUM(IFNULL(`estimates_total`.event_man_hours, 0)) as event_man_hours, employees.emp_field_estimator, users.active_status, users.id, users.firstname, users.lastname");

        $this->db->join("($calcQuery) totals", "totals.estimate_id = estimates.estimate_id");
        $this->db->join("workorders", "workorders.estimate_id = estimates.estimate_id",'left');
        $this->db->join("($subquery) estimates_total", "estimates_total.id=workorders.id", 'left');
        $this->db->join("invoices", "estimates.estimate_id = invoices.estimate_id",'left');
        $this->db->join("clients", "clients.client_id = estimates.client_id",'left');

        $this->db->join('employees', 'employees.emp_user_id = estimates.user_id', 'left');
        $this->db->join('users', 'estimates.user_id = users.id', 'left');
        $this->db->where($wdata);
        if($where && !empty($where))
            $this->db->where($where);
        if($group_by && !empty($group_by)) {
            $this->db->group_by($group_by);
        }
        $query = $this->db->get('estimates');

        return $query;

    }*/
}
//end of file estimates_model.php
