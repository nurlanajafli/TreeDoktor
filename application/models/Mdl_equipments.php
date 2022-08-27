<?php

class Mdl_equipments extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->table = 'equipment_groups';
		$this->table1 = 'equipment_items';
		$this->table2 = 'equipment_gps_tracker_distance';
		$this->primary_key = 'equipment_groups.group_id';
	}

	/*
	 * function get_groups
	 *
	 * param select, wheredata, ...
	 * returns rows or false
	 *
	 */

	function get_groups($select = '', $wdata = '')
	{
		if ($select != '') {
			$this->db->select($select);
		}
		if ($wdata != '') {
			$this->db->where($wdata);
		}
		$query = $this->db->get($this->table);
		//if ($query->num_rows() > 0) {
			return $query;
		/*} else {
			return FALSE;
		}*/
	}
	//*******************************************************************************************************************
	//*************
	//*************           Add a new group model
	//*************
	//*******************************************************************************************************************
	public function add_new_group()
	{

		//Array for MySql update;
		//Set note date to avoide escaped values;
		$group_date_created = date('Y-m-d');
		//Array with values;
		$data = array(
			'group_name' => strip_tags($this->input->post('new_group_name')),
			'group_color' => strip_tags($this->input->post('new_group_color')),
			'group_date_created' => $group_date_created
		);

		if ($this->db->insert('equipment_groups', $data) == FALSE) {
			return FALSE;
			//there was a problem creating a client;
		} else {
			return $this->db->insert_id();
		}
	}

// end add_new_group;

	//*******************************************************************************************************************
