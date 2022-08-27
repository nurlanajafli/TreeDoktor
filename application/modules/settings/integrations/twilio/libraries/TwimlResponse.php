<?php

namespace application\modules\settings\integrations\twilio\libraries;


use application\modules\settings\integrations\twilio\libraries\Services\Services_Twilio_Twiml;

/**
 * Extends the Services_Twilio_Twiml class to provide a respond
 * method that allows us to control the exit path of the TwiML
 * to properly manage headers & session data
 *
 * @method TwimlResponse say() say($message, $params = array())
 * @method TwimlResponse hangup() hangup()
 * @method TwimlResponse redirect() redirect($url = '', $params = array())
 * @method TwimlResponse gather() gather($params)
 * @method TwimlResponse dial() dial($to, $options = array())
 * @method TwimlResponse client() client($clientID, $params = array())
 * @method TwimlResponse number() number($phoneNumber, $params = array())
 * @method TwimlResponse pause() pause($params = array())
 * @method TwimlResponse message() message($message = '', $params = array())
 * @method TwimlResponse sms() sms($message, $params = array())
 * @method TwimlResponse conference() conference($name, $params = array())
 * @method TwimlResponse record() record($params = array())
 */
class TwimlResponse extends Services_Twilio_Twiml
{
    /**
     * Response method
     */
    public function respond()
    {
        if (!headers_sent()) {
            header("Content-type: text/xml");
        }

        echo $this;
    }
}
