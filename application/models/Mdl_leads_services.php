<?php

class Mdl_leads_services extends JR_Model
{
	protected $_table = 'lead_services';
	protected $primary_key = 'id';

	public $has_many = array('mdl_services' => array('primary_key' => 'services.service_id', 'model' => 'mdl_services'));

	public function __construct() {
		parent::__construct();
	}

	
	function get_with_services($where = [])
	{
		$this->db->join('services', 'services.service_id = lead_services.services_id');
		if(!empty($where))
			$this->db->where($where);
		$query = $this->db->get('lead_services');
		return $query->result();
	}

	function get_services_id($where = []) {
	    $this->db->select('service_id, service_name');
        $this->db->join('services', 'services.service_id = lead_services.services_id');
        $this->db->where($where);
        $query = $this->db->get('lead_services');
        return $query->result();
    }

    function get_lead_services($where = []) {
        $this->db->select('service_id, service_name, service_description, service_markup, service_attachments, service_default_crews, is_product, is_bundle, cost');
        $this->db->join('services', 'services.service_id = lead_services.services_id');
        $this->db->where($where);
        $query = $this->db->get('lead_services');
        return $query->result();
    }
}
