<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;


/**
 * Class WorkerChannel
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/taskrouter/api/worker-channel
 */
class WorkerChannelTwilio extends BaseTaskRouterClient
{
    /**
     * @param \Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance $workerInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function setEnableOnlyIdleActivity(\Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance $workerInstance)
    {
        $workerChannels = $this->workSpace->workers($workerInstance->sid)->workerChannels->read();
        if ($workerChannels) {
            foreach ($workerChannels as $workerChannel) {
                if ($workerChannel->taskChannelUniqueName !== 'voice') {
                    $workerChannel->update([
                        'available' => false
                    ]);
                }
            }
        }
    }
}
