<?php

class Mdl_est_reason extends JR_Model
{
	protected $_table = 'estimate_reason_status';
	protected $primary_key = 'reason_id';

	public function __construct() {
		parent::__construct();
	}

    /**
     * @param string $select
     * @return array
     */
    public function get_all_active_join_estimate_statuses(string $select = 'estimate_statuses.*') {
        return $this->db->select($select)
            ->from($this->_table)
            ->join('estimate_statuses', 'estimate_statuses.est_status_id = estimate_reason_status.reason_est_status_id')
            ->where('estimate_reason_status.reason_active', 1)
            ->get()->result_array();
    }
}
