<?php 

function only_images($files){
	$result = [];
	if(!is_array($files) || empty($files))
		return [];

	foreach ($files as $key => $file) {
        if(preg_match('/' . implode('|', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'gif', 'svg']) . '/', strtolower(pathinfo($file, PATHINFO_EXTENSION))))
			$result[] = $file;
	}
	return $result;
}

// TODO: Remove, deprecated function
function save_signature($post){
	if(!is_array($post) || empty($post) || !$post['signature_image'])
		return false;

	$patch = event_signature_patch($post['ev_estimate_id'], $post['ev_event_id']);
	$img = str_replace('data:image/png;base64,', '', $post['signature_image']);
	$img = str_replace('[removed]', '', $img);
	$img = str_replace(' ', '+', $img);
	$data = base64_decode($img);

    try {
        $im = imagecreatefromstring($data);

        $tmp = imagecreate(imagesx($im), imagesy($im));

        imagealphablending($tmp, false);
        imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $im, 0, 0, 0, 0, imagesx($im), imagesy($im), imagesx($im), imagesy($im));

        imagepng($tmp, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $post['ev_event_id'] . '.png', 0);
        imagedestroy($im);
        imagedestroy($tmp);

        bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $post['ev_event_id'] . '.png', $patch, ['ContentType' => 'image/png']);
        @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $post['ev_event_id'] . '.png');
        return TRUE;
    } catch (Exception $e) {
        return FALSE;
    }
}

function event_signature_patch($estimate, $event){

	$base_path = 'uploads/events';
	$base_path = $base_path.'/'.$estimate.'/'.$event.'.png';
	return $base_path;
}

if (!function_exists('validation_errors_array')) {

   function validation_errors_array($prefix = '', $suffix = '') {
      if (FALSE === ($OBJ = & _get_validation_object())) {
        return '';
      }

      return $OBJ->error_array($prefix, $suffix);
   }
}


/* -------------------report fields callbacks ---------------------*/

function is_finished_event($val){
  return (isset($val['er_event_status_work']) && strtolower($val['er_event_status_work'])=='finished')?true:false;
}

function is_paid_event($val){
  return (isset($val->er_event_payment) && strtolower($val->er_event_payment)=='yes')?true:false;
}

function is_event_damage($val){
  return (isset($val->er_event_damage) && strtolower($val->er_event_damage)=='yes')?true:false;
}

function is_malfunctions_equipment($val){
  return (isset($val->er_malfunctions_equipment) && strtolower($val->er_malfunctions_equipment)=='yes')?true:false;
}

function is_expenses($val){
  return (isset($val->er_expenses) && strtolower($val->er_expenses)=='yes')?true:false;
}



function er_event_payment($val, $data=[]){
  if(!$val)
    return 'No';
  return $val;
}

function er_event_payment_type($type, $data=[]){
  if(!$data['er_event_payment'] || $data['er_event_payment']=='No')
    return '';
  return $type;
}

function er_payment_amount($amount, $data=[]){
  if(!$data['er_event_payment'] || $data['er_event_payment']=='No')
    return '';
  return $amount;
}
function report_date($date, $data=[])
{
  return getDateTimeWithDate($date, "Y-m-d");
}

function datetime_to_time($datetime, $data=[]){
  if(!$datetime)
    return "00:00";

  return date(getPHPTimeFormatWithOutSeconds(), strtotime($datetime));
}

function time_to_format($time, $data=[]){
  if(!$time)
    return "00:00";

  return date(getPHPTimeFormatWithOutSeconds(), strtotime($time));
}

function time_to_hours($time, $data=[]){
  return (intval($time))?gmdate("H:i", $time):'00:00';
}

function estimator_time($time, $data=[]){
  $time_estimator = estimator_time_summ($time, $data);
  $hours = floor($time_estimator);
  $minutes = ($time_estimator - $hours)*60;
  return $hours . ':' . str_pad(floor($minutes), 2, '0', STR_PAD_LEFT);
}
function estimator_time_summ($time, $data=[]){
  
  $time_estimator = 0;
  if(isset($data['estimate_services_data']) && count($data['estimate_services_data'])){
    foreach($data['estimate_services_data'] as $estimate_time)
      $time_estimator += $estimate_time['service_time'];
  }

  return $time_estimator;
}

function estimator_time_class($time, $data=[]){
  $time_estimator = estimator_time_summ($time, $data);
  return ($data['full_time'] > $time_estimator*3600)?'text-danger':'text-success';
}



/* -------------------report fields callbacks ---------------------*/