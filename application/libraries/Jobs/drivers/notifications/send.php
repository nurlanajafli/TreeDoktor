<?php

use application\modules\user\models\User;

class send extends CI_Driver implements JobsInterface
{
    public function getPayload($data = NULL)
    {
        if (!is_array($data) || !sizeof($data) || !isset($data['user_id'])) {
            return false;
        }

        return $data;
    }

    public function execute($job = NULL)
    {
        $payload = json_decode($job->job_payload, true);

        if (isset($payload['job'])) {
            // send from Queueable
            $queue = [
                'id' => $job->job_id,
                'queue' => 'default',
                'payload' => $job->job_payload
            ];

            $job = app('queue')->getQueueJob($queue);

            try {
                $job->fire();

                $jobPayload = $job->payload();
                $command = unserialize($jobPayload['data']['command']);

                if (isset($command->notification)) {
                    // send via additional channels
                    if (method_exists($command->notification, 'viaAdditional') && sizeof($command->notification->viaAdditional())) {
                        \Illuminate\Support\Facades\Notification::sendNow(
                            $command->notifiables,
                            $command->notification,
                            $command->notification->viaAdditional()
                        );
                    }
                }
            }
            catch (Exception $e) {
                die($e);
            }
        } else {
            // send from CI pushJob
            $users_ids = is_array($payload['user_id']) ? $payload['user_id'] : [$payload['user_id']];

            unset($payload['user_id']);

            try {
                $notification = new application\notifications\UserPush($payload);
                $via = method_exists($notification, 'viaAdditional') && sizeof($notification->viaAdditional())
                    ? array_merge($notification->via(), $notification->viaAdditional())
                    : $notification->via();

                \Illuminate\Support\Facades\Notification::sendNow(
                    User::whereIn('id', $users_ids)->get(),
                    $notification,
                    $via
                );
            }
            catch (Exception $e) {
                die($e);
            }
        }

        return TRUE;
    }
}
