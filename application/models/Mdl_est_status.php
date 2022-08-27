<?php

class Mdl_est_status extends JR_Model
{
	protected $_table = 'estimate_statuses';
	protected $primary_key = 'est_status_id';

	public $has_many = array('mdl_est_reason' => array('primary_key' => 'reason_est_status_id', 'model' => 'mdl_est_reason'));

	public function __construct() {
		parent::__construct();
	}

    /**
     * @return bool|CI_DB_result
     */
    public function get_all_active() {
        return $this->get_many_by(['est_status_active' => 1]);
    }

    /**
     * @return array
     */
    public function get_all_active_statuses_name() {
        $statuses = $this->get_all_active();
        $data = [];
        foreach($statuses as $status) {
            $data[$status->est_status_id] = $status->est_status_name;
        }
        return $data;
    }
}
