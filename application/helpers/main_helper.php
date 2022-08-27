<?php

use Twilio\Rest\Client;
use application\modules\categories\models\Category;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function get_last_message_id_for_page()
{
    $CI = &get_instance();
    $CI->db->select_max('id', 'id');
    $CI->db->where('from', $CI->session->userdata('user_id'));
    $CI->db->or_where('to', $CI->session->userdata('user_id'));
    $maxIdRow = $CI->db->get('chat')->row();

    $CI->db->where('id', $maxIdRow->id);
    $row = $CI->db->get('chat')->row();

    if (!$row)
        return 0;

    if ($row->from == $CI->session->userdata('user_id') || ($row->to == $CI->session->userdata('user_id') && $row->recd))
        return $maxIdRow->id;
    else
        return $maxIdRow->id - 1;
}

function get_last_message_id()
{
    $CI = &get_instance();
    $CI->db->select_max('id', 'id');
    $CI->db->where('from', $CI->session->userdata('user_id'));
    $CI->db->or_where('to', $CI->session->userdata('user_id'));
    return $CI->db->get('chat')->row()->id;
}

function slack_error_notification($subject = null, $url = null, $file = null, $line = null, $message = null, $postFields = [])
{
    $msg = [
        'blocks' => [
            [
                'type' => 'divider'
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*" . $subject . "*"
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Domain*: <" . config_item('base_url') . "|" . config_item('base_url') . ">"
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Date:* \n" . date('Y-m-d H:i:s')
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*URL:*\n<" . $url . "|" . $url . ">"
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*File:*\n" . ($file ? ($file . ' *Line:* ' . $line) : '—')
                ],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "\n\n*Message:*\n" . $message ? strip_tags(str_replace(["<br />","<br>","<br/>"],"\n", $message)) : '—'
                ]
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "\n\n*Data:*```" . json_encode(json_decode($postFields), JSON_PRETTY_PRINT) . "```"
                ]
            ],
            [
                'type' => 'divider',
            ],
            [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'plain_text',
                        'text' => 'Regards: ArboStar Development Department'
                    ]
                ]
            ],
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://hooks.slack.com/services/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');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg));
    $result = curl_exec($ch);
    curl_close($ch);

    return true;
}

function slack_attention_notification($message = null) {

    $msg = [
        'blocks' => [
            [
                'type' => 'divider'
            ], [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*URL:*\n<" . base_url() . "|" . base_url() . ">"
                ]
            ], [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*Attention*"
                ]
            ], [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "\n\n*Message:*\n" . $message ? strip_tags(str_replace(["<br />","<br>","<br/>"],"\n", $message)) : '—'
                ]
            ], [
                'type' => 'divider',
            ], [
                'type' => 'context',
                'elements' => [
                    [
                        'type' => 'plain_text',
                        'text' => 'Regards: ArboStar Development Department'
                    ]
                ]
            ],
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://hooks.slack.com/services/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');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($msg));
    $result = curl_exec($ch);
    curl_close($ch);

    return true;
}

function get_user_id()
{
    $CI = &get_instance();
    return $CI->session->userdata('user_id');
}

function make_filename($exts)
{
    $uploadFilename = 'pic' . uniqid('', true) . '_thumb.' . $exts;
    return $uploadFilename;
}


function is_worked_time($time = null)
{
    $workingTimeFrom = (int)config_item('twilioWorkingTimeFrom') ?? 7;
    $workingTimeTo = (int)config_item('twilioWorkingTimeTo') ?? 19;

    if (!$time)
        $time = date("H:i:s");

    $day_hr = date('G', strtotime($time));
    return ($day_hr >= $workingTimeFrom && $day_hr < $workingTimeTo);
}

function is_weekend($date = null)
{
    $weekendDaysArray = json_decode(config_item('twilioWeekendDays'));
    if (!$date)
        $date = date("Y-m-d");

    $week_day = date('w', strtotime($date));
    return in_array($week_day, $weekendDaysArray);
}

function get_count_days($date = null)
{
    if (!$date)
        return null;

    $time = strtotime($date);
    $now = time();
    $result = ($now - $time) / 86400;
    if ((int)$result == 1)
        return (int)$result;

    return (int)$result;
}

function arrau_field_summ($array, $field)
{
    $summ = 0;
    foreach ($array as $key => $value) {
        $summ += $value[$field];
    }
    return $summ;
}

function mhrs_to_user($id, $date, $todate = NULL)
{
    $data = worker_mhrs_and_total($id, $date, $todate);
    return isset($data['mhrs_return']) ? $data['mhrs_return'] : 0;
}

