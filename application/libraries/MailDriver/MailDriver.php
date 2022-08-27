<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

use application\modules\emails\models\Email;

/**
 * MailDriver Class
 */
class MailDriver extends MY_Driver_Library
{
    public $CI;
    private $driver;
    private $email;

    /**
     * Class constructor
     */
    public function __construct($mail)
    {
        parent::__construct($mail->config);

        $this->CI =& get_instance();
        $this->CI->load->config('email');
        $driver = $mail->_driver ?? config_item('default_mail_driver');
        $this->driver = $driver;
        $this->email = $mail;

        // call class corresponding $config['driver] to define which protocol should be used for sending emails
        $this->CI->load->library('MailDriver/'.ucfirst($driver));
        $this->CI->{$this->driver}->setEmail($this->email);
    }

    public function send()
    {
        if (empty($this->email)) {
            return ['error' => 'No email'];
        }

        try {
            $result = $this->CI->{$this->driver}->send();
        }
        catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }

        if ($result === false && ($this->driver === 'smtp' || $this->driver === 'sendmail')) {
            return ['error' => $this->email->print_debugger()];
        }

        if (is_array($result)) {
            $error = $result['error'] ?? null;
            $toDb = $result['toDb'] ?? false;
            $messageId = $result['messageId'] ?? null;

            if ($toDb) {
                $emailInfo = $this->CI->{$this->driver}->getEmailInfo();

                $email = [
                    'email_message_id' => $messageId,
                    'email_from' => $emailInfo['from'] ?? '',
                    'email_to' => $emailInfo['recipients'] ?? '',
                    'email_cc' => $emailInfo['cc'] ?? null,
                    'email_bcc' => $emailInfo['bcc'] ?? null,
                    'email_subject' => $emailInfo['subject'] ?? '',
                    'email_status' => $error ? 'error' : 'accepted',
                    'email_user_id' => request()->user()->id ?? null,
                    'email_template_id' => null,
                    'email_provider' => $this->driver,
                    'email_error' => $error,
                    'email_created_at' => getNowDateTime(),
                    'email_updated_at' => getNowDateTime()
                ];

                $storedEmail = Email::createEmail($email);

                if (isset($storedEmail['error']) || !$storedEmail) {
                    $errorMessage = !$storedEmail ? 'No data' : $storedEmail['error'];

                    return [
                        'messageId' => $messageId,
                        'error' => $errorMessage
                    ];
                }

                $this->email->setSentEmail($storedEmail);

                return [
                    'messageId' => $messageId,
                    'email' => $storedEmail->toArray()
                ];
            } else {
                return ['error' => $error];
            }
        } else {
            return [$this->getResultId($result)];
            //return ['error' => 'Unexpected error'];
        }
    }

    public function getResultId($result = false)
    {
        return $this->CI->{$this->driver}->getResultId($result);
    }

    public function setDriver($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function parseCallbackMessage($payload) {
        return $this->CI->{$this->driver}->parseCallbackMessage($payload);
    }
}
