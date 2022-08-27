<?php

namespace application\modules\settings\integrations\twilio\libraries;


/**
 * Variant of the DialList that iterates over a single VBX_User's devices
 *
 * @package default
 */
class DialListUser extends DialList
{

    /**
     * @return int
     */
    public function count()
    {
        if (isset($this->users[0]->twilioVoiceDevices)) {
            $result = count($this->users[0]->twilioVoiceDevices);
        } else {
            $result = 0;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function get_state()
    {
        // return list of device IDs left in the user
        $device_ids = [];
        if (isset($this->users[0]->twilioVoiceDevices)) {
            if (count($this->users[0]->twilioVoiceDevices)) {
                foreach ($this->users[0]->twilioVoiceDevices as $device) {
                    array_push($device_ids, $device->id);
                }
            }
        }


        return [
            'type' => get_class($this),
            'device_ids' => $device_ids,
            'user_id' => $this->users[0]->id
        ];
    }

    /**
     * @return mixed|null
     */
    public function next()
    {
        $device = null;

        while ($device == null && count($this->users[0]->twilioVoiceDevices)) {
            $user_device = array_shift($this->users[0]->twilioVoiceDevices);
            if ($user_device->is_active) {
                $device = $user_device;
            }
        }

        return $device;
    }
}
