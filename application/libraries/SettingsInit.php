<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class SettingsInit
{
	
	function __construct()
	{
		$CI =& get_instance();
		
		//if ($CI->input->is_cli_request()){
        //    return;
        //}
        $tz = date('P');
        $CI->db->query("SET SESSION time_zone='" . $tz . "'");
        if(!$CI->db->table_exists('settings'))
            return false;
		$CI->load->model('mdl_settings_orm', 'settings');
		$CI->settings->install();
	}
}
