<?php

use application\modules\tasks\models\Task;
use application\modules\user\models\User;
use application\notifications\UserPush;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Carbon;

class task_push extends CI_Driver implements JobsInterface
{
    public function getPayload($data = NULL)
    {
        if (empty($data['task_id']) || empty($data['task_date']) || empty($data['task_start'])) {
            return false;
        }

        return $data;
    }

    public function execute($job = NULL)
    {
        $payload = json_decode($job->job_payload, true);

        if (!$payload) {
            return false;
        }

        $task = Task::select([
            'task_id',
            'task_client_id',
            'task_category',
            'task_address',
            'task_status',
            'task_date',
            'task_start',
            'task_assigned_user'
        ])->with([
            'category' => function ($query) {
                $query->select([
                    'category_id',
                    'category_name'
                ]);
            },
            'client' => function ($query) {
                $query->select([
                    'client_id',
                    'client_name'
                ]);
            }
        ])->new()
          ->find($payload['task_id']);

        // check if task exists and task status
        if (!$task || $task->task_status !== 'new') {
            die('No task or status not "new"');
        }

        if ($task->task_category <= 0) {
            die('No push notifications for day off task');
        }

        $task->setAppends(['task_schedule_start']);

        // check for task date changes
        $taskDate = Carbon::createFromFormat(getDateFormat(), $task->task_date)->format('Y-m-d');
        if ($taskDate !== $payload['task_date'] || $task->task_start !== $payload['task_start']) {
            if (empty($task->task_date) || empty($task->task_start)) {
                die('No task start date');
            }

            $min = (int) config_item('client_task_push_reminder_min') ?? 60;

            if ($min <= 0) {
                die('Task notifications disabled');
            }

            $taskDateTime = Carbon::createFromFormat(getDateFormat() . ' H:i:s', $task->task_date . ' ' . $task->task_start);
            $minReminderTime = (new Carbon())->addMinutes($min);

            if ($minReminderTime->gte($taskDateTime)) {
                die('Task starts before minimum notification time');
            }

            // remove all task jobs
            $this->removeJobs($task);

            $delay = $taskDateTime->subMinutes($min);

            // add new job
            pushJob('notifications/task_push', [
                'task_id' => "$task->task_id",
                'task_date' => $taskDate,
                'task_start' => $task->task_start,
                'notificationTime' => $delay->toDateTimeString()
            ], $delay->timestamp);

            return true;
        }

        if (!empty($task->task_assigned_user)) {
            $address = $task->task_address ?? '';
            $client = $task->client ? (!$address ? '' : ', ') . $task->client->client_name : '';
            $pushData = [
                'action' => 'Agenda/est',
                'params' => [],
                'title' => $task->task_schedule_start . ' | ' . ($task->category ? $task->category->category_name : 'Task reminder'),
                'body' => $address . $client
            ];

            try {
                $notification = new UserPush($pushData);
                $via = method_exists($notification, 'viaAdditional') && sizeof($notification->viaAdditional())
                    ? array_merge($notification->via(), $notification->viaAdditional())
                    : $notification->via();

                $user = User::find($task->task_assigned_user);

                Notification::sendNow(
                    $user,
                    $notification,
                    $via
                );

                // remove all task jobs
                $this->removeJobs($task);

                return true;
            }
            catch (Exception $e) {
                die($e);
            }
        }

        die('No task assigned user');
    }

    /**
     * Delete redundant tasks
     *
     * @param $task
     */
    private function removeJobs($task) {
        $CI =& get_instance();
        $sql = "DELETE FROM jobs WHERE job_driver = 'notifications/task_push' AND job_attempts = 0 AND job_payload LIKE '{\"task_id\":\"$task->task_id\"%';";
        $CI->db->query($sql);
    }
}
