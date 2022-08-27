<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Utility
{

	private $CI;

	function __construct()
	{
		$this->CI = & get_instance();
		$this->CI->load->model('mdl_schedule', 'schedule_model');
	}

	public function getEndDatesFromProjectid($enddate)
	{
		$where = ' project.start_time <="' . date("Y-m-d", strtotime('sunday this week')) . '"  and project.end_time LIKE "%' . $enddate . '%" order by project.crew_id asc';
		$day_crews = $this->CI->schedule_model->day_project('', $where);
		//echo $this->CI->db->last_query();
		return $day_crews;
	}


}

/* End of file Utility.php */
/* Location: ./libraries/Utilities.php */
