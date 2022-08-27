<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
use Carbon\Carbon;

function getDateFormat()
{
    if (config_item('dateFormat'))
        return config_item('dateFormat');
    return 'Y-m-d';
}

function getTimeFormat($withOutSeconds = false)
{
    if($withOutSeconds)
        return config_item('time') == 12 ? 'g:i A' : 'H:i';
    return config_item('time') == 12 ? 'g:i:s A' : 'H:i:s';
}

function getTimeFormatWithOutSeconds()
{
    return config_item('time') == 12 ? 'hh:mm' : 'HH:mm';
}

function getPHPTimeFormatWithOutSeconds()
{
    return config_item('time') == 12 ? 'g:i A' : 'H:i';
}

function getDateTimeWithTimestamp($timestamp, $time = false)
{
    if (!$timestamp)
        return null;
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $format = getDateFormat();
    if ($time)
        $format .= ' ' . getTimeFormat();
    return $date->format($format);
}

function getDateTimeWithDate($date, $format, $time = false, $withOutDays = false, $withoutSeconds = true, $withOutYear = false)
{
    if (!$date)
        return null;
    $date = DateTime::createFromFormat($format, $date);
    if (!$date)
        return null;
    $format = getDateFormat();
    if ($withOutDays)
        $format = getDateFormatWithOutDays();
    elseif ($withOutYear)
        $format = getDateFormatWithOutYear();
    if ($time)
        $format .= ' ' . getTimeFormat($withoutSeconds);
    return $date->format($format);
}

function getTimeWithDate($date, $format, $withOutSeconds = false)
{
    if (!$date)
        return null;
    $date = DateTime::createFromFormat($format, $date);
    $format = getTimeFormat();
    if ($withOutSeconds)
        $format = getPHPTimeFormatWithOutSeconds();
    return $date->format($format);
}

function getTimeWithTimestamp($timestamp, $withOutSeconds = false)
{
    if (!$timestamp)
        return null;
    $date = new DateTime();
    $date->setTimestamp($timestamp);
    $format = getTimeFormat();
    if ($withOutSeconds)
        $format = getPHPTimeFormatWithOutSeconds();
    return $date->format($format);
}

function getDateForJS($date)
{
    if (!$date)
        return null;
    $date = DateTime::createFromFormat(getDateFormat(), $date);
    return $date->format('Y-m-d');
}

function getJSDateFormat()
{
    if (getDateFormat() == 'M d, Y') {
        return 'M dd, yyyy';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'yyyy-mm-dd';
    } elseif (getDateFormat() == 'd/m/Y') {
        return 'dd/mm/yyyy';
    } elseif (getDateFormat() == 'm/d/Y') {
        return 'mm/dd/yyyy';
    } elseif (getDateFormat() == 'd M Y') {
        return 'dd M yyyy';
    }
}

function getJSDateFormatWithOutYear()
{
    if (getDateFormat() == 'M d, Y') {
        return 'M dd';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'mm-dd';
    } elseif (getDateFormat() == 'd/m/Y') {
        return 'dd/mm';
    } elseif (getDateFormat() == 'm/d/Y') {
        return 'mm/dd';
    } elseif (getDateFormat() == 'd M Y') {
        return 'dd M';
    }
}

function getMomentJSDateFormat()
{
    if (getDateFormat() == 'M d, Y') {
        return 'MMM DD, YYYY';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'YYYY-MM-DD';
    } elseif (getDateFormat() == 'd/m/Y') {
        return 'DD/MM/YYYY';
    } elseif (getDateFormat() == 'm/d/Y') {
        return 'MM/DD/YYYY';
    } elseif (getDateFormat() == 'd M Y') {
        return 'DD MMM YYYY';
    }
}

function getDateFormatWithOutDays()
{
    if (getDateFormat() == 'M d, Y') {
        return 'M, Y';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'Y-m';
    } elseif (getDateFormat() == 'd/m/Y' || getDateFormat() == 'm/d/Y') {
        return 'm/Y';
    } elseif (getDateFormat() == 'd M Y') {
        return 'M Y';
    }
}

function getIntTimeFormat()
{
    if (config_item('time'))
        return config_item('time');
    return 12;
}

function getFormatDhlDefaultDate()
{
    if (getDateFormat() == 'M d, Y') {
        return '%M %d, %Y';
    } elseif (getDateFormat() == 'Y-m-d') {
        return '%Y-%m-%d';
    } elseif (getDateFormat() == 'd/m/Y') {
        return '%d/%m/%Y';
    } elseif (getDateFormat() == 'm/d/Y') {
        return '%m/%d/%Y';
    } elseif (getDateFormat() == 'd M Y') {
        return '%d %M %Y';
    }
}

