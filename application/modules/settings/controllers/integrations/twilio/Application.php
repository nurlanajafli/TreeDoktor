<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\accounts\ApplicationTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioApplicationModel;
use Illuminate\Validation\ValidationException;

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class AccountsApplicationController
 */
class Application extends BaseController
{

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function index()
    {
        $applications = (new SoftTwilioApplicationModel())->getList();
        $this->load->view('settings/integrations/twilio/application/index', [
            'applications' => $applications,
        ]);
    }

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create()
    {
        $applicationTwilio = new ApplicationTwilio();
        $data = [];

        if (request()->isMethod('POST')) {
            try {
                //todo:: add voice/STATUS CALLBACK URL
                $validatedRequest = request()->validate([
                    'friendlyName' => 'required|string',
                    'voiceUrl' => 'required|string',
                    'statusCallback' => 'string',
                    'apiVersion' => 'required|string',
                    'voiceMethod' => 'required|string',
                ]);

            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                $result = $applicationTwilio->create($validatedRequest);
                if ($result) {
                    return $this->response(['data' => 'ok'], 200);
                }
            }
            return $this->response(['error' => $data['error']], 200);
        }
        $this->load->view('settings/integrations/twilio/application/create', [
            'data' => $data,
        ]);
    }

    /**
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(string $sid)
    {
        $applicationTwilio = new ApplicationTwilio();
        $application = $applicationTwilio->getBySid($sid);
        $data = [];
        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate([
                    'friendlyName' => 'required',
                    'voiceUrl' => 'required',
                    'statusCallback' => 'required|string',
                ]);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }

            if (!isset($data['errors'])) {
                $result = $applicationTwilio->update($application, $validatedRequest);
                if ($result) {
                    return $this->response(['data' => 'ok'], 200);
                }
            }
            return $this->response(['data' => $data['errors']], 200);
        }

        $data['application'] = $application;

        $this->load->view('settings/integrations/twilio/application/update', [
            'data' => $data,
            'sid' => $sid
        ]);
    }

    /**
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete(string $sid)
    {
        $applicationTwilio = new ApplicationTwilio();
        if ($applicationTwilio->delete($sid)) {
            return $this->response(['data' => 'ok'], 200);
        }
    }
}