function worker_mhrs_and_total($id, $date, $todate = NULL)
{
    $CI = &get_instance();
    $CI->load->model('mdl_workorders');

    if (!$todate)
        $where["team_date"] = strtotime($date);
    else {
        $where["team_date >="] = strtotime($date);
        $where["team_date <="] = strtotime($todate);
    }

    $where["users.id"] = $id;

    $subWhere = $where;
    unset($subWhere["users.id"]);

    $data = $CI->mdl_workorders->employees_mhr_return($subWhere, $where, TRUE);

    return $data;
}

function estimator_mhrs_and_total($id, $date, $todate = NULL)
{
    $CI = &get_instance();
    $CI->load->model('mdl_workorders');

    if (!$todate)
        $subWhere["team_date"] = strtotime($date);
    else {
        $subWhere["team_date >="] = strtotime($date) - 3600;
        $subWhere["team_date <="] = strtotime($todate . ' 23:59:59');
    }

    $where["users.id"] = $id;

    $subWhere['users.id'] = $id;
    //unset($subWhere["users.id"]);

    $data = $CI->mdl_workorders->estimators_mhr_rate($subWhere, $where, TRUE);
    //echo "<pre>";
    //echo $CI->db->last_query();die;
    return $data;
}


function demages_complain($id, $date, $todate = NULL)
{
    $CI = &get_instance();
    $CI->load->model('mdl_schedule');
    $wdata = array();
    if (!$todate) {
        $wdata['team_date >='] = strtotime($date . ' 00:00:00');
        $wdata['team_date <='] = strtotime($date . ' 23:59:59');
    } else {
        $wdata['team_date >='] = strtotime($date . ' 00:00:00');
        $wdata['team_date <='] = strtotime($todate . ' 23:59:59');
    }

    $wdata['user_id'] = $id;

    $data = $CI->mdl_schedule->sum_demage_complain($wdata);
    return $data;
}

function avg_dmg_cmp($id, $date, $todate)
{
    $CI = &get_instance();
    $CI->load->model('mdl_schedule');
    $wdata = array();

    $wdata['team_date >='] = strtotime($date . ' 00:00:00');
    $wdata['team_date <='] = strtotime($todate . ' 23:59:59');

    $wdata['user_id'] = $id;

    $data = $CI->mdl_schedule->avg_dmg_cmp($wdata);
    return $data;
}

function check_receive_email($client_id, $emails)
{
    $CI = &get_instance();
    $CI->load->model('mdl_clients');
    $emailsArray = explode(',', $emails);

    foreach($emailsArray as $key=>$email)
    {
        $client_contact = $CI->mdl_clients->get_client_contact(['cc_client_id' => $client_id, 'cc_email' => $email]);
        $result['status'] = 'ok';
        if (is_array($client_contact) && !empty($client_contact)) {
            if (strtolower($email) == strtolower($client_contact['cc_email'])) {
                if ($client_contact['cc_email_check'] === '0' && $client_contact['cc_email_manual_approve'] == 0) {
                    //$result['status'] = 'error';
                    $result['message'] = 'Invalid customer email address';
                    $result['emails'][$key] = $email;
                } elseif ($client_contact['cc_email_check'] == NULL) {
                    $check = check_email_exists($email);
                    if (!$check) {
                        $CI->mdl_clients->update_client_contact(array('cc_email_check' => 0), array('cc_client_id' => $client_id, 'cc_email' => $email));
                        //$result['status'] = 'error';
                        $result['message'] = 'Invalid customer email address';
                        $result['emails'][$key] = $email;
                    } else
                        $CI->mdl_clients->update_client_contact(array('cc_email_check' => 1), array('cc_client_id' => $client_id, 'cc_email' => $email));
                }

            } else
                $check = check_email_exists($email);
        } else
            $check = check_email_exists($email);

        if (isset($check) && !$check) {
            //$result['status'] = 'error';
            $result['message'] = 'Invalid customer email address';
            $result['emails'][$key] = $email;
        }
    }

    return $result;
}

function get_center_polygon($coords)
{
    if (!$coords || !is_array($coords))
        return FALSE;
    //var_dump($coords); die;
    $count = count($coords);//countOk
    $center[0] = 0;
    $center[1] = 0;
    foreach ($coords as $k => $v) {
        $coord = explode(',', $v);
        $center[0] += $coord[0];
        $center[1] += $coord[1];
    }
    $center[0] = $center[0] / $count;
    $center[1] = $center[1] / $count;
    return $center;
}

