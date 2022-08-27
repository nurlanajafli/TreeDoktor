<?php

namespace application\modules\settings\integrations\twilio\classes\task_router;

use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioWorkspaceModel;
use Twilio\Rest\Client;

class BaseTaskRouterClient extends BaseTwilio
{

    public $workSpace;
    public $modelWorkspaceId;

    /**
     * TwilioTaskRouterRouter constructor.
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Exception
     */
    public function __construct(string $workspaceSid)
    {
        parent::__construct();
        $this->workSpace = $this->twilioClient->taskrouter->workspaces($workspaceSid);
        $workspaceModel = SoftTwilioWorkspaceModel::findBySid($workspaceSid);
        if (is_null($workspaceModel)) {
            throw new \Exception('Workspace model not found');
        }
        $this->modelWorkspaceId = $workspaceModel->getAttribute(SoftTwilioWorkspaceModel::ATTR_ID);
    }
}