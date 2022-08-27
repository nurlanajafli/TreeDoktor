<?php

namespace application\modules\settings\integrations\twilio\libraries;

use application\libraries\Twilio\Services_Twilio_TinyHttp;
use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\BaseTaskRouterClient;
use application\modules\settings\integrations\twilio\libraries\Services\Services_Twilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use DateTimeZone;
use Exception;
use Services_Twilio_RequestValidator;

use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

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
class OpenVBXException extends Exception
{
}

/**
 * Class VBX_AccountsException
 * @package application\modules\soft_twilio_calls\libraries
 */
class VBX_AccountsException extends Exception
{
}

/**
 * Class OpenVBX
 * @package application\modules\soft_twilio_calls\libraries
 */
class OpenVBX
{
    protected static $version = '1.2.20';
    protected static $schemaVersion;

    /**
     * @var Plugin
     */
    public static $currentPlugin = null;

    private static $_twilioService;
    private static $_twilioValidator;

    const TYPE_VOICE = 'voice';
    const TYPE_FAX = 'fax';
    const TYPE_SMS = 'sms';
    const TYPE_TELEPATHY = 'telepathy';

    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';

    /**
     * @param $sql
     * @return |null
     * @throws AppletUI\Exceptions\PluginDataException
     */
    public static function query($sql)
    {
        //todo:: check for using
        return PluginData::sqlQuery($sql);
    }

    /**
     * @param $sql
     * @return mixed
     * @throws AppletUI\Exceptions\PluginDataException
     */
    public static function one($sql)
    {
        return PluginData::one($sql);
    }

    /**
     * @return bool
     */
    public static function isAdmin()
    {
        $ci =& get_instance();
        $is_admin = $ci->session->userdata('is_admin');

        return ($is_admin == 1);
    }

    /**
     * @return string
     */
    public static function getTwilioAccountType()
    {
        try {
            $ci =& get_instance();
            $ci->load->model('vbx_accounts');
            return $ci->vbx_accounts->getAccountType();
        } catch (VBX_AccountsException $e) {
            error_log($e->getMessage());
            self::setNotificationMessage($e->getMessage());
            return 'Full';
        }
    }

    /**
     * @return mixed
     */
    public static function getCurrentUser()
    {
        $ci =& get_instance();
        $user_id = $ci->session->userdata('user_id');
        return VBX_User::get($user_id);
    }