function getStaticGmapURLForDirection($origin, $destination, $waypoints, $size = "500x500", $jobs = [])
{
    $CI = &get_instance();
    $data = array();
    $markers = array();
    $waypoints_label_iter = 1;
    $markers[] = 'markers=icon:https://chart.apis.google.com/chart?chst=d_map_pin_icon' . urlencode('&') . 'chld=home%257CFFFFFF' . urlencode('|') . $origin;
    //$markers[] = 'markers=icon:https://chart.apis.google.com/chart?chst=d_bubble_texts_big' . urlencode('&') . 'chld=edge_bc%257CFF8%257C000%257Cedge%257C23' . urlencode('|') . $origin;

    foreach ($waypoints as $key => $waypoint) {
        //$markers[] = "markers=color:blue" . urlencode("|") . "label:" . urlencode($waypoints_labels[$waypoints_label_iter++] . '|' . $waypoint);
        $jobTime = isset($jobs[$key]) && isset($jobs[$key]['planned_service_time']) ? round($jobs[$key]['planned_service_time'], 2) : NULL;
        $estimatorName = isset($jobs[$key]) && isset($jobs[$key]['estimator']) ? '-' . $jobs[$key]['estimator'] : NULL;
        if (!$estimatorName && isset($jobs['names']) && isset($jobs['names'][$key]))
            $estimatorName = $jobs['names'][$key];
        //$markers[] = 'markers=icon:https://chart.apis.google.com/chart?chst=d_bubble_text_small' . urlencode('&') . 'chld=bbT%257C' . urlencode($waypoints_labels[$waypoints_label_iter++] . $jobTime) . '%257C96BBFC%257C000'/* . $jobTime*/ . '%7C' . $waypoint;
        //$markers[] = 'markers=icon:https://chart.apis.google.com/chart?chst=d_bubble_texts_big' . urlencode('&') . 'chld=edge_bc%257CFF8%257C000%257C' . urlencode($waypoints_labels[$waypoints_label_iter++]) . '%257C2' . $jobTime . '%7C' . $waypoint;
        //$markers[] = 'markers=icon:https://chart.apis.google.com/chart?chst=d_bubble_texts_big' . urlencode('&') . 'chld=edge_bc%257CFF8%257C000%257C' . urlencode($waypoints_labels[$waypoints_label_iter++]) . '%257C2' . $jobTime . '%7C' . $waypoint;
        if (isset($jobs['names']) && isset($jobs['names'][$key])) {
            $markers[] = 'markers=color:red%7Clabel:' . $waypoints_label_iter++ . '%7C' . $waypoint;;
        } else {
            if ($jobTime)
                $markers[] = 'markers=icon:' . base_url() . 'cron/icons/' . $waypoints_label_iter++ . $estimatorName . '/' . floatval($jobTime) . '%7C' . $waypoint;
            else
                $markers[] = 'markers=icon:' . base_url() . 'cron/icons/' . $waypoints_label_iter++ . '/' . ltrim($estimatorName, '-') . '%7C' . $waypoint;
        }
    }
    //echo "<pre>";var_dump($markers, $jobs);die;
    $markers[] = 'markers=icon:https://chart.apis.google.com/chart?chst=d_map_pin_icon' . urlencode('&') . 'chld=home%257CFFFFFF' . urlencode('|') . $destination;

    while (count($waypoints) > 8) {//countOk
        unset($waypoints[count($waypoints) - 1]);//countOk
    }

    $url = "https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$destination&avoid=tolls&waypoints=" . implode('|', $waypoints) . "&key=" . $CI->config->item('gmaps_key');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, false);
    $result = curl_exec($ch);
    curl_close($ch);

    $googleDirection = json_decode($result, true);
    $polyline = false;
    if ($googleDirection !== null && is_array($googleDirection) && !empty($googleDirection)) {
        $polyline = urlencode($googleDirection['routes'][0]['overview_polyline']['points']);
    }
    //var_dump($polyline);die;

    if (!$polyline) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, false);
        $result = curl_exec($ch);
        curl_close($ch);

        $googleDirection = json_decode($result, true);
        if ($googleDirection !== null && is_array($googleDirection) && !empty($googleDirection)) {
            $polyline = urlencode($googleDirection['routes'][0]['overview_polyline']['points']);
        }
        if (!$polyline) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, false);
            $result = curl_exec($ch);
            curl_close($ch);

            $googleDirection = json_decode($result, true);
            if ($googleDirection !== null && is_array($googleDirection) && !empty($googleDirection)) {
                $polyline = urlencode($googleDirection['routes'][0]['overview_polyline']['points']);
            }
        }
    }

    $markers = implode('&', $markers);

    $data['data'] = $googleDirection;
    $data['link'] = "https://maps.googleapis.com/maps/api/staticmap?size=$size&maptype=roadmap&path=enc:$polyline&$markers&key=" . $CI->config->item('gmaps_key');

    //var_dump($data['data'], $CI->config->item('gmaps_key'));die;
    return $data;

}

