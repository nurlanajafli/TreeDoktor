<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
interface_exists('MailDriverInterface', FALSE) OR require_once(APPPATH . '/libraries/MailDriver/MailDriverInterface.php');

/**
 * Smtp Class
 */
class Smtp extends MY_Email implements MailDriverInterface
{
    public $CI;
    public $_driver = 'smtp'; // driver name, same class name
    private $email;

    private $emailInfo;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->emailInfo = [];
    }

    public function send($auto_clear = true)
    {
        // temporary blocked
        return ['error' => 'Service unavailable now!'];

        $config = config_item('mailerDrivers')[$this->_driver];
        $this->email->initialize($config);
        $this->email->from($config['smtp_user'], $config['fromName']);

        $this->emailInfo['from'] = $config['smtp_user'];

        if (!$this->checkIfVerifiedEmail($config['smtp_user'])) {
            return false;
        }
        return $this->email->parent_send();
    }

    public function checkIfVerifiedEmail($email)
    {
        return true;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    // TODO: add CC and BCC
    public function getEmailInfo() {
        $headers = $this->email->_headers;
        $subject = str_replace(['=?'.$this->email->charset.'?Q?', '?=', '_'], ['', '', ' '], $headers['Subject']);

        $this->emailInfo['subject'] = $subject;

        $recipients = $this->email->_recipients;
        $this->emailInfo['recipients'] = $recipients;

        if (gettype($recipients == 'array')) {
            $this->emailInfo['recipients'] = $recipients[0];
        }

        return $this->emailInfo;
    }

    public function parseCallbackMessage($payload)
    {
        // TODO: Implement parseCallbackMessage() method.
    }
}
