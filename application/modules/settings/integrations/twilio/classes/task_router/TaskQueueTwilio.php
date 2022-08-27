<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;

use application\modules\settings\models\integrations\twilio\SoftTwilioTaskQueueModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkflowModel;
use Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance;

/**
 * Class TaskQueueTwilio
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/taskrouter/api/task-queue
 */
class TaskQueueTwilio extends BaseTaskRouterClient
{
    /**
     * @return array
     */
    public function getListAllTaskQueues()
    {
        $result = [];
        $taskQueues = $this->getAllTaskQueues();
        if (!is_null($taskQueues) && !empty($taskQueues)) {
            foreach ($taskQueues as $taskQueue) {
                $result[] = [
                    'sid' => $taskQueue->sid,
                    'friendlyName' => $taskQueue->friendlyName,
                    'assignmentActivitySid' => $taskQueue->assignmentActivitySid,
                    'reservationActivitySid' => $taskQueue->reservationActivitySid,
                    'maxReservedWorkers' => $taskQueue->maxReservedWorkers,
                    'targetWorkers' => $taskQueue->targetWorkers
                ];
            }
        }
        return $result;
    }

    /**
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance[]
     */
    public function getAllTaskQueues()
    {
        return $this->workSpace->taskQueues->read([]);
    }

    /**
     * @param array $options
     * @return array | \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance
     * @throws \Twilio\Exceptions\TwilioException
     * @throws \Exception
     */
    public function create($options = [])
    {
        if (in_array('friendlyName', $options) || in_array('friendlyName', array_keys($options))) {
            $friendlyName = $options['friendlyName'];
            unset($options['friendlyName']);
        } else {
            throw new \Exception('Not found friendlyName attribute at options array given');
        }
        $taskQueue = $this->workSpace->taskQueues->create($friendlyName, $options);
        if ($taskQueue) {
            $taskQueueModel = new SoftTwilioTaskQueueModel();
            $data['sid'] = $taskQueue->sid;
            $data['workspace_id'] = $this->modelWorkspaceId;
            $data['friendlyName'] = $taskQueue->friendlyName;
            $data['reservationActivitySid'] = $taskQueue->reservationActivitySid;
            $data['assignmentActivitySid'] = $taskQueue->assignmentActivitySid;
            $data['maxReservedWorkers'] = $taskQueue->maxReservedWorkers;
            $data['targetWorkers'] = $taskQueue->targetWorkers;
            $taskQueueModel->setRawAttributes($data)->save();
        }
        return $taskQueue;
    }

    /**
     * @param TaskQueueInstance $taskQueue
     * @param array $data
     * @return TaskQueueInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(TaskQueueInstance $taskQueue, array $data)
    {
        $result = $taskQueue->update($data);
        if ($result instanceof TaskQueueInstance) {
            SoftTwilioTaskQueueModel::updateBySid($taskQueue->sid, $data);
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function findBySid(string $sid)
    {
        return $this->workSpace->taskQueues($sid)->fetch();
    }

    /**
     * @param string $sid
     * @return bool
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function removeTaskQueue(string $sid)
    {
        $result = $this->workSpace->taskQueues($sid)->delete();
        if ($result) {
            SoftTwilioTaskQueueModel::deleteBySid($sid);
        }
        return $result;
    }
}
