<?php
namespace application\modules\settings\callback;

use application\core\Interfaces\CallbackInterface;

class Recording implements CallbackInterface
{

    /**
     * @param array $params
     * @return mixed|void
     */
    public function handle(array $params)
    {
        $CI = & get_instance();
        $CI->load->library('../modules/settings/controllers/integrations/twilio/Client_twilio_calls', null, 'Voice');
        $CI->Voice->recording();
        exit;
    }
}