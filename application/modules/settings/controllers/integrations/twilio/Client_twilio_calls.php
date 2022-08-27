<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\BaseTaskRouterClient;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;
use application\modules\settings\integrations\twilio\libraries\Applet;
use application\modules\settings\integrations\twilio\libraries\AppletInstance;
use application\modules\settings\integrations\twilio\libraries\OpenVBX;
use application\modules\settings\integrations\twilio\libraries\Plugin;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;
use application\modules\settings\models\integrations\twilio\SoftTwilioCallsFlow;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use application\modules\settings\models\integrations\twilio\VBXIncomingNumbers;
use application\modules\user\models\User;
use application\modules\settings\models\Settings;
use ElephantIO\Engine\SocketIO\Version1X;
use Twilio\Rest\Client;
use ElephantIO\Client as WSClient;

define('VBX_ROOT', dirname(substr(BASEPATH, 0, strlen(BASEPATH) - 1)) . '');
define('VBX_PARENT_TENANT', 1);
define('PLUGIN_PATH', VBX_ROOT . '/assets/OpenVBX/plugins');

class Client_twilio_calls extends MX_Controller
{
    protected $response;
    private $flow;
    private $flow_id;
    private $flow_type = 'voice';
    protected $say_params;

    const dial_timeout = 20;
    const voice = 'man';
    const language = 'en';

    /**
     * @var array
     */
    protected $twilioSettingsArray = [];

    /**
     * Client_twilio_calls constructor.
     * @throws \Twilio\Exceptions\TwimlException
     */
    public function __construct()
    {
        parent::__construct();

        $this->twilioSettingsArray = Settings::getTwilioSettings();

        $this->say_params = [
            'voice' => static::voice,
            'language' => static::language
        ];

        $this->flow_id = get_cookie('flow_id');
        $this->response = new TwimlResponse;

        $this->load->model('mdl_calls');
        $this->load->model('mdl_calls_hold');
        $this->load->model('mdl_voices');
        $this->load->model('mdl_sms');
        $this->load->model('mdl_calls_reservations');
        $this->load->model('mdl_clients');
        $this->load->model('mdl_user');
    }

    /**
     * @param $type
     * @param $flow_id
     * @param $inst_id
     * @throws \Twilio\Exceptions\TwimlException
     */
    public function twiml($type, $flow_id, $inst_id)
    {
        $this->flow_id = get_cookie('flow_id');
        $this->response = new TwimlResponse;
        $this->applet($flow_id, $inst_id, $type);
    }

    /**
     * @param $flow_id
     */
    public function start_sms($flow_id)
    {
        log_message("info", "Calling SMS Flow $flow_id");
        $body = $this->input->get_post('Body');
        $this->flow_type = 'sms';

        $this->session->set_userdata('sms-body', $body);

        $flow_id = $this->set_flow_id($flow_id);
        $flow = $this->get_flow();
        $flow_data = [];
        if (is_object($flow) && strlen($flow->sms_data)) {
            $flow_data = get_object_vars(json_decode($flow->sms_data));
        }

        $instance = isset($flow_data['start']) ? $flow_data['start'] : null;
        if (is_object($instance)) {
            $this->applet($flow_id, 'start', 'sms');
        } else {
            $this->response->say('Error 4-oh-4 - Flow not found.', $this->say_params);
            $this->response->respond();
        }
    }

    /**
     * @param $flow_id
     */
    public function start_voice($flow_id)
    {
        $this->flow_type = 'voice';

        $flow_id = $this->set_flow_id($flow_id);
        $flow = $this->get_flow();
        $flow_data = [];
        if (is_object($flow) && strlen($flow->data)) {
            $flow_data = get_object_vars(json_decode($flow->data));
        }

        $instance = isset($flow_data['start']) ? $flow_data['start'] : null;

        if (is_object($instance)) {
            $this->applet($flow_id, 'start');
        } else {
            $this->response->say('Error 4-oh-4 - Flow not found.', $this->say_params);
            $this->response->respond();
        }
    }

    /**
     * @param $flow_id
     * @param $inst_id
     */
    public function sms($flow_id, $inst_id)
    {
        $this->flow_type = 'sms';
        $redirect = $this->session->userdata('redirect');
        if (!empty($redirect)) {
            $this->response->redirect($redirect);
            $this->session->set_userdata('last-redirect', $redirect);
            $this->session->unset_userdata('redirect');
            return $this->response->respond();
        }
        return $this->applet($flow_id, $inst_id, 'sms');
    }

    /**
     * @param $flow_id
     * @param $inst_id
     */
    public function voice($flow_id, $inst_id)
    {
        return $this->applet($flow_id, $inst_id, 'voice');
    }

    /**
     * @param $applet
     * @param $plugin_dir_name
     */
    private function applet_headers($applet, $plugin_dir_name)
    {
        $plugin = Plugin::get($plugin_dir_name);
        $plugin_info = ($plugin) ? $plugin->getInfo() : false;

        header("X-OpenVBX-Applet-Version: {$applet->version}");
        if ($plugin_info) {
            header("X-OpenVBX-Plugin: {$plugin_info['name']}");
            header("X-OpenVBX-Plugin-Version: {$plugin_info['version']}");
        }
        header("X-OpenVBX-Applet: {$applet->name}");
    }

