<?php


function get_free_intervals($task_date, $intervals)
{
	$free_time = $free_intervals = [];
	$length = config_item('AppointmentTaskLength') ?: 45;
	$lenthSec = $length * 60;
	$day_start = strtotime($task_date.' '.config_item('office_schedule_start').':00:00');
	$day_end = strtotime($task_date.' '.config_item('office_schedule_end').':00:00');

	for($i=$day_start; $i<=$day_end; $i+=$lenthSec) {
		if($i >= time()) {
			$free_intervals[$i] = 0;
		}
	}

	foreach($intervals as $busy_interval){
		$start_busy = strtotime($task_date.' '.$busy_interval['task_start']);
		$end_busy = strtotime($task_date.' '.$busy_interval['task_end']);

		foreach ($free_intervals as $key => $value) {
			if(
				($key >= $start_busy && $key < $end_busy) ||
				($key + $lenthSec > $start_busy && $key + $lenthSec < $end_busy)
			) {
				$free_intervals[$key] = 1;
				/*if(isset($free_intervals[$key + $lenthSec]) && $key + $lenthSec >= $end_busy) {
					$diff = $key + $lenthSec - $end_busy;
					for ($i = $key + $lenthSec; $i <= $day_end; $i += $lenthSec) {
						//unset($free_intervals[$i]);
						$free_intervals[$i - $diff] = 0;
					}
				}*/
			}
		}
	}

	$result = [];
	$start = false;
	$end = false;

	foreach ($free_intervals as $key => $value) {
		if($value===0 && $start===false){
			$start = $key;
		}

		if(isset($free_intervals[$key-$lenthSec]) && $value==1 && $free_intervals[$key-$lenthSec]===0 && $start!==false && $end===false){
			$end = $key;
		}

		if(!isset($free_intervals[$key+$lenthSec]) && $start!==false && $end===false){
			$end = $key;
		}

		if($start !== false && $end !== false){
			$result[] = ['start'=>$start, 'end'=>$end];
			$start = false;
			$end = false;
		}


	}
	return $result;
}
function calendar_button_tmp($letter, $data, $replace_array)
{
	$CI = &get_instance();

	$body = false;
	list($result, $view) = Modules::find('appointment/calendar_button/'.$letter->system_label, 'clients', 'views/');

	if(!$result)
		return $body;

	$body = $CI->load->view('clients/appointment/calendar_button/'.$letter->system_label, [
		'task'=>$data['task']->toArray(),
		'estimator'=>$data['task']->user,
		'client_name'=>$data['task']->client->client_name??null,
		'employee'=>!empty($data['task']->user)?$data['task']->user:null,
		'template'=>$letter->email_template_text
	], true);

	$keywords = array_keys($replace_array);
	$values = array_values($replace_array);
	$body = str_replace(
		$keywords,
		$values,
		$body
	);

	return $body;
}
function task_to_calendar($footer, $template)
{
	if(!$footer)
		return $template;

	$template = str_replace('</body>', $footer, $template);
	return $template;
}

function delete_special($appointments){
	if(!is_array($appointments) || empty($appointments))
		return [];


	foreach ($appointments as $key => $value) {
		$appointments[$key]['task_desc'] = str_replace(array("\n", "\r\n", "\r", "\t", "    "), " ", $value['task_desc']);
		$appointments[$key]['lead_body'] = str_replace(array("\n", "\r\n", "\r", "\t", "    "), " ", $value['lead_body']);
		$appointments[$key]['cc_phone_view'] = numberTo($value['cc_phone']);
		$appointments[$key]['full_address'] = task_address($value);

	}

	return $appointments;
}

function get_recomendations($estimators, $appointments, $date_start){
	$recomendations_week = get_recomendations_week($estimators, $date_start);
	$recomendations_week = set_estimators_appointments($recomendations_week, $appointments);
	$all_recomendations = get_recomendations_from_week($recomendations_week);

	$all_recomendations = filter_recomendations($all_recomendations);
	$all_recomendations = closest_available_time($all_recomendations);
	$all_recomendations = calculations_by_filling($all_recomendations);
	return $all_recomendations;
}


