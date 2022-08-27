<?php

function get_lat_lon($address, $city = null, $state = null, $zip = NULL, $country = NULL)
{
	$CI =& get_instance();
	$country = $country ? $country : $CI->config->item('office_country');
	$g_address = trim($address) . ",";
	$g_city = $city ? ' ' . trim($city) . "," : null;
	$g_state = $state ? ' ' . trim($state) : null;
	$g_zip = $zip ? ' ' . trim($zip) . "," : ",";
	$g_country = ' ' . trim($country);

	$data = array(
	    'lat' => NULL,
        'lon' => NULL,
        'address' => NULL,
        'city' => NULL,
        'state' => NULL,
        'zip' => NULL,
        'country' => NULL,
    );
	$g_addr_str = urlencode($g_address . $g_city . $g_state . $g_zip . $g_country);
	
	$key = $CI->config->item('gmaps_geocoding_key') ? $CI->config->item('gmaps_geocoding_key') : $CI->config->item('gmaps_key');

	$url = "https://maps.google.com/maps/api/geocode/json?address=$g_addr_str&key=" . $key;
    $jsonData = @file_get_contents($url);
	$client_geo_address = json_decode($jsonData);

	if(isset($client_geo_address->{'results'}[0]))
	{
        $addressComponents = $client_geo_address->results[0]->address_components;

        $street_number = '';
        $street = '';
        $city = '';
        $state = '';
        $zip = '';
        $country = '';

        foreach ($addressComponents as $component){
            if($component->types[0] == 'street_number') {
                $street_number = $component->short_name;
            }
            elseif($component->types[0] == 'route') {
                $street = $component->long_name;
            }
            elseif($component->types[0] == 'locality') {
                $city = $component->short_name;
            }
            elseif($component->types[0] == 'administrative_area_level_1') {
                $state = $component->short_name;
            }
            elseif($component->types[0] == 'postal_code') {
                $zip = $component->short_name;
            }
            elseif($component->types[0] == 'country') {
                $country = $component->long_name;
            }
        }

		$data['lat'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
		$data['lon'] = $client_geo_address->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
		$data['address'] = $street_number. ' ' . $street;
		$data['city'] = $city;
		$data['state'] = $state;
		$data['zip'] = $zip;
		$data['country'] = $country;
	}
	return $data;
}

function getNearestHospitalInfo($lat, $lng, $strAddress = null) {
    $hospitalUrl = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $strAddress . '&type=hospital&name=hospital&radius=1000&key=' . (config_item('gmaps_geocoding_key') ?: config_item('gmaps_key'));
    if($lat !== false && $lat !== '' && $lng !== false && $lng !== '') {
        $hospitalUrl = 'https://maps.googleapis.com/maps/api/place/textsearch/json?location=' . $lat . ',' . $lng . '&query=hospital&type=hospital&name=hospital&radius=1000&key=' . (config_item('gmaps_geocoding_key') ?: config_item('gmaps_key'));
    }

    $gmapsData = json_decode(@file_get_contents($hospitalUrl));

    if((!isset($gmapsData->results) || empty($gmapsData->results)) && $strAddress) {
        $hospitalUrl = 'https://maps.googleapis.com/maps/api/place/textsearch/json?query=' . $strAddress . '&type=hospital&name=hospital&radius=1000&key=' . (config_item('gmaps_geocoding_key') ?: config_item('gmaps_key'));
        $gmapsData = json_decode(@file_get_contents($hospitalUrl));
    }

    return [
        isset($gmapsData->results) && !empty($gmapsData->results) ? $gmapsData->results[0]->formatted_address : '—',
        isset($gmapsData->results) && !empty($gmapsData->results) ? $gmapsData->results[0]->name : '—',
        isset($gmapsData->results) && !empty($gmapsData->results) ? $gmapsData->results[0]->geometry->location->lat . ',' . $gmapsData->results[0]->geometry->location->lng : '—'
    ];
}

function get_team_name($id)
{

	$CI = & get_instance();
	$data['id'] = $id;
	$teams = $CI->mdl_estimates->get_teams($select = 'team_name', $data)->row();
	return $teams->team_name;
}

function get_equipment_name($id)
{

	$CI = & get_instance();
	$data['id'] = $id;
	$equips = $CI->mdl_estimates->get_equipments($select = 'equipment_name', $data)->row();
	return $equips->equipment_name;
}

function multifile_array($field = 'files')
{
	if (!isset($_FILES) || empty($_FILES))
		return;
	$files = array();
	$i = 0;
	$all_files = $_FILES[$field]['name'];
	foreach ((array)$all_files as $key => $fileTypes) {
		/*if(!$filename)
			continue;*/
		foreach ($fileTypes as $nkey => $fileType) {
			if(is_array($fileType))
			{
				foreach ($fileType as $num => $filename) {
					$files[++$i]['name'] = $filename;
					$files[$i]['type'] = $_FILES[$field]['type'][$key][$nkey][$num];
					$files[$i]['tmp_name'] = $_FILES[$field]['tmp_name'][$key][$nkey][$num];
					$files[$i]['error'] = $_FILES[$field]['error'][$key][$nkey][$num];
					$files[$i]['size'] = $_FILES[$field]['size'][$key][$nkey][$num];
					$files[$i]['field'] = $nkey;
				}
			}
			else
			{
				$files[++$i]['name'] = $fileType;
				$files[$i]['type'] = $_FILES[$field]['type'][$key][$nkey];
				$files[$i]['tmp_name'] = $_FILES[$field]['tmp_name'][$key][$nkey];
				$files[$i]['error'] = $_FILES[$field]['error'][$key][$nkey];
				$files[$i]['size'] = $_FILES[$field]['size'][$key][$nkey];
				$files[$i]['field'] = $key;
			}
		}
	}
	$_FILES = $files;
}

function repack_service_uploads($from = NULL, $to = NULL)
{
	if(isset($_FILES['service_files']['name'][$from]))
	{
		foreach($_FILES['service_files'] as $fieldName => &$value)
		{
			$value[$to] = $value[$from];
			unset($value[$from]);
		}
	}
	return TRUE;
}

function check_dir($estimate_no, $nocreate = FALSE, $client_id = NULL, $service_id = NULL)
{
	$dir = './uploads/clients_files/' . $client_id . '/estimates/' . $estimate_no . '/' . $service_id . '/';
	$dir = rtrim($dir, '/');
	$path = NULL;
	$folders = explode('/', $dir);
	@chmod($path, 0777);
	foreach ($folders as $folder) {
		$path .= $folder . '/';
		if (!is_dir($path)) {
			mkdir($path);
			chmod($path, 0777);
		}
	}
	return $path;
}

function get_image_dir($client_id, $estimate_no, $service_id)
{
	return 'uploads/clients_files/' . $client_id . '/estimates/' . $estimate_no . '/' . $service_id . '/';
}

function recursive_rm_files($path)
{
	/*if (!function_exists('directory_map')) {
		$CI = & get_instance();
		$CI->load->helper('directory');
	}
	if (is_dir($path))
		$path = rtrim($path, '/') . '/';
	$files = directory_map($path, 1);
	if ($files) {
		foreach ($files as $file) {
			if (is_dir($path . $file))
				recursive_rm_files($path . $file . '/');
			else
				unlink($path . $file);
		}
	}
	@rmdir($path);*/
    bucket_unlink_all(rtrim($path, '/') . '/');
}

function makedir($path)
{
	$path = rtrim($path, '/');
	$folders = explode('/', $path);
	$path = FCPATH . 'uploads/';

	@chmod($path, 0777);
	foreach ($folders as $folder) {
		if ($folder == 'uploads')
			continue;
		$path .= $folder . '/';

		if (!is_dir($path)) {
			@mkdir($path);
			@chmod($path, 0777);
		}
	}
}

function interest_sum($sum, $invoice_interest_data, $payments_data, $term)
{
	$overue_sum = 0;
	if($invoice_interest_data && !empty($invoice_interest_data))
	{
		foreach ($invoice_interest_data as $interset_row) {
			if ($payments_data && !empty($payments_data)) {
				foreach ($payments_data as $pay) {
					if ($pay['payment_date'] < (strtotime($interset_row->overdue_date) - $term * 86400))
						$sum -= $pay['payment_amount'];
				}
			}
			$interest = abs($interset_row->rate / 100);
			$overue = $sum * $interest;
			$sum += $overue;
			$overue_sum += $overue;
			foreach ($payments_data as $pay) {
				if ($pay['payment_date'] < (strtotime($interset_row->overdue_date) - $term * 86400))
					$sum += $pay['payment_amount'];
			}
		}
	}
	return round($overue_sum, 2);
}

function get_interest_sum($id)
{
	$CI = & get_instance();
	$CI->load->model('mdl_invoices');
	$CI->load->model('mdl_estimates');
	$CI->load->model('mdl_clients');
	$CI->load->model('mdl_estimates_orm');
	$CI->load->model('mdl_services_orm');
	$CI->load->model('mdl_crews_orm');
	$CI->load->model('mdl_equipment_orm');
	$invoice = $CI->mdl_invoices->invoices(['invoices.id' => $id]);
	
	if(!is_array($invoice) || empty($invoice))
		return 0;

	$est_data = $CI->mdl_estimates_orm->with('mdl_services_orm')->get_full_estimate_data(array('estimates.estimate_id' => $invoice[0]->estimate_id))[0];
	$discount_data = $CI->mdl_clients->get_discount(array('discounts.estimate_id' => $invoice[0]->estimate_id));
	$discount = 0;
	$discountPercents = 0;
	if($discount_data) {
		$discount = $discount_data['discount_amount'];
		$discountPercents = $discount_data['discount_percents'];
	}
	$result = 0;
	$sum = 0;
	if($invoice[0]->interest_status == 'No')
	{
		/*foreach ($est_data->mdl_services_orm as $service_data)
		{
			if($service_data->service_status == 2)
				$sum += $service_data->service_price;
		}
		$discount = $discountPercents ? round($sum * $discount / (100 + $discount), 2, PHP_ROUND_HALF_DOWN) : $discount;
		$sum -= $discount;
		$invoice_interest_data = $CI->mdl_invoices->getInterestData($id);
		$payments_data = $CI->mdl_clients->get_payments(array('client_payments.estimate_id' => $invoice->row()->estimate_id));
		$term = 30;

		$result = interest_sum($sum, $invoice_interest_data, $payments_data, $term);*/
		$sum = $CI->mdl_invoices->get_sum_interes($invoice[0]->id);
		if(isset($sum['sum']) && $sum)
			$result = $sum['sum'];
	}
	
	return $result;
}

function encrypt_data($key, $text){
	$text = trim($text);
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$encrypted_text = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv));
	return $encrypted_text;
}

