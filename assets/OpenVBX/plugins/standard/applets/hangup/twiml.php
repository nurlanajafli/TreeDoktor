<?php

use application\modules\settings\integrations\twilio\libraries\TwimlResponse;

$response = new TwimlResponse;
$response->hangup();
$response->respond();