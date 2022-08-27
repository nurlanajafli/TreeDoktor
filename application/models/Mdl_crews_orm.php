<?php
class Mdl_crews_orm extends JR_Model
{
	protected $_table = 'estimates_services_crews';
	protected $primary_key = 'crew_id';
	//public $belongs_to = array('mdl_crews' => array('primary_key' => 'crew_user_id', 'model' => 'administration/mdl_crews'));
	//public $belongs_to = array('mdl_worked' => array('primary_key' => 'login_worked_id', 'model' => 'reports/mdl_worked'));

	public function __construct() {
		parent::__construct();
	}

	function get_service_crew_in_string($wdata = []) {
		$this->db->select("GROUP_CONCAT(crew_name SEPARATOR ', ') as crews_names", FALSE);
		$this->db->join('estimates_services_crews', 'crews.crew_id = estimates_services_crews.crew_user_id');
		$this->db->where($wdata);
		$this->db->group_by('crew_service_id');
		return $this->db->get('crews')->row_array();
	}
}
