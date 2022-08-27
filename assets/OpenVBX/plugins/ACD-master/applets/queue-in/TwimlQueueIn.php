<?php

use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\AppletUI\AudioSpeechPickerWidget;
use application\modules\settings\integrations\twilio\libraries\OpenVBX;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;
use application\modules\user\models\User;

/**
 * Class TwimlQueueIn
 */
class TwimlQueueIn
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
        $this->dial_whom_number = AppletInstance::getValue('dial-whom-number');
        $this->dial_whisper = AppletInstance::getValue('dial-whisper', true);

        $this->no_answer_action = AppletInstance::getValue('no-answer-action', 'hangup');
        $this->no_answer_group_voicemail = AppletInstance::getAudioSpeechPickerValue('no-answer-group-voicemail');
        $this->no_answer_redirect = AppletInstance::getDropZoneUrl('no-answer-redirect');
        $this->no_answer_redirect_number = AppletInstance::getDropZoneUrl('no-answer-redirect-number');


        if (count($settings)) {
            foreach ($settings as $setting => $value) {
                if (isset($this->$setting)) {
                    $this->$setting = $value;
                }
            }
        }
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
        if (!empty($_COOKIE[$this->cookie_name])) {
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
