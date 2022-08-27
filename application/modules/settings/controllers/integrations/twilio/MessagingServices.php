<?php

use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use application\modules\settings\models\Settings;
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
use Twilio\Rest\Messaging\V1\ServiceInstance;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Class MessagingServices
 */
class MessagingServices extends MX_Controller
{

    public function __construct()
    {
        $this->twilioSettingsArray = Settings::getTwilioSettings(true);
        if (!isset($this->twilioSettingsArray[BT::VOICE_ACCOUNT_SID])) {
            redirect('settings/integrations/twilio/install/sms');
        }
    }

    /**
     * Method set for ajax get form
     */
    public function create()
    {
        $data = [];

        if (request()->isMethod('GET')) {
            $accountSid = request()->query(BT::SMS_ACCOUNT_SID) ?? $this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]['stt_key_value'] ?? '';
            $authToken = request()->query(BT::SMS_AUTH_TOKEN) ?? $this->twilioSettingsArray[BT::SMS_AUTH_TOKEN]['stt_key_value'] ?? '';
            if (empty($accountSid) || empty($authToken)) {
                return $this->response(['error' => 'Credentials invalid'], 200);
            }

            try {
                $accountTwilio = new AccountTwilio($accountSid, $authToken);
                $messagingServices = $accountTwilio->getMessagingServices();
                $numbers = $accountTwilio->getIncomingPhoneNumbers();
            } catch (Exception $e) {
                $data['errors'] = $e->getMessage();
            }

            $usedPhoneNumbersArray = [];
            if (!empty($messagingServices)) {
                foreach ($messagingServices as $messagingService) {
                    $phoneNumbers = $messagingService->phoneNumbers->read();
                    if (!empty($phoneNumbers)) {
                        foreach ($phoneNumbers as $phoneNumber) {
                            $usedPhoneNumbersArray[] = $phoneNumber->sid;
                        }
                    }
                }
            }

            $data['data'] = $this->load->view('settings/integrations/twilio/sms/messaging_services/create', [
                'numbers' => $numbers,
                'usedPhoneNumbersArray' => $usedPhoneNumbersArray,
                'accountSid' => $accountSid,
                'authToken' => $authToken,
            ], true);

            return $this->response($data, 200);


        } elseif (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate([
                    BT::SMS_ACCOUNT_SID => 'required|string',
                    BT::SMS_AUTH_TOKEN => 'required|string',
                    'requestUrl' => 'required|string',
                    'callbackUrl' => 'required|string',
                    'friendlyName' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                try {
                    $accountTwilio = new AccountTwilio(
                        $validatedRequest[BT::SMS_ACCOUNT_SID],
                        $validatedRequest[BT::SMS_AUTH_TOKEN]
                    );
                    /** @var ServiceInstance $service */
                    $service = $accountTwilio->getAccountsClient()->messaging->services->create(request()->input('friendlyName'),
                        [
                            'InboundRequestUrl' => request()->input('requestUrl'),
                            'StatusCallback' => request()->input('callbackUrl'),
                        ]);

                    $primaryNumbers = request()->input('sms_twilio_primary_number');
                    $smsTwilioNumbers = request()->input('twilioNumber');
                    if (is_array($smsTwilioNumbers) && !is_null($primaryNumbers)) {
                        $data['primaryNumberSid'] = $smsTwilioNumbers[key($primaryNumbers)];
                    }
                    $data['messagingServiceSid'] = $service->sid;

                    $twilioNumbers = array_unique($smsTwilioNumbers, SORT_STRING);

                    if (!empty($twilioNumbers)) {
                        foreach ($twilioNumbers as $twilioNumber) {
                            $accountTwilio->getAccountsClient()
                                ->messaging
                                ->services($service->sid)
                                ->phoneNumbers
                                ->create($twilioNumber);
                        }
                    }

                } catch (Exception $e) {
                    $data['errors'] = $e->getMessage();
                }
            }

            return $this->response(['data' => $data], 200);
        }
    }

