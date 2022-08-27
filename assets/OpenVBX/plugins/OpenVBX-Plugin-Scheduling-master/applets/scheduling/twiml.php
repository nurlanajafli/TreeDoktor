<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;

$response = new Response();

$fallback = AppletInstance::getDropZoneUrl('fallback');
$choices = AppletInstance::getValue('choices[]');
$start = AppletInstance::getValue('timestart[]');
$finish = AppletInstance::getValue('timefinish[]');


$ci = &get_instance();

$choice_array = [];

if (is_array($start) && count($start) == 1) {

    $new_start = [0 => $start];
    $start = $new_start;

    $new_finish = [0 => $finish];
    $finish = $new_finish;

}

foreach ($choices as $a => $q) {
    $choice_array[$a] = AppletInstance::getDropZoneUrl('choices[' . $a . ']');
}

function verify_day($key, $today)
{

    $sunday = AppletInstance::getValue('sunday[]');
    $monday = AppletInstance::getValue('monday[]');
    $tuesday = AppletInstance::getValue('tuesday[]');
    $wednesday = AppletInstance::getValue('wednesday[]');
    $thursday = AppletInstance::getValue('thursday[]');
    $friday = AppletInstance::getValue('friday[]');
    $saturday = AppletInstance::getValue('saturday[]');

    switch ($today) {

        case "Sunday":
            if ($sunday == 'true' || $sunday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;

        case "Monday":

            if ($monday == 'true' || $monday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;

        case "Tuesday":

            if ($tuesday == 'true' || $tuesday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;

        case "Wednesday":

            if ($wednesday == 'true' || $wednesday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;

        case "Thursday":

            if ($thursday == 'true' || $thursday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;

        case "Friday":

            if ($friday == 'true' || $friday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;

        case "Saturday":

            if ($saturday == 'true' || $saturday[$key] == "true") {
                return 1;
            } else {
                return 0;
            }

            break;
    }

}

function verify_time($currentTime, $startTime, $endTime)
{

    $now = explode(":", $currentTime);
    $currentHour = (int)$now[0];
    $currentMinute = (int)$now[1];

    $start = explode(":", $startTime);
    $startHour = (int)$start[0];
    $startMinute = (int)$start[1];

    $end = explode(":", $endTime);
    $endHour = (int)$end[0];
    $endMinute = (int)$end[1];

    $pass = true;

    if ($startHour <= $endHour) {

        if ($currentHour < $startHour) {
            $pass = false;
        };

        if ($currentHour > $endHour) {
            $pass = false;
        };

        if ($currentHour == $startHour) {
            if ($currentMinute < $startMinute) {
                $pass = false;
            };
        };

        if ($currentHour == $endHour) {
            if ($currentMinute > $endMinute) {
                $pass = false;
            };
        };

    } else {

        if (($currentHour < $startHour) && ($currentHour > $endHour)) {
            $pass = false;
        };

        if ($currentHour == $startHour) {
            if ($currentMinute < $startMinute) {
                $pass = false;
            };
        };

        if ($currentHour == $endHour) {
            if ($currentMinute > $endMinute) {
                $pass = false;
            };
        };

    };

    if ($pass == false) {
        return 0;
    } else {
        return 1;
    };

}

$oops = true;
$do_fallback = false;
if (!is_array($start)) {
    $start = [$start];
}
if (!is_array($finish)) {
    $finish = [$finish];
}

if (is_array($start)) {
    foreach ($start as $k => $b) {

        $ci->config->set_item('time_reference', 'local');
        $ci->load->helper('date');

        $hour = date("G");

        $server_time = mktime($hour, date("i"), date("s"));

        $server_time_formatted = date("G:i", $server_time);

        $server_day_formatted = date("l", $server_time);

        $currentTime = $server_time_formatted;

        $finish_time = $finish[$k];

        if (verify_time($currentTime, $b, $finish_time) == 1 and verify_day($k, $server_day_formatted) == 1) {
            $response->addRedirect($choice_array[$k]);
            $response->Respond();
            $oops = false;
            $do_fallback = false;
            //echo "passed check ";
            break;
        } elseif (verify_time($currentTime, $b, $finish_time) == 0 or verify_day($k, $server_day_formatted) == 0) {
            $do_fallback = true;
            $oops = true;
            //echo "failed check ";
        }
    }
}

if ($oops == true and $do_fallback == true) {
    $response->addRedirect($fallback);
    $response->Respond();
}

?>