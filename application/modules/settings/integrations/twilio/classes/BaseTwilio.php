<?php

namespace application\modules\settings\integrations\twilio\classes;

use application\modules\settings\models\Settings;
use Twilio\Rest\Client;

/**
 * Download the helper library from https://www.twilio.com/docs/node/install
 * Find your Account SID and Auth Token at twilio.com/console
 * and set the environment variables. See http://twil.io/secure
 *
 * Class BaseTwilio
 * @package application\modules\soft_twilio_calls\classes
 */
class BaseTwilio
{

    const VOICE_ACCOUNT_SID = 'voice_twilio_account_sid';
    const VOICE_AUTH_TOKEN = 'voice_twilio_auth_token_sid';
    const VOICE_PHONE_NUMBERS = 'voice_twilio_account_phone_numbers';
    const SMS_ACCOUNT_SID = 'sms_twilio_account_sid';
    const SMS_AUTH_TOKEN = 'sms_twilio_auth_token_sid';
    const SMS_MESSAGING_SERVICE_SID = 'messagingServiceSid';
    const SMS_TWILIO_NUMBER = 'twilioNumber';


    const SUPPORT_EXPRESSION = "skills HAS 'support'";

    /**
     * Client for accessing the Twilio API
     * @var \Twilio\Rest\Client
     */
    public $twilioClient;

    /**
     * @var string
     */
    public $accountSid = '';

    /**
     * @var string
     */
    public $authToken = '';

    /**
     * @var string
     */
    public $workspaceSid = '';

    /**
     * @var string
     */
    public $workflowSid = '';

    /**
     * @var string
     */
    public $appSid = '';

    /**
     * @var string
     */
    public $wrapUpActivitySid = '';

    /**
     * @var string
     */
    public $busyActivitySid = '';

    /**
     * @var string
     */
    public $reservedActivitySid = '';

    /**
     * @var string
     */
    public $offlineActivitySid = '';

    /**
     * @var string
     */
    public $onlineActivitySid = '';

    /**
     * @var string
     */
    public $myNumber = '';

    /**
     * @var array
     */
    public $voiceNumbers = [];

    /**
     * @var \Twilio\Rest\Api\V2010\AccountContext
     */
    public $account;

    /**
     * TwilioTaskRouterRouter constructor.
     * @param string $twilio_account_sid
     * @param string $twilio_auth_token_sid
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct($twilio_account_sid = '', $twilio_auth_token_sid = '')
    {
        $settingsModel = Settings::getTwilioSettings();
        if (!empty($twilio_account_sid) && !empty($twilio_auth_token_sid)) {
            $this->accountSid = $twilio_account_sid;
            $this->authToken = $twilio_auth_token_sid;
        } else {
            $this->accountSid = $settingsModel['voice_twilio_account_sid']['stt_key_value'];
            $this->authToken = $settingsModel['voice_twilio_auth_token_sid']['stt_key_value'];
        }
        if (isset($settingsModel['voice_twilio_account_phone_numbers']['stt_key_value']) && !empty($settingsModel['voice_twilio_account_phone_numbers']['stt_key_value'])) {
            $this->voiceNumbers = json_decode($settingsModel['voice_twilio_account_phone_numbers']['stt_key_value'], true);
        }
        $this->myNumber = empty($this->voiceNumbers) ? '' : $this->voiceNumbers[0];

        $this->twilioClient = new Client($this->accountSid, $this->authToken);
        $this->account = $this->twilioClient->getAccount();
    }
}