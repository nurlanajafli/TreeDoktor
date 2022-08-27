<?php

class Mdl_leads_status extends JR_Model
{
	protected $_table = 'lead_statuses';
	protected $primary_key = 'lead_status_id';

	public $has_many = array('mdl_leads_reason' => array('primary_key' => 'reason_lead_status_id', 'model' => 'mdl_leads_reason'));

	public function __construct() {
		parent::__construct();
	}

    public function get_active_statuses(){
        return $this->get_many_by('lead_status_active', 1);
    }

    /**
     * @return bool|CI_DB_result
     */
    public function get_all_active() {
	    return $this->get_many_by(['lead_status_active' => 1]);
    }

    /**
     * @return array
     */
    public function get_all_active_statuses_name() {
        $statuses = $this->get_all_active();
        $data = [];
        foreach($statuses as $status) {
            $data[$status->lead_status_id] = $status->lead_status_name;
        }
        return $data;
    }
}