    /**
     * @param $flow_id
     * @param $inst_id
     * @param string $type
     */
    public function applet($flow_id, $inst_id, $type = 'voice')
    {
        $flow_id = $this->set_flow_id($flow_id);
        $flow = $this->get_flow();
        $instance = null;
        $applet = null;

        try {
            switch ($type) {
                case 'sms':
                    if (isset($_REQUEST['Body']) && $inst_id == 'start') {
                        $_COOKIE['sms-body'] = $_REQUEST['Body'];
                        $sms = $_REQUEST['Body'];

                        // Expires after three hours
                        set_cookie('sms-body', $sms, 60 * 60 * 3);
                    } else {
                        $sms = isset($_COOKIE['sms-body']) ? $_COOKIE['sms-body'] : null;
                        set_cookie('sms-body', null, time() - 3600);
                    }
                    $sms_data = $flow->sms_data;
                    if (!empty($sms_data)) {
                        /** @var stdClass $flow_data */
                        $flow_data = get_object_vars(json_decode($sms_data));
                        /** @var stdClass $instance */
                        $instance = isset($flow_data[$inst_id]) ? $flow_data[$inst_id] : null;
                    }

                    if (!is_null($instance)) {
                        list($plugin_dir_name, $applet_dir_name) = explode('---', $instance->type);

                        $applet = Applet::get($plugin_dir_name, $applet_dir_name, null, $instance);
                        $applet->flow_type = $type;
                        $applet->instance_id = $inst_id;
                        $applet->sms = $sms;
                        if ($sms) {
                            $_POST['Body'] = $_GET['Body'] = $_REQUEST['Body'] = $sms;
                        }
                        $this->session->unset_userdata('sms-body');

                        $applet->currentURI = site_url("client_twilio_calls/applet/sms/$flow_id/$inst_id");

                        $baseURI = site_url("client_twilio_calls/applet/sms/$flow_id/");
                        $this->applet_headers($applet, $plugin_dir_name);
                        echo $applet->twiml($flow, $baseURI, $instance);
                    }
                    break;
                case 'voice':
                    $voice_data = $flow->data;
                    if (!empty($voice_data)) {
                        /** @var stdClass $flow_data */
                        $flow_data = get_object_vars(json_decode($voice_data));
                        /** @var stdClass $instance */
                        $instance = isset($flow_data[$inst_id]) ? $flow_data[$inst_id] : null;
                    }

                    if (!is_null($instance)) {
                        list($plugin_dir_name, $applet_dir_name) = explode('---', $instance->type);

                        $applet = Applet::get($plugin_dir_name, $applet_dir_name, null, $instance);
                        $applet->flow_type = $type;
                        $applet->instance_id = $inst_id;
                        $applet->currentURI = site_url("/callback/settings/voice?flow=$flow_id&applet=$inst_id");
                        $baseURI = site_url("/callback/settings/voice?flow=$flow_id");
                        $this->applet_headers($applet, $plugin_dir_name);

                        echo $applet->twiml($flow, $baseURI, $instance);
                    }
                    break;
            }

            if (!is_object($applet)) {
                $this->response->say('Unknown applet instance in flow ' . $flow_id, $this->say_params);
                $this->response->respond();
            }

        } catch (Exception $ex) {
            $this->response->say('Error: ' . $ex->getMessage(), $this->say_params);
            $this->response->respond();
        }
    }

    /**
     * @param $path
     * @param bool $singlepass
     */
    public function redirect($path, $singlepass = false)
    {
        $path = str_replace('!', '/', $path);
        $this->response->redirect(site_url($path), ['method' => 'POST']);
        $this->response->respond();
    }

    /**
     * Assigment twilio method
     */
    public function assignment()
    {
        $TaskAttributes = json_decode($this->input->post('TaskAttributes'));
        if ($TaskAttributes == false) {
            var_dump($this->input->post());
            exit;
        }
        $WorkerAttributes = json_decode($this->input->post('WorkerAttributes'));

        $call_note = [
            'call_type' => 'taskrouter',
            'call_from' => $TaskAttributes->from,
            'call_to' => $TaskAttributes->to,
            'call_client_id' => $TaskAttributes->clientId,
            'call_user_id' => null,
            'call_route' => 1,
            'call_date' => date('Y-m-d H:i:s'),
            'call_twilio_sid' => $TaskAttributes->call_sid,
            'call_complete' => '0',
            'call_disabled' => 1,
            'call_workspace_sid' => $this->input->post('WorkspaceSid'),
        ];

        $this->mdl_calls->insert($call_note);
        $idleActivitySid = SoftTwilioActivityModel::where('friendlyName', 'Idle')->first()->sid;
        $assignment_instruction = [
            'instruction' => 'dequeue',
            'to' => 'client:' . $WorkerAttributes->contact_uri,
            'from' => $TaskAttributes->from,
            'record' => 'record-from-answer',
            'status_callback_url' => base_url('/callback/settings/recording'),
            'post_work_activity_sid' => $idleActivitySid
        ];


        header('Content-Type: application/json');
        die(json_encode($assignment_instruction));
    }

    /**
     * Recording twilio voicemails method
     */
    public function recording()
    {
        OpenVBX::addVoiceMessage(AppletInstance::getUserGroupPickerValue('permissions'));
        $this->response->hangup();
        $this->response->respond();
        return;
    }

    /**
     * Play method
     */
    public function play()
    {
        $instance = request()->get('instance');
        $flow_id = request()->get('flow_id');
        $flow = $this->get_flow($flow_id);

        $voice_data = $flow->data;
        if (!empty($voice_data)) {
            /** @var stdClass $flow_data */
            $flow_data = get_object_vars(json_decode($voice_data));
            /** @var stdClass $instance */
            $instance = isset($flow_data[$instance]) ? $flow_data[$instance] : null;
        }

        AppletInstance::setInstance($instance);
        AppletInstance::setFlow($flow);
        //get instance
        $repeat = (array)AppletInstance::getValue('repeat[]', []);
        $says = (array)AppletInstance::getValue('say[]', []);
        $pause = (array)AppletInstance::getValue('pause[]', []);
        $numDigits = (int)AppletInstance::getValue('gather', 0);
        $response = $this->response;

        if ($numDigits > 0) {
            $response = $this->response->gather([
                'action' => base_url('/client_twilio_calls/redirectToVoice'),
                'method' => 'POST',
                'numDigits' => $numDigits
            ]);
        }

        if (!empty($says)) {
            foreach ($says as $key => $say) {
                $playData = [];
                $PauseData = [];
                if ($repeat[$key] > 0) {
                    $playData = ['loop' => $repeat[$key]];
                }
                $response->play(AppletInstance::getValue('music[' . $key . ']_play', ''), $playData);
                if (!empty($say)) {
                    $response->say($say);
                }
                if ($pause[$key] > 0) {
                    $PauseData = ['length' => $pause[$key]];
                }
                $response->pause($PauseData);
            }
        }

        $this->response->redirect(base_url('client_twilio_calls/redirectToVoice'), ['method' => 'POST']);
        $this->response->respond();
        return;
    }

