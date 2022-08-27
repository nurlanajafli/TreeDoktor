<?php
class Mdl_expenses_orm extends JR_Model
{
	protected $_table = 'estimates_services_expenses';
	protected $primary_key = 'ese_id';

	//public $belongs_to = array('mdl_worked' => array('primary_key' => 'login_worked_id', 'model' => 'reports/mdl_worked'));

	public function __construct() {
		parent::__construct();
	}
}
