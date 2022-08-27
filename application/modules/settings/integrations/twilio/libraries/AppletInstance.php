<?php

namespace application\modules\settings\integrations\twilio\libraries;

use application\modules\settings\models\integrations\twilio\SoftTwilioWorkerModel;
use application\modules\user\models\User;

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
class AppletInstance
{
    private static $id = '';
    private static $instance = [];
    private static $flow = null;
    private static $baseURI = null;
    public static $plugin = null;
    private static $pluginName = '';
    private static $appletName = '';
    private static $flowType = '';

    /**
     * @param $name
     * @return string
     */
    public static function getPluginStoreKey($name)
    {
        return static::$id . '::' . $name;
    }

    /**
     * @return null
     */
    public static function getPlugin()
    {
        return static::$plugin;
    }

    /**
     * @param $flow_type
     */
    public static function setFlowType($flow_type)
    {
        static::$flowType = $flow_type;
    }

    /**
     * @return string
     */
    public static function getFlowType()
    {
        return static::$flowType;
    }

    /**
     * @param $name
     * @param string $default
     * @return array|mixed|string
     */
    public static function getValue($name, $default = '')
    {
        /* If this is an array selection: name[] or name[1]
         * return an array or value of the array,
         * otherwise handle as specific value
        /* If AppletInstance singleton is empty */
        if (empty(static::$instance)) {
            return $default;
        }

        /* Match arrays or value of array */
        $regex_match_array = '/(.*)\[(.*)\]$/';
        if (preg_match($regex_match_array, $name, $matches)) {

            /* Grab all the properties from the instance and
             * find the ones that match the selector
             */
            $keys = get_object_vars(static::$instance);
            $container = $matches[1];
            $container_key = $matches[2];

            /* Initalize return value based on selector key */
            $list = '';
            if (empty($container_key)) {
                $list = [];
            }

            foreach ($keys as $key => $value) {
                if (preg_match('/^' . $container . '\[\]$/', $key, $inner_matches)) {
                    if (is_array($value)) {
                        /* Value of array selection: items[3] */
                        if (is_string($container_key) && strlen($container_key) > 0) {
                            $list = array_key_exists($container_key, $value) ? $value[$container_key] : '';
                        } else {
                            $list = $value;
                        }
                    } else {
                        $list = $value;
                    }
                }
            }

            if (is_null($list)) {
                return $default;
            }

            return $list;
        }

        return isset(static::$instance->$name) ? static::$instance->$name : $default;
    }

    /**
     * @param string $name
     * @return array|mixed|string
     */
    public static function getDropZoneValue($name = 'dropZone')
    {
        return static::getValue($name);
    }

    /**
     * @param string $name
     * @return array|mixed|string
     */
    public static function getDropZoneUrl($name = 'dropZone')
    {
        $values = static::getDropZoneValue($name);
        if (empty($values)) {
            return '';
        }

        if (is_string($values)) {
            $values = [$values];
        }

        /* Build drop zone urls from values */
        $urls = [];

        foreach ($values as $i => $value) {
            if (empty($value)) {
                $urls[$i] = '';
                continue;
            }
            $parts = explode('/', $value);
            $value = $parts[count($parts) - 1];

            $urls[$i] = join('/', [static::$baseURI, $value]);
            $urls[$i] = static::$baseURI . '&applet=' . $value;
        }

        if (count($urls) > 1) {
            return $urls;
        }

        $url = !empty($urls) ? $urls[0] : '';
        return $url;
    }

    /**
     * @param string $name
     * @param string $default
     * @return array|mixed|string
     */
    public static function getSmsBoxValue($name = 'smsBox', $default = '')
    {
        return static::getValue($name, $default);
    }

    /**
     * @param string $name
     * @param string $default
     * @return array|mixed|string
     */
    public static function getTextBoxValue($name = 'textBox', $default = '')
    {
        return static::getValue($name, $default);
    }

    /**
     * @param string $name
     * @param string $default
     * @return array|mixed|string
     */
    public static function getSpeechBoxValue($name = 'speechBox', $default = '')
    {
        return static::getValue($name, $default);
    }