function get_recomendations_week($estimators, $date_start)
{
	$days = [];
	for($i=0; $i<5; $i++){

		/*$day_number = date('w', strtotime($date_start.'+'.$i.' days'));*/
		$current_date = date('Y-m-d', strtotime($date_start.'+'.$i.' days'));
		/*if($day_number==0){
			$current_date = date('Y-m-d', strtotime($date_start.'+'.($i+1).' days'));
		}*/

		$days[$current_date] = [];
		foreach ($estimators as $key => $estimator) {
			unset($estimator->user_signature);
			$days[$current_date][$estimator->id] = [];
			$days[$current_date][$estimator->id]['estimator'] = $estimator;
		}
	}

	return $days;
}


function set_estimators_appointments($recomendations_week, $appointments){

	$count_appointments = [];

	foreach ($recomendations_week as $date => $estimators) {
		$days_count_appointments = [];
		foreach ($estimators as $estimator_id => $estimator) {

			$recomendations_week[$date][$estimator_id]['appointments'] = search_estimator_appointments($estimator_id, $date, $appointments);

			$estimators[$estimator_id]['count_appointments'] = $recomendations_week[$date][$estimator_id]['count_appointments'] = count($recomendations_week[$date][$estimator_id]['appointments']);//countOk
			if(!isset($recomendations_week[$date][$estimator_id]['total_appointments']))
				$recomendations_week[$date][$estimator_id]['total_appointments'] = 0;

			$recomendations_week[$date][$estimator_id]['total_appointments'] += $estimators[$estimator_id]['count_appointments'];

			$days_count_appointments[] = count($recomendations_week[$date][$estimator_id]['appointments']);//countOk
		}


		$middle_day_count_appointments = 0;
		if(!empty($days_count_appointments))
			$middle_day_count_appointments = array_sum($days_count_appointments)/count($days_count_appointments);//countOk

		foreach ($estimators as $estimator_id => $estimator) {
			$recomendations_week[$date][$estimator_id]['day_filling_coeff'] = 1.05;
			if($estimator['count_appointments'] > $middle_day_count_appointments)
				$recomendations_week[$date][$estimator_id]['day_filling_coeff'] = 0.95;
		}
	}


	foreach ($recomendations_week as $date => $estimators) {
		foreach ($estimators as $estimator_id => $estimator) {
			$count_appointments[] = $recomendations_week[$date][$estimator_id]['total_appointments'];
		}
	}

	$middle_count_appointments = 0;
	if(!empty($count_appointments))
		$middle_count_appointments = array_sum($count_appointments)/count($count_appointments);//countOk

	foreach ($recomendations_week as $date => $estimators) {
		foreach ($estimators as $estimator_id => $estimator) {
			$recomendations_week[$date][$estimator_id]['total_filling_coeff'] = 1.05;
			if($estimator['total_appointments'] > $middle_count_appointments)
				$recomendations_week[$date][$estimator_id]['total_filling_coeff'] = 0.95;

		}
	}

	foreach ($recomendations_week as $date => $estimators) {
		foreach ($estimators as $estimator_id => $estimator) {
			$recomendations_week[$date][$estimator_id]['free_intervals'] = get_free_intervals($date, $recomendations_week[$date][$estimator_id]['appointments']);
			$recomendations_week[$date][$estimator_id]['all_intervals'] = combine_intervals($recomendations_week[$date][$estimator_id]['free_intervals'], $recomendations_week[$date][$estimator_id]['appointments']);
		}
	}

	return $recomendations_week;
}


function search_estimator_appointments($estimator_id, $date, $appointments)
{
	$result = [];
	if(!is_array($appointments) || empty($appointments))
		return [];

	foreach ($appointments as $key => $appointment) {
		if($appointment['task_assigned_user']==$estimator_id && $appointment['task_date']==$date)
			$result[] = $appointment;
	}

	return $result;
}


