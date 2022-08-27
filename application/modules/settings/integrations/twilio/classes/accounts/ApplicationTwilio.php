<?php

namespace application\modules\settings\integrations\twilio\classes\accounts;

use application\modules\settings\integrations\twilio\classes\BaseTwilio;
use application\modules\settings\models\integrations\twilio\SoftTwilioApplicationModel;
use Twilio\Rest\Api\V2010\Account\ApplicationInstance;
/**
 * Class ActivityModel
 * @package application\modules\soft_twilio_calls\classes\task_router
 * @documentation https://www.twilio.com/docs/voice/api/applications-resource
 */
class ApplicationTwilio extends BaseTwilio
{
    /**
     * @param string $companyName
     * @param int $flowId
     * @return ApplicationInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function initialize(string $companyName, int $flowId)
    {
        $friendlyName = ucfirst($companyName) . '_TwiMLApp';
        $application = $this->account->applications->create([
            'FriendlyName' => $friendlyName . '_' . $flowId,
            'VoiceUrl' => base_url('/callback/settings/voice?flow=' . $flowId),
            'statusCallback' => base_url('/callback/settings/recording'),
            'VoiceMethod' => 'POST',
        ]);

        if ($application) {
            $applicationModel = new SoftTwilioApplicationModel();
            $data['sid'] = $application->sid;
            $data['flow_id'] = $flowId;
            $data['friendlyName'] = $application->friendlyName;
            $data['statusCallback'] = $application->statusCallback;
            $data['voiceUrl'] = $application->voiceUrl;
            $result = $applicationModel->setRawAttributes($data)->save();
        }

        return $application;
    }

    /**
     * @param SoftTwilioApplicationModel $application
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function disconnect(SoftTwilioApplicationModel $application)
    {
        $this->account->applications($application->sid)->delete();
        $application->delete();
    }

    /**
     * @param string $sid
     * @return \Twilio\Rest\Api\V2010\Account\ApplicationInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function getBySid(string $sid)
    {
        return $this->account->applications($sid)->fetch();
    }

    /**
     * @return array
     */
    public function getList()
    {
        $applications = $this->account->applications->read();
        $result = [];
        if (!is_null($applications) && !empty($applications)) {
            foreach ($applications as $application) {
                $result[] = [
                    'sid' => $application->sid,
                    'friendlyName' => $application->friendlyName,
                    'voiceUrl' => $application->voiceUrl,
                ];
            }
        }
        return $result;
    }

    /**
     * @param array $data
     * @return ApplicationInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function create(array $data)
    {
        $application = $this->account->applications->create($data);

        if ($application) {
            $applicationModel = new SoftTwilioApplicationModel();
            $data['sid'] = $application->sid;
            $data['friendlyName'] = $application->friendlyName;
            $data['voiceUrl'] = $application->voiceUrl;
            unset($data['apiVersion'], $data['voiceMethod']);
            $applicationModel->setRawAttributes($data)->save();
        }
        return $application;
    }

    /**
     * @param ApplicationInstance $application
     * @param array $data
     * @return ApplicationInstance
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(ApplicationInstance $application, array $data)
    {
        $result = $application->update($data);
        if ($result instanceof ApplicationInstance) {
            SoftTwilioApplicationModel::updateBySid($application->sid, $data);
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
        $result = $this->getBySid($sid)->delete();
        if ($result) {
            SoftTwilioApplicationModel::deleteBySid($sid);
        }
        return $result;
    }
}
