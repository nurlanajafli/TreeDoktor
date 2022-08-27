<?php

class Mdl_payroll extends JR_Model
{
	protected $_table = 'payroll';
	protected $primary_key = 'payroll_id';

	public $has_many = array('mdl_worked' => array('primary_key' => 'worked_payroll_id', 'model' => 'reports/mdl_worked'));

	public function __construct() {
		parent::__construct();
	}

    /**
     * @param $date
     * @return array|null
     */
    public function getPayday($date): ?array
    {
        return $this->db
            ->select('payroll_day')
            ->where('payroll_start_date<=', $date)
            ->where('payroll_end_date>=',   $date)
            ->get($this->_table)
            ->row_array();
    }
}
