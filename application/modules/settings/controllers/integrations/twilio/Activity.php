<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\task_router\ActivityTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use Illuminate\Validation\ValidationException;

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class Activity Controller
 */
class Activity extends BaseController
{

    /**
     * @var array
     */
    const VALIDATE_RULES = [
        'friendlyName' => 'required',
        'available' => 'required|accepted',
    ];

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function index(string $workspaceSid)
    {
        $title = 'Soft twilio calls';
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        $activities = SoftTwilioActivityModel::getListByWorkspaceId($workspaceModel->id);

        $this->load->view('settings/integrations/twilio/activity/index', [
            'title' => $title,
            'activities' => $activities,
            'workspaceSid' => $workspaceSid
        ]);

    }

    /**
     * @param string $workspaceSid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create(string $workspaceSid)
    {
        $title = 'Soft twilio calls';
        $activityTwilio = new ActivityTwilio($workspaceSid);
        $data = [];

        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                $validatedRequest['available'] = $validatedRequest['available'] == 'on' ? true : false;
                $result = $activityTwilio->create($validatedRequest);
                if ($result) {
                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/activity');
                }
            }
        }
        $this->load->view('settings/integrations/twilio/activity/create', [
            'title' => $title,
            'data' => $data,
            'workspaceSid' => $workspaceSid,
        ]);
    }

    /**
     * @param string $workspaceSid
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(string $workspaceSid, string $sid)
    {
        $title = 'Soft twilio calls';
        $activityTwilio = new ActivityTwilio($workspaceSid);
        $activity = $activityTwilio->getActivityBySid($sid);

        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate(['friendlyName' => 'required']);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                $result = $activityTwilio->update($activity, $validatedRequest);
                if ($result) {
                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/activity');
                }
            }
        }

        $data['activity'] = $activity;

        $this->load->view('settings/integrations/twilio/activity/update', [
            'title' => $title,
            'data' => $data,
            'workspaceSid' => $workspaceSid,
        ]);
    }

    /**
     * @param string $workspaceSid
     * @param string $sid
     * @return bool
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete(string $workspaceSid, string $sid)
    {
        $activityTwilio = new ActivityTwilio($workspaceSid);
        try {
            $activityTwilio->delete($sid);
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        if (isset($data['error'])) {
            return $this->response(['error' => $data['error']], 200);
        }
        return $this->response(['data' => 'ok'], 200);
    }
}