    /**
     * Go to voicemail after task no answer
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function redirectToVoice()
    {
        $workspace = (new SoftTwilioWorkspaceModel())->first();
        $twilio = new BaseTaskRouterClient($workspace->sid);

        $twilio->twilioClient->calls($this->input->post('CallSid'))->update([
            "url" => base_url('client_twilio_calls/viewVoicemail'),
            "method" => "POST"
        ]);
        $this->load->model('mdl_calls_tasks');
        $taskRow = $this->mdl_calls_tasks->get_by(['twilio_calls' => $this->input->post('CallSid')]);

        if (isset($taskRow->twilio_tasks)) {
            $task = $twilio->workSpace->tasks($taskRow->twilio_tasks)->fetch();
            $task->update([
                'assignmentStatus' => 'canceled',
                'reason' => 'will be recording'
            ]);
        }

        $this->mdl_calls_tasks->delete_by(['twilio_calls' => $this->input->post('CallSid')]);
    }

    /**
     * Render voice mail method
     */
    public function viewVoicemail()
    {
        $this->response->say('Please leave a message. Press the pound key when you are finished.', [
            'voice' => 'alice',
            'language' => 'en'
        ]);
        $this->response->record([
            'action' => base_url('/callback/settings/recording'),
            'method' => "POST",
            'finishOnKey' => '*',
            'playBeep' => 'true'
        ]);
        $this->response->respond();
        return;
    }

    /**
     * Dial
     *
     * Callback method that responds to a Twilio request and provides
     * a number for Twilio to dial.
     *
     * Overloaded by Twilio Client integration - Twilio Client connection
     * requests automatically include the "1" Digit to immediately connect
     * the call
     *
     * @return void
     */
    public function dial()
    {
        $request = request();
        $to = $request->input('to');
        $callerid = $request->input('callerid');
        $record = $request->input('record');
        $digits = clean_digits($request->input('Digits'));

        if ($digits !== false && $digits == 1) {
            $options = [
                'action' => site_url("client_twilio_calls/dial_status") . '?' . http_build_query(compact('to')),
                'callerId' => $callerid,
                'timeout' => static::dial_timeout
            ];

            if ($record !== false) {
                $options['record'] = $record;
            }

            if (filter_var($request->input('to'), FILTER_VALIDATE_EMAIL)) {
                $this->dial_user_by_email($request->input('to'), $options);
            } elseif (preg_match('|client:[0-9]{1,4}|', $request->input('to'))) {
                $this->dial_user_by_client_id($request->input('to'), $options);
            } else {
                $to = normalize_phone_to_E164($to);
                $this->response->dial($to, $options);
            }
        } else {
            $gather = $this->response->gather(['numDigits' => 1]);
            $gather->say("Hello, to accept, press 1.", $this->say_params);
        }

        $this->response->respond();
    }

    /**
     * Dial a user by 'client:1' format
     *
     * @param string $client_id
     * @param array $options
     * @return void
     * @todo not implemented
     */
    protected function dial_user_by_client_id($client_id, $options)
    {
        $user_id = intval(str_replace('client:', '', $client_id));
        $dial = $this->response->dial(null, $options);
        $dial->client($user_id);
    }

    /**
     * Dial a user identified by their email address
     *
     * Uses $user->setting('online') to determine if user "wants" to be contacted via
     * Twilio Client. Passed in "online" status via $_POST can override the
     * attempt to dial Twilio Client even if the person has set their status
     * to online. The $_POST var should be representative of the Presence
     * Status of the user being dialed (if known).
     *
     * @param string $user_email
     * @param array $options
     * @return void
     */
    protected function dial_user_by_email($user_email, $options)
    {
        $request = request();
        $user = User::where('user_email', '=', $user_email)->findOrFail();

        if ($user instanceof User) {
            //todo::check for this inline status
            $dial_client = ($user->active_status == 'yes');

            /**
             * Only override the user status if we've been given
             * an explicit opinion on the user's online status
             */
            $client_status = $request->input('online');
            if (!empty($client_status) && $client_status == 'offline') {
                $dial_client = false;
            }

            $options['sequential'] = 'true';
            $dial = $this->response->dial(null, $options);

            foreach ($user->twilioVoiceDevices as $device) {
                if ($device->is_active) {
                    if (strpos($device->value, 'client:') !== false && $dial_client) {
                        if ($dial_client) {
                            $dial->client($user->id);
                        }
                    } else {
                        $dial->number($device->value);
                    }
                }
            }
        } else {
            $this->response->say("We're sorry, that user doesn't exist in our system." .
                " Please contact your system administrator. Goodbye.");
        }
    }

    /**
     * Dial status method
     */
    public function dial_status()
    {
        if ($this->input->get_post('DialCallStatus') == 'failed') {
            $this->response->say('The number you have dialed is invalid. Goodbye.', $this->say_params);
        }
        $this->response->hangup();
        $this->response->respond();
    }