function combine_intervals($free_intervals, $busy_intervals){
	$CI = &get_instance();
	$CI->load->helper('date_time_format_helper');
	if(!is_array($free_intervals) || empty($free_intervals)){
		return [];
	}

	$result = [];

	$i = 0;
	foreach ($free_intervals as $f_key => $free_value) {
		$result[$i] = [];

//		$result[$i]['current_formated']=['start'=>date("Y-m-d H:i", $free_value['start']), 'end'=>date("Y-m-d H:i", $free_value['end']), 'start_time'=>date("H:i", $free_value['start']), 'end_time'=>date("H:i", $free_value['end'])];
		$result[$i]['current_formated']=['start'=>date("Y-m-d H:i", $free_value['start']), 'end'=>date("Y-m-d H:i", $free_value['end']), 'start_time'=>date(getPHPTimeFormatWithOutSeconds(), $free_value['start']), 'end_time'=>date(getPHPTimeFormatWithOutSeconds(), $free_value['end'])];
		$result[$i]['current']=$free_value;
		$result[$i]['current']['small_intervals'] = get_small_intervals($free_value);
		foreach ($busy_intervals as $b_key => $busy_value) {

			if($free_value['end']==strtotime($busy_value['task_start']))
				$result[$i]['next'] = $busy_value;

			if($free_value['start']==strtotime($busy_value['task_end']))
				$result[$i]['prev'] = $busy_value;
		}
		$i++;
	}
	return $result;
}

function get_small_intervals($interval){
	$CI = &get_instance();
	$CI->load->helper('date_time_format_helper');
	$length = config_item('AppointmentTaskLength') ?: 45;
	$int = $length*60;
	$result = [];
    for($i=(int)$interval['start'];  $i<(int)$interval['end']; $i=($i+$int)){
		$result[] = ['start'=>date(getPHPTimeFormatWithOutSeconds(), $i), 'end'=>date(getPHPTimeFormatWithOutSeconds(), $i+$int), 'date'=>date("d-m-Y", $interval['start'])];
    }
    return $result;
}

function get_recomendations_from_week($recomendations_week){
	if(!is_array($recomendations_week) || empty($recomendations_week))
		return [];

	$result = [];
	$now = time();
	$length = config_item('AppointmentTaskLength') ?: 45;
	foreach ($recomendations_week as $date => $estimators) {
		foreach ($estimators as $estimator_id => $estimator) {
			if(!isset($estimator['all_intervals']) || empty($estimator['all_intervals']))
				continue;

			foreach ($estimator['all_intervals'] as $key => $value) {
				$interval = $value;
				$interval['date'] = $date;
				$interval['estimator'] = $estimator['estimator'];
				$interval['total_filling_coeff'] = $estimator['total_filling_coeff'];
				$interval['day_filling_coeff'] = $estimator['day_filling_coeff'];

				$small_intervals = $interval['current']['small_intervals'];
				$small_intervals_result = [];
				foreach ($small_intervals as $skey => $small_interval) {
					if($now + $length*60 > strtotime($small_interval['date'].' '.$small_interval['end']))
						continue;

					$small_intervals_result[] = $small_interval;
				}
				$interval['current']['small_intervals'] = $small_intervals_result;

				$result[] = $interval;
			}
		}
	}
	return $result;
}

function filter_recomendations($all_recomendations){
	if(!is_array($all_recomendations) || empty($all_recomendations))
		return [];

	$now = time();
	$result = [];
	$length = config_item('AppointmentTaskLength') ?: 45;
	foreach ($all_recomendations as $key => $recomendation) {

		if(($recomendation['current']['end']-$recomendation['current']['start'])/60 < $length)
			continue;

		if(($now > $recomendation['current']['end']) || ($now+$length*60 > $recomendation['current']['end']))
			continue;

		$recomendation['total'] = 70;

		$result[] = $recomendation;
	}

	return $result;
}

