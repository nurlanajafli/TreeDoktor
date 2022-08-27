<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use application\modules\emails\models\Email;
use Mailgun\Mailgun;

const ALLOWED_ENTITIES = [
    'client',
    'clientNote',
    'estimate',
    'invoice',
    'workorder'
];

class MY_Email extends CI_Email {

    public $_driver;
    public $_CI;
    public $_callback = false;
    public $sentEmail = null;

    public function __construct(array $config = array()) {
        $CI =& get_instance();
        $this->config = $CI->config;
        $this->_CI = $CI;
        if (isset($config['driver']))
            $this->_driver = $config['driver'];

        parent::__construct($config);
    }

    /*public function clear($clear_attachments = FALSE)
    {
        $CI = & get_instance();
        $this->_subject		= "";
        $this->_body		= "";
        $this->_finalbody	= "";
        $this->_header_str	= "";
        $this->_replyto_flag = FALSE;
        $this->_recipients	= array();
        $this->_cc_array	= array();
        $this->_bcc_array	= array();
        $this->_headers		= array();
        $this->_debug_msg	= array();

        $this->_set_header('User-Agent', $this->useragent);
        $this->_set_header('Date', $this->_set_date());
          $this->_set_header('List-Unsubscribe', $CI->config->item('account_email_address'));

        if ($clear_attachments !== FALSE)
        {
            $this->_attach_name = array();
            $this->_attach_type = array();
            $this->_attach_disp = array();
        }

        return $this;
    }*/

      /**
       * @param  array  $config
       * @return $this|void
       * Set $config['driver'] = 'amazon' for send email via amazon
       */
      public function initialize($config = array())
      {
          $this->_CI->load->config('email');

          if (is_array($config)) {
              $defaultDriver = isset($config['driver']) ? $config['driver'] : config_item('default_mail_driver');
              $driver = config_item('mailerDrivers')[$defaultDriver];

              // merge  default config
              if (isset($config['driver'])) {
                  $this->_driver = $config['driver'];
              }

              if ($driver) $config = array_merge($config, $driver);
              foreach ($config as $key => $val)
              {
                  if (isset($this->$key))
                  {
                      $method = 'set_'.$key;

                      if (method_exists($this, $method))
                      {
                          $this->$method($val);
                      }
                      else
                      {
                          $this->$key = $val;
                      }
                  }
              }
          }

          $this->clear();

          $this->_smtp_auth = ($this->smtp_user == '' AND $this->smtp_pass == '') ? FALSE : TRUE;
          $this->_safe_mode = ((boolean)@ini_get("safe_mode") === FALSE) ? FALSE : TRUE;

          return $this;
      }

      /**
       * Set Email Subject (Overwrite)
       *
       * @access	public
       * @param	string
       * @return	void
       */
      public function subject($subject)
      {
          // to hotmail via amazon, encoded subjects fall in junk
//          if ($this->_driver !== 'amazon')
//              $subject = $this->_prep_q_encoding($subject);
          $this->set_header('Subject', $subject);
          return $this;
      }

    function send_mailgun()
    {
        if ($this->_replyto_flag == false) {
            $this->reply_to($this->_headers['From']);
        }
        if ((!isset($this->_recipients) AND !isset($this->_headers['To'])) AND
            (!isset($this->_bcc_array) AND !isset($this->_headers['Bcc'])) AND
            (!isset($this->_headers['Cc']))) {
            $this->_set_error_message('lang:email_no_recipients');
            return false;
        }

        $this->_build_headers();

        if ($this->bcc_batch_mode && is_array($this->_bcc_array) && !empty($this->_bcc_array)) {
            if (count($this->_bcc_array) > $this->bcc_batch_size)//countOk
            {
                return $this->batch_bcc_send();
            }
        }

        $this->_build_message();

        $files = [];
        $mailgun = new Mailgun('key-702419a5651d7c56de34422a6ca4904b', new \Http\Adapter\Guzzle6\Client());

        $msg = $mailgun->MessageBuilder();

        $msg->setFromAddress($this->_headers['From'] . ' <' . $this->_headers['X-Sender'] . '>');
        if (is_array($this->_recipients)) {
            foreach ($this->_recipients as $key => $value) {
                $msg->addToRecipient($value);
            }
        } else {
            $msg->addToRecipient($this->_recipients);
        }
        if(isset($this->_headers['Cc']) && $this->_headers['Cc']) {
            $msg->addCcRecipient($this->_headers['Cc']);
        }
        if (isset($this->_headers['Bcc']) && $this->_headers['Bcc']) {
            $msg->addBccRecipient($this->_headers['Bcc']);
        }
        $msg->setSubject($this->_headers['Subject']);
        $msg->setTextBody('Your mail do not support HTML');
        $msg->setHtmlBody($this->_body);

        foreach ($this->_attachments as $attachment){
            $files['attachment'][] = [
                'filename' => basename($attachment['name'][1] ?? $attachment['name'][0]),
                'fileContent' => base64_decode($attachment['content']),
            ];
        }
        try {
            $result = $mailgun->post($this->config->item('mailgun_msg'), $msg->getMessage(), $files);
        } catch (Exception $e) {
            return false;
        }

        return $result;
    }


