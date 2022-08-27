<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;

$response = new TwimlResponse;

$next = AppletInstance::getDropZoneUrl('next');
$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');

if (!empty($prompt)) {
    AudioSpeechPickerWidget::setVerbForValue($prompt, $response);
}

if (!empty($next)) {
    $response->redirect($next);
}

$response->respond();