    /**
     * Get the twilio API version from the API endpoint settings
     *
     * @return mixed string/null
     * @deprecated url versioning is handled by Twilio Services library
     */
    public static function getTwilioApiVersion()
    {
        $url = 'https://api.twilio.com/2010-04-01';
        if (preg_match('/.*\/([0-9]+-[0-9]+-[0-9]+)$/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param $message
     */
    public static function setNotificationMessage($message)
    {
        $ci =& get_instance();
        $ci->session->set_flashdata('error', $message);
    }

    /**
     * @param $owner
     * @return bool
     */
    public static function addVoiceMessage($owner)
    {
        return static::addMessage($owner);
    }

    /**
     * @param $owner
     * @return bool
     */
    public static function addMessage($owner)
    {
        try {
            $ci =& get_instance();
            $ci->load->model('mdl_calls');
            //todo : how to connected flow and workspace for wsClient send data
            $workspace = (new SoftTwilioWorkspaceModel())->first();
            $twilio = new BaseTaskRouterClient($workspace->sid);
            $callSid = urldecode($ci->input->post('CallSid'));
            $dialCallSid = urldecode($ci->input->post('DialCallSid'));
            $clientId = null;
            $userTwilioSid = null;
            $userId = null;
            $recording = $ci->input->post('RecordingUrl') ? $ci->input->post('RecordingUrl') : null;

            $callRows = $ci->mdl_calls->get_calls(['call_twilio_sid' => $callSid]);

            if ($callRows && !empty($callRows)) {
                $data = [
                    'call_complete' => 1,
                    'call_disabled' => 0,
                ];
                if (isset($recording) && !empty($recording) && !is_null($recording)) {
                    $data['call_voice'] = $recording;
                }

                $ci->mdl_calls->update($callRows[0]['call_id'], $data);

                $wsClient = new WSClient(new Version1X($ci->config->item('wsClient')));
                $wsClient->initialize();
                if (!is_null($workspace)) {
                    $wsClient->emit('room', [$workspace->sid]);
                }
                $wsClient->emit('message', ['method' => 'updateHistory']);
                $wsClient->close();

                return false;
            }

            $direction = $ci->input->post('Direction');
            $call = $twilio->twilioClient->calls($callSid)->fetch();
            $callDuration = $ci->input->post('CallDuration', false, 0);
            $callDuration = $callDuration ? $callDuration : $ci->input->post('DialCallDuration', false, 0);
            $callDuration = $ci->input->post('QueueResult') == 'hangup' ? 0 : $callDuration;
            $callDate = $call->startTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format("Y-m-d H:i:s");

            if ($direction == 'outbound-api') {
                $fromNumber = urldecode($ci->input->post('From'));
                $workerUri = str_replace('client:', '', urldecode($ci->input->post('To')));
                $toNumber = $ci->config->item('myNumber');
                $callRoute = 1;
                $clientNumber = $fromNumber;
            } elseif ($direction == 'inbound') {
                $callRoute = 0;
                $fromNumber = $ci->config->item('myNumber');
                $toNumber = $ci->input->post('To');
                $clientNumber = $toNumber;

                if ($ci->input->post('To') && $ci->input->post('DialCallSid') && (strpos($ci->input->post('To'),
                            'client:') !== false || strpos($ci->input->post('To'), 'client:') !== false)) {
                    $parentCall = $twilio->twilioClient->calls($ci->input->post('DialCallSid'))->fetch();
                    $toNumber = $parentCall->to;
                    $fromNumber = $ci->input->post('From');
                    $clientNumber = $fromNumber;
                    $workerUri = str_replace('client:', '', $parentCall->to);
                    $callRoute = 1;
                    $callDate = $parentCall->startTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format("Y-m-d H:i:s");
                    $callDuration = ($parentCall->duration) ? $parentCall->duration : 0;
                }

                if (strpos($ci->input->post('From'), 'sip:') === false && strpos($ci->input->post('From'),
                        'client:') === false && $ci->input->post('DialCallSid')) {
                    $parentCall = $twilio->twilioClient->calls($ci->input->post('DialCallSid'))->fetch();
                    $toNumber = $parentCall->to;
                    $fromNumber = $ci->input->post('From');
                    $clientNumber = $fromNumber;
                    $workerUri = str_replace('client:', '', $parentCall->to);
                    $callRoute = 1;
                    $callDate = $parentCall->startTime->setTimezone(new DateTimeZone(date_default_timezone_get()))->format("Y-m-d H:i:s");
                    $callDuration = ($parentCall->duration) ? $parentCall->duration : 0;
                }

                if (!$toNumber && $ci->input->post('DialCallSid')) {
                    if (ltrim($fromNumber, '+') == $ci->config->item('myNumber')) {
                        $callRoute = 0;
                    }

                    $parentCall = $twilio->twilioClient->calls($ci->input->post('DialCallSid'))->fetch();
                    $toNumber = $parentCall->to;
                    $clientNumber = $toNumber;

                    if (strpos($fromNumber, 'client:') !== false || strpos($fromNumber, 'sip:') !== false) {
                        $callRoute = 0;
                    }
                }

                if (ltrim($toNumber, '+') == ltrim($fromNumber, '+') || ltrim($fromNumber,
                        '+') == $ci->config->item('myNumber')) {
                    $fromNumber = $ci->input->post('From');
                    $clientNumber = $fromNumber;
                    $callRoute = 1;

                    if (strpos($fromNumber, 'client:') !== false || strpos($fromNumber, 'sip:') !== false) {
                        $callRoute = 0;
                    }
                }
            }

            /***************SIP***********************/
            if ((isset($fromNumber) && $fromNumber == 'sip:treedoctors@treedoctorsoffice.sip.us1.twilio.com') || $ci->input->post('From') == '+14162018244') {
                $fromNumber = 'TD Hard Phone';
                $toNumber = str_replace(['sip:', '@treedoctorsoffice.sip.us1.twilio.com'], ['', ''], $toNumber);
                $clientNumber = $toNumber;
            }
            if ((isset($toNumber) && $toNumber == 'sip:treedoctors@treedoctorsoffice.sip.us1.twilio.com') || $ci->input->post('To') == '+14162018244') {
                $toNumber = 'TD Hard Phone';
                $fromNumber = str_replace(['sip:', '@treedoctorsoffice.sip.us1.twilio.com'], ['', ''], $fromNumber);
                $clientNumber = $fromNumber;
            }
            /***************SIP***********************/

            $client_data = $ci->mdl_clients->find_by_phone(trim($clientNumber));

            if ($client_data) {
                $clientId = $client_data['client_id'];
            }

            if (isset($workerUri) && !empty($workerUri)) {
                $workers = $twilio->workSpace->workers->read();

                foreach ($workers as $worker) {
                    $attrs = json_decode($worker->attributes);
                    if (isset($attrs->contact_uri) && $attrs->contact_uri == $workerUri) {
                        $userTwilioSid = $worker->sid;
                        break;
                    }
                }

                $user = null;

                if ($userTwilioSid) {
                    $user = $ci->mdl_user->find_by_fields(['twilio_worker_id' => $userTwilioSid]);
                    $userId = $user ? $user->id : null;
                }
            }

            $callNewVoicemail = !$callDuration && $callRoute && $recording ? 1 : 0;
            if (!$callNewVoicemail) {
                $callNewVoicemail = $callRoute && !$recording ? 1 : 0;
                $callDuration = $callNewVoicemail ? 0 : $callDuration;
            }

            $callRowsExist = false;
            $result = false;

            if ($ci->input->get('TaskCallSid')) {
                $callRowsExist = $ci->mdl_calls->get_calls(['call_twilio_sid' => $ci->input->get('TaskCallSid')]);
            }
            $ownerId = (isset($owner) && !is_null($owner)) ? $owner->id : null;
            if ($callRowsExist && !empty($callRowsExist)) {
                $ci->mdl_calls->update($callRowsExist[0]['call_id'], [
                    'call_client_id' => $clientId,
                    'call_route' => $callRoute,
                    'call_user_id' => $userId ? $userId : $ownerId,
                    'call_voice' => $recording,
                    'call_complete' => 1,
                    'call_date' => $callDate,
                    'call_duration' => $callDuration,
                    'call_new_voicemail' => $callNewVoicemail
                ]);
            } else {
                $fromNumber = is_numeric($fromNumber) ? '+' . ltrim($fromNumber, '+') : $fromNumber;
                $call_note = [
                    'call_from' => $fromNumber,
                    'call_to' => $toNumber,
                    'call_type' => 'dialer',
                    'call_client_id' => $clientId,
                    'call_route' => $callRoute,
                    'call_twilio_sid' => $callSid,
                    'call_user_id' => $userId ? $userId : $ownerId,
                    'call_voice' => $recording,
                    'call_complete' => 1,
                    'call_date' => $callDate,
                    'call_workspace_sid' => $workspace->sid,
                    'call_duration' => $callDuration,
                    'call_new_voicemail' => $callNewVoicemail
                ];

                if (isset($twilio->myNumber) && !empty($twilio->myNumber)) {
                    if (strpos($call_note['call_from'], $twilio->myNumber) !== false) {
                        $call_note['call_from'] = $twilio->myNumber;
                    } elseif (strpos($call_note['call_to'], $twilio->myNumber) !== false) {
                        $call_note['call_to'] = $twilio->myNumber;
                    }
                }

                $result = $ci->mdl_calls->insert($call_note);
            }

            $wsClient = new WSClient(new Version1X($ci->config->item('wsClient')));
            $wsClient->initialize();
            if (!is_null($workspace)) {
                $wsClient->emit('room', [$workspace->sid]);
            }
            $wsClient->emit('message', ['method' => 'updateHistory']);
            $wsClient->close();

            return $result;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Returns the OpenVBX software version
     *
     * Post 1.1.3 this pulls from the file in `OpenVBX/config/version.php` instead
     * of pulling from the database. This way the version number can be known without
     * a functional database (ie: install)
     *
     * @return string
     */
    public static function version()
    {
        if (empty(static::$version)) {
            static::$version = '1.2.20';
        }
        return static::$version;
    }

    /**
     * Returns the version of the database schema
     *
     * @static
     * @return int
     */
    public static function schemaVersion()
    {
        if (empty(self::$schemaVersion)) {
            $ci =& get_instance();
            if ($ci->db) {
                $reenable_cache = false;

                $ci->load->model('vbx_settings');
                if (isset($ci->cache) && $ci->cache->enabled()) {
                    $ci->cache->enabled(false);
                    $reenable_cache = true;
                }

                self::$schemaVersion = $ci->vbx_settings->get('schema-version', VBX_PARENT_TENANT);

                if ($reenable_cache) {
                    $ci->cache->enabled(true);
                }
            }
        }
        return self::$schemaVersion;
    }

    /**
     * Returns the latest version of the schema on the server,
     * regardless if its been imported
     *
     * @static
     * @return array
     */
    public static function getLatestSchemaVersion()
    {
        $updates = scandir(VBX_ROOT . '/updates/');
        foreach ($updates as $i => $update) {
            $updates[$i] = intval(preg_replace('/.(sql|php)$/', '', $update));
        }

        sort($updates);
        return $updates[count($updates) - 1];
    }

    /**
     * Set the title of the current page
     *
     * @static
     * @param string $title
     * @param bool $overwrite whether to replace or append to the current title
     * @return mixed
     */
    public static function setPageTitle($title, $overwrite = false)
    {
        $ci =& get_instance();
        return $ci->template->write('title', $title, $overwrite);
    }

    /**
     * Get a set of modified http options for TinyHttp so that we
     * can modify how the api client identifies itself as well as
     * inject some debug options
     *
     * @return array
     */
    protected static function get_http_opts()
    {
        $ci =& get_instance();

        $_http_opts = [
            'host' => 'https://api.twilio.com',
            'opts' => [
                'curlopts' => [
                    CURLOPT_USERAGENT => static::getVbxUserAgentString(),
                ]
            ]
        ];

        // optionally load in the included cert for api communication
        if ($use_certificate = $ci->config->item('twilio_use_certificate')) {
            $_http_opts['opts']['curlopts'][CURLOPT_CAINFO] = APPPATH . '/modules/soft_twilio_calls/libraries/Services/cacert.pem';
        }

        // internal api development override, you'll never need this
        if ($_http_settings = $ci->config->item('_http_settings')) {
            if (!empty($_http_settings['host'])) {
                $_http_opts['host'] = $_http_settings['host'];
            }
        }

        // set debug mode if applicable
        if ($api_debug = $ci->config->item('api_debug')) {
            if ($api_debug === true) {
                $_http_opts['opts']['debug'] = true;
            }
        }

        return $_http_opts;
    }

    /**
     * @return string
     */
    public static function getVbxUserAgentString()
    {
        return 'openvbx/' . OpenVBX::version();
    }

    /**
     * Validate that the current request came from Twilio
     *
     * If no url is passed then the default $_SERVER['REQUEST_URI'] will be passed
     * through site_url().
     *
     * If no post_vars are passed then $_POST will be used directly.
     *
     * @param bool/string $uri
     * @param bool/array $post_vars
     * @return bool
     */
    public static function validateRequest($url = false, $post_vars = false)
    {
        $ci =& get_instance();
        if ($ci->tenant->type == VBX_Settings::AUTH_TYPE_CONNECT) {
            return true;
        }

        if (!(self::$_twilioValidator instanceof Services_Twilio_RequestValidator)) {
            self::$_twilioValidator = new Services_Twilio_RequestValidator($ci->twilio_token);
        }

        if (empty($url)) {
            // we weren't handed a uri, use the default
            $url = site_url($ci->uri->uri_string());
        } elseif (strpos($url, '://') === false) {
            // we were handed a relative uri, make it full
            $url = site_url($url);
        }

        // without rewrite enabled we need to ensure that the query string
        // is properly appended to the url when being reconstructed
        if ($ci->vbx_settings->get('rewrite_enabled', VBX_PARENT_TENANT) < 1 &&
            !empty($_SERVER['QUERY_STRING']) && strpos($url, $_SERVER['QUERY_STRING']) === false) {
            parse_str($_SERVER['QUERY_STRING'], $qs);

            // make sure that the rewrite var doesn't stay in the query
            // string if we're not doing rewriting
            if ($ci->vbx_settings->get('rewrite_enabled', VBX_PARENT_TENANT) < 1) {
                foreach ($qs as $name => $value) {
                    if ($name == 'vbxsite') {
                        unset($qs[$name]);
                    }
                }
            }

            if (!empty($qs)) {
                $url .= '?' . http_build_query($qs);
            }
        }

        if (empty($post_vars)) {
            // we weren't handed post-vars, use the default
            $post_vars = $_POST;
        }

        return self::$_twilioValidator->validate(self::getRequestSignature(), $url, $post_vars);
    }

    /**
     * Get the X-Twilio-Signature header value
     *
     * @return mixed string, boolean false if not found
     * @todo maybe needs some special love for nginx?
     */
    public static function getRequestSignature()
    {
        $request_signature = false;
        if (!empty($_SERVER['HTTP_X_TWILIO_SIGNATURE'])) {
            $request_signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'];
        }
        return $request_signature;
    }
}
