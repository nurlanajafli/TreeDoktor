<?php
class Mdl_vehicles extends JR_Model
{
	protected $_table = 'vehicles';
	protected $primary_key = 'vehicle_id';

	//public $belongs_to = array('mdl_worked' => array('primary_key' => 'login_worked_id', 'model' => 'reports/mdl_worked'));

	public function __construct() {
		parent::__construct();
	}
	
	function get_service_equipment($wdata = []) {
		$this->db->select("items.vehicle_name as item_name, attachments.vehicle_name as attach_name, equipment_item_id, equipment_item_option, equipment_attach_id, equipment_attach_option, equipment_attach_tool, equipment_tools_option, items.vehicle_per_hour_price", FALSE);
		$this->db->join('vehicles items', 'items.vehicle_id = estimates_services_equipments.equipment_item_id', 'left');
		$this->db->join('vehicles attachments', 'attachments.vehicle_id = estimates_services_equipments.equipment_attach_id', 'left');
		$this->db->where($wdata);
		//$this->db->group_by('equipment_service_id');
		return $this->db->get('estimates_services_equipments')->result_array();
	}
}
