<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;

$ci = &get_instance();
$twilio = new BaseTwilio();

$workflowSid = AppletInstance::getValue("workflow_sid");

$callerId = AppletInstance::getValue('callerId', null);

if (empty($callerId) && !empty($ci->input->post('From'))) {
    $callerId = $ci->input->post('From');
}

$taskAttributes = [
    'clientId' => $callerId,
    'skills' => ['support']
];


include_once('TwimlQueueIn.php');
define('DIAL_COOKIE', 'state-' . AppletInstance::getInstanceId());

$transcribe = false;//(bool) $CI->vbx_settings->get('transcriptions', $CI->tenant->id);
$voice = 'woman';//$CI->vbx_settings->get('voice', $CI->tenant->id);
$language = 'en';//$CI->vbx_settings->get('voice_language', $CI->tenant->id);
$timeout = 20;//$CI->vbx_settings->get('dial_timeout', $CI->tenant->id);

switch (AppletInstance::getValue('recording-enable', 'yes')) {
    case 'yes':
    case 'true':
    case 'record-from-answer':
        $record = true;
        break;
    case 'record-from-ringing':
        $record = 'record-from-ringing';
        break;
    case 'no':
    case '':
    case null:
    default:
        $record = false;
}

$dialer = new TwimlQueueIn([
    'transcribe' => $transcribe,
    'voice' => $voice,
    'language' => $language,
    'sequential' => true,
    'timeout' => $timeout,
    'record' => $record
]);

$dialer->set_state();
if (AppletInstance::getValue('play_before_task_action', 'no') === 'on' && $dialer->state === 'new') {
    $play = AppletInstance::getAudioSpeechPickerValue('play_before_task');
    if (!AudioSpeechPickerWidget::setVerbForValue($play, $dialer->response)) {
        $dialer->response->say('Please wait while we connect your call.', [
            'voice' => $voice,
            'language' => $language
        ]);
    }
}

/**
 * Respond based on state
 *
 * **NOTE** dialing is done purely on a sequential basis for now.
 * Due to a limitation in Twilio Client we cannot do simulring.
 * If ANY device picks up a call Client stops ringing.
 *
 * The flow is as follows:
 * - Single User: Sequentially dial devices. If user is online
 *   then the first device will be Client.
 * - Group: Sequentially dial each user's 1st device. If user
 *   is online Client will be the first device.
 * - Number: The number will be dialed.
 */
try {
    $dialer->response->enqueue([
        'Task' => json_encode($taskAttributes),
        'workflowSid' => $workflowSid,
        'waitUrl' => base_url("client_twilio_calls/play?instance=" . AppletInstance::getInstanceId() . "&flow_id=" . AppletInstance::getFlow()->id)
    ]);
} catch (Exception $e) {
    error_log('Dial Applet exception: ' . $e->getMessage());
    $dialer->response->say("We're sorry, an error occurred while dialing. Goodbye.");
    $dialer->hangup();
}

$dialer->save_state();
$dialer->respond();