function decrypt_data($key, $text){
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$decrypted_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($text), MCRYPT_MODE_ECB, $iv);
	return trim($decrypted_text);
}

function getRotation($imagePath)
{
	if(!is_file($imagePath))
		return 0;
	
	if (!is_readable($imagePath)) {
		throw new Exception("Cannot read '{$imagePath}' file!");
	}

	if (!function_exists('exif_read_data')) {
		return 0;
	}
	
	$data        = exif_read_data($imagePath);
	$orientation = isset($data['Orientation'])
		? (int)$data['Orientation']
		: 0;

	switch($orientation) {
		case 1:
		case 2:
			return 0;
			break;
		case 3:
		case 4:
			return 180;
			break;
		case 5:
		case 6:
			return 90;
			break;
		case 7:
		case 8:
			return 270;
			break;
		default:
			return 0;
			break;
	}
}

function is_image($filename)
{
	if(!$filename)
		return false;
	return boolval(getimagesize($filename));
}

function is_pdf($filename)
{
	if(!$filename)
		return false;
	$handle = fopen($filename, "r");
	$header = fgets($handle);
	fclose($handle);
	return strpos($header, '%PDF') !== FALSE;
}

function check_email_exists($email)
{
    $email = trim($email);
    $acceptableStatus = [
        'Ok',
        'Unverifiable',
        'RetryLater'
    ];
    $acceptableReason = [
        'MailboxFull',
        'PossibleSpamTrapDetected',
        'TransientNetworkFault'
    ];

	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
		return FALSE;
    $apikey = '03AA5881';

    $url_v3  = 'https://api.hippoapi.com/v3/more/json/' . $apikey . '/' . $email;

	$ch = curl_init($url_v3);
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Content-type: application/json')
	);
	curl_setopt_array($ch, $options);

	$result = json_decode(curl_exec($ch));

    $emailVerify = $result->emailVerification->mailboxVerification ?? [];

    if (is_object($emailVerify) && (in_array($emailVerify->reason, $acceptableReason, true) || in_array($emailVerify->result, $acceptableStatus, true))) {
        $emailVerify->result = 'Ok';
    } elseif(!is_object($emailVerify)) {
        $emailVerify = new stdClass();
        $emailVerify->result = 'Ok';
    } else {
        $emailVerify->result = 'Bad';
    }

    // Return statuses:
    // 0  - Email Not Exist;
    // 1  - Email Confirmed;
    // 2  - MX is present, but the email address cannot be verified for some reason
    // -1 - Uncatchable response

    return $emailVerify->result == 'Ok' ? 1 : 0;

	/*************PHANTOMJS**************************/
	/*$filePath = FCPATH . 'assets/js/phantom_email_checker.js';
	exec("phantomjs $filePath $email", $output, $return_var);
	return isset($output[0]) ? $output[0] : false;*/
}

