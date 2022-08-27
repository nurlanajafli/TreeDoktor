<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;
use application\modules\settings\integrations\twilio\libraries\OpenVBX;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;
use application\modules\user\models\User;

$CI =& get_instance();
$transcribe = true; //(bool) $CI->vbx_settings->get('transcriptions', $CI->tenant->id);

$response = new TwimlResponse; // start a new Twiml response
// if we've got a transcription
if (!empty($_REQUEST['RecordingUrl'])) {
    // add a voice message
    OpenVBX::addVoiceMessage(AppletInstance::getUserGroupPickerValue('permissions'));
    $response->hangup();
} else {
    $permissions = AppletInstance::getUserGroupPickerValue('permissions'); // get the prompt that the user configured
    $isUser = $permissions instanceOf User ? true : false;

    if ($isUser) {
        $prompt = $permissions->voicemail;
    } else {
        $prompt = AppletInstance::getAudioSpeechPickerValue('prompt');
    }

    if (!AudioSpeechPickerWidget::setVerbForValue($prompt, $response)) {
        // fallback to default voicemail message
        $response->say('Please leave a message. Press the pound key when you are finished.', [
            'voice' => 'woman',
            'language' => 'en'
        ]);
    }

    // add a <Record>, and use VBX's default transcription handler
    $record_params = ['transcribe' => 'false'];
    if ($transcribe) {
        $record_params['transcribe'] = 'true';
        $record_params['transcribeCallback'] = base_url('client_twilio_calls/transcribe');
    }

    $response->record($record_params);
}

$response->respond(); // send response
