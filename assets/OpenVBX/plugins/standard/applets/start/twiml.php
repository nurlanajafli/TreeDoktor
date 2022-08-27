<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;

$response = new TwimlResponse;

$next = AppletInstance::getDropZoneUrl('next');

if (!empty($next)) {
    $response->redirect($next);
}

$response->respond();

