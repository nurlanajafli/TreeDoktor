<?php

namespace application\modules\settings\integrations\twilio\classes\accounts;

use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Messaging\V1\ServiceInstance;

/**
 * Class ActivitiesTwilio
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/iam/api/account
 */
class AccountTwilio extends BaseTwilio
{
    public function __construct($twilio_account_sid = '', $twilio_auth_token_sid = '')
    {
        parent::__construct($twilio_account_sid, $twilio_auth_token_sid);
    }

    /**
     * @return \Twilio\Rest\Client
     */
    public function getAccountsClient()
    {
        return $this->twilioClient->accounts->getClient();
    }

    /**
     * @return \Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance[]
     */
    public function getIncomingPhoneNumbers()
    {
        return $this->getAccountsClient()->incomingPhoneNumbers->read();
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getIncomingPhoneNumberBySid(string $sid)
    {
        return $this->getAccountsClient()->incomingPhoneNumbers($sid)->fetch();
    }

    /**
     * @return \Twilio\Rest\Api\V2010\Account\AvailablePhoneNumberCountryInstance[]
     */
    public function getAvailablePhoneNumbers()
    {
        return $this->getAccountsClient()->availablePhoneNumbers->read();
    }

    /**
     * @param $country
     * @return \Twilio\Rest\Api\V2010\Account\AvailablePhoneNumberCountry\TollFreeList
     */
    public function getAvailablePhoneNumbersTollFreeListByCountry($country)
    {
        //$a = $this->getAccountsClient()->availablePhoneNumbers()->tollFree;
        return $this->getAccountsClient()->availablePhoneNumbers->getContext($country)->tollFree;
    }

    /**
     * @return \Twilio\Rest\Messaging\V1\ServiceInstance[]
     */
    public function getMessagingServices()
    {
        return $this->getAccountsClient()->messaging->services->read();
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Messaging\V1\ServiceInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getMessagingService(string $sid)
    {
        return $this->getAccountsClient()->messaging->services($sid)->fetch();
    }

}
