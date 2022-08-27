<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;

use application\modules\settings\integrations\twilio\classes\task_router\BaseTaskRouterClient;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;
use Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance;

/**
 * Class ActivityModel
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/taskrouter/api/activity
 */
class ActivityTwilio extends BaseTaskRouterClient
{
    /**
     * @return array
     */
    public function getList()
    {
        $activities = $this->workSpace->activities->read();
        $result = [];
        if (!is_null($activities) && !empty($activities)) {
            foreach ($activities as $activity) {
                $result[] = [
                    'sid' => $activity->sid,
                    'friendlyName' => $activity->friendlyName,
                    'available' => $activity->available,
                ];
            }
        }
        return $result;
    }

    /**
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance[]
     */
    public function getAllActivities()
    {
        return $this->workSpace->activities->read([]);
    }

    /**
     * @param bool $available
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance[]
     */
    public function getActivitiesByAvailable(bool $available)
    {
        return $this->workSpace->activities->read(['available' => $available]);
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getActivityBySid(string $sid)
    {
        return $this->workSpace->activities($sid)->fetch();
    }

    /**
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create(array $data)
    {
        $friendlyName = $data['friendlyName'];
        $activity = $this->workSpace->activities->create($friendlyName, ['available' => $data['available']]);
        if ($activity) {
            $activityModel = new SoftTwilioActivityModel();
            $data['sid'] = $activity->sid;
            $data['workspace_id'] = $this->modelWorkspaceId;
            $data['friendlyName'] = $activity->friendlyName;
            $data['available'] = $activity->available;
            $activityModel->setRawAttributes($data)->save();
        }
        return $activity;
    }

    /**
     * @param \Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance $activity
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(ActivityInstance $activity, array $data)
    {
        $result = $activity->update($data);
        if ($result instanceof ActivityInstance) {
            SoftTwilioActivityModel::updateBySid($activity->sid, $data);
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
        $result = $this->workSpace->activities($sid)->delete();
        if ($result) {
            SoftTwilioActivityModel::deleteBySid($sid);
        }
        return $result;
    }
}
