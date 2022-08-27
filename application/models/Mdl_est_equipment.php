<?php

class Mdl_est_equipment extends JR_Model
{
	protected $_table = 'estimate_equipment';
	protected $primary_key = 'eq_id';

	public function __construct() {
		parent::__construct();
	}

	function get_service_equipment_in_string($wdata = []) {
		$this->db->select("GROUP_CONCAT(eq_name SEPARATOR ', ') as eq_items", FALSE);
		$this->db->join('estimates_services_equipments', 'estimate_equipment.eq_id = estimates_services_equipments.equipment_item_id');
		$this->db->where($wdata);
		$this->db->group_by('equipment_service_id');
		return $this->db->get('estimate_equipment')->row_array();
	}

}