    public function whisper()
    {
        $name = $this->input->get_post('name');
        if (empty($name)) {
            $name = "Open VeeBee Ex";
        }

        /* If we've received any input */
        $digits = clean_digits($this->input->get_post('Digits'));

        $this->response->respond();
    }

    /**
     * Transcribe method
     */
    public function transcribe()
    {
        OpenVBX::addVoiceMessage(AppletInstance::getUserGroupPickerValue('permissions'));
    }

    /**
     * @param $id
     * @return int|mixed
     */
    private function set_flow_id($id)
    {
        $this->session->set_userdata('flow_id', $id);
        if ($id != $this->flow_id AND $id > 0) {
            $this->get_flow($id);

            if (!empty($this->flow)) {
                $id = $this->flow->id;
                $this->flow_id = $id;
                set_cookie('flow_id', $id, 0);
            } else {
                $id = -1;
            }
        } else {
            $id = $this->flow_id;
        }
        return $id;
    }

    /**
     * fetch the current flow and set up shared objects if necessary
     * @param int $flow_id
     * @return SoftTwilioCallsFlow
     */
    private function get_flow($flow_id = 0)
    {
        if ($flow_id < 1) {
            $flow_id = $this->flow_id;
        }

        if (is_null($this->flow)) {
            $this->flow = SoftTwilioCallsFlow::findOrFail($flow_id);
        }

        if ($flow_id > 0) {
            if (!empty($this->flow)) {
                if ($this->flow_type == 'sms') {
                    // make flow data visible to all applets
                    Applet::$flow_data = $this->flow->sms_data;
                } else {
                    // make flow data visible to all applets
                    Applet::$flow_data = $this->flow->data;
                }
            }
        }

        return $this->flow;
    }

    /**
     * Twilio Events Callback Method
     */
    /*public function taskCallbacks()
    {
        $request = request();
        $workspace = (new SoftTwilioWorkspaceModel())->first();
        $twilio = new BaseTaskRouterClient($workspace->sid);

        if (
            $request->input('EventType') == 'reservation.created'
            &&
            $request->input('EventType') !== 'reservation.accepted'
        ) {
            $taskAttributesJson = $request->input('TaskAttributes') ?? null;
            $taskSid = $request->input('TaskSid') ?? null;
            $workerSid = $request->input('WorkerSid') ?? null;
            $this->addReservationWorkersToTaskAttributes($twilio, $workerSid, $taskSid, $taskAttributesJson);
        }

        if (
            $request->input('EventType') == 'task.canceled'
            ||
            $request->input('EventType') == 'task.system-deleted'
            ||
            $request->input('EventType') == 'task.deleted'
            ||
            $request->input('EventType') == 'reservation.accepted'
        ) {
            if ($taskAttributesJson = $request->input('TaskAttributes')) {
                $this->setReservationWorkersToIdle($twilio, $taskAttributesJson);
            }
        }

        if ($request->input('EventType') == 'reservation.timeout') {
            if ($taskAttributesJson = $request->input('TaskAttributes')) {
                $isSetAllTaskWorkersToIdle = $this->isAllWorkersTimeoutForCurrentTask($twilio, $taskAttributesJson);
                if ($isSetAllTaskWorkersToIdle) {
                    $this->setReservationWorkersToIdle($twilio, $taskAttributesJson);
                }
            }
        }

        if ($request->input('EventType') == 'reservation.wrapup') {
            $taskSid = $request->input('TaskSid') ?? null;

            $twilio->workSpace->tasks($taskSid)->update([
                "assignmentStatus" => "completed",
                "reason" => "the agent hang up"
            ]);
        }

        if ($request->input('EventType') == 'task.created') {
            $data['twilio_calls'] = json_decode($request->input('TaskAttributes'))->call_sid;
            $data['twilio_tasks'] = $request->input('TaskSid');
            $this->load->model('mdl_calls_tasks');
            $this->mdl_calls_tasks->insert($data);
        }


        $wsClient = new WSClient(new Version1X($this->config->item('wsClient')));
        $wsClient->initialize();
        $wsClient->emit('room', [$this->input->post('WorkspaceSid')]);
        $wsClient->emit('message', ['method' => 'updateQueueCounter']);
        $wsClient->close();
    }*/

    /**
     * @param BaseTaskRouterClient $twilio
     * @param string $workerSid
     * @param string $taskSid
     * @param $postTaskAttributes
     * @throws \Twilio\Exceptions\TwilioException
     */
    /*private function addReservationWorkersToTaskAttributes(
        BaseTaskRouterClient $twilio,
        string $workerSid,
        string $taskSid,
        $postTaskAttributes
    ) {
        if (!is_null($workerSid) && !is_null($taskSid) && !is_null($postTaskAttributes)) {
            $TaskAttributes = json_decode($postTaskAttributes);
            if (isset($TaskAttributes->workersReservation)) {
                $workersReservation = array_merge($TaskAttributes->workersReservation, [$workerSid]);
            } else {
                $workersReservation = [$workerSid];
            }
            $TaskAttributes->workersReservation = $workersReservation;
            $twilio->workSpace->tasks($taskSid)->update([
                'attributes' => json_encode($TaskAttributes)
            ]);
        }
    }*/

    /**
     * @param BaseTaskRouterClient $twilio
     * @param $postTaskAttributes
     * @throws \Twilio\Exceptions\TwilioException
     */
    /*private function setReservationWorkersToIdle(BaseTaskRouterClient $twilio, $postTaskAttributes)
    {
        $TaskAttributes = json_decode($postTaskAttributes);
        if (isset($TaskAttributes->workersReservation)) {
            foreach ($TaskAttributes->workersReservation as $workerSid) {
                $worker = $twilio->workSpace->workers($workerSid)->fetch();
                $activities = SoftTwilioActivityModel::all()->keyBy('friendlyName')->toArray();
                if ($worker->activitySid == $activities['WrapUp']['sid']) {
                    $worker->update([
                        'activitySid' => $activities['Idle']['sid']
                    ]);
                }
            }
        }
    }*/

