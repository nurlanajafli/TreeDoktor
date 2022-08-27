<?php

use application\modules\messaging\models\Messages;
use application\modules\messaging\models\SmsCounter;

function message($type, $message)
{

	switch ($type) {
		case "success":
			$message_top = '<div id="showMsg" class="col-md-10 m-bottom_10 m-t-sm" style="position: fixed;top: 50px; z-index: 100; display:none; opacity: 0.8;">';
			$message_top .= '<div class="alert alert-success shadow overflow">';
			$message_top .= '<button type="button" class="close" data-dismiss="alert">×</button>';

			$message_bottom = '</div></div>';
			break;

		case "alert":
			$message_top = '<div id="showMsg" class="col-md-10 m-bottom_10 m-t-sm" style="position: fixed;top: 50px; z-index: 100; display:none; opacity: 0.8;">';
			$message_top .= '<div class="alert alert-danger shadow overflow">';
			$message_top .= '<button type="button" class="close" data-dismiss="alert">×</button>';

			$message_bottom = '</div></div>';
			break;

	};

	return $message_top . $message . $message_bottom;
}

function get_chat_users()
{
	$CI =& get_instance();
	$CI->load->model('mdl_user');

	return $CI->mdl_user->get_chat_userlist();
}

/**
 * Show sms user limit info block
 *
 * @param null $type
 * @return string
 */
function show_sms_user_limit($type = null): string
{
    $sms_unlimited = !!config_item('sms_unlimited');

    if ($sms_unlimited) {
        return '';
    }

    $smsCounts = SmsCounter::getCurrentCountRemain();

    $mobile = $type === 'mobile' ? ' hidden-md hidden-lg' : '';

    if (isset($smsCounts->count) && isset($smsCounts->remain) && $smsCounts->count > 0 && $smsCounts->remain > 0) {
        $bg = 'bg-info';
        $text = $smsCounts['remain'] . '&nbsp;/&nbsp;' . $smsCounts['count'];
    } else {
        $bg = 'bg-danger';
        $text = 'You&nbsp;can\'t&nbsp;send&nbsp;SMS. Please&nbsp;increase&nbsp;limit&nbsp;of SMS&nbsp;messages';
    }

    return '<span class="badge badge-sm ' . $bg . ' messenger-limit-info' . $mobile . '" 
        data-toggle="tooltip" data-placement="bottom" data-html="true" title="' . $text . '">i</span>';
}

/**
 * @param $numSegments
 * @param bool $error
 * @param bool $addToLimit
 * @return void
 */
function check_update_sms_limits($numSegments, bool $error = false, bool $addToLimit = false) {
    $sms_unlimited = !!config_item('sms_unlimited');

    if (!$sms_unlimited && !$error && $numSegments) {
        SmsCounter::updateCurrentRemain($numSegments, $addToLimit);
    }
}

/**
 * Get user SMS limit
 *
 * @return array
 */
function get_user_sms_limit(): array
{
    $remain = 0;
    $sms_limit = 0;
    $sms_unlimited = !!config_item('sms_unlimited');

    if (!$sms_unlimited) {
        $smsCounts = SmsCounter::getCurrentCountRemain();

        if (isset($smsCounts->count) && isset($smsCounts->remain) && $smsCounts->count > 0 && $smsCounts->remain > 0) {
            $sms_limit = $smsCounts->count;
            $remain = $smsCounts->remain;
        }
    }

    return [
        'remain' => $remain,
        'limit' => $sms_limit
    ];
}

/**
 * Get count unreaded SMS
 *
 * @return int
 */
function get_count_unreaded_sms(): int
{
    return Messages::where('sms_readed', 0)->count('sms_number');
}

/**
 * Count message segments
 *  https://www.twilio.com/docs/glossary/what-sms-character-limit
 *
 * @param string|null $body
 * @return int
 */
function count_sms_segments(string $body = null): int
{
    $segments = 1;

    if (!$body) {
        return 0;
    }

    $baseCharsLength = 160;
    $segmentCharsLength = 152;
    $charsLength = iconv_strlen($body);
    $bytesLength = strlen($body);

    if ($charsLength !== $bytesLength) {
        $baseCharsLength = 70;
        $segmentCharsLength = 66;
    }

    if ($charsLength > $baseCharsLength) {
        $segments = ceil($charsLength / $segmentCharsLength);
    }

    return $segments;
}

/**
 * Send Free SMS subscription update notification
 *
 * @param array $data = [
 *     'subscription' => (array),
 *     'order' => ([array]),
 *     'beforeUpdate' => ([array]),
 *     'createdSubscription' => ([bool])
 * ]
 */
function free_subscription_notification(array $data) {
    $CI =& get_instance();

    $company_name = config_item('company_name_short') ?? ucfirst(config_item('company_dir'));
    $subject = 'Free SMS subscription update notification from "' . $company_name . '"';
    $toEmail = config_item('arbostar_email') ?? 'info@arbostar.com';
    $from_email = config_item('account_email_address');

    $data['company'] = $company_name;

    $message = $CI->load->view('billing/tmpl/free_subscription_update_tmpl', $data, true);

    pushJob('common/sendemail', [
        'subject' => $subject,
        'message' => $message,
        'from' => $from_email,
        'from_name' => $company_name,
        'to' => $toEmail
    ]);
}
