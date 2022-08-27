<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;

use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioTaskQueueModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkerModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkflowModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use DB;
use Exception;
use Throwable;
use Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance;
use Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance;
use Twilio\Rest\Taskrouter\V1\WorkspaceInstance;

/**
 * Class WorkspaceTwilio
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/taskrouter/api/workspace
 */
class WorkspaceTwilio extends BaseTwilio
{
    /**
     * @return array
     */
    public function getList()
    {
        $workspaces = $this->twilioClient->taskrouter->workspaces->read();
        $result = [];
        if (!is_null($workspaces) && !empty($workspaces)) {
            foreach ($workspaces as $workspace) {
                $result[] = [
                    'sid' => $workspace->sid,
                    'friendlyName' => $workspace->friendlyName,
                    'defaultActivityName' => $workspace->defaultActivityName,
                    'timeoutActivityName' => $workspace->timeoutActivityName,
                    'eventCallbackUrl' => $workspace->eventCallbackUrl
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Taskrouter\V1\WorkspaceInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getWorkspaceBySid(string $sid)
    {
        return $this->twilioClient->taskrouter->workspaces($sid)->fetch();
    }

    /**
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\WorkspaceInstance
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Exception
     */
    public function create(array $data)
    {
        $workspace = $this->twilioClient->taskrouter->workspaces->create($data['friendlyName'], [
            "eventCallbackUrl" => $data['eventCallbackUrl'],
            "template" => "FIFO",
        ]);
        try {
            \DB::beginTransaction();
            $workspaceModel = new SoftTwilioWorkspaceModel();
            $workspaceModel->setRawAttributes([
                'sid' => $workspace->sid,
                'friendlyName' => $workspace->friendlyName,
                'defaultActivityName' => $workspace->defaultActivityName,
                'timeoutActivityName' => $workspace->timeoutActivityName,
                'eventCallbackUrl' => $workspace->eventCallbackUrl,

            ])->save();

            $workSpace = $this->twilioClient->taskrouter->workspaces($workspace->sid);
            $activities = $workSpace->activities->read();
            foreach ($activities as $activity) {
                if ($activity->friendlyName == 'Unavailable') {
                    $activity = $activity->update(['friendlyName' => 'Busy']);
                    $busyActivity = $activity;
                } elseif ($activity->friendlyName == 'Available') {
                    $activity = $activity->update(['friendlyName' => 'Idle']);
                }
                /** @var ActivityInstance $activity */
                (new SoftTwilioActivityModel())->setRawAttributes([
                    'sid' => $activity->sid,
                    'workspace_id' => $workspaceModel->id,
                    'friendlyName' => $activity->friendlyName,
                    'available' => $activity->available
                ])->save();
            }
            $activityTwilio = new ActivityTwilio($workspace->sid);
            $reservedActivity = $activityTwilio->create(['friendlyName' => 'Reserved', 'available' => false]);
            $wrapUpActivity = $activityTwilio->create(['friendlyName' => 'WrapUp', 'available' => false]);
            $this->twilioClient->taskrouter->workspaces($workspace->sid)->update(["timeoutActivitySid" => $wrapUpActivity->sid]);

            $taskQueues = $workSpace->taskQueues->read();
            foreach ($taskQueues as $taskQueue) {
                $taskQueue = $taskQueue->update([
                    'targetWorkers' => static::SUPPORT_EXPRESSION,
                    'reservationActivitySid' => $reservedActivity->sid,
                    'assignmentActivitySid' => $busyActivity->sid,
                ]);
                $defaultTaskQueueSid = $taskQueue->sid;
                /** @var TaskQueueInstance $taskQueue */
                (new SoftTwilioTaskQueueModel())->setRawAttributes([
                    'sid' => $taskQueue->sid,
                    'workspace_id' => $workspaceModel->id,
                    'friendlyName' => $taskQueue->friendlyName,
                    'reservationActivitySid' => $taskQueue->reservationActivitySid,
                    'assignmentActivitySid' => $taskQueue->assignmentActivitySid,
                    'maxReservedWorkers' => $taskQueue->maxReservedWorkers,
                    'targetWorkers' => $taskQueue->targetWorkers,
                ])->save();
            }

            $workflows = $workSpace->workflows->read();
            foreach ($workflows as $workflow) {
                $configuration = json_decode($workflow->configuration, true);
                $configuration['task_routing']['filters'][0]['filter_friendly_name'] = '';
                $configuration['task_routing']['filters'][0]['expression'] = static::SUPPORT_EXPRESSION;
                $configuration['task_routing']['filters'][0]['targets'][0]['queue'] = $defaultTaskQueueSid;
                $configuration['task_routing']['filters'][0]['targets'][0]['order_by'] = WorkflowTwilio::WORKFLOW_FILTER_ORDER_BY;
                $configuration['task_routing']['default_filter']['queue'] = $defaultTaskQueueSid;
                $configuration['task_routing']['default_filter']['task_queue_sid'] = $defaultTaskQueueSid;

                $workflow = $workflow->update([
                    'assignmentCallbackUrl' => base_url('/callback/settings/voice-assignment'),
                    'configuration' => json_encode($configuration),
                    'taskReservationTimeout' => 30
                ]);
                $workflowModel = new SoftTwilioWorkflowModel();
                $workflowModel->setRawAttributes([
                    'sid' => $workflow->sid,
                    'workspace_id' => $workspaceModel->id,
                    'friendlyName' => $workflow->friendlyName,
                    'taskReservationTimeout' => $workflow->taskReservationTimeout,
                    'assignmentCallbackUrl' => $workflow->assignmentCallbackUrl,
                    'fallbackAssignmentCallbackUrl' => $workflow->fallbackAssignmentCallbackUrl,
                    'configuration' => $workflow->configuration
                ])->save();
            }

            \DB::commit();
        } catch (Throwable $e) {
            // Woopsy
            \DB::rollback();
            $workSpace = $this->twilioClient->taskrouter->workspaces($workspace->sid);
            $workSpace->delete();
            throw new \Exception($e->getMessage());
        }

        return $workspace;
    }

    /**
     * @param WorkspaceInstance $workspace
     * @param array $data
     * @return WorkspaceInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(WorkspaceInstance $workspace, array $data)
    {
        $result = $workspace->update($data);
        if (is_array($result)) {
            SoftTwilioWorkspaceModel::updateBySid($workspace->sid, $data);
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return bool
     * @throws \Twilio\Exceptions\TwilioException
     * @throws Exception
     */
    public function delete(string $sid)
    {
        $result = $this->twilioClient->taskrouter->workspaces($sid)->delete();
        if ($result) {
            try {
                DB::beginTransaction();
                $workspace = SoftTwilioWorkspaceModel::findBySid($sid);
                SoftTwilioWorkerModel::deleteByWorkspaceId($workspace->id);
                SoftTwilioTaskQueueModel::deleteByWorkspaceId($workspace->id);
                SoftTwilioActivityModel::deleteByWorkspaceId($workspace->id);
                SoftTwilioWorkflowModel::deleteByWorkspaceId($workspace->id);
                SoftTwilioWorkerModel::deleteByWorkspaceId($workspace->id);
                SoftTwilioWorkspaceModel::deleteBySid($sid);
                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }

        }
        return $result;
    }
}
