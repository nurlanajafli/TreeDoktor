<?php
namespace application\modules\settings\callback;

use application\core\Interfaces\CallbackInterface;

class Voice implements CallbackInterface
{

    /**
     * @param array $params
     * @return mixed|void
     */
    public function handle(array $params)
    {
        $CI = & get_instance();
        $CI->load->library('../modules/settings/controllers/integrations/twilio/Client_twilio_calls', null, 'Voice');
        if (count($params) == 1 && isset($params['flow']) && is_numeric($params['flow'])) {
            $CI->Voice->start_voice($params['flow']);
        } elseif (count($params) == 2 && isset($params['flow']) && is_numeric($params['flow']) && isset($params['applet'])) {
            $CI->Voice->voice($params['flow'], $params['applet']);
        }
        exit;
    }
}