    /**
     * @param BaseTaskRouterClient $twilio
     * @param $postTaskAttributes
     * @return bool
     */
    /*private function isAllWorkersTimeoutForCurrentTask(BaseTaskRouterClient $twilio, $postTaskAttributes)
    {
        $taskAttributes = json_decode($postTaskAttributes);
        $skills = $taskAttributes->skills;
        $result = false;
        $idleActivitySid = SoftTwilioActivityModel::where('friendlyName', 'Idle')->first()->sid;
        $workers = $twilio->workSpace->workers->read([
            'targetWorkersExpression' => sprintf('skills HAS "%s"', $skills[0]),
            'activitySid' => $idleActivitySid
        ]);
        if (empty($workers)) {
            $result = true;
        }
        return $result;
    }*/

    /**
     * From Client calls
     * @param null $parentCallSid
     */
    function recordingTaskrouter($parentCallSid = null)
    {
        $fromNumber = urldecode($this->input->post('From'));
        $toNumber = $this->config->item('myNumber');
        $clientNumber = $fromNumber;
        $userTwilioSid = 0;
        $userId = 0;
        $clientId = 0;

        if ($userTwilioSid) {
            $user = $this->mdl_user->find_by_fields(['twilio_worker_id' => $userTwilioSid]);
            $userId = $user ? $user->id : null;
        }

        $client_data = $this->mdl_clients->find_by_phone(trim($clientNumber));

        if ($client_data) {
            $clientId = $client_data['client_id'];
        }

        $workspaceSid = SoftTwilioWorkspaceModel::firstOrFail();

        $call_note = [
            'call_type' => 'taskrouter',
            'call_from' => $fromNumber,
            'call_to' => $toNumber,
            'call_client_id' => $clientId,
            'call_user_id' => $userId,
            'call_route' => 1,
            'call_date' => date('Y-m-d H:i:s'),
            'call_twilio_sid' => urldecode($this->input->post('CallSid')),
            'call_twilio_parent_call_sid' => $parentCallSid,
            'call_complete' => '1',
            'call_workspace_sid' => $workspaceSid->sid,
        ];

        $this->mdl_calls->insert($call_note);
    }

    /**
     * From Client_calls
     */
    function emergency_gather()
    {
        $this->load->model('mdl_numbers');
        $digits = 9;
        if ($digits == 201 or $digits == 9) {
            $twilio = new BaseTwilio();
            $this->response->dial($twilio->myNumber, [
                'record' => 'record-from-answer',
                'action' => base_url('/callback/settings/recording')
            ])->number($twilio->myNumber);
            $this->response->respond();
        }
    }

    /**
     * From Client_calls
     */
    function emergency_gather_result($key = null)
    {
        $this->load->model('mdl_numbers');
        $data['status'] = $this->input->post('DialCallStatus') ? $this->input->post('DialCallStatus') : null;
        $data = [];
        $emergs = $this->mdl_numbers->get_emergency();
        $data['emergency'] = isset($emergs[$key]) ? $emergs[$key] : null;
        $data['key'] = $key + 1;

        if (!$data['emergency']) {
            //todo:: set audio as dynamic
            $this->response->play(base_url('assets/' . $this->config->item('company_dir') . '/audio/tree_doctors_5.mp3'));
            $this->response->record([
                'action' => base_url('/callback/settings/recording'),
                'method' => 'POST',
                'finishOnKey' => '*',
                'playBeep' => 'true'
            ]);
        } else {
            $this->response->play(base_url('assets/' . $this->config->item('company_dir') . '/audio/tree_doctors_3.mp3'));
            $this->response->dial($data['emergency']->extention_numbe, [
                'timeout' => '20',
                'action' => base_url('client_twilio_calls/emergency_gather_result/' . $data['key'])
            ])->number($data['emergency']->extention_numbe);
        }
        $this->response->respond();
    }

    /**
     * From Client_calls
     */
    function connection_loss()
    {
        $gather = $this->response->gather([
            'action' => base_url('client_calls/gather'),
            'method' => 'POST',
        ]);
        $gather->say('Sorry, your phone call got disconnected, please stay on the line for the next agent');
        $gather->play(base_url('assets/' . $this->config->item('company_dir') . '/audio/tree_doctors_1.mp3'));
        //todo:: todo something with gather
        $this->response->redirect('https://td.onlineoffice.io/client_twilio_calls/gather', ['method' => 'POST']);
        $this->response->respond();
    }

    /**
     * From Client_calls
     */
    function send_sms_to_client()
    {
        $number = $this->input->post('PhoneNumber');
        $text = $this->input->post('sms', true);
        $errors = [];

        if (empty($text)) {
            $errors[] = 'Undefined sms text';
        }
        if (strlen($number) < (int)config_item('phone_clean_length')) {
            $msg = 'Invalid Phone Number';
            if (empty($number)) {
                $msg = 'Undefined Phone Number';
            }
            $errors[] = $msg;
            die(json_encode(['status' => 'error', 'messages' => $errors]));
        }

        $this->load->driver('messages');
        $sendResult = $this->messages->send($number, $text);

        if (!is_array($sendResult)) {
            die(json_encode(['status' => 'error', 'message' => 'Unexpected error. Please try later.']));
        }

        $errors = [];

        foreach ($sendResult as $result) {
            if (isset($result['error'])) {
                $errors[] = $result['error'];
            }
        }

        if (sizeof($errors)) {
            $result = [
                'status' => 'error'
            ];

            if (sizeof($errors) === 1) {
                $result['message'] = $errors[0];
            } else {
                $result['messages'] = $errors;
            }

            die(json_encode($result));
        }

        die(json_encode(['status' => 'ok', 'result' => $sendResult[0]]));
    }

