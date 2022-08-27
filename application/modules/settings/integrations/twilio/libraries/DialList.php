<?php

namespace application\modules\settings\integrations\twilio\libraries;

use application\modules\user\models\User;
use Countable;
use stdClass;

/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/
 *
 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.
 *
 *  The Original Code is OpenVBX, released June 15, 2010.
 *
 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.
 *
 * Contributor(s):
 **/
class DialListException extends \Exception
{
}

/**
 * Class DialList
 * @package application\modules\soft_twilio_calls\libraries
 */
class DialList implements Countable
{
    public $users;
    public $user;


    /**
     * DialList constructor.
     * @param array $users
     */
    public function __construct($users = array())
    {
        // clone users in to the object since we're gonna
        // mess with their device lists
        if (!empty($users)) {
            foreach ($users as $user) {
                $this->users[] = clone $user;
            }
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->users);
    }

    /**
     * Get a DialList object try
     * Pass in a VBX_User or VBX_Group object to begin
     *
     * @param
     * @return DialList
     */
    public static function get($users_or_group)
    {
        $users = [];
        $class = 'application\modules\settings\integrations\twilio\libraries\DialListUser';
        switch (true) {
            case $users_or_group instanceof User:
                // individual user, add to list and continue
                array_push($users, $users_or_group);
                break;
        }
        return new $class($users);
    }

    /**
     * Return the object state as a list of user ids
     * Use DialList::load($user_ids); to repopulate an object from the list
     *
     * @return array
     */
    public function get_state()
    {
        $user_ids = [];
        if (count($this->users)) {
            foreach ($this->users as $user) {
                array_push($user_ids, $user->id);
            }
        }

        return [
            'type' => get_class($this),
            'user_ids' => $user_ids
        ];
    }

    /**
     * Return the first user's primary device in the current list state
     * User is removed from the list, reducing the length of the list by 1
     *
     * @return mixed VBX_Device or NULL
     */
    public function next()
    {
        $device = null;

        while ($device == null && count($this->users)) {
            $user = array_shift($this->users);

            if (!is_null($user->twilioVoiceDevices) && count($user->twilioVoiceDevices)) {
                foreach ($user->twilioVoiceDevices as $user_device) {
                    if ($user_device->is_active) {
                        $device = $user_device;
                        break;
                    }
                }
            } else {
                $device = [];
            }
        }

        return $device;
    }
}
