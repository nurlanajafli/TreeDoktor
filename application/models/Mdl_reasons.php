<?php

class Mdl_reasons extends JR_Model
{
	protected $_table = 'reasons_absence';
	protected $primary_key = 'reason_id';
	public $has_many = array('mdl_absence' => array('primary_key' => 'absence_reason_id', 'model' => 'mdl_absence'));
	//public $has_many = array('mdl_emp_login' => array('primary_key' => 'login_worked_id', 'model' => 'mdl_emp_login'));
	function __construct()
	{
		parent::__construct();
	}
}
