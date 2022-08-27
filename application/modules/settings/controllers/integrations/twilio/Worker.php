<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\integrations\twilio\classes\task_router\ActivityTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\WorkerTwilio;
use application\modules\settings\integrations\twilio\classes\task_router\WorkerChannelTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkerModel;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use application\modules\user\models\User;
use Illuminate\Validation\ValidationException;

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class Worker Controller
 */
class Worker extends BaseController
{

    /**
     * @var array
     */
    const VALIDATE_RULES = [
        'user_id' => 'required',
        'activitySid' => 'required',
        'is_support' => 'string',
        'attributes' => 'sometimes|string'
    ];

    /**
     * @param string $workspaceSid
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function index(string $workspaceSid)
    {
        $title = 'Soft twilio calls';
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        $workers = SoftTwilioWorkerModel::getListByWorkspaceId($workspaceModel->id);
        $availableUsers = User::getAllAvailableTwilioUsers();

        $this->load->view('settings/integrations/twilio/workers/index', [
            'title' => $title,
            'workers' => $workers,
            'workspaceSid' => $workspaceSid,
            'availableUsers' => $availableUsers
        ]);
    }

    /**
     * @param string $workspaceSid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     * @throws Exception
     */
    public function create(string $workspaceSid)
    {

        $title = 'Soft twilio calls';
        $workerTwilio = new WorkerTwilio($workspaceSid);
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        $data = [];

        if (request()->isMethod('POST')) {
            $validatedRequest = request()->validate(static::VALIDATE_RULES);
            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }

            if (!isset($data['errors'])) {

                $userModel = User::findOrFail($validatedRequest['user_id']);
                $contact_uri = 'agent_' . $userModel->id;

                $attributes = $workerTwilio->prepareAttributes($validatedRequest, $contact_uri);
                $worker = $workerTwilio->create(
                    $userModel->full_name, [
                        'attributes' => $attributes,
                        'activitySid' => $validatedRequest['activitySid']
                    ]
                );

                (new WorkerChannelTwilio($workspaceSid))->setEnableOnlyIdleActivity($worker);

                if ($worker) {
                    try {
                        DB::beginTransaction();
                        $workerModel = new SoftTwilioWorkerModel();
                        $workerModel->sid = $worker->sid;
                        $workerModel->workspace_id = $workspaceModel->id;
                        $workerModel->user_id = $userModel->id;
                        $workerModel->friendlyName = $userModel->full_name;
                        $workerModel->activityName = $worker->activityName;
                        $workerModel->save();

                        $userModel->twilio_worker_id = $worker->sid;
                        $userModel->twilio_workspace_id = $worker->workspaceSid;
                        $userModel->save();
                        DB::commit();
                    } catch (Throwable $e) {
                        DB::rollBack();
                        throw new Exception($e->getMessage());
                    }

                    redirect('settings/integrations/twilio/workspace/' . $workspaceSid . '/worker');
                }
            }
        }

        $activityTwilio = new ActivityTwilio($workspaceSid);
        $data['activities'] = $activityTwilio->getList();
        $data['availableUsers'] = User::getAllAvailableTwilioUsers();

        $this->load->view('settings/integrations/twilio/workers/create', [
            'title' => $title,
            'data' => $data,
            'workspaceSid' => $workspaceSid,
        ]);
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
        $workerTwilio = new WorkerTwilio($workspaceSid);
        $worker = $workerTwilio->getWorkerBySid($sid);

        if (request()->isMethod('POST')) {

            try {
                $validatedRequest = request()->validate(static::VALIDATE_RULES);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }

            if (!isset($data['errors'])) {
                $userModel = User::findOrFail($validatedRequest['user_id']);
                $contact_uri = 'agent_' . $userModel->id;
                $attributes = $workerTwilio->prepareAttributes($validatedRequest, $contact_uri);
                $result = $workerTwilio->update($sid, [
                    'FriendlyName' => $userModel->full_name,
                    'attributes' => $attributes,
                    'activitySid' => $validatedRequest['activitySid']
                ]);

                if ($result) {
                    $workerModel = SoftTwilioWorkerModel::findBySid($sid);
                    $workerModel->user_id = $userModel->id;
                    $workerModel->friendlyName = $userModel->full_name;
                    $workerModel->activityName = $result->activityName;
                    $workerModel->save();

                    redirect('/settings/integrations/twilio/workspace/' . $workspaceSid . '/worker');
                }
            }
        }
        $attributes = json_decode($worker->attributes, true);
        $activityTwilio = new ActivityTwilio($workspaceSid);

        $data['worker'] = $worker;
        $data['activities'] = $activityTwilio->getList();
        $data['availableUsers'] = User::getAllAvailableTwilioUsers();
        $data['is_support'] = in_array('support', $attributes['skills']);

        $this->load->view('settings/integrations/twilio/workers/update', [
            'title' => $title,
            'data' => $data,
            'workspaceSid' => $workspaceSid,
        ]);
    }

    /**
     * @param string $workspaceSid
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function delete(string $workspaceSid, string $sid)
    {
        $workerTwilio = new WorkerTwilio($workspaceSid);
        if ($workerTwilio->delete($sid)) {
            return $this->response(['data' => 'ok'], 200);
        }
    }
}
