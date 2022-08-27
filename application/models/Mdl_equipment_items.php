<?php

class Mdl_equipment_items extends MY_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->table = 'equipment_items';
		
	}

	function get_followup($statusList = [], $periodicity = NULL, $every = FALSE, $clientTypes = FALSE)
	{
		$result['key']['field'] = NULL;
		$result['key']['client_id'] = NULL;
		$result['key']['estimator_id'] = NULL;
		return $result;
	}
	
	function get_followup_variables($id = NULL)
	{
		return TRUE;
	}
	
}

//End model.