function closest_available_time($all_recomendations){
	$now = time();

	$times = [];
	foreach ($all_recomendations as $key => $value) {
		$times[] = $value['current']['start'];
		$times_d[] = date("Y-m-d H:i:s", $value['current']['start']);
	}

	$times = array_unique($times, SORT_NUMERIC);
	$top = [];

	foreach ($times as $key => $time) {
		if(count($top) != 5)//countOk
			$top[]=$time;
	}

	//$coeffs = [1.2, 1.1, 1, 0.9, 0.8];
	$coeffs = [2, 1.8, 1.5, 1.2, 1];
	foreach ($all_recomendations as $key => $value) {

		$search_key = array_search($value['current']['start'], $top);
		$all_recomendations[$key]['closest_available_time'] = 0.8;
		if($search_key!==FALSE){
			$all_recomendations[$key]['closest_available_time'] = $coeffs[$search_key];
		}

		$all_recomendations[$key]['total'] = $all_recomendations[$key]['total']*$all_recomendations[$key]['closest_available_time'];
	}

	return $all_recomendations;
}


function recomendations_set_priority($all_recomendations, $priority_status){

	if($priority_status!="Priority" && $priority_status!="Emergency"){
		return $all_recomendations;
	}

	if(!is_array($all_recomendations) || empty($all_recomendations))
		return [];

	foreach ($all_recomendations as $key => $value) {
		if($priority_status=="Priority"){
			$all_recomendations[$key]['emergency'] = 1;
			$all_recomendations[$key]['priority'] = 1.5;
			$all_recomendations[$key]['total'] = $all_recomendations[$key]['total']*$all_recomendations[$key]['priority'];
		}
		if($priority_status=="Emergency"){
			$all_recomendations[$key]['priority'] = 1;
			$all_recomendations[$key]['emergency'] = 2;
			$all_recomendations[$key]['total'] = $all_recomendations[$key]['total']*$all_recomendations[$key]['emergency'];
		}
	}

	return $all_recomendations;
}



function recomendations_distance($all_recomendations, $client_points){
	if(!is_array($all_recomendations) || empty($all_recomendations))
		return [];
	if(!is_array($client_points) || empty($client_points))
		return $all_recomendations;

	$distance_top = [];


	foreach ($all_recomendations as $key => $value) {
		if(!isset($value['next']))
			$value['next'] = ['task_latitude'=>config_item('office_lat'), 'task_longitude'=>config_item('office_lon')];

		if(!isset($value['prev']))
			$value['prev'] = ['task_latitude'=>config_item('office_lat'), 'task_longitude'=>config_item('office_lon')];

		$to_AB = haversineGreatCircleDistance($value['prev']['task_latitude'], $value['prev']['task_longitude'], $client_points['appointment_lat'], $client_points['appointment_lon']);

		$from_AB = haversineGreatCircleDistance($value['next']['task_latitude'], $value['next']['task_longitude'], $client_points['appointment_lat'], $client_points['appointment_lon']);

		$all_recomendations[$key]['middle distance'] = ($to_AB+$from_AB)/2;
		$distance_top[] = $all_recomendations[$key]['middle distance'];
	}

	$distance_top = array_unique($distance_top, SORT_NUMERIC);
	$top = [];
	foreach ($distance_top as $key => $distance) {
		if(count($top) != 5)//countOk
			$top[]=$distance;
	}

	$coeffs = [1.2, 1.1, 1, 0.9, 0.8];
	foreach ($all_recomendations as $key => $value) {
		$all_recomendations[$key]['middle distance_coeff'] = 1;

		$search_key = array_search($value['middle distance'], $top);
		$all_recomendations[$key]['distance_coeff'] = 0.8;
		if($search_key!==FALSE){
			$all_recomendations[$key]['distance_coeff'] = $coeffs[$search_key];
		}

		$all_recomendations[$key]['total'] = $all_recomendations[$key]['total']*$all_recomendations[$key]['distance_coeff'];
	}

	return $all_recomendations;
}

function calculations_by_filling($all_recomendations)
{
	foreach ($all_recomendations as $key => $value) {

		$all_recomendations[$key]['total'] = $value['total'] * $value['total_filling_coeff'];
		$all_recomendations[$key]['total'] = $value['total'] * $value['day_filling_coeff'];
	}

	return $all_recomendations;
}

