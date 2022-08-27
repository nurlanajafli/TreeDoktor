<?php

class Mdl_absence extends JR_Model
{
	protected $_table = 'schedule_absence';
	public $belongs_to = array('mdl_reasons' => array('primary_key' => 'absence_reason_id', 'model' => 'mdl_reasons'));
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('mdl_reasons');
	}

	function get_with_user($where = array())
	{
		$this->_database->join('users', 'users.id = schedule_absence.absence_user_id', 'left');
		if(!empty($where))
			$this->_database->where($where);
		$result = $this->_database->get($this->_table);
		$result = $result->{$this->_return_type(1)}();

		$this->_temporary_return_type = $this->return_type;
		foreach ($result as $key => &$row)
		{
			$row = $this->trigger('after_get', $row, ($key == count($result) - 1));//countOk
		}
		$this->_database->with = array();
		return $result;
	}
	
	function get_count_by_reason($where = array(), $weekdays = FALSE)
	{
		$this->_database->join('reasons_absence', 'reasons_absence.reason_id = schedule_absence.absence_reason_id', 'left');
		if(!empty($where))
			$this->_database->where($where);
		if($weekdays == TRUE)
			$this->_database->where_not_in('DAYOFWEEK(absence_ymd)', array(7, 1));
		return $this->_database->count_all_results($this->_table);
		
	}

}

//End model.
