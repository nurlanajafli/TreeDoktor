<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;

$response = new Response();

$url = AppletInstance::getBaseURI();
$response->addRedirect($url.'/start');
$response->Respond();
?>