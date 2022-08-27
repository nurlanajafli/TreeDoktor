<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class MY_Input
 * Created by Gleba Ruslan 2014-11-12
 * _clean_input_keys - Added allow "!" for POST input name.
 */
class MY_Input extends CI_Input
{
    function _clean_input_keys($str, $fatal = true)
	{
		if (!preg_match("/^[a-z0-9:_\/|!?%-]+$/i", $str)) {
            if ($fatal === true) {
                return false;
            } else {
                set_status_header(503);
                echo 'Disallowed Key Characters.';
                exit(7); // EXIT_USER_INPUT
            }
		}
		if (UTF8_ENABLED === TRUE) {
			$str = $this->uni->clean_string($str);
		}

		return $str;
	}
	
	function _clean_input_data($str)
	{
		if (is_array($str)) {
			$new_array = array();
			foreach ($str as $key => $val) {
				$new_array[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
			}
			return $new_array;
		}
		
		if(isset($_SERVER['REQUEST_URI']) && (strpos($_SERVER['REQUEST_URI'], '/user/save') !== FALSE ||
                in_array($_SERVER['REQUEST_URI'], array(
                    '/clients/ajax_save_template',
                    '/clients/ajax_send_email',
                    '/clients/ajax_send_newsletters',
                    '/estimates/add_estimate',
                    '/estimates/ajax_presave_scheme',
                    '/estimates/save_estimate',
                    '/estimates/update_estimate',
                    '/schedule/data?editing=true',
                    '/workorders/send_pdf_to_email',
                    '/invoices/send_pdf_to_email',
                    '/schedule/ajax_send_letter',
                    '/estimates/send_pdf_to_email',
                    '/clients/ajax_save_voice',
                    '/administration/ajax_save_followup',
                    '/brands/save',
                )))
        )
			return $str;

		if (!is_php('5.4') && get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}

		// Clean UTF-8 if supported
		if (UTF8_ENABLED === TRUE) {
			$str = $this->uni->clean_string($str);
		}

		// Remove control characters
		$str = remove_invisible_characters($str);

		// Should we filter the input data?
		if ($this->_enable_xss === TRUE) {
			$str = $this->security->xss_clean($str);
		}

		// Standardize newlines if needed
		if ($this->_standardize_newlines == TRUE) {
			if (strpos($str, "\r") !== FALSE) {
				$str = str_replace(array("\r\n", "\r", "\r\n\n"), PHP_EOL, $str);
			}
		}

        return $str;
	}

	function post($index = NULL, $xss_clean = FALSE, $default_value = NULL)
    {
        // Check if a field has been provided
        if ($index === NULL AND ! empty($_POST))
        {
            $post = array();

            // Loop through the full _POST array and return it
            foreach (array_keys($_POST) as $key)
            {
                $post[$key] = $this->_fetch_from_array($_POST, $key, $xss_clean);
            }

            return $post;
        }

        $ret_val = $this->_fetch_from_array($_POST, $index, $xss_clean);

        if(!$ret_val && $default_value && !is_string($ret_val))
            $ret_val = $default_value;
        elseif (!$ret_val && !is_string($ret_val))
        	$ret_val = FALSE;

        return $ret_val;
    }
}