function resizeImage($imagePath, $watermark = true, $maxWidth = 1200, $maxHeight = 1200)
{
    $CI = &get_instance();
    $CI->load->library('image_lib');

    $size = getimagesize($imagePath);

    $rotation_angle = FALSE;
    if (function_exists('exif_read_data') && isset($size['mime']) && in_array($size['mime'], ['image/jpeg', 'image/jpg'])) {
        $imgdata = @exif_read_data($imagePath);
        if ($imgdata) {
            if (isset($imgdata) && isset($imgdata['Orientation']) && ($imgdata['Orientation'] == 6 || $imgdata['Orientation'] == 5))
                $rotation_angle = '270';
            if (isset($imgdata) && isset($imgdata['Orientation']) && ($imgdata['Orientation'] == 3 || $imgdata['Orientation'] == 4))
                $rotation_angle = '180';
            if (isset($imgdata) && isset($imgdata['Orientation']) && ($imgdata['Orientation'] == 8 || $imgdata['Orientation'] == 7))
                $rotation_angle = '90';
        }
    }

    $config['width'] = $maxWidth;
    $config['height'] = $maxHeight;

    if ($size[0] > $config['width'] || $size[1] > $config['height']) {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $imagePath;
        $config['maintain_ratio'] = TRUE;
        $CI->image_lib->clear();
        $CI->image_lib->initialize($config);
        $CI->image_lib->resize();
    }
    if ($rotation_angle) {
        $CI->image_lib->clear();
        $config = [
            'width' => $config['width'],
            'height' => $config['height'],
        ];
        $config['image_library'] = 'gd2';
        $config['source_image'] = $imagePath;
        $config['rotation_angle'] = $rotation_angle;
        $CI->image_lib->initialize($config);
        $CI->image_lib->rotate();
    }
    $CI->image_lib->clear();
    if ((($size[0] > ($config['width'] / 2)) || ($size[1] > ($config['height'] / 2))) && $watermark) {
        $config = [
            'width' => $config['width'],
            'height' => $config['height'],
        ];
        $config['wm_text'] = date(getDateFormat() . ' ' . getTimeFormat());
        $config['wm_font_size'] = '50';
        $config['wm_font_color'] = 'ffffff';
        $config['wm_shadow_color'] = '626262';
        $config['wm_shadow_distance'] = 1;
        $config['wm_vrt_offset'] = '10';
        $config['wm_hor_offset'] = '10';
        $config['wm_vrt_alignment'] = 'top';
        $config['wm_hor_alignment'] = 'left';
        $config['image_library'] = 'gd2';
        $config['source_image'] = $imagePath;
        $CI->image_lib->initialize($config);
        $CI->image_lib->watermark();
    }
}

function numberTo($number = FALSE)
{
    if (!$number)
        return FALSE;
    $str = config_item('phone_mask_php_regex_pattern_preview');//'($1) $2-$3';
    $extPosition = 4;
    preg_match_all('/\$\d/', $str, $matches);
    if($matches && !empty($matches) && is_array($matches) && isset($matches[0]) && is_array($matches[0]) && is_countable($matches[0])) {
        $extPosition = ((int) str_replace('$', '', $matches[0][count($matches[0]) - 1])) + 1;
    }

    $pattern = '/^' . config_item('phone_mask_php_regex_pattern') . '$/';//'/^(\d{3})(\d{3})(\d{4})(\d{0,})$/';
    if (strlen($number) > config_item('phone_clean_length')) {
        $str .= ' Ext.$' . $extPosition;
    }

    $number = preg_replace($pattern, $str, $number);

    if(config_item('phone_preview_prefix')) {
        $number = config_item('phone_country_code') . ' ' . $number;
    }

    return $number;
}

function numberFrom($number = FALSE)
{
    if (!$number)
        return;

    if(config_item('phone_preview_prefix')) {
        $number = str_replace(config_item('phone_country_code'), '', $number);
    }

    $number = preg_replace("/[^0-9]/", '', $number);
    return $number;
}

function google_time_destination($origins, $destination)
{
    $CI = &get_instance();
    if (!$origins || $origins == '')
        $origins = config_item('office_location');
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=$origins&destinations=$destination&avoid=tolls&key=" . $CI->config->item('gmaps_key');
    /*&key=AIzaSyBDUPTmUYuYnv9r8d6-TXanSefGZclfKTw */

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, false);
    $result = curl_exec($ch);
    curl_close($ch);

    $googleDirection = json_decode($result, true);

    return $googleDirection;

}

//function check_transaction($transId, $driver = false)
//{
//    if(!$driver)
//        $driver = config_item('payment_default');
//	if(!$transId)
//		return FALSE;
//	$CI = & get_instance();
//	$CI->load->driver('payment');
//    $CI->payment->setAdapter($driver);
//
//	$beanstream = new \Beanstream\Gateway(_FIRST_DATA_MERCHANT_ID, _FIRST_DATA_API_KEY, 'api', 'v1');
//	$result = FALSE;
//	try {
//		$result = $CI->payment->getTransaction($transId);
//	} catch (Exception $e) {
//
//	}
//
//	if($result)
//	{
//		if(isset($result['approved']) && $result['approved'] == 1)
//			return TRUE;
//		elseif(isset($result['approved']) && $result['approved'] == 2)
            //return FALSE;
