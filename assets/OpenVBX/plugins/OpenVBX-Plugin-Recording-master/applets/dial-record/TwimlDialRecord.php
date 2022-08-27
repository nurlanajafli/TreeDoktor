<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;
use application\modules\settings\integrations\twilio\libraries\OpenVBX;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;
use application\modules\user\models\User;

class TwimlDialRecord
{
    /**
     * Use the CodeIgniter session class to set the cookie
     * Not using this has caused issues on some systems, but
     * until we know that this squashes our bugs we'll leave
     * the toggle to allow the legacy method of tracking
     *
     * @var bool
     */
    private $use_ci_session = true;

    static $hangup_stati = array('completed', 'answered');
    static $voicemail_stati = array('no-answer', 'failed');
    static $default_voicemail_message = 'Please leave a message. Press the pound key when you are finished.';

    protected $cookie_name;

    public $state;
    public $response;

    public $dial;

    protected $timeout = false;
    protected $transcribe = true;
    protected $voice = 'man';
    protected $language = 'en';
    protected $record = false;
    protected $sequential = false;

    /**
     * Default timeout is the same as the Twilio default timeout
     *
     * @var int
     */
    public $default_timeout = 20;

    public function __construct($settings = array())
    {
        $this->response = new TwimlResponse;

        $this->cookie_name = 'state-' . AppletInstance::getInstanceId();
        $this->version = AppletInstance::getValue('version', null);

        $this->callerId = AppletInstance::getValue('callerId', null);
        if (empty($this->callerId) && !empty($_REQUEST['From'])) {
            $this->callerId = $_REQUEST['From'];
        }

        /* Get current instance	 */
        $this->dial_whom_selector = AppletInstance::getValue('dial-whom-selector');
        $this->dial_whom_user_or_group = AppletInstance::getUserGroupPickerValue('dial-whom-user-or-group');
        $this->dial_whom_number = AppletInstance::getValue('dial-whom-number');
        $this->dial_whisper = AppletInstance::getValue('dial-whisper', true);

        $this->no_answer_action = AppletInstance::getValue('no-answer-action', 'hangup');
        $this->no_answer_group_voicemail = AppletInstance::getAudioSpeechPickerValue('no-answer-group-voicemail');
        $this->no_answer_redirect = AppletInstance::getDropZoneUrl('no-answer-redirect');
        $this->no_answer_redirect_number = AppletInstance::getDropZoneUrl('no-answer-redirect-number');

        $this->dial_whom_instance = get_class($this->dial_whom_user_or_group);

        if (count($settings)) {
            foreach ($settings as $setting => $value) {
                if (isset($this->$setting)) {
                    $this->$setting = $value;
                }
            }
        }
    }

// Helpers

    public function getDial()
    {
        if (empty($this->dial)) {
            $url = current_url();
            $param = '';
            if (isset($_GET) && !empty($_GET)) {
                foreach ($_GET as $key => $value) {
                    $param .= $key . '=' . $value . '&';
                }
                $url .= '?' . $param;
                $url = substr($url, 0, -1);
            }
            $this->dial = $this->response->dial(null, [
                'action' => $url,
                'callerId' => $this->callerId,
                'timeout' => (!empty($this->timeout)) ? $this->timeout : $this->default_timeout,
                'sequential' => ($this->sequential ? 'true' : 'false'),
                'record' => (isset($this->record) ? $this->record : false)
            ]);
        }
        return $this->dial;
    }

    public function callOpts($params)
    {
        $opts = [];

        if ($params['whisper_to'] AND $this->dial_whisper) {
            $opts['url'] = site_url('client_twilio_calls/whisper?name=' . urlencode($params['whisper_to']));
        }
        if ($this->timeout) {
            $opts['timeout'] = $this->timeout;
        }

        return $opts;
    }

    public function dial($device_or_user)
    {
        $dialed = false;

        if ($device_or_user instanceof User) {
            $dialed = $this->dialUser($device_or_user);
        } elseif ($device_or_user instanceof \stdClass) {
            $dialed = $this->dialDevice($device_or_user);
        } else {
            $dialed = $this->dialNumber($device_or_user);
        }

        return $dialed;
    }

    /**
     * Add a device to the Dialer
     *
     * @param $device
     * @return bool
     */
    public function dialDevice($device)
    {
        $dialed = false;

        if ($device->is_active) {
            /** @var User $user */
            $user = User::findOrFail($device->user_id);
            $dial = $this->getDial();

            $call_opts = $this->callOpts([
                'whisper_to' => $user->firstname . ' ' . $user->lastname
            ]);
            unset($call_opts['timeout']);
            if (strpos($device->value, 'client:') !== false) {
                $call_opts['statusCallbackEvent'] = 'initiated ringing answered completed';
                $dial->client(str_replace('client:', '', $device->value), $call_opts);
            } else {
                $dial->number($device->value, $call_opts);
            }

            $this->state = 'calling';
            $dialed = true;
        }
        return $dialed;
    }