    function get_mailgun($id)
    {
        if (!$id) {
            return false;
        }
        $mailgun = new Mailgun('key-702419a5651d7c56de34422a6ca4904b', new \Http\Adapter\Guzzle6\Client());

        $queryString = array(

            'message-id' => $id
        );

        # Make the call to the client.
        $result = $mailgun->get($this->config->item('mailgun_event'), $queryString);


        return $result;
    }

    function send_mailchimp()
    {
        $apikey = 'de70703399c6810d10de02ec75f7c058-us17';

        $campaignId = "120515";

        $memberId = md5(strtolower("membermail"));
        $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/campaigns/' . $campaignId . '/actions/test';

        $jsonEmail = '{"test_emails":["the mail you want to send thing sat"],"send_type":"html"}';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERPWD, 'apikey:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonEmail);

        $result = curl_exec($ch);
        curl_close($ch);

        var_dump($result);
    }

    function send($auto_clear = true)
    {
        $CI = &get_instance();
        $CI->load->driver('MailDriver', $this);
        return $CI->maildriver->send();
	}

	function parseCallbackMessage($payload) {
        $CI = &get_instance();
        $CI->load->driver('MailDriver', $this);
        return $CI->maildriver->parseCallbackMessage($payload);
    }

    function send_smtp()
    {
        return parent::send();
    }

    function getResultId($result = false)
    {
        $CI = &get_instance();
        return $CI->maildriver->getResultId($result);
    }

	public function parent_send()
    {
        return parent::send();
    }

    /**
     * Set email object
     *
     * @param Email $email
     */
    public function setSentEmail(Email $email) {
        $this->sentEmail = $email;
    }

    /**
     * Get email object
     *
     * @return Email|null
     */
    public function getSentEmail(): ?Email
    {
        return $this->sentEmail;
    }

    /**
     * Set custom userId to email
     *
     * @param int|null $userId
     */
    public function setCustomUserId(int $userId = null) {
        if ($this->sentEmail) {
            $this->sentEmail->email_user_id = $userId ?? request()->user()->id ?? null;
            $this->sentEmail->save();
        }
    }

    /**
     * Set emailTemplateId to email
     *
     * @param int|null $templateId
     */
    public function setTemplateId(int $templateId = null) {
        if ($this->sentEmail && $templateId) {
            $this->sentEmail->email_template_id = $templateId;
            $this->sentEmail->save();
        }
    }

    /**
     * Set relations with email
     *
     * @param null|array $entities
     *      $entities = ['entity', 'id']
     *      $entities = [['entity', 'id'], ['entity', 'id']]
     */
    public function setEmailEntities(array $entities = null) {
        if ($this->sentEmail && is_array($entities) && sizeof($entities)) {
            // check single or multidimensional array
            if (sizeof($entities) - sizeof($entities, COUNT_RECURSIVE) === 0) {
                $entities = [$entities];
            }

            foreach ($entities as $entity) {
                if (!in_array($entity['entity'], ALLOWED_ENTITIES)) {
                    continue;
                }

                $path = $entity['entity'] === 'clientNote' ? 'client' : $entity['entity'];

                $model = 'application\modules\\' . $path . 's\models\\' . ucfirst($entity['entity']);
                if (class_exists($model)) {
                    $ent = $model::find($entity['id']);

                    if (!$ent) {
                        continue;
                    }

                    $ent->emails()->attach($this->sentEmail);
                }
            }
        }
    }
}