//		else {
//			//return FALSE;
//			var_dump($result);
//		}
//	}
//	else
//		return TRUE;
//
//
//}

function format_price($sum)
{
    $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
    $curr = 'CAD';
    $sum = $formatter->parseCurrency('$' . $sum, $curr);
    return $sum;
}

function check_price($price)
{
    if (preg_match('/^[$]{0,1}[ ]{0,}([0-9,.]{1,})$/is', trim($price), $matches)) {
        if (strpos($matches[0], '$') !== FALSE)
            return 1;
        else
            return 2;
    }
    return FALSE;
}

function get_quart($date = NULL)
{
    if (!$date)
        $date = date('Y-m-d');
    $res = intval((date('n', strtotime($date)) + 2) / 3);
    if ($res == 1) {
        $result['start'] = date('Y-01-01', strtotime($date));
        $result['end'] = date('Y-03-t', strtotime($date));
        $result['quart'] = 1;
        $result['months'] = 'January, February, March';
    } elseif ($res == 2) {
        $result['start'] = date('Y-04-01', strtotime($date));
        $result['end'] = date('Y-06-t', strtotime($date));
        $result['quart'] = 2;
        $result['months'] = 'April, May, June';
    } elseif ($res == 3) {
        $result['start'] = date('Y-07-01', strtotime($date));
        $result['end'] = date('Y-09-t', strtotime($date));
        $result['quart'] = 3;
        $result['months'] = 'July, August, September';
    } elseif ($res == 4) {
        $result['start'] = date('Y-10-01', strtotime($date));
        $result['end'] = date('Y-12-t', strtotime($date));
        $result['quart'] = 4;
        $result['months'] = 'October, November, December';
    }
    return $result;
}

function mappin_svg($color, $content = NULL, $star = NULL, $contentColor = '#a0a0a0', $tree = NULL)
{
    $marker_style = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="52" viewBox="0 0 38 38"><path fill="' . $color . '" stroke="#000" stroke-width="2" d="M34.305 16.234c0 8.83-15.148 19.158-15.148 19.158S3.507 25.065 3.507 16.1c0-8.505 6.894-14.304 15.4-14.304 8.504 0 15.398 5.933 15.398 14.438z"/>';
    if ($star)
        $marker_style .= '<text transform="translate(16 18.5)" stroke="#000" stroke-width="1" fill="#fff378" x="10" y="-10" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="30" text-anchor="middle">&#9733;</text>';
    if ($tree)
        $marker_style .= '<text transform="translate(16 18.5)" stroke="#000" stroke-width="0.5" fill="#176d3e" x="2" y="30" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="25" text-anchor="middle">&#127876;</text>';
    if ($content !== NULL) {
        if ($content == 'phone')
            $marker_style .= '<text transform="translate(19 25)" fill="' . $contentColor . '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="20" text-anchor="middle">&#9743;</text>';
        elseif ($content == 'schedule')
            $marker_style .= '<text transform="translate(19 25)" fill="' . $contentColor . '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="25" text-anchor="middle">&#128197;</text>';
        elseif ($content == '&#9899;')
            $marker_style .= '<text transform="translate(19 25)" fill="' . $contentColor . '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="12" text-anchor="middle">' . $content . '</text>';
        else
            $marker_style .= '<text transform="translate(19 22)" fill="' . $contentColor . '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="20" text-anchor="middle">' . $content . '</text>';
    }
    $marker_style .= '</svg>';
    $text = 'data:image/svg+xml;base64,' . base64_encode($marker_style);
    return $text;
}

function task_pin($color, $content = NULL, $star = NULL, $contentColor = '#a0a0a0', $fontsize = 170, $height = 30, $width = 30)
{
    $marker_style = '<svg xmlns="http://www.w3.org/2000/svg" height="' . $height . '" width="' . $width . '" viewBox="0 0 480 480"><path fill="' . $color . '" stroke="#000000" stroke-width="5" fill-rule="nonzero" marker-start="" marker-mid="" marker-end="" id="svg_16" d="M11.186155649009088,476.0885041676267 L103.15873182946643,311.96152697217735 L103.15873182946643,311.96152697217735 C5.7211678769939365,262.4517019249397 -22.082518426899092,169.18577638907354 38.9245432416211,96.49309300509664 C99.93046014410076,23.800830456576065 229.78398858465704,-4.52719321136221 338.9146322808233,31.04856760666257 C448.04524461353026,66.62412580024365 500.74136732913627,154.45988688920258 460.65260040526954,233.97186576659885 C420.5659505145268,313.4838134709767 300.8158765142848,358.6497282889444 184.00005749974966,338.315473910676 L11.186155649009088,476.0885041676267 z" style="color: rgb(0, 0, 0);" class=""/>';
    if ($star)
        $marker_style .= '<text transform="translate(16 18.5)" stroke="#000" stroke-width="1" fill="#fff378" x="10" y="-10" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="30" text-anchor="middle">&#9733;</text>';
    $marker_style .= '<text xmlns="http://www.w3.org/2000/svg" transform="translate(240 230)" fill="#' . $contentColor . '" style="font-family: Arial, sans-serif;font-weight:bold;text-align:center;" font-size="' . $fontsize . '" text-anchor="middle">' . $content . '</text>';
    $marker_style .= '</svg>';
    $text = 'data:image/svg+xml;base64,' . base64_encode($marker_style);
    return $text;

}

