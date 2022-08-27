<?php    if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/*
 * Lead Model
 * created by: Konstantine Mereshkin
 * created on: 2013
 */

class Mdl_leads extends MY_Model
{

	function __construct()
	{
		parent::__construct();

		// Variables set for MY_Controller
		$this->table = 'leads';
		$this->primary_key = "leads.lead_id";
	}

//*******************************************************************************************************************
//*************
//*************																	Insert_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	
	function insert_leads($data)
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
			exit;
		}
	}

//*******************************************************************************************************************
//*************
//*************																Get_client_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	

	function find_by_id($id)
	{
	    $this->db->select("leads.*, clients.*, lead_statuses.*, lead_reason_status.*, 
	        if(leads.lead_estimator IS NULL OR leads.lead_estimator = '0', 'none', leads.lead_estimator) as lead_estimator, workorders.id as workorder_id", false);
        $this->db->join('estimates', 'estimates.lead_id = leads.lead_id', 'left');
        $this->db->join('workorders', 'workorders.estimate_id = estimates.estimate_id', 'left');
        //todo: if(lead_estimator) to 'none' костыль для аппки - поддеркжа старого индусского функционала.
        //      27.05.2021 в аппку добавлена поддержка варианта null, может быть удален когда поддержка версии аппки 1.16 будет прекращена
		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->where(['leads.lead_id' => $id]);
		$query = $this->db->get($this->table);
		return $query->row();
	}
	
	function get_client_leads($id, $where=[])
	{
		$status = "estimated"; 
		$this->db->select("leads.*, lead_statuses.*, lead_reason_status.*, clients.*,  users.*, c.client_name as reffered_client, c.client_id as reffered_client_id, u.id as reffered_user_id, CONCAT(u.firstname, ' ', u.lastname) as reffered_user, estimate_statuses.est_status_confirmed, estimates.estimate_id", FALSE);
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('clients as c', 'leads.lead_reffered_client = c.client_id', 'left');
		
		$this->db->join('estimates', 'leads.lead_id = estimates.lead_id', 'left');
		$this->db->join("estimate_statuses", "estimate_statuses.est_status_id = estimates.status",'left');

		$this->db->join('users as u', 'leads.lead_reffered_user = u.id', 'left');
		$this->db->join('users', 'leads.lead_author_id = users.id', 'left');
		$this->db->where(array('leads.client_id' => $id));
		$this->db->order_by('leads.lead_id', 'desc');
		if(count($where))
                $this->db->where($where);
		$query = $this->db->get($this->table);
		/*
		$query = $this->db->query("	SELECT 		leads.lead_id,
												leads.lead_no,
												leads.lead_body,
												leads.lead_date_created,
												leads.lead_status,
									            leads.lead_created_by,
									            leads.tree_removal,
												leads.tree_pruning,
												leads.stump_removal,
												leads.hedge_maintenance,
												leads.shrub_maintenance,
												leads.wood_disposal,
												leads.arborist_report,
												leads.development,
												leads.root_fertilizing,
												leads.tree_cabling,
												leads.emergency,
												leads.other,
												leads.lead_estimator,
												leads.lead_scheduled,
												leads.lead_call,
												leads.client_id,
												leads.lead_address,
												leads.lead_city,
												leads.lead_state,
												leads.lead_zip
									
									FROM 		leads
									 
									INNER JOIN 	clients
									
									ON 			leads.client_id=clients.client_id
									
									WHERE 		leads.client_id = $id");
		/*
		 *
		leads.latitude,
		leads.longitude,
		leads.min_lat,
		leads.min_lon,
		leads.max_lat,
		leads.max_lon
		 * */
		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/

	} //* end get_leads()
	function get_client_leads_app($id, $wdata = [], $order = 'leads.lead_id ASC')
	{
		$this->db->select('leads.lead_id, leads.lead_no, leads.lead_date_created, lead_statuses.lead_status_name as lead_status, 
		    leads.lead_body, estimator.firstname, estimator.lastname, CONCAT(users.firstname, " ", users.lastname) as user_name, 
		    leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_country, leads.lead_zip, latitude, longitude, 
		    leads.lead_status_id, leads.lead_estimator, lead_reason_status.reason_id as lead_reason_status_id, leads.lead_add_info', FALSE);
		$this->db->from($this->table);
		$this->db->join('users', 'leads.lead_author_id = users.id', 'left');
		$this->db->join('users as estimator', 'leads.lead_estimator = estimator.id', 'left');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id', 'left');
        $this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->where('leads.client_id', $id);
		if(!empty($wdata)){
			foreach($wdata as $key=>$value){
				$this->db->where($key, $value);
			}
		}
		if($order)
		    $this->db->order_by($order);
		$query = $this->db->get();
				
		return $query;
	}

//*******************************************************************************************************************
//*************
//*************																	Get leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	

	function get_leads($wdata, $query_status = "New", $order = 'leads.lead_priority DESC')
	{
		if(isset($wdata['lead_id']))
		{
			$wdata['leads.lead_id'] = $wdata['lead_id'];
			unset($wdata['lead_id']);
		}
        $clientPermissionsSubQuery = $this->mdl_clients->getPermissionsSubQuery();
		// MK changed the query to pull dynamic data from the tables.
		$this->db->select("leads.*, lead_statuses.*, lead_reason_status.*, clients_contacts.*, users.id, users.firstname, users.lastname, users.color, 
		clients.client_id, clients.client_brand_id, clients.client_name, 
		clients.client_address, clients.client_city, clients.client_date_created, clients.client_main_intersection, clients.client_main_intersection2, c.client_name as reffered_client, c.client_id as reffered_client_id, u.id as reffered_user_id, 
			CONCAT(u.firstname, ' ', u.lastname) as reffered_user_text, 
			COUNT(workorders.id) as count_workorders, IF(leads.lead_author_id IS NULL, leads.lead_created_by, CONCAT(author.firstname, ' ', author.lastname)) as lead_created_by, 
			CONCAT(crc.cc_name, ', ', IFNULL(estimates.estimate_no, 'No Estimates'), ' - ', c.client_address, ', ', c.client_city) as reffered_client_text", FALSE);

		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
		$this->db->join('users', 'leads.lead_estimator = users.id', 'left');
        $this->db->join('users author', 'leads.lead_author_id = author.id', 'left');

		$this->db->join('clients as c', 'leads.lead_reffered_client = c.client_id', 'left');
		$this->db->join('clients_contacts as crc', 'c.client_id = crc.cc_client_id AND crc.cc_print = 1', 'left');
		$this->db->join('estimates', 'estimates.client_id = c.client_id', 'left');

		$this->db->join('workorders', 'clients.client_id = workorders.client_id', 'left');
		
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id', 'left');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		
		$this->db->join('users as u', 'leads.lead_reffered_user = u.id', 'left');

        $this->db->join('reference', 'reference.id = leads.lead_reffered_by', 'left');
        
		if($query_status == 'New')
			$where['lead_statuses.lead_status_default'] = 1;
		elseif($query_status == 'not_estimated')
            $where['lead_statuses.lead_status_estimated !='] = 1;
		elseif($query_status != '')
			$where['lead_statuses.lead_status_id'] = $query_status;
		
		if (is_cl_permission_owner() && $clientPermissionsSubQuery) {
            $this->db->join('(' . $clientPermissionsSubQuery .') perm', 'perm.client_id = leads.client_id', 'left');
            $this->db->where('perm.client_id IS NOT NULL');
		} elseif (is_cl_permission_none()) {
		    $where['leads.lead_estimator'] = -1;
        }

		if($order)
			$this->db->order_by($order);
		if(isset($where) && !empty($where) && $where)
			$this->db->where($where);

		if($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->group_by('leads.lead_id');
		$query = $this->db->get($this->table);

		return $query;
	} //* end get_leads()

//*******************************************************************************************************************
//*************
//*************																	Get my leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	

	function get_my_leads($where, $order = 'leads.lead_priority	DESC')
	{
		// MK changed the query to pull dynamic data from the tables.
		$this->db->select('leads.*, lead_statuses.*, lead_reason_status.*, users.id, users.firstname, users.lastname, 
		clients.client_id, clients.client_name,
		clients.client_address, clients.client_city');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('users', 'leads.lead_estimator = users.id');
		$this->db->where($where);
		if($order)
			$this->db->order_by($order);
		$query = $this->db->get($this->table);

		return $query;
	} //* end get_my_leads()

//*******************************************************************************************************************
//*************
//*************									Update_leads; Returns bool;
//*************
//*******************************************************************************************************************	

	function update_leads($update_data, $wdata)
	{
		if ($update_data != '' && $wdata != '') {

			$this->db->where($wdata);
			$update = $this->db->update($this->table, $update_data);
			//echo $this->db->last_query();
			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			echo "data not received";
		}
	}

//*******************************************************************************************************************
//*************
//*************									Delete_leads; Returns bool;
//*************
//*******************************************************************************************************************	


	function delete_leads($id)
	{
		if ($id) {
			$this->db->where('lead_id', $id);
			$this->db->delete($this->table);

			if ($this->db->affected_rows() > 0) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}

	function search_leads()
	{

		$this->db->select('*');
		$fil_date = strip_tags($this->input->post('fil_date'));
		$fil_status = strip_tags($this->input->post('fil_status'));

		$this->db->or_like('lead_date_created', $fil_date);
		if ($fil_status != '')
			$this->db->or_like('lead_status', $fil_status);
		if ($row = $this->db->get('leads'))
			return $row;
		return FALSE;
	}


//*******************************************************************************************************************
//*************
//*************									Count Lead function;
//*************
//*******************************************************************************************************************	

	function getCountLeads($lead_status = null)
	{

		$wdata = array();
		if ($lead_status) {
			$wdata['lead_status_id'] = $lead_status;
		}

		return $this->record_count($or_wdata = array(), $wdata);

	}

	//function countLeads ends

	function getLeadsWithTotalSum($client_id)
	{
		$this->db->select('leads.*, lead_statuses.*, lead_reason_status.*, SUM(service_price) as sum');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		
		$this->db->join('estimates', 'leads.lead_id = estimates.lead_id', 'left');
		$this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id', 'left');
		$this->db->where('leads.client_id', $client_id);
		$this->db->group_by('estimates_services.estimate_id');
		return $this->db->get($this->table)->result_array();
	}

	function getLeadsByStatusDate($where = array())
	{
		$this->db->where($where);
		$this->db->where('status_type', 'lead');
		$this->db->join('status_log', 'lead_id = status_item_id');
		$this->db->join('users', 'status_log.status_user_id = users.id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->order_by('status_log.status_user_id');
		return $this->db->get($this->table)->result_array();
	}

	function get_all()
	{
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$query = $this->db->get($this->table);
		return $query->result();
	}
	
	function get_refferals($where = array(), $type = 'all')
	{
		
		
		if($type == 'client')
		{
			$this->db->select("COUNT(DISTINCT(estimates.estimate_id)) as count, SUM(estimates_services.service_price) as sum, c.client_name as reffered, leads.lead_reffered_client, c.client_id as reffered_id", FALSE);
			$this->db->join('clients as c', 'leads.lead_reffered_client = c.client_id');
		}
		elseif($type == 'user')
		{
			$this->db->select("COUNT(DISTINCT(estimates.estimate_id)) as count, SUM(estimates_services.service_price) as sum, CONCAT(u.firstname, ' ', u.lastname) as reffered, u.id as reffered_id", FALSE);
			$this->db->join('users as u', 'leads.lead_reffered_user = u.id');
		}
		else
		{
			$this->db->select("estimates.*,  estimate_statuses.*, leads.lead_reffered_client, clients.*,  users.*, c.client_name as reffered_client, CONCAT(u.firstname, ' ', u.lastname) as reffered_user", FALSE);
			$this->db->join('clients as c', 'leads.lead_reffered_client = c.client_id', 'left');
			$this->db->join('users as u', 'leads.lead_reffered_user = u.id', 'left');
		}
		
		$this->db->join('estimates', 'leads.lead_id = estimates.lead_id');
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id');
		
		$this->db->join('estimate_statuses', 'estimates.status = estimate_statuses.est_status_id');
		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('users', 'leads.lead_author_id = users.id', 'left');
		
		if($type != 'all')
			$this->db->group_by('reffered');
		
		if(!empty($where))
			$this->db->where($where);
		$query = $this->db->get($this->table);
		
		//if($query->num_rows() >= 1)
			return $query->result();
		
		/*else
			return FALSE;*/
	}
	
	function get_refferal_data($where = array(), $type = 'all')
	{
		if($type == 'client')
		{
			$this->db->select("estimates.estimate_id, estimates.estimate_no, SUM(estimates_services.service_price) as sum", FALSE);
			$this->db->join('clients as c', 'leads.lead_reffered_client = c.client_id');
		}
		elseif($type == 'user')
		{
			$this->db->select("estimates.estimate_id, estimates.estimate_no, SUM(estimates_services.service_price) as sum", FALSE);
			$this->db->join('users as u', 'leads.lead_reffered_user = u.id');
		}
		
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		
		$this->db->join('estimates', 'leads.lead_id = estimates.lead_id');
		$this->db->join('estimates_services', 'estimates.estimate_id = estimates_services.estimate_id');
		
		$this->db->join('estimate_statuses', 'estimates.status = estimate_statuses.est_status_id');
		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('users', 'leads.lead_author_id = users.id', 'left');
		
		$this->db->group_by('estimates.estimate_id');
		
		if(!empty($where))
			$this->db->where($where);
		$query = $this->db->get($this->table);
		
		//if($query->num_rows() >= 1)
			return $query->result();
		
		/*else
			return FALSE;*/
	}
	
	function get_refferal_stat($where = array(), $group_by = false, $subQuery = false)
	{
		$select = "COUNT(leads.lead_id) AS count, reference.slug as lead_reffered_by, reference.id as lead_reffered_by_id, COUNT(workorders.id) AS sum_conf, clients.client_type";
		if($subQuery)
			$select .= ", SUM(totals.sum_for_services) as total";
		if($subQuery)
		{
			$this->load->model('mdl_estimates_orm');
			$subWhere = $where;
			$subWhere = array_merge(['workorders.id IS NOT NULL' => NULL], $where);
			//$subWhere = array_merge(['lead_reffered_by IS NOT NULL' => NULL], $subWhere);

            if(array_key_exists('reference.slug IS NULL', $subWhere))
                unset($subWhere['reference.slug IS NULL']);
            if(isset($subWhere['reference.slug !=']))
                unset($subWhere['reference.slug !=']);
            if(isset($subWhere['reference.slug']))
                unset($subWhere['reference.slug']);

            //echo "<br>Sub Where:";
            //echo "<br>";
            //echo "<br>";echo "<br>";

			$extraJoin[0]['table'] =  'clients';
			$extraJoin[0]['condition'] = 'clients.client_id = estimates.client_id';
			$extraJoin[1]['table'] = 'leads';
			$extraJoin[1]['condition'] = 'leads.lead_id = estimates.lead_id';

			$totalsSubQuery = $this->mdl_estimates_orm->calcQuery($subWhere, $extraJoin);
		}
		$this->db->select($select, FALSE);
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		
		$this->db->join('clients', 'leads.client_id = clients.client_id', 'left');
		$this->db->join('estimates', 'leads.lead_id = estimates.lead_id', 'left');
		$this->db->join('workorders', 'estimates.estimate_id = workorders.estimate_id', 'left');
        $this->db->join('reference', 'reference.id = leads.lead_reffered_by', 'left');
		if($subQuery)
			$this->db->join("($totalsSubQuery) totals", 'estimates.estimate_id = totals.estimate_id', 'left');
		//$this->db->where('lead_reffered_by IS NOT NULL', null, false);
		//var_dump($where);
		//echo "<br>";
        if(!empty($where))
			$this->db->where($where);
		if($group_by)
			$this->db->group_by($group_by);

		$this->db->order_by('count', 'desc');
		$query = $this->db->get($this->table);
		//if($query->num_rows() >= 1)
			return $query->result();
		/*else
			return FALSE;*/
	}
	
	function get_count_leads($wdata = array())
	{
		$this->db->select("COUNT(lead_id) AS count_leads, CONCAT(firstname, ' ', lastname) as username", FALSE);
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		
		$this->db->join('users', 'leads.lead_author_id = users.id');
		if(!empty($wdata))
			$this->db->where($wdata);
		$this->db->group_by('lead_author_id');
		$this->db->order_by('count_leads', 'DESC');
		$query = $this->db->get($this->table);
		return $query->result_array();
	}
	
	function get_leads_by_services($wdata = [], $services = [], $count = FALSE)
	{
		if($count)
			$this->db->select("SUM(service_price) AS count", FALSE);
		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
		$this->db->join('estimates', 'estimates.lead_id = leads.lead_id AND status = 6');
		$this->db->join('estimates_services', 'estimates_services.estimate_id = estimates.estimate_id AND service_status = 2');
		if(!empty($wdata))
			$this->db->where($wdata);
		if(!empty($services))
		{
			$sql = '(';
			foreach($services as $k=>$v)
			{
				
				$sql .=  $v;
				if(isset($services[$k+1]))
					$sql .= ' OR ';
			}
			$sql .= ')';
			$this->db->where($sql);
		}
		//SELECT * FROM (`leads`)
		//JOIN 
		
		
		$query = $this->db->get($this->table);
		if($count)
			return $query->row_array();
		return $query->result_array();
	}
	
	function confirmed_client($where = array(), $new = FALSE, $estimated = FALSE)
	{
        $this->db->select('COUNT(DISTINCT(estimates.estimate_id)) as total');

        $sql = "DATE_FORMAT(from_unixtime(estimates.date_created), '%Y-%m-%d') != clients.client_date_created";
        if($new)
            $sql = "DATE_FORMAT(from_unixtime(estimates.date_created), '%Y-%m-%d') = clients.client_date_created";
        else
            $sql = "DATE_FORMAT(from_unixtime(estimates.date_created), '%Y-%m-%d') != clients.client_date_created";

        $this->db->join('clients', 'clients.client_id = estimates.client_id  AND ' . $sql);
        $this->db->join('users', 'users.id = estimates.user_id');
        $this->db->join('leads', 'leads.lead_id = estimates.lead_id');
        $this->db->join('employees', 'users.id = employees.emp_user_id');

        if($estimated==TRUE){
            $this->db->join('workorders', 'workorders.estimate_id = estimates.estimate_id');
        }

        if(!empty($where))
            $this->db->where($where);

        $result = $this->db->get('estimates')->row_array();

        return element('total', $result, 0);
	}

	function confirmed_client_old($where = array(), $new = FALSE, $estimated = FALSE) {
        $this->db->select('COUNT(leads.lead_id) as total');

        $sql = "DATE_FORMAT(lead_date_created, '%Y-%m-%d') != clients.client_date_created";
        if($new)
            $sql = "DATE_FORMAT(lead_date_created, '%Y-%m-%d') = clients.client_date_created";
        else
            $sql = "DATE_FORMAT(lead_date_created, '%Y-%m-%d') != clients.client_date_created";

        $this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
        $this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');
        $this->db->join('clients', 'leads.client_id = clients.client_id AND ' . $sql);

        if($estimated==TRUE){
            $this->db->join('estimates', 'estimates.lead_id = leads.lead_id');
            $this->db->join('workorders', 'workorders.estimate_id = estimates.estimate_id');
        }

        if(!empty($where))
            $this->db->where($where);

        $result = $this->db->get('leads')->row_array();

        return element('total', $result, 0);
    }
	/*
	function old_clients_leads($where = array(), $new = FALSE)
	{
		$this->db->select('COUNT(leads.lead_id) as total');
		if($new)
			$sql = "DATE_FORMAT(lead_date_created, '%Y-%m-%d') = clients.client_date_created";
		else
			$sql = "DATE_FORMAT(lead_date_created, '%Y-%m-%d') != clients.client_date_created";

		$this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id');
		$this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');

		$this->db->join('clients', 'leads.client_id = clients.client_id AND ' . $sql);
		if(count($where))
			$this->db->where($where);

		$result = $this->db->get('leads')->row_array();
		return $result;
		
	}
	*/

	function get_followup($statusList = [], $periodicity = NULL, $every = FALSE, $clientTypes = FALSE, $tagsIds = FALSE)
	{
		$followUpConfig = $this->config->item('followup_modules')['leads'];		
		$dbStatuses = [];
		foreach ($statusList as $value) {
			//if(isset($followUpConfig['statuses'][$value]))
				$dbStatuses[] = $value;
		}
		
		$this->db->select("DATEDIFF('" . date('Y-m-d') . "', IFNULL(FROM_UNIXTIME(MAX(status_log.status_date)), lead_date_created)) as datediff, IFNULL(FROM_UNIXTIME(MAX(status_log.status_date)), lead_date_created) as this_status_date, clients.client_id, NULL as estimator_id, clients_contacts.*, leads.*", FALSE);

		$this->db->join('status_log', "status_item_id = lead_id AND lead_status = status_value AND status_type = 'lead'", 'left');
		$this->db->join('clients', 'leads.client_id = clients.client_id');
		$this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND cc_print = 1', 'left');
		$this->db->join('client_tags', 'clients.client_id = client_tags.client_id', 'left');
        $this->db->join('followup_settings_tags', 'followup_settings_tags.tag_id = client_tags.tag_id', 'left');

		$this->db->where_in('lead_status_id', $dbStatuses);
		if($clientTypes)
			$this->db->where_in('client_type', $clientTypes);

		if(!$every){
            //$this->db->having("DATE_FORMAT(DATE_ADD(this_status_date, INTERVAL " . intval($periodicity) . " DAY), '%Y-%m-%d') = '" . date('Y-m-d') . "'");
        } else {
            //$this->db->having("(datediff % " . intval($periodicity) . ") = 0 AND datediff > 0");
        }

		/*$this->db->limit(1);*/

		$this->db->group_by('leads.lead_id');
        /*$this->db->get($this->table);
        var_dump($this->db->last_query());exit;*/
		return $this->db->get($this->table)->result_array();
	}

	function get_followup_variables($id)
	{
		$this->load->model('mdl_clients');
		$lead = $this->find_by_id($id);
		$client = $this->mdl_clients->find_by_id($lead->client_id);
		$contact = $this->mdl_clients->get_primary_client_contact($lead->client_id);

		$result['JOB_ADDRESS'] = $lead->lead_address;
		$result['ADDRESS'] = $client->client_address;
		$result['EMAIL'] = $contact['cc_email'];
		$result['NAME'] = $contact['cc_name'];
		$result['PHONE'] = $contact['cc_phone'];
		$result['NO'] = $lead->lead_no;
		$result['LEAD_NO'] = $lead->lead_no;
		$result['ESTIMATE_NO'] = NULL;
		$result['INVOICE_NO'] = NULL;
		$result['ESTIMATOR_NAME'] = NULL;
		
		$result['AMOUNT'] = NULL;
		$result['TOTAL_DUE'] = NULL;

		$result['CCLINK'] = NULL;

		return $result;
	}
	
	function get_leads_by_statuses($count = TRUE, $status_type = 'lead', $subWhere = [])
	{
		$this->db->select("MAX(status_id)");
		$this->db->from('status_log');
		$this->db->where(["status_type" => $status_type]);
		if($subWhere)
			$this->db->where($subWhere);
		$this->db->group_by('status_item_id');
		$subquery = $this->db->_compile_select();
  
		$this->db->_reset_select();
		if($count)
			$this->db->select("COUNT(DISTINCT(status_item_id)) as total, status_value", FALSE);
		else
		{
			$this->db->select("COUNT(DISTINCT(status_item_id)) as total, leads.lead_reason_status as status_value", FALSE);
			$this->db->join('leads', 'leads.lead_id = status_log.status_item_id');
		}
		
		$this->db->where("status_id IN ($subquery)", NULL, FALSE);
		if($count)
			$this->db->group_by('status_value');
		else
			$this->db->group_by('lead_reason_status');
		$query = $this->db->get('status_log');
		if($query->num_rows() == 1) 
			return $query->row_array();
		else
			return $query->result_array();
	}
	function get_last_status_item($where = [], $status_type = 'lead')
	{
		$this->db->select("MAX(status_id), status_value");
		$this->db->from('status_log');
		$this->db->join('leads', 'leads.lead_id = status_log.status_item_id');
		$this->db->where(["status_type" => $status_type]);
		$this->db->group_by('status_item_id');
		return $this->db->get()->row_array(); 
	}

	function getAppLeads($wdata = [], $or_where = []) {
        $this->db->select("leads.lead_address, leads.lead_city, leads.lead_state, leads.lead_zip, leads.lead_body, leads.lead_call, leads.lead_date_created, 
            if(leads.lead_estimator IS NULL OR leads.lead_estimator = '0', 'none', leads.lead_estimator) as lead_estimator, leads.lead_id, leads.lead_no, leads.lead_priority, leads.latitude, leads.longitude, leads.lead_reffered_by,
            lead_statuses.lead_status_id, lead_statuses.lead_status_active, lead_statuses.lead_status_declined, 
            lead_statuses.lead_status_default, lead_statuses.lead_status_estimated, lead_statuses.lead_status_for_approval, 
            users.id, users.firstname, users.lastname, users.color, GROUP_CONCAT(DISTINCT TRIM(services.service_name) ORDER BY services.service_name ASC SEPARATOR ', ') AS services, 
            clients_contacts.cc_email, clients_contacts.cc_name, clients_contacts.cc_phone, 
		    clients.client_name, clients.client_id, clients.client_brand_id, COUNT(workorders.id) as count_workorders", FALSE);
        //todo: if(lead_estimator) to 'none' костыль для аппки - поддеркжа старого индусского функционала.
        //      27.05.2021 в аппку добавлена поддержка варианта null, может быть удален когда поддержка версии аппки 1.16 будет прекращена
        $this->db->join('clients', 'leads.client_id = clients.client_id');
        $this->db->join('clients_contacts', 'clients.client_id = clients_contacts.cc_client_id AND clients_contacts.cc_print = 1', 'left');
        $this->db->join('users', 'leads.lead_estimator = users.id', 'left');
        $this->db->join('clients as c', 'leads.lead_reffered_client = c.client_id', 'left');
        $this->db->join('workorders', 'clients.client_id = workorders.client_id', 'left');

        $this->db->join('lead_services', 'leads.lead_id = lead_services.lead_id', 'left');
        $this->db->join('services', 'lead_services.services_id = services.service_id', 'left');
        $this->db->join('lead_statuses', 'leads.lead_status_id = lead_statuses.lead_status_id', 'left');
        $this->db->join('lead_reason_status', 'leads.lead_reason_status_id = lead_reason_status.reason_id', 'left');

        $this->db->join('users as u', 'leads.lead_reffered_user = u.id', 'left');

        $this->db->order_by('leads.lead_id', 'DESC');

        if(isset($wdata) && count($wdata) && $wdata)
            $this->db->where($wdata);

        if(isset($or_where) && count($or_where) && $or_where)
            $this->db->or_where($or_where);

        $this->db->group_by('leads.lead_id');

        $this->_scopeLeadsPermissions();

        $result = $this->db->get($this->table)->result();

        return $result;
    }

    private function _scopeLeadsPermissions() {
        $CI =& get_instance();

        if(!isset($CI->user) || !isset($CI->user->permissions) || !isset($CI->user->permissions['CL'])) {
            return false;
        }

        if($CI->user->permissions['CL'] === \application\modules\clients\models\Client::PERM_NONE) {
            $this->db->where('leads.lead_id', -1);
        } elseif ($CI->user->permissions['CL'] === \application\modules\clients\models\Client::PERM_OWN) {
            $this->db->where("(leads.lead_author_id = {$CI->user->id} OR leads.lead_estimator = {$CI->user->id})");
        }

        return true;
    }

    public  function getLeadServices($leadId)
    {
        $this->db->select('services.service_id, services.is_product, services.is_bundle');
        $this->db->from('leads');
        $this->db->join('lead_services', 'leads.lead_id = lead_services.lead_id', 'left');
        $this->db->join('services', 'services.service_id = lead_services.services_id AND service_status = 1', 'left');
        $this->db->where([
           'leads.lead_id' => $leadId,
        ]);

        $leadServices = $this->db->get()->result();

        $est_services = [];
        $est_products = [];
        $est_bundles = [];
        foreach ($leadServices as $leadService) {
            switch (true) {
                case $leadService->is_product === '1':
                    array_push($est_products, $leadService->service_id);
                    break;
                case $leadService->is_bundle === '1':
                    array_push($est_bundles, $leadService->service_id);
                    break;
                default:
                    array_push($est_services, $leadService->service_id);
            }
        }

        return compact('est_services', 'est_products', 'est_bundles');
    }

    public function getLeadsDraft($draftPrefix)
    {
        $ids = [];

        $this->db->select('l.lead_id')
            ->from('leads as l')
            ->join('lead_statuses as ls', 'l.lead_status_id = ls.lead_status_id', 'left')
            ->where(['ls.lead_status_name' => 'Draft']);

        if ($leads = $this->db->get()->result()) {
            foreach ($leads as $lead) {
                $ids[] = $draftPrefix . (int) $lead->lead_id;
            }
        }

        return $ids;
    }
}
//end of file lead_model.php