    /**
     * From Client_calls
     */
    function send_voice_msg()
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $client = new BaseTwilio();
        $userId = $this->session->userdata['user_id'];
        $caller = $this->config->item('myNumber');
        $number = '+1' . $this->input->post('PhoneNumber');
        $voice = $this->input->post('voice');
        $swissNumberProto = $phoneUtil->parse($number, 'CA');
        $number = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);

        $client->twilioClient->account->calls->create($number, $caller, [
            "url" => base_url('/client_twilio_calls/voiceName/' . $voice . '/' . $userId)
        ]);
    }

    /**
     * From Client_calls
     */
    function voiceName($voice, $userId = null)
    {
        $voice = $this->mdl_voices->get($voice);
        $data['userId'] = $userId;

        $voice->voice_resp = str_replace('[USERID]', $userId, $voice->voice_resp);
        if ($voice && !empty($voice)) {
            $this->response->message($voice->voice_resp);
        }
        $this->response->respond();
    }

    /**
     * From Client_calls
     */
    function extensionToWorker($ext = null, $finished = false)
    {
        $client = new BaseTwilio();
        $this->load->model('mdl_numbers');
        $this->load->model('mdl_user');
        $extension = $this->mdl_numbers->get_extention_number(array(
            'extention_key' => $ext,
            'extention_emergency' => 0
        ));

        $workers = $client->twilioClient->taskrouter
            ->workspaces($this->config->item('workspaceSid'))
            ->workers($extension->twilio_worker_id)->fetch();

        $attr = json_decode($workers->attributes);

        if (!$this->input->post('DialCallStatus') || $this->input->post('DialCallStatus') == 'no-answer') {
            if ($finished) {
                $this->extensionToNumber($ext);
            } else {
                $this->response->dial($attr->contact_uri, [
                    'timeout' => "10",
                    'record' => "record-from-answer",
                    'action' => base_url('client_twilio_calls/extensionToWorker/' . $ext . '/1/')
                ])->client($attr->contact_uri, [
                    'statusCallbackEvent' => 'initiated ringing answered completed'
                ]);
                $this->response->respond();
            }
        } else {
            $this->recording();
        }
    }

    /**
     * From Client_calls
     */
    function extensionToNumber($ext = null, $finished = false)
    {
        $this->load->model('mdl_numbers');
        $this->load->model('mdl_user');
        $extension = $this->mdl_numbers->get_extention_number(array(
            'extention_key' => $ext,
            'extention_emergency' => 0
        ));

        $selfNumber = isset($extension->emp_phone) && $extension->emp_phone ? $extension->emp_phone : $extension->extention_number;

        if (!$this->input->post('DialCallStatus') || $this->input->post('DialCallStatus') == 'no-answer') {
            if ($finished || !$selfNumber) {
                $this->response->say('Please leave a voicemail after a tone.');
                $this->response->record([
                    'action' => base_url('client_twilio_calls/recording/' . $extention->extention_user_id),
                    'method' => 'POST',
                    'finishOnKey' => '*',
                    'playBeep' => 'true'
                ]);
                $this->response->respond();
            } else {
                $this->response->dial($selfNumber, [
                    'timeout' => '10',
                    'record' => 'record-from-answer',
                    'action' => base_url('client_twilio_calls/extensionToNumber/' . $ext . '/1')
                ])->number($selfNumber);
            }
        } else {
            $this->recording();
        }
    }

    /**
     * From Client_calls
     */
    function forwardToAgent($contact_uri = null, $forward = false, $forwarder = null)
    {
        $forwarder = isset($forwarder) ? $forwarder : null;
        $this->response->dial($contact_uri, [
            'timeout' => '20',
            'action' => isset($forward) ? base_url('client_twilio_calls/resultForwardAgent/' . $forward . '/1/' . $forwarder) : base_url('/callback/settings/recording'),
            'record' => "record-from-answer",
        ])->client($contact_uri, ['statusCallbackEvent' => 'initiated ringing answered completed']);
        $this->response->respond();
    }

    /**
     * From Client_calls
     */
    function forwardToNumber($number = null, $forward = false, $then = false, $forwarder = null)
    {
        if ($number != config_item('office_phone')) {
            if (isset($forward)) {
                $data['timeout'] = 12;
            } elseif (!isset($then) || !$then) {
                $data['timeout'] = 20;
            }
        }
        if (isset($forwarder) && $forwarder) {
            $data['action'] = base_url('client_twilio_calls/forwardToAgent/' . $forwarder);
        } elseif (!isset($then) || !$then) {
            $data['action'] = base_url('client_twilio_calls/call_status/');
        } else {
            $data['action'] = base_url('client_twilio_calls/forwardToNumber/' . $then);
        }
        $data['record'] = "record-from-answer";

        $this->response->dial($number, $data)->number($number);
        $this->response->respond();
    }

    /**
     * From Client_calls
     */
    function voiceGather($userId = null)
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $client = new BaseTwilio();
        $this->load->model('mdl_user');
        $user = false;
        if ($userId) {
            $userRes = $this->mdl_user->get_usermeta(['users.id' => $userId]);
            if ($userRes) {
                $user = $userRes->row();
            }
        }

        if (!$user) {
            $this->response->hangup();
            $this->response->respond();
            return false;
        }

        switch ($this->input->post('Digits')) {
            default:
                $phone = str_replace(['.', ' ', '(', ')', '-'], '', $user->emp_phone);
                $swissNumberProto = $phoneUtil->parse($phone, 'CA');
                $phone = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);

                if ($user && $user->twilio_worker_id) {
                    $workers = $client->twilioClient->taskrouter->workspaces($user->twilio_workspace_id)->workers($user->twilio_worker_id)->fetch();
                    if ($workers->available) {
                        $attr = json_decode($workers->attributes);
                        $this->forwardToAgent($attr->contact_uri, $phone);
                    } else {
                        $this->forwardToNumber($phone, false, config_item('office_phone'));
                    }
                } else {
                    $this->forwardToNumber($phone, false, config_item('office_phone'));
                }
                break;
        }
    }

    /**
     * From Client_calls
     */
    function hold_call($call_sid = null, $worker_id = null)
    {
        $data['ch_call_twilio_sid'] = $call_sid;
        $data['ch_date'] = date('Y-m-d H:i:s');
        $data['ch_call_number'] = $this->input->post('From');//$number;
        $userData = $this->mdl_user->get_user('id', ['twilio_worker_id' => $worker_id]);
        $data['ch_user_id'] = null;
        if ($userData->num_rows()) {
            $data['ch_user_id'] = $userData->row()->id;
        }
        $data['ch_client_id'] = null;
        $clData = $this->mdl_clients->find_by_phone($data['ch_call_number']);
        if (isset($clData['client_id']) && $clData['client_id']) {
            $data['ch_client_id'] = $clData['client_id'];
        }

        $this->mdl_calls_hold->insert($data);

        pushJob('common/socket_send', [
            'room' => [$this->config->item('workspaceSid')],
            'message' => ['method' => 'updateInHoldList']
        ]);

        $files = bucketScanDir('uploads/sounds');
        for ($i = 0; $i < 10; $i++) {
            foreach ($files as $key => $val) {
                $this->response->play(base_url('uploads/sounds/' . $val));
                $this->response->say('Please wait for the connection with operator');
                $this->response->pause(['length' => 1]);
            }
        }
        $this->response->respond();
    }

    /**
     * From Client_calls
     */
    function dial_from_hold_call($call_sid, $contact_uri = null)
    {
        $this->mdl_calls_hold->delete_by(['ch_call_twilio_sid' => $call_sid]);

        pushJob('common/socket_send', [
            'room' => [$this->config->item('workspaceSid')],
            'message' => ['method' => 'updateInHoldList']
        ]);

        if ($contact_uri) {
            $this->response->dial($contact_uri, [
                'record' => 'record-from-answer',
                'action' => base_url('/callback/settings/recording'),
                'statusCallbackEvent' => 'initiated ringing answered completed'
            ])->client($contact_uri);
            $this->response->respond();
        }
    }

    /**
     * From Client_calls
     */
    function call_status_callback()
    {
        $callSid = $this->input->post('CallSid');
        $client = new BaseTwilio();
        $calls = $client->twilioClient->calls->read(['parentCallSid' => $callSid]);
        $holdCall = $this->mdl_calls_hold->get_by(['ch_call_twilio_sid' => $callSid]);

        if ($holdCall) {
            $this->dial_from_hold_call($callSid);
        }

        foreach ($calls as $call) {
            $holdCall = $this->mdl_calls_hold->get_by(['ch_call_twilio_sid' => $call->sid]);
            if ($holdCall) {
                $this->dial_from_hold_call($call->sid);
            }
        }
        $this->recording();
    }

    /**
     * From Client_calls
     */
    function send_sms($id = null)
    {
        $client = new BaseTwilio();

        $this->load->model('mdl_sms_messages');
        $sms_sid = $this->input->post('SmsSid');

        if (is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid)) {
            sleep(2);
            if (is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid)) {
                unlink(sys_get_temp_dir() . '/' . $sms_sid);
            }
        } else {
            $fp = fopen(sys_get_temp_dir() . '/' . $sms_sid, 'w+');
            fclose($fp);
        }

        $findMessage = null;

        if ($id) {
            $findMessage = $this->mdl_sms_messages->get($id);
        } else {
            $findMessage = $this->mdl_sms_messages->get_by(['sms_sid' => $sms_sid]);
        }

        $message = $client->twilioClient->messages($sms_sid)->fetch();

        if ($findMessage) {
            $this->mdl_sms_messages->update($findMessage->sms_id, [
                'sms_status' => $message->status,
                'sms_error' => $message->errorMessage,
                'sms_sid' => $sms_sid,
            ], true);

            $socketData = [
                'method' => 'updateSmsStatus',
                'params' => [
                    'sms_id' => $findMessage->sms_id,
                    'sms_status' => $message->status,
                    'sms_error' => $message->errorMessage,
                ]
            ];
        } else {
            $date = new DateTime();
            $tz = $date->getTimezone();
            $row = [
                'sms_sid' => $sms_sid,
                'sms_number' => ltrim($message->to, '+1'),
                'sms_body' => $message->body,
                'sms_date' => $message->dateSent->setTimezone(new DateTimeZone($tz->getName()))->format('Y-m-d H:i:s'),
                'sms_support' => 0,
                'sms_readed' => 1,
                'sms_user_id' => 0,
                'sms_incoming' => 0,
                'sms_auto' => 1,
                'sms_status' => $message->status,
                'sms_error' => $message->errorMessage,
            ];
            $sms_id = $this->mdl_sms_messages->insert($row);

            $row['sms_sid'] = $sms_id;

            $socketData = [
                'method' => 'newSmsMessage',
                'params' => $row
            ];
        }

        pushJob('common/socket_send', [
            'room' => ['sms'],
            'message' => $socketData
        ]);
    }

    /**
     * From Client_calls
     */
    function receive_sms()
    {
        if ($this->input->post('From') && $this->input->post('Body')) {
            $from = $this->input->post('From');
            $body = $this->input->post('Body', true);

            $client = new BaseTwilio();
            $this->load->model('mdl_user');
            $this->load->model('mdl_sms_messages');
            $smsForUser = $this->mdl_user->check_sms_for_user($from);

            $signature = null;

            if ($smsForUser) {
                $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
                $swissNumberProto = $phoneUtil->parse($smsForUser->emp_phone, 'CA');
                $to = $phoneUtil->format($swissNumberProto, \libphonenumber\PhoneNumberFormat::E164);
                $this->response->message($body, ['to' => $to]);
                $this->response->respond();
            }

            /*******************INSERT TO DB*************/

            $sms_sid = $this->input->post('SmsSid');

            if (is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid)) {
                sleep(2);
                if (is_file(sys_get_temp_dir() . '/' . $sms_sid) && file_exists(sys_get_temp_dir() . '/' . $sms_sid)) {
                    unlink(sys_get_temp_dir() . '/' . $sms_sid);
                }
            } else {
                $fp = fopen(sys_get_temp_dir() . '/' . $sms_sid, 'w+');
                fclose($fp);
            }

            $findMessage = null;
            $findMessage = $this->mdl_sms_messages->get_by(['sms_sid' => $sms_sid]);
            $message = $client->twilioClient->messages($sms_sid)->fetch();

            if ($findMessage) {
                $this->mdl_sms_messages->update($findMessage->sms_id, [
                    'sms_status' => $message->status,
                    'sms_error' => $message->errorMessage,
                    'sms_sid' => $sms_sid,
                ], true);

                $socketData = [
                    'method' => 'updateSmsStatus',
                    'params' => [
                        'sms_id' => $findMessage->sms_id,
                        'sms_status' => $findMessage->sms_id,
                        'sms_error' => $message->errorMessage
                    ]
                ];
            } else {
                $date = new DateTime();
                $tz = $date->getTimezone();
                $row = [
                    'sms_sid' => $sms_sid,
                    'sms_number' => ltrim($message->from, '+1'),
                    'sms_body' => $message->body,
                    'sms_date' => isset($message->dateSent) && $message->dateSent ? $message->dateSent->setTimezone(new DateTimeZone($tz->getName()))->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                    'sms_support' => 0,
                    'sms_readed' => 0,
                    'sms_user_id' => 0,
                    'sms_incoming' => 1,
                    'sms_status' => $message->status,
                    'sms_error' => $message->errorMessage,
                ];
                $sms_id = $this->mdl_sms_messages->insert($row);

                $row['sms_sid'] = $sms_id;

                $socketData = [
                    'method' => 'newSmsMessage',
                    'params' => $row
                ];
            }

            pushJob('common/socket_send', [
                'room' => ['sms'],
                'message' => $socketData
            ]);

            /*******************INSERT TO DB*************/
        }
    }

    /**
     * From Client_calls
     */
    function voicemail2text()
    {
        $this->load->library('email');
        $addOnData = json_decode($this->input->post('AddOns'));

        $client = new Client(
            $this->twilioSettingsArray[BaseTwilio::VOICE_ACCOUNT_SID]['stt_key_value'],
            $this->twilioSettingsArray[BaseTwilio::VOICE_AUTH_TOKEN]['stt_key_value']
        );

        $headers['User-Agent'] = 'twilio-php/' . VersionInfo::string() . ' (PHP ' . phpversion() . ')';
        $headers['Accept-Charset'] = 'utf-8';
        $headers['Accept'] = 'application/json';

        //TODO: костыль для ДЕМО, перенести в конфиг !!!
        $extname = 'nexiwave_voicemail2text';
        if (isset($addOnData->results->nexiwave_voicemail2text_2)) {
            $extname = 'nexiwave_voicemail2text_2';
        }

        if ((isset($addOnData) && $addOnData != '') && (isset($addOnData->results) && $addOnData->results != '') && (isset($addOnData->results->$extname) && $addOnData->results->$extname != '') && (isset($addOnData->results->$extname->links) && $addOnData->results->$extname->links != '') && (isset($addOnData->results->$extname->links->recording) && $addOnData->results->$extname->links->recording != '')) {
            $recordingLink = $addOnData->results->$extname->links->recording;
            $recordingFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Record-' . uniqid() . '.wav';
            $recordingBlob = file_get_contents($recordingLink);
            file_put_contents($recordingFile, $recordingBlob);

            $recordingURI = explode('/', $recordingLink);
            $recordingSid = $recordingURI[count($recordingURI) - 1];//countOk
            $callData = $client->recordings($recordingSid)->fetch();

            $call = $this->mdl_calls->get_by(['call_twilio_sid' => $callData->callSid]);
            if ($call) {
                $response = $client->getHttpClient()->request(
                    'GET',
                    $addOnData->results->$extname->payload[0]->url . '.json',
                    [],
                    [],
                    $headers,
                    $this->config->item('accountSid'),
                    $this->config->item('authToken')
                )->getContent();
                $data = isset($response['redirect_to']) ? json_decode(file_get_contents($response['redirect_to']),
                    true) : false;

                if (isset($data['text']) && $data['text']) {
                    $this->mdl_calls->update($call->call_id, ['call_text' => $data['text']]);

                    $emailTo = $this->config->item('account_email_address');
                    if ($call->call_user_id) {
                        $userData = $this->mdl_user->find_by_id($call->call_user_id);
                        if ($userData->user_email) {
                            $emailTo = $userData->user_email;
                        }
                    }

                    $subject = 'New Voicemail';

                    $config['mailtype'] = 'html';
                    $this->email->initialize($config);

                    $this->email->to($emailTo);
                    $this->email->from($this->config->item('account_email_address'),
                        $this->config->item('company_name_short'));
                    $this->email->subject($subject);
                    $this->email->attach($recordingFile);
                    $this->email->message('You Have a New Voicemail<br>Number: ' . $call->call_from . '<br>Message: "' . $data['text'] . '"');
                    $this->email->send();
                }
                @unlink($recordingFile);
            }
        }
        return false;
    }
}

