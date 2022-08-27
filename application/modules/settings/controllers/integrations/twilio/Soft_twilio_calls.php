<?php

use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use application\modules\settings\integrations\twilio\classes\accounts\ApplicationTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkerModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioApplicationModel;
use application\modules\settings\integrations\twilio\libraries\Applet;
use application\modules\settings\integrations\twilio\libraries\Plugin;
use application\modules\settings\integrations\twilio\libraries\TwimlResponse;
use application\modules\settings\models\integrations\twilio\SoftTwilioCallsFlow;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use application\modules\user\models\User;
use application\modules\settings\models\Settings;
use application\modules\settings\models\integrations\twilio\SoftTwiliosoftAudioFiles;
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
use Illuminate\Support\Facades\Storage;
use Twilio\Exceptions\RestException;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

define('VBX_ROOT', dirname(substr(BASEPATH, 0, strlen(BASEPATH) - 1)) . '');
define('VBX_PARENT_TENANT', 1);
define('PLUGIN_PATH', VBX_ROOT . '/assets/OpenVBX/plugins');

/**
 * Class Soft_twilio_calls
 */
class Soft_twilio_calls extends MX_Controller
{

    protected $response;
    private $flow;
    private $flow_id;
    private $flow_type = 'voice';
    protected $say_params;

    /**
     * @var array
     */
    protected $twilioSettingsArray = [];

    protected $voiceAccountTwilio;
    protected $smsAccountTwilio;

    /**
     * Soft_twilio_calls constructor.
     * @throws \Twilio\Exceptions\TwimlException
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

        $this->twilioSettingsArray = Settings::getTwilioSettings(true);

        if (empty($this->twilioSettingsArray)) {
            redirect('/settings/integrations/twilio/install');
        }
        /** Check Twilio Voice Credentials */
        try {
            $this->voiceAccountTwilio = new AccountTwilio();
            $this->voiceAccountTwilio->getIncomingPhoneNumbers();
        } catch (\Twilio\Exceptions\RestException $e) {
            $mess = message('alert', 'Voice Twilio credentials has been invalidate');
            $this->session->set_flashdata('user_message', $mess);
            redirect('/settings/integrations/twilio/install?restore=1');
        }
        /** //Check Twilio Voice Credentials */