// TODO: deprecated, remove?
function method_sms_send($number, $text)
{
    $CI = &get_instance();
    $client = new Client($CI->config->item('accountSid'), $CI->config->item('authToken'));


    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    /*$swissNumberProto = $phoneUtil->parse($number, "CA");

    $isValid = $phoneUtil->isValidNumber($swissNumberProto);*/
    $isValid = false;
    try {
        $swissNumberProto = $phoneUtil->parse($number, 'CA');
        $isValid = $phoneUtil->isValidNumber($swissNumberProto);
    } catch (\libphonenumber\NumberParseException $e) {
    }
    if (!$isValid)
        return false;
    $to = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);

    $sms = $text;
    try {
        $client->messages->create($to, array(
            'from' => $CI->config->item('myNumber'),
            'body' => $sms,
            'messagingServiceSid' => $CI->config->item('messagingServiceSid')
        ));
    } catch (Exception $e) {
    }
}

function getDates($startTime, $endTime)
{
    $day = 86400;
    $format = 'Y-m-d';
    $startTime = strtotime(str_replace('/','-',$startTime));
    $endTime   = strtotime(str_replace('/','-',$endTime));

    if ($startTime > $endTime) {
        $endsave = $endTime;
        $endTime = $startTime;
        $startTime = $endsave;
    }


    $numDays = round(($endTime - $startTime) / $day);

    $days = array(date($format, $startTime));

    for ($i = 1; $i < $numDays; $i++) {
        $days[] = date($format, ($startTime + ($i * $day)));
    }

    $days[] = date($format, $endTime);

    return array_unique($days);
}

function pushJob($class, $arg = NULL, $availableFrom = null, $delay = 0, $storeJobIdToEntity = null)
{
    $CI =& get_instance();
    $CI->load->driver('Jobs');
    return $CI->jobs->pushJob($class, $arg, $availableFrom, $delay, $storeJobIdToEntity);
}

/**
 * Delete job
 *
 * @param int|null $jobId
 * @return bool
 */
function deleteJob($jobId = null) {
    if ($jobId) {
        $CI =& get_instance();
        $CI->load->model('mdl_jobs');

        return $CI->mdl_jobs->delete($jobId);
    }

    return false;
}

function calculate_percentage($all, $percentage)
{
    return $all ? round($percentage * 100 / $all, 2) : 0;
}

function return_kilobytes($size_str)
{
    switch (substr($size_str, -1)) {
        case 'M':
        case 'm':
            return (int)$size_str * 1024;
        case 'K':
        case 'k':
            return (int)$size_str;
        case 'G':
        case 'g':
            return (int)$size_str * 1048576;
        default:
            return $size_str / 1024;
    }
}

function ajax_response($status, $data = [])
{
    echo json_encode(['status' => $status, 'data' => $data]);
    exit;
}


/*****DEPRECATED********/
function is_lock_arbostar()
{
    /*$file = '/tmp/lock_arbostar';
    return file_exists($file);*/
    return false;
}

function lock_arbostar()
{

    if (is_lock_arbostar()) {
        $CI =& get_instance();
        echo $CI->load->view("includes/header", ['title' => 'Update Arbostar'], true);
        echo "<h1 class='text-center'>Arbostar Update. We will be back in a minute!</h1>";
        echo "<script>setTimeout(function(){ document.location.reload(); }, 60000);</script>";
        echo $CI->load->view("includes/footer", [], true);
        die;
    }


    /*$file = '/tmp/lock_arbostar';
    file_put_contents($file, "");*/

    return true;
}

function unlock_arbostar()
{
    $file = '/tmp/lock_arbostar';
    @unlink($file);
    return true;
}

/*****\DEPRECATED********/

/**
 * Check if specific driver allows to send an email
 * @param  string $email
 * @param  null $driverName
 * @return mixed
 */
function checkIfEmailIsAllowed($email, $driverName = null)
{
    $CI =& get_instance();
    $CI->load->library('email');
    $CI->config->load('email');

    if (!$driverName) $driverName = config_item('default_mail_driver');

    $CI->load->library('MailDriver/' . ucfirst($driverName));

    return $CI->{$driverName}->checkIfVerifiedEmail($email);
}

