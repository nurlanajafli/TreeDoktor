<?php
class Mdl_equipment_orm extends JR_Model
{
	protected $_table = 'estimates_services_equipments';
	protected $primary_key = 'equipment_id';

	//public $belongs_to = array('mdl_worked' => array('primary_key' => 'login_worked_id', 'model' => 'reports/mdl_worked'));

	public function __construct() {
		parent::__construct();
	}
}
