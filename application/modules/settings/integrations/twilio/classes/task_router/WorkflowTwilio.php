<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;

use application\modules\settings\integrations\twilio\classes\task_router\BaseTaskRouterClient;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkflowModel;
use Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance;

/**
 * Class WorkflowModel
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/taskrouter/api/workflow
 */
class WorkflowTwilio extends BaseTaskRouterClient
{
    const WORKFLOW_FILTER_ORDER_BY = 'worker.level DESC';

    /**
     * @return array
     */
    public function getList()
    {
        $workflows = $this->workSpace->workflows->read();
        $result = [];
        if (!is_null($workflows) && !empty($workflows)) {
            foreach ($workflows as $workflow) {
                $result[] = [
                    'sid' => $workflow->sid,
                    'friendlyName' => $workflow->friendlyName,
                    'taskReservationTimeout' => $workflow->taskReservationTimeout,
                    'assignmentCallbackUrl' => $workflow->assignmentCallbackUrl,
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getWorkflowBySid(string $sid)
    {
        return $this->workSpace->workflows($sid)->fetch();
    }

    /**
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create(array $data)
    {
        $workflow = $this->workSpace->workflows->create($data['friendlyName'], $data['configuration'], [
            'taskReservationTimeout' => $data['taskReservationTimeout'],
            'assignmentCallbackUrl' => $data['assignmentCallbackUrl'],
            'fallbackAssignmentCallbackUrl' => $data['fallbackAssignmentCallbackUrl'],
        ]);
        if ($workflow) {
            $workflowModel = new SoftTwilioWorkflowModel();
            $data['sid'] = $workflow->sid;
            $data['workspace_id'] = $this->modelWorkspaceId;
            $workflowModel->setRawAttributes($data)->save();
        }
        return $workflow;
    }

    /**
     * @param \Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance $workflow
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkflowInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(WorkflowInstance $workflow, array $data)
    {
        $result = $workflow->update($data);
        if ($result instanceof WorkflowInstance) {
            SoftTwilioWorkflowModel::updateBySid($workflow->sid, $data);
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return bool
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete(string $sid)
    {
        $result = $this->workSpace->workflows($sid)->delete();
        if ($result) {
            SoftTwilioWorkflowModel::deleteBySid($sid);
        }
        return $result;
    }
}