    /**
     * Add the user's devices to a Dial Verb
     * Ignore non-active devices
     *
     * @param User $user
     * @return bool
     */
    public function dialUser($user)
    {
        $dialed = false;

        if (count($user->twilioVoiceDevices)) {
            $dial = $this->getDial();

            $call_opts = $this->callOpts(['whisper_to' => $user->firstname . ' ' . $user->lastname]);
            unset($call_opts['timeout']);
            foreach ($user->twilioVoiceDevices as $device) {
                if ($device->is_active) {
                    if (strpos($device->value, 'client:') !== false) {
                        $call_opts['statusCallbackEvent'] = 'initiated ringing answered completed';
                        $dial->client(str_replace('client:', '', $device->value), $call_opts);
                    } else {
                        $dial->number($device->value, $call_opts);
                    }

                    $this->state = 'calling';
                    $dialed = true;
                    break;
                }
            }
        }

        return $dialed;
    }

    /**
     * Dial a number directly, no special sauce here
     *
     * @param string $number
     * @return bool
     */
    public function dialNumber($number)
    {
        $dial = $this->getDial();
        $number = normalize_phone_to_E164($number);
        $dial->number($number);
        $this->state = 'calling';
        return true;
    }

    /**
     * Handle nobody picking up the dail
     *
     * @return void
     * @throws Exception
     */
    public function noanswer()
    {
        $_status = null;
        if ($this->dial_whom_selector == 'number') {
            $this->no_answer_number();
        } else {
            $this->no_answer_object();
        }
    }

    /**
     * If the result of a no-answer is to redirect to
     * a new number we handle that here. If no number just hangup
     *
     * @return void
     */
    protected function no_answer_number()
    {
        if (empty($this->no_answer_redirect_number)) {
            $this->response->hangup();
        }

        $this->response->redirect($this->no_answer_redirect_number);
    }

    /**
     * If the result of a no-answer is to take a voicemail then
     * we determine if its a user or group voicemail and then prompt for a record
     *
     * Also, if the result of no-answer is to redirect then that is handled here too.
     * An empty redirect value will cause a hangup.
     *
     * @return void
     * @throws Exception
     */
    protected function no_answer_object()
    {
        if ($this->no_answer_action === 'voicemail') {
            switch ($this->dial_whom_instance) {
                case 'application\modules\user\models\User':
                    $voicemail = $this->dial_whom_user_or_group->voicemail;
                    break;
                default:
                    $voicemail = null;
            }

            if (!AudioSpeechPickerWidget::setVerbForValue($voicemail, $this->response)) {
                // fallback to default voicemail message
                $this->response->say(self::$default_voicemail_message, [
                    'voice' => $this->voice,
                    'language' => $this->language
                ]);
            }

            $record_params = ['transcribe' => $this->transcribe ? 'true' : 'false'];

            if ($this->transcribe) {
                $record_params['transcribeCallback'] = site_url('client_twilio_calls/transcribe');
            }

            $this->response->record($record_params);
            $this->state = 'recording';
        } else {
            if ($this->no_answer_action === 'redirect') {
                if (empty($this->no_answer_redirect)) {
                    $this->hangup();
                }

                $this->response->redirect($this->no_answer_redirect);
            } else {
                if ($this->no_answer_action === 'hangup') {
                    $this->hangup();
                } else {
                    throw new Exception("Unexpected no_answer_action");
                }
            }
        }
    }

    /**
     * Handle callback after someone leaves a message
     *
     * @return void
     */
    public function add_voice_message()
    {
        OpenVBX::addVoiceMessage($this->dial_whom_user_or_group);
        $this->response->say('Your message has been recorded. Goodbye.');
        $this->hangup();
    }

    /**
     * Add a hangup to the response
     *
     * @return void
     */
    public function hangup()
    {
        $this->response->hangup();
    }

    /**
     * Send the response
     *
     * @return void
     */
    public function respond()
    {
        $this->response->respond();
    }

    /**
     * Figure out our state
     *
     * - First check the DialCallStatus & CallStatus, they'll tell us if we're done or not
     * - then check our state from the cookie to see if its empty, if so, we're new
     * - then use the cookie value
     *
     * @return void
     */
    public function set_state()
    {
        $call_status = isset($_REQUEST['CallStatus']) ? $_REQUEST['CallStatus'] : null;
        $dial_call_status = isset($_REQUEST['DialCallStatus']) ? $_REQUEST['DialCallStatus'] : null;

        $this->state = $this->_get_state();

        if (in_array($dial_call_status, self::$hangup_stati)
            || in_array($call_status, self::$hangup_stati)
            && $this->state != 'recording') {
            $this->state = 'hangup';
        } elseif (in_array($dial_call_status, self::$voicemail_stati)) {
            $this->state = 'voicemail';
        } elseif (!$this->state) {
            $this->state = 'new';
        }
    }

    /**
     * Get the state from the cookie
     *
     * @return string json or std
     */
    private function _get_state()
    {
        $state = null;
        if ($this->use_ci_session) {
            $CI =& get_instance();
            $state = $CI->session->get_userdata($this->cookie_name);
        } elseif (!empty($_COOKIE[$this->cookie_name])) {
            $state = $_COOKIE[$this->cookie_name];
        }

        return $state;
    }

    /**
     * Store the state for use on the next go-around
     *
     * @return void
     */
    public function save_state()
    {
        $state = $this->state;
        if ($this->use_ci_session) {
            $CI =& get_instance();
            $CI->session->set_userdata($this->cookie_name, $state);
        } else {
            set_cookie($this->cookie_name, $state, time() + (5 * 60));
        }
    }
}
