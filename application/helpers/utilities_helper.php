<?php

/*
	*
	*	send mail common function
	*
	*/
function send_mail($to, $subject, $body, $from_email = '', $from_name = '', $attach = '')
{
	$CI = & get_instance();
	// send mail
	$CI->load->library('email');

	$config['mailtype'] = 'html';
	$config['protocol'] = 'sendmail';


	$CI->email->initialize($config);

	if ($from_email == '') {
		$CI->email->from(_ADMIN_EMAIL, _ADMIN_NAME);
	} else {
		$CI->email->from($from_email, $from_name);
	}
	if (!empty($attach)) {
		$CI->email->attach($attach);
	}
	$CI->email->to($to);
	$CI->email->subject($subject);
	$CI->email->message($body);
	//$CI->email->bcc($CI->config->item('account_email_address'));

	$send = $CI->email->send();

	if (!is_array($send) || isset($send['error'])) {
		echo "Mail can not send. > " . $CI->email->print_debugger();
		return 0;
	} else {
		return 1;
	}
}

/**
 * prints array in readable form
 *
 *
 */

function pr($arr, $die = false)
{
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
	if ($die == true) {
		die("Printing array....");
	}
}

/*
*
*	function to get month calendar
*/
function cal($m, $y)
{
	$week = get_weekdays();
	$w = 7;
	$m = 12;
	$y = 2013;
	$days = cal_days_in_month(CAL_GREGORIAN, $m, $y);
	$cal = array();

	$weeksmake = ceil($days / 7);
	$w = 1;
	for ($d = 1; $d <= 35; $d++) {
		$date = "$y-$m-$d";
		$get_week = date("l", strtotime($date));

		foreach ($week as $wk => $wv) {
			if ($get_week == $wv && $d > 0 && $d <= $days) {
				$cal[$w][$date] = $get_week;
			}
		}
		if ($w % 5 == 0) {
			$w = 0;
		}
		$w++;
	}
	return $cal;
}

/*
*	function to encrypr password
*/
function encrypt_pass($pass)
{
	return md5($pass . "FtG45HH" . $pass);
}

/**
 * Create a Random String
 *
 * Useful for generating passwords or hashes.
 *
 * @access    public
 * @param    string    type of random string.  Options: alunum, numeric, nozero, unique
 * @param    integer    number of characters
 * @return    string
 */
if (!function_exists('random_string')) {
	function random_string($type = 'alnum', $len = 8)
	{
		switch ($type) {
			case 'alnum'    :
			case 'numeric'    :
			case 'nozero'    :

				switch ($type) {
					case 'alnum'    :
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric'    :
						$pool = '0123456789';
						break;
					case 'nozero'    :
						$pool = '123456789';
						break;
				}

				$str = '';
				for ($i = 0; $i < $len; $i++) {
					$str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
				}
				return $str;
				break;
			case 'unique' :
				return md5(uniqid(mt_rand()));
				break;
		}
	}
}

/*
*	function used to get total days in a week
*
*/

function get_week_days($date)
{
	$arr = array();
	$arr["cdate"] = $date;
	//$arr["cur_date"][] = $date;
	$arr["cday"] = date("w", strtotime($date));
	if ($arr["cday"] == 0) {
		$arr["cday"] = 7;
	}

	$arr["week"] = 7;

	$arr["days_before"] = array();
	$arr["total_days_before"] = 0;
	for ($db = 0; $db < $arr["week"]; $db++) {
		if ($db < $arr["cday"]) {
			$arr["days_before"][] = date("Y-m-d", strtotime("{$arr['cdate']} -{$db} days"));
			$arr["total_days_before"]++;
		}
	}

	$arr["last_week_day"] = $arr["total_days_before"] + 7;
	$arr["last_week_date"] = date("Y-m-d", strtotime("{$arr['cdate']} -{$arr['last_week_day']} days"));

	$arr["total_days_after"] = $arr["week"] - $arr["cday"];
	$arr["days_after"] = array();
	if ($arr["cday"] < 7) {
		for ($da = 1; $da <= $arr["total_days_after"]; $da++) {
			$arr["days_after"][] = date("Y-m-d", strtotime("{$arr['cdate']} +{$da} days"));
		}
	}

	$arr["next_week_day"] = $arr["total_days_after"] + 1;
	$arr["next_week_date"] = date("Y-m-d", strtotime("{$arr['cdate']} +{$arr['next_week_day']} days"));

	$arr["total_week_days"] = array_merge($arr["days_before"], $arr["days_after"]);
	sort($arr["total_week_days"]);
	return $arr;
}

/**
 * Create a Random String
 *
 * Useful for generating passwords or hashes.
 *
 * @access    public
 * @param    string    type of random string.  Options: alunum, numeric, nozero, unique
 * @param    integer    number of characters
 * @return    string
 */
if (!function_exists('random_string')) {
	function random_string($type = 'alnum', $len = 8)
	{
		switch ($type) {
			case 'alnum'    :
			case 'numeric'    :
			case 'nozero'    :

				switch ($type) {
					case 'alnum'    :
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric'    :
						$pool = '0123456789';
						break;
					case 'nozero'    :
						$pool = '123456789';
						break;
				}

				$str = '';
				for ($i = 0; $i < $len; $i++) {
					$str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
				}
				return $str;
				break;
			case 'unique' :
				return md5(uniqid(mt_rand()));
				break;
		}
	}
}

/*
*
*	functrion decrypt string from hex
*/
function oldDecrypt($hexa)
{
	$string = '';
	for ($i = 0; $i < strlen($hexa) - 1; $i += 2) {
		$string .= chr(hexdec($hexa[$i] . $hexa[$i + 1]));
	}
	return $string;
}

/*
*
* function used to convert url string to array
*
*/

function convert_urlstring_array($httpResponse)
{
	$httpParsedResponseAr = array();
	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);

	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if (sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}
	return $httpParsedResponseAr;
}

?>
