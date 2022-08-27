<?php

namespace application\modules\settings\models\integrations\twilio;

use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\integrations\twilio\libraries\OpenVBX;
use Services_Twilio_Rest_IncomingPhoneNumber;
use stdClass;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;

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
class VBX_IncomingNumberException extends \Exception
{
}

/**
 * Class VBXIncomingNumbers
 * @package application\modules\soft_twilio_calls\models
 */
class VBXIncomingNumbers
{
    public static $areaCodeCountries = array(
        'US',
        'CA',
    );

    /**
     * VBXIncomingNumbers constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function get_numbers()
    {
        $number = (new AccountTwilio())->voiceNumbers;
        return $number;
    }

    /**
     * Clear cache method
     */
    protected function clear_cache()
    {
        $ci =& get_instance();
        $ci->api_cache->invalidate(__CLASS__, $ci->tenant->id);
    }

    /**
     * @param $params
     * @return stdClass
     * @throws \Exception
     */
    public static function get($params)
    {
        if (empty($params['number_sid']) && empty($params['phone_number'])) {
            return false;
        }

        $vbx_incoming_numbers = new self;
        $numbers = $vbx_incoming_numbers->get_numbers();
        $incoming_number = false;

        if (!empty($numbers)) {
            foreach ($numbers as $number) {
                switch (true) {
                    case !empty($params['number_sid']):
                        if ($number->id == $params['number_sid']) {
                            $incoming_number = $number;
                        }
                        break;
                    case !empty($params['phone_number']):
                        if ($number->phone_number == $params['phone_number']) {
                            $incoming_number = $number;
                        }
                        break;
                }
            }
        }

        return $incoming_number;
    }

    /**
     * Modified base_url to substitute 'localhost' for '127.0.0.1' so that
     * first time local phone number setup works against Twilio's callback
     * url validation.
     */
    protected function base_url()
    {
        $base_url = base_url();

        if (strpos($base_url, '://localhost')) {
            str_replace('://localhost', '://127.0.0.1', $base_url);
        }

        return $base_url;
    }
}
