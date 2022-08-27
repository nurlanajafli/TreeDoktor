<?php

class Mdl_deductions extends JR_Model
{
	protected $_table = 'payroll_deductions';
	protected $primary_key = 'deduction_id';

	//public $has_many = array('mdl_payroll' => array('primary_key' => 'worked_payroll_id', 'model' => 'reports/mdl_worked'));

	public function __construct() {
		parent::__construct();
	}

}