function getJSDateFormatForApp()
{
    if (getDateFormat() == 'M d, Y') {
        return 'MM dd, yyyy';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'yyyy-mm-dd';
    } elseif (getDateFormat() == 'd/m/Y') {
        return 'dd/mm/yyyy';
    } elseif (getDateFormat() == 'm/d/Y') {
        return 'mm/dd/yyyy';
    } elseif (getDateFormat() == 'd M Y') {
        return 'dd MM yyyy';
    }
}

function getJSDateFormatWithOutDays()
{
    if (getDateFormat() == 'M d, Y') {
        return 'M, yyyy';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'yyyy-mm';
    } elseif (getDateFormat() == 'd/m/Y') {
        return 'mm/yyyy';
    } elseif (getDateFormat() == 'm/d/Y') {
        return 'mm/yyyy';
    } elseif (getDateFormat() == 'd M Y') {
        return 'M yyyy';
    }
}

function getDateFormatWithOutYear()
{
    if (getDateFormat() == 'M d, Y') {
        return 'M d';
    } elseif (getDateFormat() == 'Y-m-d') {
        return 'm-d';
    } elseif (getDateFormat() == 'd/m/Y') {
        return 'd/m';
    } elseif (getDateFormat() == 'd M Y') {
        return 'd M';
    } elseif (getDateFormat() == 'm/d/Y'){
        return 'm/d';
    }
}

function get_quarter_date() {
	$current_month = date('m');
	$current_year = date('Y');
	if($current_month>=1 && $current_month<=3) {
		$dates['start_date'] = date(getDateFormat(), strtotime('1-January-'.$current_year));  // timestamp or 1-Januray 12:00:00 AM
		$dates['end_date'] = date(getDateFormat(), strtotime('31-March-'.$current_year));  // timestamp or 1-April 12:00:00 AM means end of 31 March
		$dates['last_start_date'] = date(getDateFormat(), strtotime('1-October-'.($current_year-1)));  // timestamp or 1-October Last Year 12:00:00 AM
		$dates['last_end_date'] = date(getDateFormat(), strtotime('31-December-'.($current_year-1)));  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
	}
	elseif($current_month>=4 && $current_month<=6) {
		$dates['start_date'] = date(getDateFormat(), strtotime('1-April-'.$current_year));  // timestamp or 1-April 12:00:00 AM
		$dates['end_date'] = date(getDateFormat(), strtotime('30-June-'.$current_year));  // timestamp or 1-July 12:00:00 AM means end of 30 June
		$dates['last_start_date'] = date(getDateFormat(), strtotime('1-January-'.$current_year));  // timestamp or 1-Januray 12:00:00 AM
		$dates['last_end_date'] = date(getDateFormat(), strtotime('31-March-'.$current_year));  // timestamp or 1-April 12:00:00 AM means end of 31 March
	}
	elseif($current_month>=7 && $current_month<=9) {
		$dates['start_date'] = date(getDateFormat(), strtotime('1-July-'.$current_year));  // timestamp or 1-July 12:00:00 AM
		$dates['end_date'] = date(getDateFormat(), strtotime('30-September-'.$current_year));  // timestamp or 1-October 12:00:00 AM means end of 30 September
		$dates['last_start_date'] = date(getDateFormat(), strtotime('1-April-'.$current_year));  // timestamp or 1-April 12:00:00 AM
		$dates['last_end_date'] = date(getDateFormat(), strtotime('30-June-'.$current_year));  // timestamp or 1-July 12:00:00 AM means end of 30 June
	}
	elseif($current_month>=10 && $current_month<=12) {
		$dates['start_date'] = date(getDateFormat(), strtotime('1-October-'.$current_year));  // timestamp or 1-October 12:00:00 AM
		$dates['end_date'] = date(getDateFormat(), strtotime('31-December-'.($current_year)));  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
		$dates['last_start_date'] = date(getDateFormat(), strtotime('1-July-'.$current_year));  // timestamp or 1-July 12:00:00 AM
		$dates['last_end_date'] = date(getDateFormat(), strtotime('30-September-'.$current_year));  // timestamp or 1-October 12:00:00 AM means end of 30 September
	}
	return $dates;
}

function getNowDateTime($format = false){
    if($format)
        $now = Carbon::now()->format(getDateFormat());
    else
        $now = Carbon::now()->toDateTimeString();
    return $now;
}

function getTimestamp($date){
    $datetime = DateTime::createFromFormat(getDateFormat(), $date);
    return $datetime->getTimestamp();
}