        if (isset($this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]) && isset($this->twilioSettingsArray[BT::SMS_AUTH_TOKEN])) {
            /** Check Twilio Sms Credentials */
            try {
                $this->smsAccountTwilio = new AccountTwilio(
                    $this->twilioSettingsArray[BT::SMS_ACCOUNT_SID]["stt_key_value"],
                    $this->twilioSettingsArray[BT::SMS_AUTH_TOKEN]["stt_key_value"]
                );
                $this->smsAccountTwilio->getMessagingServices();
            } catch (\Twilio\Exceptions\RestException $e) {
                $mess = message('alert', 'Sms Twilio credentials has been invalidate');
                $this->session->set_flashdata('user_message', $mess);
                redirect('/settings/integrations/twilio/install/sms?restore=1');
            }
            /** //Check Twilio Sms Credentials */
        }

        $this->_title = SITE_NAME;
        $this->response = new TwimlResponse();
    }

    /**
     * Index action
     */
    public function index()
    {
        $title = $this->_title . ' - Twilio Settings';
        $flows = SoftTwilioCallsFlow::all();
        $applications = SoftTwilioApplicationModel::all();
        $activeNumbers = $this->voiceAccountTwilio->getIncomingPhoneNumbers();
        $workspaces = SoftTwilioWorkspaceModel::all();
        $isSmsInstalled = false;
        $isMessangerShow = isset($this->twilioSettingsArray['messenger']) ? (bool) $this->twilioSettingsArray['messenger']["stt_key_value"] : false;
        $messagingServices = [];

        if (isset($this->twilioSettingsArray[BT::SMS_AUTH_TOKEN])) {
            $isSmsInstalled = true;
            $messagingServices = $this->smsAccountTwilio->getMessagingServices();
        }
        $this->load->view('settings/integrations/twilio/index', compact(
            'flows',
            'applications',
            'activeNumbers',
            'workspaces',
            'messagingServices',
            'isSmsInstalled',
            'isMessangerShow',
            'title'
        ));
    }

    /**
     * Flow list action
     */
    public function flows()
    {
        $data['title'] = 'Soft twilio calls';
        $flows = SoftTwilioCallsFlow::all();
        $application = SoftTwilioApplicationModel::allNotAssignedToFlow()->toArray();

        $flows_with_numbers = [];
        foreach ($flows as $flow) {
            $flows_with_numbers[] = [
                'id' => $flow->id,
                'name' => trim($flow->name),
                'numbers' => '',
                'voice_data' => $flow->data,
                'sms_data' => $flow->sms_data,
            ];
        }
        $data['highlighted_flows'] = [$this->session->flashdata('flow-first-save', 0)];
        $data['items'] = $flows_with_numbers;
        $data['application'] = $application;

        $this->load->view('settings/integrations/twilio/flows', $data);
    }

    /**
     * Create action
     */
    public function create()
    {
        $flow = new SoftTwilioCallsFlow();
        $flow->name = trim($this->input->post('name'));
        $application_id = trim($this->input->post('application_id'));

        try {
            $flow->save();
            if ($application_id == 0) {
                (new ApplicationTwilio())->initialize($this->config->item('company_name_short'), $flow->id);
            } else {
                $applicationModel = SoftTwilioApplicationModel::findOrFail($application_id);
                $applicationModel->flow_id = $flow->id;
                $applicationModel->save();
            }

        } catch (Exception $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        return $this->response(['url' => site_url('/settings/integrations/twilio/edit/' . $flow->id)], 200);
    }

    /**
     * Return view modal form
     */
    public function get_flow_modal_form()
    {
        $applications = SoftTwilioApplicationModel::allNotAssignedToFlow();
        $this->load->view('settings/integrations/twilio/add_flow_modal', ['applications' => $applications]);
    }

    /**
     * Delete action
     * @param $id
     * @throws Exception
     */
    public function delete($id)
    {
        $flow = SoftTwilioCallsFlow::findOrFail($id);

        if (!is_null($flow)) {
            $application = $flow->app()->first();
            if (!is_null($application)) {
                (new ApplicationTwilio())->disconnect($application);
            }
            $flow->delete();
        } else {
            $message = "Flow $id does not exist.";
            $this->session->set_flashdata('error', $message);
        }
        return $this->response(['url' => site_url('/settings/integrations/twilio/index')], 200);
    }

    /**
     * @param $id
     * @param string $type
     */
    public function edit($id, $type = 'voice')
    {
        $data['title'] = $this->_title . ' - Soft twilio calls edit flow';
        $applets = Applet::get_applets($type);

        $flow = SoftTwilioCallsFlow::findOrFail($id);
        if (is_null($flow)) {
            $this->session->set_flashdata('error', "Flow $id does not exist.");
            redirect('/settings/integrations/twilio/flows');
        }

        $flow_data = array();
        $flow_obj = null;
        switch ($type) {
            case 'sms':
                if (!is_null($flow->sms_data)) {
                    $flow_obj = json_decode($flow->sms_data);
                }
                break;
            case 'voice':
                if (!is_null($flow->data)) {
                    $flow_obj = json_decode($flow->data);
                }
                break;
        }
        if (!is_null($flow_obj)) {
            $flow_data = get_object_vars($flow_obj);
        }

        // add start instance if it's not there
        if (!isset($flow_data['start'])) {
            $temp_start = new stdClass();
            $temp_start->name = 'Flow Start';
            $temp_start->id = 'start';
            $temp_start->type = 'standard---start';
            $temp_start->data = false;
            $temp_start->sms_data = false;
            $flow_data['start'] = $temp_start;
        }

        Applet::$flow_data =& $flow_data;    // make flow data visible to all applets
        $data['flow_data'] = $flow_data;
        $data['applets'] = $applets;
        $data['editor_type'] = $type;
        $data['flow'] = $flow;

        $this->load->view('settings/integrations/twilio/flow', $data);
    }

    /**
     * Add file method
     */
    public function add_file()
    {
        $json = ['error' => false, 'message' => ''];

        if (!empty($_FILES) && isset($_FILES["audio_twilio_upload"]) && $_FILES["audio_twilio_upload"]["error"] == UPLOAD_ERR_OK) {
            $file = $_FILES["audio_twilio_upload"];
            $fileId = $_POST['fileId'];
            $index = 0;

            $name_parts = explode('.', $file['name']);
            $ext = $name_parts[count($name_parts) - 1];

            if (in_array(strtolower($ext), ['wav', 'mp3'])) {
                // Can we write to our audio upload directory?
                $audioUploadsPath = 'uploads/open_vbx/';

                $targetFile = null;

                // Make sure we pick a name that's not already in use...
                while ($targetFile == null) {
                    $candidate = $audioUploadsPath . md5(uniqid($file['name'])) . '.' . $ext;

                    if (!file_exists($candidate)) {
                        // We can use this filename
                        $targetFile = $candidate;
                        break;
                    }
                }

                $fileBlob = request()->file('audio_twilio_upload');
                Storage::put($audioUploadsPath . $fileId, file_get_contents($fileBlob));

                // Return the URL for our newly created file
                $json['url'] = site_url("/uploads/open_vbx/" . $fileId);

                // And, make a record in the database
                $audioFile = new SoftTwiliosoftAudioFiles();
                $audioFile->label = "Upload of " . $file['name'];
                $audioFile->user_id = intval($this->session->userdata('user_id'));
                $audioFile->url = $json['url'];
                $audioFile->tag = $this->input->post('tag');
                $audioFile->save();

                // We return the label so that this upload can be added the library UI without
                // refreshing the page.
                $json = [
                    'chunkIndex' => $index,         // the chunk index processed
                    'initialPreview' => $json['url'], // the thumbnail preview data (e.g. image)
                    'initialPreviewConfig' => [
                        [
                            'type' => $file['type'],      // check previewTypes (set it to 'other' if you want no content preview)
                            'caption' => $file['name'], // caption
                            'key' => basename($targetFile),       // keys for deleting/reorganizing preview
                            'fileId' => $fileId,    // file identifier
                            'size' => $file['size'],    // file size
                            'zoomData' => $json['url'], // separate larger zoom data
                        ]
                    ],
                    'append' => true
                ];
            } else {
                $json['error'] = true;
                $json['message'] = 'Unsupported file format.  Only MP3 and WAV files are supported.';
            }
        } else {
            $json['error'] = true;
            $json['message'] = 'No files were found in the upload.';
            if (isset($_FILES["fileBlob"])) {
                $error = $_FILES["fileBlob"]["error"];
                switch ($error) {
                    case UPLOAD_ERR_INI_SIZE:
                        $json['message'] = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $json['message'] = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $json['message'] = 'The uploaded file was only partially uploaded';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $json['message'] = 'No file was uploaded';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $json['message'] = 'Missing a temporary folder';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $json['message'] = 'Failed to write file to disk';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $json['message'] = 'File upload stopped by extension';
                        break;
                }
            }
        }

        return $this->response($json);
    }

    /**
     * @throws Exception
     */
    public function remove_file()
    {
        $url = site_url("/assets/OpenVBX/audio-uploads/" . request()->input('key'));
        $audioFile = SoftTwiliosoftAudioFiles::where('url', '=', $url)->first();
        if($audioFile) {
            $audioFile->delete();
        }
        $path = "assets/OpenVBX/audio-uploads/" . request()->input('key');
        unset($path);
        return $this->response(['data' => 'ok'], 200);
    }

    /**
     * Save action
     * @param $flow_id
     */
    public function save($flow_id)
    {
        $error = false;
        $message = '';

        $flow = new SoftTwilioCallsFlow();
        if ($flow_id > 0) {
            $flow = SoftTwilioCallsFlow::findOrFail($flow_id);
            if (empty($flow)) {
                $error = true;
                $message = 'Flow does not exist.';
            }
        }

        $flow->name = trim($this->input->post('name'));
        $voice_data = $this->input->post('data');
        $sms_data = $this->input->post('sms_data');

        if (!empty($voice_data)) {
            $flow->data = $voice_data;
        }

        if (!empty($sms_data)) {
            $flow->sms_data = $sms_data;
        }

        try {
            $flow->save();
            $this->session->set_flashdata('flow-first-save', $flow->id);
        } catch (Exception $e) {
            $error = true;
            $message = 'Failed to save flow.';
        }

        $flow_url = site_url('/settings/integrations/twilio/edit/' . $flow->id);

        if ($this->response_type != 'json') {
            return redirect($flow_url);
        }

        $data['json'] = [
            'error' => $error,
            'message' => $message,
            'flow_id' => $flow->id,
            'flow_url' => $flow_url
        ];

        $this->respond('Call Flows', 'flows', $data);
    }


    /**
     * Get users method
     */
    function get_users()
    {
        $users = SoftTwilioWorkerModel::allWithUsers();
        return $this->response([
            $this->load->view('settings/integrations/twilio/users', ['users' => $users], true)
        ]);
    }
}
