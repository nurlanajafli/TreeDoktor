<?php


namespace application\commands;

use application\core\Console\Command;
use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
use application\modules\settings\models\Settings;
use ElephantIO\Client as WSClient;
use ElephantIO\Engine\SocketIO\Version1X;

class SmsTwilioCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:twilio';

    public $twilioSettingsModel;
    public $CI;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->CI = &get_instance();
        $this->twilioSettingsModel = Settings::getTwilioSettings();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function handle()
    {
        $messenger = $this->CI->config->item('messenger');
        if ($messenger == true) {
            if (isset($this->twilioSettingsModel[BT::SMS_ACCOUNT_SID]) || isset($this->twilioSettingsModel[BT::SMS_AUTH_TOKEN])) {
                $accountTwilio = new AccountTwilio(
                    $this->twilioSettingsModel[BT::SMS_ACCOUNT_SID]["stt_key_value"],
                    $this->twilioSettingsModel[BT::SMS_AUTH_TOKEN]["stt_key_value"]
                );

                $messagingServices = $accountTwilio->getMessagingServices();
                if (!empty($messagingServices)) {
                    foreach ($messagingServices as $messagingService) {
                        $currentMSPhones = $messagingService->phoneNumbers->read();
                        if (!empty($currentMSPhones)) {
                            foreach ($currentMSPhones as $currentMSPhone) {
                                if (!isset($this->twilioSettingsModel[BT::SMS_TWILIO_NUMBER])) {
                                    Settings::create([
                                        Settings::ATTR_KEY_NAME => BT::SMS_TWILIO_NUMBER,
                                        Settings::ATTR_KEY_VALUE => $currentMSPhone->phoneNumber
                                    ]);
                                }
                                break;
                            }
                        }
                        if (!isset($this->twilioSettingsModel[BT::SMS_MESSAGING_SERVICE_SID])) {
                            Settings::create([
                                Settings::ATTR_KEY_NAME => BT::SMS_MESSAGING_SERVICE_SID,
                                Settings::ATTR_KEY_VALUE => $messagingService->sid
                            ]);
                        }
                        break;
                    }
                }
            } else {
                $data[BT::SMS_ACCOUNT_SID] = $this->CI->config->item('accountSid');
                $data[BT::SMS_AUTH_TOKEN] = $this->CI->config->item('authToken');
                $data[BT::SMS_MESSAGING_SERVICE_SID] = $this->CI->config->item('messagingServiceSid');
                $data[BT::SMS_TWILIO_NUMBER] = $this->CI->config->item('myNumber');
                //$data['messenger'] = $this->CI->config->item('messenger');

                foreach ($data as $key => $item) {
                    if (!isset($this->twilioSettingsModel[$key])) {
                        Settings::create([
                            Settings::ATTR_KEY_NAME => $key,
                            Settings::ATTR_KEY_VALUE => $item
                        ]);
                    }
                }
            }
        }

        $this->output->text('Thank you, all has been saved successfully');
    }
}