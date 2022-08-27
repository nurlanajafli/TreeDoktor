<?php 
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

if(!function_exists('is_field')){
	function is_field($data = [])
	{
		return (worker_type($data, true)==1)?true:false;
	}
}

if(!function_exists('is_support')){
	function is_support($data = [])
	{
		return (worker_type($data, true)==2)?true:false;
	}
}

if(!function_exists('worker_type')){
	function worker_type($user_row = [], $is_post = true)
	{
		$worker_type = 1;
		$CI = & get_instance();
		
		if($CI->uri->segment(2)=="user_update" || $CI->uri->segment(2)=="save"){
			if($is_post)
				$worker_type = element('worker_type', $CI->input->post(), element('worker_type',(array)$user_row, 1));
			else 
				$worker_type = element('worker_type',(array)$user_row, 1);
		}

		if($CI->uri->segment(2)=="user_add")
			$worker_type = ($CI->uri->segment(3)=='field')?1:2;
		
		return $worker_type;
	}
}

if(!function_exists('client_address')){
	function client_address($client){
		$result_array = [
			element('client_address', $client, FALSE), 
			element('client_city', $client, FALSE),
			element('client_state', $client, FALSE),
			element('client_zip', $client, FALSE)
		];

		return implode(',', array_filter($result_array));
	}
}

if(!function_exists('lead_address')){
	function lead_address($client){
		$result_array = [
			element('lead_address', $client, FALSE), 
			element('lead_city', $client, FALSE),
			element('lead_state', $client, FALSE),
			element('lead_zip', $client, FALSE)
		];

		return implode(', ', array_filter($result_array));
	}
}

if(!function_exists('lead_address_map')){
	function lead_address_map($client){
		$result_array = [
			element('lead_address', $client, FALSE), 
			element('lead_city', $client, FALSE),
			element('lead_state', $client, FALSE),
			element('lead_zip', $client, FALSE)
		];

		return implode(',', array_filter($result_array));
	}
}

if(!function_exists('task_address')){
	function task_address($event){
		$result_array = [
			element('task_address', $event, FALSE), 
			element('task_city', $event, FALSE),
			element('task_state', $event, FALSE),
			element('task_zip', $event, FALSE)
		];

		return implode(', ', array_filter($result_array));
	}
}

if(!function_exists('scheme_address')){
    function scheme_address($scheme){
        $result_array = [
            element('tis_address', $scheme, FALSE),
            element('tis_city', $scheme, FALSE),
            element('tis_state', $scheme, FALSE),
            element('tis_zip', $scheme, FALSE)
        ];

        return implode(', ', array_filter($result_array));
    }
}

if(!function_exists('user_fullname')){
    function user_fullname($user, $default = '') {
        if (isset($user['firstname']) && isset($user['lastname'])) {
            return $user['firstname'] . " " . $user['lastname'];
        } elseif (isset($user['firstname'])) {
            return $user['firstname'];
        } elseif (isset($user['lastname'])) {
            return $user['lastname'];
        } elseif (isset($user['emailid'])) {
            return $user['emailid'];
        } else {
            return $default;
        }
    }
}
?>