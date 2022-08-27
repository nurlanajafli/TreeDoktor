<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
interface_exists('MailDriverInterface', FALSE) OR require_once(APPPATH . '/libraries/MailDriver/MailDriverInterface.php');


/**
 * Sendmail Class
 */
class Sendmail extends MY_Email implements MailDriverInterface
{
    public $CI;
    public $_driver = 'sendmail';
    public $email;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('email');
    }

    public function send($auto_clear = true)
    {
        // temporary blocked
        return ['error' => 'Service unavailable now!'];

        if (!$this->checkIfVerifiedEmail($this->email->_headers['From'])) {
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

    public function getEmailInfo()
    {
        // TODO: Implement getEmailInfo() method.
    }

    public function parseCallbackMessage($payload)
    {
        // TODO: Implement parseCallbackMessage() method.
    }
}
