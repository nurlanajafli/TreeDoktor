<?php
namespace application\modules\settings\callback;

use application\core\Interfaces\CallbackInterface;
use application\modules\settings\integrations\twilio\classes\task_router\BaseTaskRouterClient;
use application\modules\settings\models\integrations\twilio\SoftTwilioActivityModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use ElephantIO\Engine\SocketIO\Version1X;
use ElephantIO\Client as WSClient;

class TaskCallbacks implements CallbackInterface
{

    /**
     * @param array $params
     * @return mixed|void
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function handle(array $params)
    {
        $CI = & get_instance();
        $request = request();
        $workspace = (new SoftTwilioWorkspaceModel())->first();
        $twilio = new BaseTaskRouterClient($workspace->sid);

        if (
            $request->input('EventType') == 'reservation.created'
            &&
            $request->input('EventType') !== 'reservation.accepted'
        ) {
            $taskAttributesJson = $request->input('TaskAttributes') ?? null;
            $taskSid = $request->input('TaskSid') ?? null;
            $workerSid = $request->input('WorkerSid') ?? null;
            $this->addReservationWorkersToTaskAttributes($twilio, $workerSid, $taskSid, $taskAttributesJson);
        }

        if (
            $request->input('EventType') == 'task.canceled'
            ||
            $request->input('EventType') == 'task.system-deleted'
            ||
            $request->input('EventType') == 'task.deleted'
            ||
            $request->input('EventType') == 'reservation.accepted'
        ) {
            if ($taskAttributesJson = $request->input('TaskAttributes')) {
                $this->setReservationWorkersToIdle($twilio, $taskAttributesJson);
            }
        }

        if ($request->input('EventType') == 'reservation.timeout') {
            if ($taskAttributesJson = $request->input('TaskAttributes')) {
                $isSetAllTaskWorkersToIdle = $this->isAllWorkersTimeoutForCurrentTask($twilio, $taskAttributesJson);
                if ($isSetAllTaskWorkersToIdle) {
                    $this->setReservationWorkersToIdle($twilio, $taskAttributesJson);
                }
            }
        }

        if ($request->input('EventType') == 'reservation.wrapup') {
            $taskSid = $request->input('TaskSid') ?? null;

            $twilio->workSpace->tasks($taskSid)->update([
                "assignmentStatus" => "completed",
                "reason" => "the agent hang up"
            ]);
        }

        if ($request->input('EventType') == 'task.created') {
            $data['twilio_calls'] = json_decode($request->input('TaskAttributes'))->call_sid;
            $data['twilio_tasks'] = $request->input('TaskSid');
            $CI->load->model('mdl_calls_tasks');
            $CI->mdl_calls_tasks->insert($data);
        }


        $wsClient = new WSClient(new Version1X($CI->config->item('wsClient')));
        $wsClient->initialize();
        $wsClient->emit('room', [$CI->input->post('WorkspaceSid')]);
        $wsClient->emit('message', ['method' => 'updateQueueCounter']);
        $wsClient->close();
    }

    /**
     * @param BaseTaskRouterClient $twilio
     * @param string $workerSid
     * @param string $taskSid
     * @param $postTaskAttributes
     * @throws \Twilio\Exceptions\TwilioException
     */
    private function addReservationWorkersToTaskAttributes(
        BaseTaskRouterClient $twilio,
        string $workerSid,
        string $taskSid,
        $postTaskAttributes
    ) {
        if (!is_null($workerSid) && !is_null($taskSid) && !is_null($postTaskAttributes)) {
            $TaskAttributes = json_decode($postTaskAttributes);
            if (isset($TaskAttributes->workersReservation)) {
                $workersReservation = array_merge($TaskAttributes->workersReservation, [$workerSid]);
            } else {
                $workersReservation = [$workerSid];
            }
            $TaskAttributes->workersReservation = $workersReservation;
            $twilio->workSpace->tasks($taskSid)->update([
                'attributes' => json_encode($TaskAttributes)
            ]);
        }

    }

    /**
     * @param BaseTaskRouterClient $twilio
     * @param $postTaskAttributes
     * @throws \Twilio\Exceptions\TwilioException
     */
    private function setReservationWorkersToIdle(BaseTaskRouterClient $twilio, $postTaskAttributes)
    {
        $TaskAttributes = json_decode($postTaskAttributes);
        if (isset($TaskAttributes->workersReservation)) {
            foreach ($TaskAttributes->workersReservation as $workerSid) {
                $worker = $twilio->workSpace->workers($workerSid)->fetch();
                $activities = SoftTwilioActivityModel::all()->keyBy('friendlyName')->toArray();
                if ($worker->activitySid == $activities['WrapUp']['sid']) {
                    $worker->update([
                        'activitySid' => $activities['Idle']['sid']
                    ]);
                }
            }
        }
    }

    /**
     * @param BaseTaskRouterClient $twilio
     * @param $postTaskAttributes
     * @return bool
     */
    private function isAllWorkersTimeoutForCurrentTask(BaseTaskRouterClient $twilio, $postTaskAttributes)
    {
        $taskAttributes = json_decode($postTaskAttributes);
        $skills = $taskAttributes->skills;
        $result = false;
        $idleActivitySid = SoftTwilioActivityModel::where('friendlyName', 'Idle')->first()->sid;
        $workers = $twilio->workSpace->workers->read([
            'targetWorkersExpression' => sprintf('skills HAS "%s"', $skills[0]),
            'activitySid' => $idleActivitySid
        ]);
        if (empty($workers)) {
            $result = true;
        }
        return $result;
    }
}
