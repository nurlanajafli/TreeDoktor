<?php

use application\modules\settings\integrations\twilio\classes\task_router\ActivityTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\TaskQueueTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioTaskQueueModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use Illuminate\Validation\ValidationException;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class Task_queues
 */
class TaskQueue extends BaseController
{
    const VALIDATE_RULES = [
        'friendlyName' => 'required',
        'reservationActivitySid' => 'required',
        'assignmentActivitySid' => 'required',
        'maxReservedWorkers' => 'required',
        'targetWorkers' => 'required',
    ];

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function index(string $workspaceSid)
    {
        $title = 'Soft twilio calls';
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        $taskQueues = SoftTwilioTaskQueueModel::getListByWorkspaceId($workspaceModel->id);
        $this->load->view('settings/integrations/twilio/task_queue/index', [
            'title' => $title,
            'taskQueues' => $taskQueues,
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
        $taskQueueTwilio = new TaskQueueTwilio($workspaceSid);
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        if (is_null($workspaceModel)) {
            redirect('settings/integrations/twilio/workspace/' . $workspaceSid);
        }

        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }

            if (!isset($data['errors'])) {
                $result = $taskQueueTwilio->create($validatedRequest);
                if ($result) {
                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/task-queue');
                }
            }
        }
        $data['unavailableActivities'] = SoftTwilioActivityModel::getActivitiesByAvailableByWorkspace($workspaceModel->id, 0);
        $this->load->view('settings/integrations/twilio/task_queue/create', [
            'title' => $title,
            'data' => $data,
            'workspaceSid' => $workspaceSid,
        ]);
    }

    /**
     * @param string $workspaceSid
     * @param bool $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function task_queue_save(string $workspaceSid, $sid = false)
    {
        $taskQueueTwilio = new TaskQueueTwilio($workspaceSid);
        $errorMessage = null;
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        if (is_null($workspaceModel)) {
            return $this->response(['error' => 'Workspace not found'], 400);
        }

        if (request()->ajax() && request()->isMethod('GET')) {
            $taskQueue = ($sid !== false && !empty($sid)) ? $taskQueueTwilio->findBySid($sid) : null;

            $data['unavailableActivities'] = SoftTwilioActivityModel::getActivitiesByAvailableByWorkspace($workspaceModel->id, 0);
            $data['sid'] = $taskQueue->sid ?? false;
            $data['friendlyName'] = $taskQueue->friendlyName ?? false;
            $data['reservationActivitySid'] = $taskQueue->reservationActivitySid ?? false;
            $data['assignmentActivitySid'] = $taskQueue->assignmentActivitySid ?? false;
            $data['maxReservedWorkers'] = $taskQueue->maxReservedWorkers ?? false;
            $data['targetWorkers'] = $taskQueue->targetWorkers ?? false;
            $data['workspaceSid'] = $workspaceSid;

            return $this->response([
                'data' => $this->load->view('settings/integrations/twilio/task_queue/task_queue_modal_form', $data, true)
            ]);
        }

        $data['friendlyName'] = $this->input->post('friendlyName');
        $data['reservationActivitySid'] = $this->input->post('reservationActivitySid');
        $data['assignmentActivitySid'] = $this->input->post('assignmentActivitySid');
        $data['maxReservedWorkers'] = $this->input->post('maxReservedWorkers');
        $data['targetWorkers'] = $this->input->post('targetWorkers');
        $sid = $this->input->post('sid') ?? '';

        try {
            if (!empty($sid)) {
                $taskQueue = $taskQueueTwilio->findBySid($sid);
                $newTaskQueue = $taskQueueTwilio->update($taskQueue, $data);
            } else {
                $newTaskQueue = $taskQueueTwilio->create($data);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }

        if (!is_null($errorMessage)) {
            return $this->response(['error' => $errorMessage], 400);
        }

        return $this->response([
            'taskQueue' => [
                'sid' => $newTaskQueue->sid,
                'friendlyName' => $newTaskQueue->friendlyName,
            ]
        ], 200);
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
        $taskQueueTwilio = new TaskQueueTwilio($workspaceSid);
        $taskQueue = $taskQueueTwilio->findBySid($sid);
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        if (is_null($workspaceModel)) {
            redirect('settings/integrations/twilio/workspace/' . $workspaceSid);
        }

        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }
            if (!isset($data['errors'])) {
                $result = $taskQueueTwilio->update($taskQueue, $validatedRequest);
                if ($result) {
                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/task-queue');
                }
            }
        }
        $data['unavailableActivities'] = SoftTwilioActivityModel::getActivitiesByAvailableByWorkspace($workspaceModel->id, 0);
        $data['taskQueue'] = $taskQueue;
        $this->load->view('settings/integrations/twilio/task_queue/update', [
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
        $taskQueueTwilio = new TaskQueueTwilio($workspaceSid);
        if ($taskQueueTwilio->removeTaskQueue($sid)) {
            return $this->response(['data' => 'ok'], 200);
        }
    }

    /**
     * @param string $workspaceSid
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function task_queue_delete(string $workspaceSid, string $sid)
    {
        if (empty($sid) || !$sid) {
            return $this->response(['error' => 'Bad request'], 400);
        }
        $result = (new TaskQueueTwilio($workspaceSid))->removeTaskQueue($sid);
        if ($result) {
            return $this->response(['success' => $result], 200);
        }
        return $this->response(['error' => 'Something went wrong'], 400);
    }
}