//*************
//*************												insert_items					Insert_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	
	function insert_items($data)
	{
		if ($data) {
			$insert = $this->db->insert($this->table1, $data);
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
//*************												update_items					Insert_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	
	function update_items($data, $id)
	{
		if ($data) {
			$this->db->where('item_id', $id);

			if ($this->db->update($this->table1, $data)) {
				return TRUE;
			} else {
				return FALSE;
			}
		}
	}
	
	function update_item_by($data, $wdata = '')
	{		
		if($wdata != '')
		{
			$this->db->where($wdata);
			
			$this->db->update($this->table1, $data);
			return TRUE;
		}
		else
			return FALSE;
	}

	//*******************************************************************************************************************
//*************
//*************												delete_item					Insert_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	
	function delete_item($id)
	{
		$sql = 'DELETE equipment_items, equipment_services, equipment_parts FROM equipment_items ';
		$sql .= 'LEFT JOIN equipment_services ON equipment_services.service_item_id = equipment_items.item_id ';
		$sql .= 'LEFT JOIN equipment_parts ON equipment_parts.part_item_id = equipment_items.item_id ';
		$sql .= 'WHERE equipment_items.item_id = ' . intval($id);
		if ($this->db->query($sql)) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

//*******************************************************************************************************************
//*************
//*************																Get_client_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	

	function get_group_items($id = NULL)
	{
		$this->db->select("eg.*, ei.*", FALSE);
		if($id)
			$this->db->where('ei.group_id', $id);
		$this->db->join("equipment_items AS ei", "ei.group_id = eg.group_id");
		$this->db->order_by('ei.item_name');
		$query = $this->db->get($this->table . " AS eg");
		//if ($query->num_rows() > 0) {
			return $query->result_array();
		/*} else {
			return FALSE;
		}*/


	} //* end get_group_items()

	function get_items($wdata = array(), $order = NULL)
	{
		if($wdata && ((is_array($wdata) && count($wdata)) || strlen($wdata)))
			$this->db->where($wdata);
		$this->db->join('equipment_groups', 'equipment_groups.group_id = equipment_items.group_id');
		if($order)
			$this->db->order_by($order);
		else
			$this->db->order_by('equipment_items.group_id');
		$query = $this->db->get('equipment_items');
		return $query->result();
	}

	function get_item($wdata = array())
	{
		if($wdata && ((is_array($wdata) && count($wdata)) || strlen($wdata)))
			$this->db->where($wdata);
		$this->db->join('equipment_groups', 'equipment_groups.group_id = equipment_items.group_id');
		$this->db->order_by('equipment_items.group_id');
		$query = $this->db->get('equipment_items');
		return $query->row();
	}
	//*******************************************************************************************************************
//*************
//*************            								update_group(*.*);
//*************
//*******************************************************************************************************************
	//Update client profile.
	public function update_group($update_data, $wdata)
	{

		$this->db->where($wdata);
		if ($this->db->update($this->table, $update_data)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}//end of update_group;

	//*******************************************************************************************************************
//*************
//*************												delete_group					Insert_leads; Returns row(); or FALSE;
//*************
//*******************************************************************************************************************	
	function delete_group($id)
	{
		$this->db->where('group_id', $id);
		if ($this->db->delete($this->table)) {
			return TRUE;
		} else {
			return FALSE;
		}

	}

	function get_services($wdata = array(), $items = FALSE, $order = FALSE)
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		if ($items) {
			$this->db->join('equipment_items', 'equipment_services.service_item_id = equipment_items.item_id');
			$this->db->join('equipment_groups', 'equipment_items.group_id = equipment_groups.group_id');
		}
		$this->db->join('equipment_service_types', 'equipment_services.service_type_id = equipment_service_types.equipment_service_id', 'left');
		if ($order)
			$this->db->order_by($order);
		$this->db->order_by('service_id', 'desc');
		$query = $this->db->get('equipment_services');
		return $query->result_array();
	}

	function delete_services($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->delete('equipment_services');
		return TRUE;
	}

	function update_service($id, $data)
	{
		$this->db->where('service_id', $id);
		$this->db->update('equipment_services', $data);
		return TRUE;
	}

	function insert_service($data)
	{
		$this->db->insert('equipment_services', $data);
		return $this->db->insert_id();
	}

	function get_parts($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->order_by('part_id', 'desc');
		$query = $this->db->get('equipment_parts');
		return $query->result_array();
	}

	function delete_parts($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->delete('equipment_parts');
		return TRUE;
	}

	function update_part($id, $data)
	{
		$this->db->where('part_id', $id);
		$this->db->update('equipment_parts', $data);
		return TRUE;
	}

	function insert_part($data)
	{
		$this->db->insert('equipment_parts', $data);
		return $this->db->insert_id();
	}

	function insert_service_setting($data)
	{
		if(isset($data['service_start']) && $data['service_start'])
			$data['service_start'] = date("Y-m-d", strtotime(date("Y-m-d", strtotime($data['service_start'])) . "-".$data['service_period_months']." months"));

		$this->db->insert('equipment_services_setting', $data);
		return $this->db->insert_id();
	}

	function get_service_settings($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->join('equipment_service_types', 'equipment_service_types.equipment_service_id = equipment_services_setting.service_type_id');//, 'left');
		//$this->db->join('equipment_services_reports', 'equipment_services_setting.id = equipment_services_reports.report_service_settings_id AND equipment_services_setting.service_created = equipment_services_reports.report_date_created', 'left');
		$query = $this->db->get('equipment_services_setting');
		return $query->result_array();
	}

	function delete_services_setting($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->delete('equipment_services_setting');
		return TRUE;
	}

	function update_services_setting($id, $data, $dateCreated = NULL)
	{
		if(intval($data['service_postpone_on'])){
			$setDate = $dateCreated ? $dateCreated : date('Y-m-d');
			$this->db->set('service_start', "'" . $setDate . "'", FALSE);
		}
		else{
			$setDate = $dateCreated ? $dateCreated : date('Y-m-d');
			$this->db->set("service_start", "'" . $setDate . "'", FALSE);
		}

		$this->db->where('id', $id);
		$this->db->update('equipment_services_setting', $data);		
		return TRUE;
	}

	function get_months_services($wdata = array(), $date_where = NULL, $order = FALSE) 
	{
		$date = date('Y-m');
		if($date_where)
			$date = date('Y-m', strtotime($date_where)); 

		$this->db->select('equipment_services_setting.*,  equipment_service_types.*, equipment_items.item_name, equipment_items.group_id, 

			
			DATE_FORMAT(( IFNULL(`service_start`, `service_last_update`) + INTERVAL IF(`service_postpone_on`=0, `service_period_months`, `service_postpone_on`) MONTH), "%Y-%m-%d") as curr_service_date', FALSE); 

		$this->db->join('equipment_service_types', 'equipment_service_types.equipment_service_id = equipment_services_setting.service_type_id', 'left');

		$this->db->join('equipment_items', 'equipment_items.item_id = equipment_services_setting.item_id');

		//$this->db->where("DATE_FORMAT(`service_start`, '%Y-%m') BETWEEN DATE_FORMAT(IFNULL(IFNULL(`service_start`, `service_postpone`), `service_last_update`), '%Y-%m') AND '".$date."'");
		
		//$this->db->where("( '".$date."' >= DATE_ADD(IFNULL(DATE_FORMAT(`service_start`, '%Y-%m'), DATE_FORMAT(`service_last_update`, '%Y-%m')), INTERVAL `service_period_months` MONTH)) ");
		

		 /*DATE_FORMAT((IFNULL(`service_start`, `service_last_update`) + INTERVAL `service_period_months` MONTH), '%Y-%m'))*/ 

		$this->db->where("( '".$date."' >= DATE_FORMAT((IFNULL(`service_start`, `service_last_update`) + INTERVAL IF(`service_postpone_on`=0, `service_period_months`, `service_postpone_on`) MONTH), '%Y-%m')) ");

		$this->db->where("IFNULL(`service_start`, `service_last_update`) IS NOT NULL");
		//$this->db->or_where("(service_start IS NOT NULL AND DATE_FORMAT(service_start, '%Y-%m') = '".$date."'))");

		$this->db->where($wdata);
		
		
		if($order)
			$this->db->order_by($order);
		else
			$this->db->order_by('service_postpone', 'DESC');
		$query = $this->db->get('equipment_services_setting');
		/*echo "<pre>";
		echo $this->db->last_query();
		die;*/
		return $query->result_array();
	}

	function insert_services_report($data)
	{
		$this->db->insert('equipment_services_reports', $data);
		return $this->db->insert_id();
	}

	function get_services_reports($wdata = array(), $limit = '', $start = '')
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->join('equipment_service_types', 'equipment_service_types.equipment_service_id = equipment_services_reports.report_service_id', 'left');
		$this->db->join('equipment_items', 'equipment_items.item_id = equipment_services_reports.report_item_id', 'left');
		$this->db->join('equipment_services_setting', 'equipment_services_setting.id = equipment_services_reports.report_service_settings_id', 'left');
		$this->db->join('users', 'users.id = equipment_services_reports.report_create_user', 'left');
		//$this->db->order_by('report_date_created DESC');
		$this->db->order_by('report_id DESC');
		if ($limit != '') {
			$this->db->limit($limit, $start);
		}
		
		$query = $this->db->get('equipment_services_reports');
		//echo $this->db->last_query();die;
		return $query->result_array();
	}

	function delete_services_reports($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->delete('equipment_services_reports');
		return TRUE;
	}

	function update_services_report($id, $data)
	{
		$this->db->where('report_id', $id);
		$this->db->update('equipment_services_reports', $data);
		return TRUE;
	}
	
	function services_reports_count($wdata = array())
	{
		if ($wdata && !empty($wdata))
			$this->db->where($wdata);
		$this->db->join('equipment_service_types', 'equipment_service_types.equipment_service_id = equipment_services_reports.report_service_id');//, 'left');
		$this->db->join('equipment_items', 'equipment_items.item_id = equipment_services_reports.report_item_id');//, 'left');
		$this->db->join('equipment_services_setting', 'equipment_services_setting.id = equipment_services_reports.report_service_settings_id', 'left');
		$this->db->join('users', 'users.id = equipment_services_reports.report_create_user', 'left');
		$this->db->order_by('report_date_created DESC');
		$this->db->from('equipment_services_reports');
		
		
		//$this->db->group_by('r1.report_id');
		return $num_results = $this->db->count_all_results();
	}
	
	function get_summ_gps_distance($where = array(), $select = '')
	{
		if($select == 'sum')
			$this->db->select('SUM(egtd_counter) as count');
		if(!empty($where))
			$this->db->where($where);
		
		$this->db->join('equipment_gps_tracker_distance', 'equipment_gps_tracker_distance.egtd_item_id = equipment_items.item_id AND equipment_gps_tracker_distance.egtd_date >= equipment_items.item_gps_start_date', 'left');

		$this->db->order_by('egtd_date');
			
		$query = $this->db->get($this->table1);
		
		if($select == 'sum')
			return $query->row();
		else
			return $query->result();
	}
	function insert_gps_distance($data)
	{
		if(empty($data))
			return FALSE;
		$insert = $this->db->insert($this->table2, $data);
		
		return TRUE;
	}
	
	function get_equipment_files($wdata = []) {
		$this->db->select('*');
		$this->db->from('equipment_files');
		
		if(count($wdata))
			$this->db->where($wdata);
			
		$query = $this->db->get();
		
		return $query->result();
	}
	
	function insert_equipment_file($data) {
		$this->db->insert('equipment_files', $data);
		return TRUE;
	}
	
	function update_equipment_file($id, $data) {
		$this->db->where('file_id', $id);
		$this->db->update('equipment_files', $data);
		return TRUE;
	}
	
	function delete_equipment_file($id) {
		if($id != null && $id != '') {
			$this->db->where('file_id', $id);
			$this->db->delete('equipment_files');
		}
		return TRUE;
	}
}

//End model.
