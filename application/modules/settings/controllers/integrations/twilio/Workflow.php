<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\task_router\TaskQueueTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\WorkflowTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkflowModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use Illuminate\Validation\ValidationException;

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class Workflow
 */
class Workflow extends BaseController
{

    /**
     * @var array
     */
    const VALIDATE_RULES = [
        'friendlyName' => 'required|unique:soft_twilio_workflows,friendlyName',
        'fallbackAssignmentCallbackUrl' => 'url|string',
    ];

    /**
     * @param string $workspaceSid
     */
    public function index(string $workspaceSid)
    {
        $title = 'Soft twilio calls';
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        $workflows = SoftTwilioWorkflowModel::getListByWorkspaceId($workspaceModel->id);
        $this->load->view('settings/integrations/twilio/workflow/index', [
            'title' => $title,
            'workflows' => $workflows,
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
        $workflowTwilio = new WorkflowTwilio($workspaceSid);
        $taskQueues = (new TaskQueueTwilio($workspaceSid))->getListAllTaskQueues();
        $data = [];

        if (request()->isMethod('POST')) {
            try {
                request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                $workflowData  = $this->getWorkflowData(request()->input());
                $result = $workflowTwilio->create($workflowData);
                if ($result) {
                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/workflow');
                }
            }
        }
        $this->load->view('settings/integrations/twilio/workflow/create', [
            'title' => $title,
            'data' => $data,
            'workspaceSid' => $workspaceSid,
            'taskQueues' => $taskQueues
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
        $workflowTwilio = new WorkflowTwilio($workspaceSid);
        $workflow = $workflowTwilio->getWorkflowBySid($sid);

        if (request()->isMethod('POST')) {
            try {
                request()->validate([
                    'friendlyName' => 'required',
                    'fallbackAssignmentCallbackUrl' => 'url|string',
                ]);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                $workflowData  = $this->getWorkflowData(request()->input());
                $result = $workflowTwilio->update($workflow, $workflowData);
                if ($result) {
                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/workflow');
                }
            }
        }

        $data['workflow'] = $workflow;
        $taskQueues = (new TaskQueueTwilio($workspaceSid))->getListAllTaskQueues();
        $workflowConfiguration = json_decode($workflow->configuration);

        $this->load->view('settings/integrations/twilio/workflow/update', [
            'title' => $title,
            'data' => $data,
            'taskQueues' => $taskQueues,
            'workspaceSid' => $workspaceSid,
            'workflowConfiguration' => $workflowConfiguration
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
        $workflowTwilio = new WorkflowTwilio($workspaceSid);
        /** @var WorkflowTwilio $workflowTwilio */
        if ($workflowTwilio->delete($sid)) {
            return $this->response(['data' => 'ok'], 200);
        }
    }

    /**
     * @param $request
     * @return array
     */
    private function getWorkflowData($request)
    {
        $result = [];
        if (!empty($request)) {
            foreach ($request as $key => $value) {
                if ($key == 'task_routing') {
                    if (!$value['default_filter']['queue']) {
                        unset($value['default_filter']);
                    }

                    $result['configuration'] = json_encode([$key => $value]);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}