function sort_members($members)
{
    if (!count($members))
        return [];

    $weekTeamLeaders = [];
    $weekTeamEmp = [];
    foreach ($members as $key => $value) {
        if ($value->team_leader_user_id == $value->user_id)
            $weekTeamLeaders[] = $value;
        else
            $weekTeamEmp[] = $value;
    }
    return array_merge($weekTeamLeaders, $weekTeamEmp);
}

function getAmount($money)
{
    $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
    $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

    $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

    $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
    $removedThousandSeparator = preg_replace('/(,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

    return round(str_replace(',', '.', $removedThousandSeparator), 2);
}

function money($num, $cents = true)
{
    //($product->cost, 2, '.', ',')
    $CI = &get_instance();
    $CI->load->library('parser');

    $currency_symbol = get_currency();
    $template = (config_item('currency_symbol_position')) ? config_item('currency_symbol_position') : '{currency} {amount}';

    if($cents)
        $amount = number_format((float)$num, 2, '.', ',');
    else
        $amount = number_format((float)$num, 0, '.', ',');
    $data = [
        'currency' => $currency_symbol,
        'amount' => $amount
    ];

    return $CI->parser->parse_string($template, $data, true);
}

function get_currency()
{
    return (config_item('currency_symbol')) ? config_item('currency_symbol') : '$';
}

function service_select_option($service)
{
    return ['key' => $service->service_id, 'name' => $service->service_name];
}

function getCategories($categories, $isChildren = false, $cnt = 0)
{
    if ($isChildren == true) {
        $cnt++;
        if ($cnt == 3) {
            $cnt = 0;
            return [];
        }
    }
    foreach ($categories as $category) {
        $result[] = [
            'id' => $category['category_id'],
            'text' => $category['category_name'],
            'children' => getCategories($category['categories'], true, $cnt)
        ];
    }
//    if (!empty($result) && $isChildren === false)
//        array_unshift($result, ['id' => 0, 'text' => ' ']);

    return !empty($result) ? $result : [];
}

function getClasses($classes, $isChildren = false)
{
    foreach ($classes as $class) {
        $result[] = [
            'id' => $class['class_id'],
            'text' => (!empty($class['class_active']) || strpos($class['class_name'], '(deleted)')) ? $class['class_name'] : $class['class_name'] . ' (deleted)',
            'parent' => !empty($class['class_parent_id']) ? $class['class_parent_id'] : 0,
            'active' => $class['class_active'],
            'children' => getClasses(isset($class['classes']) ? $class['classes'] : $class['classes_without_inactive'], true)
        ];
    }
    if (!empty($result) && $isChildren === false)
        array_unshift($result, ['id' => 0, 'text' => ' ']);

    return !empty($result) ? $result : [];
}

function deleteEmptyChildrenFromArray(array &$array)
{
    foreach ($array as $key => $arr){
        if(empty($array[$key]['children']))
            unset($array[$key]['children']);
        else
            deleteEmptyChildrenFromArray($array[$key]['children']);
    }
}

function getCategoriesItemsForSelect2() {
    $CI = &get_instance();
    $CI->load->model('mdl_services');
    $CI->load->model('mdl_services');
    $CI->load->model('mdl_services');
    $CI->load->library('Common/EstimateActions');
    $response = [
        "services" => "[]",
        "bundles" => "[]",
        "products" => "[]"
    ];
    $bundles = $CI->mdl_services->find_all(array('service_status' => 1, 'is_bundle' => 1), 'service_priority');

    if(!empty($bundles)){
        foreach ($bundles as $bundle){
            $result = $CI->mdl_services->get_records_included_in_bundle($bundle->service_id);
            if($result){
                foreach ($result as $record)
                    $record->non_taxable = 0;
            }
            $bundle->bundle_records = json_encode($result, true);
            $bundle->id = $bundle->service_id;
            $bundle->text = $bundle->service_name;
        }
        $response['bundles'] = json_encode($bundles);
    }
    $categoryWithProducts = Category::whereNull('category_parent_id')->with(['categoriesWithProducts', 'products'])->get()->toArray();
    $categoryWithServices = Category::whereNull('category_parent_id')->with(['categoriesWithServices', 'services'])->get()->toArray();

    if(!empty($categoryWithProducts)){
        $response['products'] =  json_encode($CI->estimateactions->getCategoryWithItemsForSelect2($categoryWithProducts));
    }
    if(!empty($categoryWithServices)){
        $response['services'] =  json_encode($CI->estimateactions->getCategoryWithItemsForSelect2($categoryWithServices));
    }
    return json_encode($response);
}

function setFavouriteShortcut(array $items){
    if(!empty($items)) {
        foreach ($items as $item) {
            $arrayName = explode(' ', preg_replace('/[^A-Za-z0-9\-]/', '', trim($item->service_name)));
            if (count($arrayName) == 1 || $arrayName[1] == '') {
                $favouriteShortcut = substr($item->service_name, 0, 2);
            } else {
                $favouriteShortcut = $arrayName[0][0] . $arrayName[1][0];
            }
            $item->favouriteShortcut = strtoupper($favouriteShortcut);
        }
    }
    return $items;
}

function add_preffix($array, $prefix){
    return array_map(function($value) use ($prefix) { return (strpos($value, $prefix)===false)?$prefix.$value:$value; }, $array);
}

function getQbInvoiceLinkForEmail(string $templateText, object $invoice){
    $replacement = '';
    if(!empty($invoice->invoice_qb_link)) {
        $result = preg_match('/\[QB_CCLINK(.*?)\]/', $templateText, $found);
        if ($result && !empty($found[1])) {
            $qbInvoiceLink = $found[1];
            if ($qbInvoiceLink[0] == '|')
                $qbInvoiceLink = substr($qbInvoiceLink, 1);
            $qbInvoiceLinkArray = explode("|", $qbInvoiceLink);
            $linkName = 'link';
            if(!empty(trim($qbInvoiceLinkArray[0]))) {
                $linkName = $qbInvoiceLinkArray[0];
            }
            $replacement = '<a href="'.$invoice->invoice_qb_link.'" target="_blank">'. $linkName .'</a>';
            if(!empty($qbInvoiceLinkArray[1]))
                $replacement = $qbInvoiceLinkArray[1] . ' ' . $replacement;
            if(!empty($qbInvoiceLinkArray[2]))
                $replacement .= ' ' . $qbInvoiceLinkArray[2];
        } elseif(strrpos($templateText, '[QB_CCLINK]')) {
            $replacement = '<a href="'.$invoice->invoice_qb_link.'" target="_blank">link</a>';
        }
    }
    return $replacement;
}
function decorate_text($string = null, $nl2br = false) {

    if(!$string) {
        return false;
    }

    $string = str_replace(['<', '>'], ['&lt;', '&gt;'], $string);

    if($nl2br) {
        $string = nl2br($string);
    }

    $pattern = '/(\bhttps?:\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig';
    if (filter_var($string, FILTER_VALIDATE_URL)) {
        if(strlen($string) >= 80){
            echo `<a class='link' target='_blank' href="` . $string . `">` . substr($string, 0, 50) . "..." . substr($string, -5) . `</a>`;
        }
        else{
            echo `<a class="link" target="_blank" href="` . $string . `">` . $string . `</a>`;
        }
    }
    else{
        $string = preg_replace("/(?:\*)([^*]+)(?:\*)/is",  "<b>$1</b>", $string);
        $string = preg_replace("/(?:_)([^_]+)(?:_)/is",  "<u>$1</u>", $string);
        $string = preg_replace("/(?:~)([^~]+)(?:~)/is",  "<i>$1</i>", $string);
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML(mb_convert_encoding($string, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NODEFDTD);

    $string = trim(str_replace(['<html>', '<body>', '</body>', '</html>'], '', $dom->saveHTML()));
    return $string;
}

/**
 * make a series of digits into a properly formatted US phone number
 *
 * @param $number
 * @return mixed|string
 */
function format_phone($number)
{
    $no = preg_replace('/[^0-9+]/', '', $number);

    if(strlen($no) == 11 && substr($no, 0, 1) == "1")
        $no = substr($no, 1);
    elseif(strlen($no) == 12 && substr($no, 0, 2) == "+1")
        $no = substr($no, 2);

    if(strlen($no) == 10)
        return "(".substr($no, 0, 3).") ".substr($no, 3, 3)."-".substr($no, 6);
    elseif(strlen($no) == 7)
        return substr($no, 0, 3)."-".substr($no, 3);
    else
        return $no;

}

if (!function_exists('clean_digits')) {
    /**
     * Cleans Hash and Star characters from returned Digits
     * If digits only contains Hash or Star characters then
     * the raw digits param is returned
     *
     * @param string $digits
     * @return string
     */
    function clean_digits($digits) {
        $trimmed = str_replace(array('#', '*'), '',  $digits);
        return strlen($trimmed) > 0 ? $trimmed : $digits;
    }
}

/**
 * @param $phone
 * @return mixed|string
 */
function normalize_phone_to_E164($phone) {

    // get rid of any non (digit, + character)
    $phone = preg_replace('/[^0-9+]/', '', $phone);

    // validate intl 10
    if(preg_match('/^\+([2-9][0-9]{9})$/', $phone, $matches)){
        return "+{$matches[1]}";
    }

    // validate US DID
    if(preg_match('/^\+?1?([2-9][0-9]{9})$/', $phone, $matches)){
        return "+1{$matches[1]}";
    }

    // validate INTL DID
    if(preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches)){
        return "+{$matches[1]}";
    }

    // premium US DID
    if(preg_match('/^\+?1?([2-9]11)$/', $phone, $matches)){
        return "+1{$matches[1]}";
    }

    return $phone;
}
