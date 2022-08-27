<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;
use application\modules\settings\integrations\twilio\libraries\DialList;

include_once('TwimlDialRecord.php');
define('DIAL_COOKIE', 'state-' . AppletInstance::getInstanceId());

$transcribe = false;
$voice = 'woman';
$language = 'en';
$timeout = $dial_timeout = AppletInstance::getValue('dial_timeout') ?? 10;

switch (AppletInstance::getValue('recording-enable', 'no')) {
    case 'yes':
    case 'true':
    case 'record-from-answer':
        $record = 'record-from-answer';
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

$dialer = new TwimlDialRecord([
    'transcribe' => $transcribe,
    'voice' => $voice,
    'language' => $language,
    'sequential' => true,
    'timeout' => $timeout,
    'record' => $record
]);

$dialer->set_state();
if (AppletInstance::getValue('say-before-dial-action', 'no') === 'on' && $dialer->state === 'new') {
    $say_or_play = AppletInstance::getAudioSpeechPickerValue('say-before-dial');
    if (!AudioSpeechPickerWidget::setVerbForValue($say_or_play, $dialer->response)) {
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
    switch ($dialer->state) {
        case 'voicemail':
            $dialer->noanswer();
            break;
        case 'hangup':
            if ($record !== false) {
                $dialer->add_voice_message();
            } else {
                $dialer->hangup();
            }
            break;
        case 'recording':
            $dialer->add_voice_message();
            break;
        default:
            if ($dialer->dial_whom_selector === 'user-or-group') {
                // create a dial list from the input state
                $dialed = false;
                $dial_list = DialList::get($dialer->dial_whom_user_or_group);
                while (count($dial_list->users[0]->twilioVoiceDevices)) {
                    $to_dial = $dial_list->next();
                    if ($to_dial instanceof \stdClass) {
                        $dialed = $dialer->dial($to_dial);
                    }
                }

                if (!isset($dialed) || !$dialed) {
                    // nobody to call, push directly to voicemail
                    $dialer->noanswer();
                }
            } elseif ($dialer->dial_whom_selector === 'number') {
                $dialer->dial($dialer->dial_whom_number);
            }
            break;
    }
} catch (Exception $e) {
    error_log('Dial Applet exception: ' . $e->getMessage());
    $dialer->response->say("We're sorry, an error occurred while dialing. Goodbye.");
    $dialer->hangup();
}

$dialer->save_state();
$dialer->respond();