function set_wo_pending($args) {
	$CI = & get_instance();
	$CI->load->library('Common/WorkorderActions');

	$pendingStatusId = $CI->workorderactions->getPendingStatusId();

	if($pendingStatusId && isset($args['wo_id'])) {
        $_POST['workorder_id'] = $args['wo_id'];
        $_POST['date'] = date('Y-m-d H:i:s');
        $_POST['workorder_status'] = $CI->workorderactions->getPendingStatusId();
        ob_start();
        Modules::run('workorders/workorders/ajax_change_workorder_status', true);
        ob_end_clean();
    }
}

function refferencedBy() {
	$CI = & get_instance();
	return $CI->config->item('refferenced_by');
}

function get_neighborhood($coords)
{
	ini_set('memory_limit', '-1');
	set_time_limit ( '0'); 
	$CI = & get_instance();
	$CI->load->library('Pointlocation');
	$getPolygons = $CI->db->query('SELECT * FROM neighborhoods')->result_array();
    $polygons = [];
		
	foreach($getPolygons as $key=>$val)
	{
		foreach(json_decode($val['coords']) as $k=>$v)
			$polygons[$val['id']][] = $v->lng . ' ' . $v->lat;
	}
	  
	if(!$coords['longitude'] || !$coords['latitude'])
		return NULL;
	$point = $coords['longitude'] . ' ' . $coords['latitude'];
	foreach($polygons as $key => $polygon) {
		if($CI->pointlocation->pointInPolygon($point, $polygon, TRUE) == 'inside')
		{
			return $key;
			break;
		}
	}
}
function GetCardType($number){
$types = [
        'electron' => '/^(4026|417500|4405|4508|4844|4913|4917)/',
        'interpayment' => '/^636/',
        'unionpay' => '/^(62|88)/',
        'discover' => '/^6(?:011|4|5)/',
        'maestro' => '/^(50|5[6-9]|6)/',
        'visa' => '/^4/',
        'mastercard' => '/^(5[1-5]|(?:222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720))/', // [2221-2720]
        'amex' => '/^3[47]/',
        'diners' => '/^3(?:0([0-5]|95)|[689])/',
        'jcb' => '/^(?:2131|1800|(?:352[89]|35[3-8][0-9]))/', // 3528-3589
        'mir' => '/^220[0-4]/',
    ];
   foreach($types as $type => $regexp){
       if( preg_match($regexp, $number) ){
           return $type;
      }
   }

   return 'undefined';
}

function GetCardMask($number, $maskingCharacter = '*') {
    return substr($number, 0, 4) . str_repeat($maskingCharacter, strlen($number) - 8) . substr($number, -4);
}
function reArrayFiles(&$file_post, $field = 'files') {
 
    $file_ary = array();
    $file_count = count($file_post['name']);//countOk
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    $_FILES = $file_ary;
}

function is_protected_estimate_status($status){
	if(!$status || !$status->est_status_id)
		return FALSE;

	if($status->est_status_confirmed || $status->est_status_default || $status->est_status_sent || $status->est_status_declined)
		return TRUE;
	return FALSE;
}