function recomendations_previus($all_recomendations, $previus_estimators)
{
	$CI = &get_instance();
	$CI->load->helper('date_time_format_helper');
	$uniqDates = [];
	foreach($all_recomendations as $key => $value)
	{
		$all_recomendations[$key]['timestamp'] = strtotime($all_recomendations[$key]['date']);
		$all_recomendations[$key]['date'] = getDateTimeWithDate($all_recomendations[$key]['date'], 'Y-m-d');

		//$all_recomendations[$key]['current']['small_intervals']['date'] = date("d-m-Y", strtotime($all_recomendations[$key]['current']['small_intervals']['date']));

		$all_recomendations[$key]['previus_estimator_coeff'] = 0.7;
		if(array_search($value['estimator']->id, $previus_estimators)!==false)
			$all_recomendations[$key]['previus_estimator_coeff'] = 1.3;

		$all_recomendations[$key]['total'] = $all_recomendations[$key]['total']*$all_recomendations[$key]['previus_estimator_coeff'];
	}

	return $all_recomendations;
}

function recomendations_preliminary_estimate($all_recomendations, $lead_preliminary_estimate)
{
	$estimate = [
		'small'=>0.8,
		'medium'=>1,
		'big'=>1.2
	];

	$coeff = 1;
	if(isset($estimate[$lead_preliminary_estimate])){
		$coeff = $estimate[$lead_preliminary_estimate];
	}

	foreach($all_recomendations as $key => $value)
	{
		$all_recomendations[$key]['preliminary_estimate_coeff'] = $coeff;
		$all_recomendations[$key]['total'] = $all_recomendations[$key]['total']*$coeff;
	}

	return $all_recomendations;
}

function haversineGreatCircleDistance(
  $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
{
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);
  $latDelta = $latTo - $latFrom;
  $lonDelta = $lonTo - $lonFrom;
  $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
    cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
  return $angle * $earthRadius;
}

function exclude_dayoff_estimators($all_recomendations, $absence){
	$total = array_column($all_recomendations, 'total');
	$date = array_column($all_recomendations, 'timestamp');
	array_multisort($date, SORT_ASC, $total, SORT_DESC, $all_recomendations);

	if(!is_array($absence) || empty($absence))
		return $all_recomendations;

	foreach ($all_recomendations as $key => $value) {
		$date = date("Y-m-d", strtotime($value['date']));
		foreach ($absence as $a_key => $a_value) {
			if((int)$value['estimator']->id == (int)$a_value['id'] && $date == $a_value['absence_ymd'])
			{
				unset($all_recomendations[$key]);
			}
		}
	}

	return $all_recomendations;
}

function group_by_estimator($all_recomendations)
{
	if(!is_array($all_recomendations) || empty($all_recomendations))
		return [];

	$all_recomendations2 = $all_recomendations;
	$uniqDates = [];
	foreach ($all_recomendations as $key => $recomendation) {
		/*$all_recomendations[$key]['gdate'] = null;*/
		/*if(!isset($uniqDates[$recomendation['date']])) {
			$all_recomendations[$key]['gDate'] = $recomendation['date'];
			$uniqDates[$recomendation['date']] = true;
		}*/
		foreach ($all_recomendations2 as $key2 => $recomendation2) {
			if($recomendation['date']==$recomendation2['date'] && $recomendation['estimator']->id==$recomendation2['estimator']->id){
				if(
					$recomendation['current']['start']!=$recomendation2['current']['start'] && $recomendation['current']['end']!=$recomendation2['current']['end']
				){
					//$all_recomendations[$key]['low_priority'][] = $recomendation2;
					$all_recomendations[$key]['current']['small_intervals'] = array_merge($all_recomendations[$key]['current']['small_intervals'], $recomendation2['current']['small_intervals']);
					$all_recomendations[$key]['current']['end'] = $recomendation2['current']['end'];
					$all_recomendations[$key]['current_formated']['end_time'] = $recomendation2['current_formated']['end_time'];
					unset($all_recomendations[$key2]);
				}

				unset($all_recomendations2[$key2]);
			}
		}
	}
	foreach ($all_recomendations as $key => $recomendation) {
		$all_recomendations[$key]['gDate'] = null;
		if(!isset($uniqDates[$recomendation['date']])) {
        	$all_recomendations[$key]['gDate'] = $recomendation['date'];
        	$uniqDates[$recomendation['date']] = true;
    	}
	}
	return $all_recomendations;
}
?>
