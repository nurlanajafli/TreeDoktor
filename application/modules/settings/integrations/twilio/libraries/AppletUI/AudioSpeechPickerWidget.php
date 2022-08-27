<?php

namespace application\modules\settings\integrations\twilio\libraries\AppletUI;


use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use application\modules\settings\integrations\twilio\libraries\AppletUI\Exceptions\VBX_IncomingNumberException;
use application\modules\settings\integrations\twilio\libraries\Services\Services_Twilio_Twiml;
use application\modules\settings\models\integrations\twilio\SoftTwiliosoftAudioFiles;
use Play;
use Say;

/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.
 *  The Original Code is OpenVBX, released June 15, 2010.
 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.
 * Contributor(s):
 **/

/**
 * Class AudioSpeechPickerWidget
 * @property $load
 */
class AudioSpeechPickerWidget extends AppletUIWidget
{
    protected $template = 'AudioSpeechPicker';

    protected $name;
    protected $mode;
    protected $say_value;
    protected $play_value;
    protected $tag;
    protected $isText;
    protected $isUpload;
    protected $isLibrary;

    /**
     * AudioSpeechPickerWidget constructor.
     * @param $name
     * @param null $mode
     * @param null $say_value
     * @param null $play_value
     * @param string $tag
     * @param bool $isText
     * @param bool $isUpload
     * @param bool $isLibrary
     */
    public function __construct(
        $name,
        $mode = null,
        $say_value = null,
        $play_value = null,
        $tag = 'global',
        $isText = true,
        $isUpload = true,
        $isLibrary = true
    ) {
        $this->isText = $isText;
        $this->isUpload = $isUpload;
        $this->isLibrary = $isLibrary;
        $this->name = $name;
        $this->mode = $mode;
        $this->say_value = $say_value;
        $this->play_value = $play_value;
        $this->tag = $tag;

        parent::__construct($this->template);
    }

    /**
     * @param array $data
     * @return false|string
     * @throws Exceptions\AppletUIWidgetException
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function render($data = array())
    {
        $hasValue = empty($this->mode) ? false : true;

        $soft_twilio_audio_files = SoftTwiliosoftAudioFiles::whereNotNull('url')
            ->where('tag', '=', $this->tag)
            ->orderBy('created_at', 'DESC')->get();

        foreach ($soft_twilio_audio_files as $i => $result) {
            $results[$i] = $result;
        }

        // Pre-fill the record text field with the the first device phone number we
        // find for the current user that is active.
        $ci = &get_instance();

        $user_phone = '';
        // set the caller id for recording via the phone
        $caller_id = '';

        $myNumber = (new AccountTwilio())->myNumber;
        $caller_id = normalize_phone_to_E164($myNumber);
        $user_phone = normalize_phone_to_E164($myNumber);

        $data = array_merge([
            'isText' => $this->isText,
            'isUpload' => $this->isUpload,
            'isLibrary' => $this->isLibrary,
            'name' => $this->name,
            'hasValue' => $hasValue,
            'mode' => $this->mode,
            'say' => $this->say_value,
            'play' => $this->play_value,
            'tag' => $this->tag,
            'library' => $results ?? [],
            'first_device_phone_number' => $user_phone ?? '',
            'caller_id' => $caller_id ?? ''
        ], $data);
        return parent::render($data);
    }

    /**
     * Set the proper verb for the pickers value
     *
     * @param string $value
     * @param object $response Services_Twilio_Twiml
     * @return mixed Services_Twilio_Twiml on success, boolean false on fail
     * @example
     *        $response = new Services_Twilio_Twiml;
     *        AudioSpeechPickerWidget::setVerbForValue($value, $response);
     *
     */
    public static function setVerbForValue($value, $response)
    {
        $matches = array();
        if (empty($value) || !($response instanceof Services_Twilio_Twiml)) {
            return false;
        } else {
            if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $value, $matches)) {
                // This is a locally hosted file, and we need to return the correct absolute URL for the file.
                return $response->play(base_url('audio-uploads/' . $matches[1]));
            } else {
                if (preg_match('/^http(s)?:\/\/(.*)/i', $value)) {
                    // it's already an absolute URL
                    return $response->play($value);
                } else {
                    $ci =& get_instance();
                    return $response->say($value, [
                        'voice' => 'man', //$ci->vbx_settings->get('voice', $ci->tenant->id),
                        'language' => 'en',//$ci->vbx_settings->get('voice_language', $ci->tenant->id)
                    ]);
                }
            }
        }
    }

    /**
     * Create the proper verb for the Picker's value
     *
     * @param mixed $value
     * @param object $defaultVerb
     * @return object subclass of Verb
     * @deprecated use AudioSpeechPickerWidget::setVerbForValue instead
     */
    public static function getVerbForValue($value, $defaultVerb)
    {
        _deprecated_notice(__METHOD__, '1.0.4', 'AudioSpeechPickerWidget::setVerbForValue');
        $matches = array();

        if (empty($value)) {
            return $defaultVerb;
        } else {
            if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $value, $matches)) {
                // This is a locally hosted file, and we need to return the correct
                // absolute URL for the file.
                return new Play(base_url("audio-uploads/" . $matches[1]));
            } else {
                if (preg_match('/^http(s)?:\/\/(.*)/i', $value)) {
                    // it's already an absolute URL
                    return new Play($value);
                } else {
                    return new Say($value);
                }
            }
        }
    }
}
