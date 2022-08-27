<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');


	function lead_appointments($schedule_appointments, $lead_id){
		if(!is_array($schedule_appointments) || empty($schedule_appointments))
			return [];

		$result = [];
		foreach ($schedule_appointments as $key => $appointment) {
			if($appointment['task_lead_id']==$lead_id)
				$result[] = $appointment;
		}

		return $result;
	}


?>