<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\settings\models\Settings;

/**
 * Class Base controller
 */
class BaseController extends MX_Controller
{

    public $twilioSettingsArray;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        if (!isUserLoggedIn()) {
            redirect('login');
        }
        $this->twilioSettingsArray = Settings::getTwilioSettings();

        if (empty($this->twilioSettingsArray)) {
            redirect('settings/integrations/twilio/install');
        }
    }
}