<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use application\modules\user\models\ExtNumbers;
use application\modules\settings\models\Settings;
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;


/**
 * Class Install_twilio
 */
class Install extends MX_Controller
{

    /**
     * Install_twilio constructor.
     */
    public function __construct()
    {

        parent::__construct();

        if (!isUserLoggedIn()) {
            redirect('login');
        }
        if (is_cl_permission_none()) {
            redirect('dashboard');
        }

        $this->twilioSettingsModel = Settings::getTwilioSettings();

        $this->_title = SITE_NAME;
    }

    /**
     * @return array
     */
    public static function getVoiceValidateRules()
    {
        return [
            BT::VOICE_ACCOUNT_SID => 'required|string',
            BT::VOICE_AUTH_TOKEN => 'required|string',
        ];
    }

    /**
     * @return array
     */
    public static function getSmsValidateRules()
    {
        return [
            BT::SMS_ACCOUNT_SID => 'required|string',
            BT::SMS_AUTH_TOKEN => 'required|string',
            BT::SMS_MESSAGING_SERVICE_SID => 'required|string',
            BT::SMS_TWILIO_NUMBER => 'string',
            'messenger' => 'nullable|string',
        ];
    }

    /**
     * Install twilio voice method
     * @throws Exception
     */
    public function index()
    {
        $restore = request()->get('restore');

        if (isset($this->twilioSettingsModel[BT::VOICE_ACCOUNT_SID]) && is_null($restore)) {
            redirect('settings/integrations/twilio');
        }

        $data['title'] = $this->_title . ' - Soft twilio installation';
        $data['twilioSettings'] = $this->twilioSettingsModel;

        if (request()->isMethod('post')) {
            try {
                $validatedRequest = request()->validate(static::getVoiceValidateRules());
            } catch (\Illuminate\Validation\ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }

            if (!isset($data['errors'])) {
                try {
                    DB::beginTransaction();

                    $numbers = (new AccountTwilio(
                        $validatedRequest[BT::VOICE_ACCOUNT_SID],
                        $validatedRequest[BT::VOICE_AUTH_TOKEN]
                    ))->getIncomingPhoneNumbers();

                    $incomingNumbers = [];

                    if ($numbers && !empty($numbers)) {
                        foreach ($numbers as $number) {
                            $incomingNumbers[] = $number->phoneNumber;
                        }
                    }
                    $validatedRequest[BT::VOICE_PHONE_NUMBERS] = json_encode($incomingNumbers, true);

                    foreach ($validatedRequest as $key => $value) {
                        $setting = Settings::where(Settings::ATTR_KEY_NAME, $key)->first();
                        if (!is_null($setting)) {
                            $setting->update([Settings::ATTR_KEY_VALUE => $value]);
                        } else {
                            Settings::create([
                                Settings::ATTR_KEY_NAME => $key,
                                Settings::ATTR_KEY_VALUE => $value
                            ]);
                        }
                    }
                    DB::commit();
                } catch (Throwable $e) {
                    DB::rollBack();
                    throw new Exception($e->getMessage());
                }
                redirect('settings/integrations/twilio');
            }

        }

        $this->load->view('settings/integrations/twilio/install', $data);
    }

    /**
     * Install twilio sms method
     * @throws Exception
     */
    public function sms()
    {
        $restore = request()->get('restore');

        if (!isset($this->twilioSettingsModel[BT::VOICE_ACCOUNT_SID])) {
            redirect('settings/integrations/twilio/install');
        } elseif (isset($this->twilioSettingsModel[BT::SMS_ACCOUNT_SID]) && is_null($restore)) {
            redirect('settings/integrations/twilio');
        }

        $data['title'] = $this->_title . ' - Soft twilio sms installation';
        $data['twilioSettings'] = $this->twilioSettingsModel;

        if (request()->isMethod('post')) {

            $validatedRequest = [];

            try {
                $validatedRequest = request()->validate(static::getSmsValidateRules());
            } catch (\Illuminate\Validation\ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if ($validatedRequest['messenger'] == 'on') {
                $validatedRequest['messenger'] = 1;
            } else {
                $validatedRequest['messenger'] = 0;
            }

            if (!isset($data['errors'])) {

                $accountTwilio = new AccountTwilio(
                    $validatedRequest[BT::SMS_ACCOUNT_SID],
                    $validatedRequest[BT::SMS_AUTH_TOKEN]
                );

                if (isset($validatedRequest[BT::SMS_TWILIO_NUMBER]) && !empty($validatedRequest[BT::SMS_TWILIO_NUMBER])) {
                    $validatedRequest[BT::SMS_TWILIO_NUMBER] = $accountTwilio->getIncomingPhoneNumberBySid($validatedRequest[BT::SMS_TWILIO_NUMBER])->phoneNumber;
                } else {
                    $currentMessagingService = $accountTwilio->getMessagingService($validatedRequest[BT::SMS_MESSAGING_SERVICE_SID]);
                    $currentMSPhones = $currentMessagingService->phoneNumbers->read();
                    $validatedRequest[BT::SMS_TWILIO_NUMBER] = $currentMSPhones[0]->phoneNumber;
                }

                try {
                    DB::beginTransaction();
                    foreach ($validatedRequest as $key => $value) {
                        $setting = Settings::where(Settings::ATTR_KEY_NAME, $key)->first();
                        if (!is_null($setting)) {
                            $setting->update([Settings::ATTR_KEY_VALUE => $value]);
                        } else {
                            Settings::create([
                                Settings::ATTR_KEY_NAME => $key,
                                Settings::ATTR_KEY_VALUE => $value
                            ]);
                        }
                    }
                    DB::commit();
                } catch (Throwable $e) {
                    DB::rollBack();
                    throw new Exception($e->getMessage());
                }
                redirect('settings/integrations/twilio');
            }

        }

        $this->load->view('settings/integrations/twilio/sms/install', $data);
    }

    public function sms_uninstall()
    {
        Settings::where('stt_key_name', '=', BT::SMS_ACCOUNT_SID)
        ->orWhere('stt_key_name', '=', BT::SMS_AUTH_TOKEN)
        ->orWhere('stt_key_name', '=', BT::SMS_TWILIO_NUMBER)
        ->orWhere('stt_key_name', '=', BT::SMS_MESSAGING_SERVICE_SID)
        ->orWhere('stt_key_name', '=', 'messenger')->delete();

        return $this->response(['data' => 'ok'], 200);
    }

    /**
     * @return bool|void
     */
    public function get_messaging_services()
    {
        if (request()->isMethod('post')) {
            try {
                $validatedRequest = request()->validate([
                    BT::SMS_ACCOUNT_SID => 'required|string',
                    BT::SMS_AUTH_TOKEN => 'required|string',
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
                    $messagingServices = $accountTwilio->getMessagingServices();
                    $numbers = $accountTwilio->getIncomingPhoneNumbers();
                } catch (Exception $e) {
                    $data['errors'] = $e->getMessage();
                }
            }

            if (isset($data['errors'])) {
                return $this->response(['error' => $data['errors'] ?? ''], 200);
            }
            if (empty($messagingServices)) {
                return $this->response(['data' => []], 200);
            }
            $data = $this->load->view('settings/integrations/twilio/sms/messaging_services/messaging_service_select', [
                'messagingServices' => $messagingServices,
                'numbers' => $numbers
            ], true);
            return $this->response(['data' => $data], 200);
        }
        return false;
    }
}