    /**
     * @param string $sid
     */
    public function update(string $sid)
    {
        $data = [];
        if (request()->isMethod('GET')) {
            try {
                $accountTwilio = new AccountTwilio(
                    $this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]["stt_key_value"],
                    $this->twilioSettingsArray[BT::SMS_AUTH_TOKEN]["stt_key_value"]
                );
                $currentMessagingService = $accountTwilio->getMessagingService($sid);
                $currentMSPhones = $currentMessagingService->phoneNumbers->read();
                $messagingServices = $accountTwilio->getMessagingServices();
                $numbers = $accountTwilio->getIncomingPhoneNumbers();
            } catch (Exception $e) {
                $data['errors'] = $e->getMessage();
            }

            $usedPhoneNumbersArray = [];
            if (!empty($messagingServices)) {
                foreach ($messagingServices as $messagingService) {
                    $phoneNumbers = $messagingService->phoneNumbers->read();
                    if (!empty($phoneNumbers)) {
                        foreach ($phoneNumbers as $phoneNumber) {
                            $usedPhoneNumbersArray[] = $phoneNumber->sid;
                        }
                    }
                }
            }

            $data['data'] = $this->load->view('settings/integrations/twilio/sms/messaging_services/update', [
                'currentMessagingService' => $currentMessagingService,
                'currentMSPhones' => $currentMSPhones,
                'numbers' => $numbers,
                'usedPhoneNumbersArray' => $usedPhoneNumbersArray,
                'accountSid' => $this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]["stt_key_value"] ?? '',
                'authToken' => $this->twilioSettingsArray[BT::SMS_AUTH_TOKEN]["stt_key_value"] ?? '',
                'primaryTwilioNumber' => $this->twilioSettingsArray[BT::SMS_TWILIO_NUMBER]
            ], true);

            return $this->response($data, 200);

        } elseif (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate([
                    'requestUrl' => 'required|string',
                    'callbackUrl' => 'required|string',
                    'friendlyName' => 'required|string',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                try {
                    $accountTwilio = new AccountTwilio(
                        $this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]["stt_key_value"],
                        $this->twilioSettingsArray[BT::SMS_AUTH_TOKEN]["stt_key_value"]
                    );
                    /** @var ServiceInstance $service */
                    $service = $accountTwilio->getMessagingService($sid);
                    $currentMSPhones = $service->phoneNumbers->read();
                    $service->update([
                            'FriendlyName' => request()->input('friendlyName'),
                            'InboundRequestUrl' => request()->input('requestUrl'),
                            'StatusCallback' => request()->input('callbackUrl'),
                        ]);

                    foreach ($currentMSPhones as $currentMSPhone) {
                        $currentMSPhone->delete();
                    }
                    $primaryNumbers = request()->input('sms_twilio_primary_number');

                    $smsTwilioNumbers = request()->input('twilioNumber');
                    $twilioNumbers = array_unique($smsTwilioNumbers, SORT_STRING);

                    if (!empty($twilioNumbers)) {
                        foreach ($twilioNumbers as $twilioNumber) {
                            $accountTwilio->getAccountsClient()
                                ->messaging
                                ->services($service->sid)
                                ->phoneNumbers
                                ->create($twilioNumber);
                        }
                    }

                    $primaryNumberSid = null;
                    $primaryPhoneNumber = null;
                    if (is_array($smsTwilioNumbers) && !is_null($primaryNumbers)) {
                        $primaryNumberSid = $smsTwilioNumbers[key($primaryNumbers)];
                        $primaryPhoneNumber = $accountTwilio->getIncomingPhoneNumberBySid($primaryNumberSid)->phoneNumber;
                    }

                    $primaryPhoneNumberModel = Settings::where('stt_key_name', '=', BT::SMS_TWILIO_NUMBER)->first();
                    $primaryPhoneNumberModel->stt_key_value = $primaryPhoneNumber;
                    $primaryPhoneNumberModel->save();
                } catch (Exception $e) {
                    $data['errors'] = $e->getMessage();
                }
            }

            return $this->response(['data' => $data], 200);
        }
    }

    /**
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete(string $sid)
    {
        $accountTwilio = new AccountTwilio($this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]['stt_key_value'], $this->twilioSettingsArray[BT::SMS_AUTH_TOKEN]['stt_key_value']);
        if ($accountTwilio->getMessagingService($sid)->delete()) {
            return $this->response(['data' => 'ok'], 200);
        }
    }

}