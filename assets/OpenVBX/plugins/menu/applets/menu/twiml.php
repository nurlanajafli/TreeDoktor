<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;

$ci = &get_instance();
$response = new TwimlResponse;
$request = request();
/* Fetch all the data to operate the menu */
$digits = $request->input('Digits');
$prompt = AppletInstance::getAudioSpeechPickerValue('prompt');
$invalid_option = AppletInstance::getAudioSpeechPickerValue('invalid-option');
$repeat_count = AppletInstance::getValue('repeat-count', 3);
$next = AppletInstance::getDropZoneUrl('next');
$selected_item = false;

/* Build Menu Items */
$choices = (array) AppletInstance::getDropZoneUrl('choices[]');
$keys = (array) AppletInstance::getDropZoneValue('keys[]');
$menu_items = AppletInstance::assocKeyValueCombine($keys, $choices);

$numDigits = 1;
foreach($keys as $key)
{
	if(strlen($key) > $numDigits)
	{
		$numDigits = strlen($key);
	}
}

if(!is_null($digits))
{
	if(!empty($menu_items[$digits]))
	{
		$selected_item = $menu_items[$digits];
	}
	else
	{
		if($invalid_option)
		{
			AudioSpeechPickerWidget::setVerbForValue($invalid_option, $response);
			$response->redirect();
		}
		else
		{
			$response->say('You selected an incorrect option.', array(
					'voice' => 'man',
					'language' => 'en'
				));
			$response->redirect();
		}

		$response->respond();
		exit;
	}

}

if(!empty($selected_item))
{
	$response->redirect($selected_item);
	$response->respond();
	exit;
}
$timeout = AppletInstance::getValue('timeout', 5);
$gather = $response->gather([
    'numDigits' => $numDigits,
    'timeout' => empty($timeout) ? 5 : $timeout
]);
AudioSpeechPickerWidget::setVerbForValue($prompt, $gather);

// Infinite loop
if($repeat_count == -1)
{
	$response->redirect();
	// Specified repeat count
}
else
{
	for($i=1; $i < $repeat_count; $i++)
	{
		$gather->pause(['length' => 5]);
		AudioSpeechPickerWidget::setVerbForValue($prompt, $gather);
	}
}

if(!empty($next))
{
	$response->redirect($next);
}

$response->respond();