    /**
     * @param string $name
     * @return array|mixed|string
     */
    public static function getAudioSpeechPickerValue($name = 'audioSpeechPicker')
    {
        $mode = static::getValue($name . '_mode');
        $say = static::getValue($name . '_say');
        $play = static::getValue($name . '_play');

        if ($mode === 'play') {
            $matches = [];
            if (preg_match('/^vbx-audio-upload:\/\/(.*)/i', $play, $matches)) {
                // This is a locally hosted file, and we need to return the correct
                // absolute URL for the file.
                return base_url("audio-uploads/" . $matches[1]);
            } else {
                // We'll assume it's an absolute URL
                return $play;
            }
        } else {
            if ($mode === 'say') {
                return $say;
            } else {
                return '';
            }
        }
    }

    /**
     * @param string $name
     * @return array|mixed|string
     */
    public static function getAudioPickerValue($name = 'audioPicker')
    {
        $value = static::getValue($name);

        return $value;
    }

    /**
     * @param string $name
     * @return User|User[]|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public static function getUserGroupPickerValue($name = 'userGroupPicker')
    {
        $owner_id = static::getValue($name . '_id');
        $owner_type = static::getValue($name . '_type');
        $owner = null;

        switch ($owner_type) {
            case 'group':
                $owner = [];//VBX_Group::get(array( 'id' => $owner_id ));
                break;
            case 'user':
                $owner = User::with(['employee'])->where('id', '=', $owner_id)->first();

                $device = new \stdClass();
                $device->user_id = $owner->id;
                $device->name = 'Primary Device';
                $device->value = 'client:agent_' . $owner->id;
                $device->is_active = 1;
                $device->sms = 1;
                $device->sequence = 0;
                $device->tenant_id = 1;
                $owner->twilioVoiceDevices = [
                    $device,
                ];

                if ($owner->employee->emp_phone) {
                    $device2 = new \stdClass();
                    $device2->user_id = $owner->id;
                    $device2->name = 'Primary Device';
                    $device2->value = $owner->employee->emp_phone;
                    $device2->is_active = 1;
                    $device2->sms = 1;
                    $device2->sequence = 0;
                    $device2->tenant_id = 1;

                    $owner->twilioVoiceDevices = [
                        $device,
                        $device2
                    ];
                }

                break;
        }

        return $owner;
    }

    /**
     * @param $instance
     * @throws PluginException
     */
    public static function setInstance($instance)
    {
        list($plugin_name, $applet_name) = explode('---', $instance->type);

        static::$instance = $instance->data;
        static::$id = $instance->id;
        static::$pluginName = $plugin_name;
        static::$appletName = $applet_name;
        static::$plugin = new Plugin(static::$pluginName);
        PluginData::setPluginId(static::$plugin->getPluginId());
    }

    /**
     * @param $flow
     */
    public static function setFlow($flow)
    {
        static::$flow = $flow;
    }

    /**
     * @return null
     */
    public static function getFlow()
    {
        return static::$flow;
    }

    /**
     * @param $baseURI
     */
    public static function setBaseURI($baseURI)
    {
        static::$baseURI = $baseURI;
    }

    /**
     * @return null
     */
    public static function getBaseURI()
    {
        return static::$baseURI;
    }

    /**
     * @return string
     */
    public static function getInstanceId()
    {
        return static::$id;
    }

    /**
     * @param $keys
     * @param $values
     * @param bool $case_insensitive
     * @return array
     */
    public static function assocKeyValueCombine($keys, $values, $case_insensitive = true)
    {
        $result = [];
        /* Filter values and keys to build assoc item pairs */
        foreach ($keys as $key_id => $key) {
            /* If using the same key over again - it will clobber so warn the user in the logs */
            $value = isset($values[$key_id]) ? $values[$key_id] : '';
            if ($case_insensitive) {
                $key = strtolower($key);
            }

            if (isset($result[$key])) {
                error_log("Clobbering keys in assocKeyValueCombine, Key: $key, Old: {$result[$key]}, New: $value");
            }
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @param $day
     * @param $key
     * @return string
     */
    public static function day_check($day, $key)
    {

        $value = AppletInstance::getValue($day);

        if (count($value) > 1) {
            if ($value[$key] == "true") {
                return "selected";
            }
        } elseif (count($value) == "true") {
            if ($value == 1) {
                return "selected";
            }
        } else {
            return "failed";
        }

    }
}
