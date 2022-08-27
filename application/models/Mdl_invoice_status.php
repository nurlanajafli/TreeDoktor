<?php

class Mdl_invoice_status extends JR_Model
{
	protected $_table = 'invoice_statuses';
	protected $primary_key = 'invoice_status_id';


	public function __construct() {
		parent::__construct();
	}

    /**
     * @return bool|CI_DB_result
     */
    public function get_all_active() {
        return $this->get_many_by(['invoice_status_active' => 1]);
    }

    /**
     * @return array
     */
    public function get_all_active_statuses_name() {
        $statuses = $this->get_all_active();
        $data = [];
        foreach($statuses as $status) {
            $data[$status->invoice_status_id] = $status->invoice_status_name;
        }
        return $data;
    }
}
