<?php

class Mdl_leads_reason extends JR_Model
{
	protected $_table = 'lead_reason_status';
	protected $primary_key = 'reason_id';

	public function __construct() {
		parent::__construct();
	}

    /**
     * @param string $select
     * @return array
     */
    public function get_all_join_lead_statuses(string $select = 'lead_reason_status.*') {
        return $this->db->select($select)
        ->from($this->_table)
        ->join('lead_statuses', 'lead_statuses.lead_status_id = lead_reason_status.reason_lead_status_id')
        ->get()->result_array();
    }
}
