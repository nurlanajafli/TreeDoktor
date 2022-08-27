<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;

use application\modules\settings\integrations\twilio\classes\task_router\BaseTaskRouterClient;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkerModel;
use application\modules\user\models\User;

/**
 * Class WrokerModel
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/taskrouter/api/worker
 */
class WorkerTwilio extends BaseTaskRouterClient
{
    /**
     * @return array
     */
    public function getList()
    {
        $workers = $this->workSpace->workers->read();
        $result = [];
        if (!is_null($workers) && !empty($workers)) {
            foreach ($workers as $worker) {
                $result[] = [
                    'sid' => $worker->sid,
                    'friendlyName' => $worker->friendlyName,
                    'activityName' => $worker->activityName,
                    'available' => $worker->available
                ];
            }
        }
        return $result;
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getWorkerBySid(string $sid)
    {
        return $this->workSpace->workers($sid)->fetch();
    }

    /**
     * @param string $friendlyName
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create(string $friendlyName, array $data)
    {
        return $this->workSpace->workers->create($friendlyName, $data);
    }

    /**
     * @param string $sid
     * @param array $data
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(string $sid, array $data)
    {
        $worker = $this->getWorkerBySid($sid);
        return $worker->update($data);
    }

    /**
     * @param string $sid
     * @return int
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete(string $sid)
    {
        $result = $this->workSpace->workers($sid)->delete();
        if ($result) {
            return SoftTwilioWorkerModel::deleteBySid($sid);
        }
    }

    /**
     * @param array $validatedRequest
     * @param string $contact_uri
     * @return string
     */
    public function prepareAttributes(array $validatedRequest, string $contact_uri)
    {
        $attributes = json_decode($validatedRequest['attributes'], true);

        if (empty($attributes)) {
            $attributes['languages'] = ["en"];
            $attributes['contact_uri'] = $contact_uri;
            $attributes['level'] = 0;
        }
        if (isset($validatedRequest['is_support']) && $validatedRequest['is_support'] == 'on') {
            if (isset($attributes['skills']) && is_array($attributes['skills'])) {
                $attributes['skills'] = array_unique(array_merge($attributes['skills'], ["support"]), SORT_REGULAR);
            } else {
                $attributes['skills'] = ["support"];
            }
        }

        return json_encode($attributes);
    }
}
