<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\task_router\WorkspaceTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioTaskQueueModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkflowModel;
use Illuminate\Validation\ValidationException;

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class Workspace
 */
class Workspace extends BaseController
{

    /**
     * @var array
     */
    const VALIDATE_RULES = [
        'friendlyName' => 'required|string',
        'eventCallbackUrl' => 'required|string', //example: /client_twilio_calls/tasksCallback
    ];

    /**
     * @param $workspaceSid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function overview($workspaceSid)
    {
        $data['title'] = 'Soft twilio calls';
        if (!$workspaceSid) {
            redirect('settings/integrations/twilio/workspace/index');
        }

        $workspaceTwilio = new WorkspaceTwilio();
        /** @var Twilio\Rest\Taskrouter\V1\WorkspaceInstance $workspace */
        $data['workspace'] = $workspaceTwilio->getWorkspaceBySid($workspaceSid);
        $data['workspaceSid'] = $workspaceSid;

        $this->load->view('settings/integrations/twilio/workspace/overview', $data);
    }

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function index()
    {
        $workspaces = SoftTwilioWorkspaceModel::getList();
        $this->load->view('settings/integrations/twilio/workspace/index', [
            'workspaces' => $workspaces
        ]);
    }

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create()
    {
        $workspaceTwilio = new WorkspaceTwilio();
        $data = [];

        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->getMessageBag()->getMessages();
            }

            if (!isset($data['errors'])) {
                try {
                    $result = $workspaceTwilio->create($validatedRequest);
                } catch (Exception $e) {
                    return $this->response(['error' => $e->getMessage()], 200);
                }
                if ($result) {
                    return $this->response(['data' => 'ok'], 200);
                }
            } else {
                return $this->response(['error' => $data['error']], 200);
            }
        }

        $this->load->view('settings/integrations/twilio/workspace/create', [
            'data' => $data
        ]);
    }

    /**
     * @param $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update($sid)
    {
        $workspaceTwilio = new WorkspaceTwilio();
        /** @var Twilio\Rest\Taskrouter\V1\WorkspaceInstance $workspace */
        $workspace = $workspaceTwilio->getWorkspaceBySid($sid);

        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->getMessageBag()->getMessages();
            }

            if (!isset($data['errors'])) {
                $result = $workspaceTwilio->update($workspace, $validatedRequest);
            }
        }
        redirect('settings/integrations/twilio/workspace/overview/' . $sid);
    }

    /**
     * @param $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete($sid)
    {
        $workspaceTwilio = new WorkspaceTwilio();
        if ($workspaceTwilio->delete($sid)) {
            return $this->response(['data' => 'ok'], 200);
        }
    }

    /**
     * @param string $workspaceSid
     */
    public function get_data_by_sid(string $workspaceSid)
    {
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        $taskQueues = SoftTwilioTaskQueueModel::getListByWorkspaceId($workspaceModel->id);
        $workflows = SoftTwilioWorkflowModel::getListByWorkspaceId($workspaceModel->id);

        return $this->response([
            'workflows' => $workflows,
            'taskQueues' => $taskQueues,
            'workspaceSid' => $workspaceSid,
            'workspace_id' => $workspaceModel->id
        ], 200);
    }
}
