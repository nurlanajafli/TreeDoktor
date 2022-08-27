<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\models\integrations\twilio\SoftTwilioApplicationModel;
use application\modules\settings\integrations\twilio\classes\accounts\AccountTwilio;
use Illuminate\Validation\ValidationException;

require_once(APPPATH . 'modules/settings/controllers/integrations/twilio/BaseController.php');

/**
 * Class AccountActiveNumbersController
 */
class ActiveNumbers extends BaseController
{
    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function index()
    {
        $applications = SoftTwilioApplicationModel::getList();
        $activeNumbers = (new AccountTwilio())->getIncomingPhoneNumbers();
        $this->load->view('settings/integrations/twilio/active_numbers/index', [
            'activeNumbers' => $activeNumbers,
            'applications' => $applications
        ]);
    }

    /**
     * @param string $sid
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public function update(string $sid)
    {
        $accountTwilio = new AccountTwilio();
        $activeNumber = $accountTwilio->getIncomingPhoneNumberBySid($sid);
        $applications = SoftTwilioApplicationModel::getList();
        $data = [];
        if (request()->isMethod('POST')) {
            try {
                $validatedRequest = request()->validate([
                    'voiceApplicationSid' => 'required',
                ]);
            } catch (ValidationException $e) {
                $data['errors'] = $e->validator->errors()->getMessages();
            }

            if (!isset($data['errors'])) {
                try {
                    $result = $activeNumber->update($validatedRequest);
                } catch (Exception $e) {
                    return $this->response(['errors' => $e->getMessage()], 200);
                }
                if ($result) {
                    return $this->response(['data' => 'ok'], 200);
                }
            } else {
                return $this->response(['errors' => $data['errors']], 200);
            }
        }

        $data['activeNumber'] = $activeNumber;

        $this->load->view('settings/integrations/twilio/active_numbers/update', [
            'data' => $data,
            'applications' => $applications,
            'sid' => $sid
        ]);
    }
}
