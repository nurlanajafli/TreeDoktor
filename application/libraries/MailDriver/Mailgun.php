<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
interface_exists('MailDriverInterface', FALSE) OR require_once(APPPATH . '/libraries/MailDriver/MailDriverInterface.php');

use Mailgun\Mailgun as BasicMailgun;

/**
 * Mailgun Class
 */
class Mailgun extends MY_Email implements MailDriverInterface
{
    public $CI;
    public $_driver = 'mailgun';
    private $email;
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->CI = & get_instance();
    }

    public function send($auto_clear = true)
    {
        // temporary blocked
        //return ['error' => 'Service unavailable now!'];

        if ($this->email->_callback) {
            $result = $this->send_mailgun();
            return call_user_func($this->email->_callback, $result);
            //return $this->{$this->email->_callback}();
        } else {
            return $this->send_client();
        }
    }

    public function getResultId($result = false) {
        if(isset($result->http_response_body) && isset($result->http_response_body->id)) {
            return $result->http_response_body->id;
        }
        return false;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function send_client()
    {
        $this->CI->load->model('mdl_clients');
        $email = $this->email->_headers['To'];
        $subject = $this->CI->input->post('subject');
        $from = preg_match("/<(.*?)>/is", $this->email->_headers['From'], $res);
        $emailText = $this->email->_body;
        $client = $this->CI->mdl_clients->get_client_by_contacts(['clients_contacts.cc_email' => $email], 1)->row();

        $result = $this->send_mailgun();

        if($result && count((array)$client) && count((array)$result))
        {
            $id = str_replace(['<', '>'], '', $result->http_response_body->id);

            $msg = $this->get_mailgun($id);

            if(isset($result->http_response_code) && $result->http_response_code == 200)
            {
                $data['nl_status'] = 1;
                $data['nl_mailgun_id'] = $result->http_response_body->id;
            }
            else
                $data['nl_status'] = 0;

            $data['nl_estimator'] = $this->CI->session->userdata('user_id');
            $data['nl_client'] = intval($client->cc_client_id);
            $data['nl_subject'] = $subject;
            $data['nl_from'] = $res[1];
            $data['nl_mailgun_status'] = 'in_progress';
            $data['nl_to'] = trim($email);
            $data['nl_text'] = $emailText;
            $data['nl_date'] = date('Y-m-d H:i:s');
            $this->CI->mdl_clients->insert_nl($data);
        }
        return $this->getResultId($result);
    }

    public function send_mailgun()
    {
        if (!$this->checkIfVerifiedEmail($this->email->_headers['From'])) {
            return false;
        }
        if ($this->email->_replyto_flag == FALSE)
        {
            $this->email->reply_to($this->email->_headers['From']);
        }
        if (( ! isset($this->email->_recipients) AND ! isset($this->email->_headers['To']))  AND
            ( ! isset($this->email->_bcc_array) AND ! isset($this->email->_headers['Bcc'])) AND
            ( ! isset($this->email->_headers['Cc'])))
        {
            $this->email->_set_error_message('lang:email_no_recipients');
            return FALSE;
        }

        $this->email->_build_headers();

        if ($this->email->bcc_batch_mode  AND  count($this->email->_bcc_array) > 0)
        {
            if (count($this->email->_bcc_array) > $this->email->bcc_batch_size)
                return $this->email->batch_bcc_send();
        }

        $this->email->_build_message();

        $files = [];
        $mailgun = new BasicMailgun('key-702419a5651d7c56de34422a6ca4904b');

        $msg = $mailgun->MessageBuilder();

        if(
            strpos($this->email->_headers['X-Sender'], 'yahoo.com') ||
            strpos($this->email->_headers['X-Sender'], 'aol.com')
        ) {
            $this->email->_headers['From'] = str_replace($this->email->_headers['X-Sender'], 'info@arbostar.com', $this->email->_headers['From']);
            $msg->setReplyToAddress($this->email->_headers['X-Sender']);
        }

        $msg->setFromAddress($this->email->_headers['From']);
        if(is_array($this->email->_recipients)) {
            foreach ($this->email->_recipients as $key => $value) {
                $msg->addToRecipient($value);
            }
        }
        else {
            $msg->addToRecipient($this->email->_recipients);
        }
        if(isset($this->email->_headers['Cc']) && $this->email->_headers['Cc'])
            $msg->addCcRecipient($this->email->_headers['Cc']);
        if(isset($this->email->_headers['Bcc']) && $this->email->_headers['Bcc'])
            $msg->addBccRecipient($this->email->_headers['Bcc']);
        elseif(!empty($this->email->_bcc_array))
            $msg->addBccRecipient(implode(', ', $this->email->_bcc_array));
        $msg->setSubject($this->email->_headers['Subject']);
        $msg->setTextBody('Your mail do not support HTML');
        $msg->setHtmlBody($this->email->_body);

        /*if(isset($this->email->_attach_name) && !empty($this->email->_attach_name)) {
            $files['attachment'] = $this->email->_attach_name;
        }*/
        foreach ($this->email->_attachments as $attachment){
            $files['attachment'][] = [
                'filename' => basename($attachment['name'][1] ?? $attachment['name'][0]),
                'fileContent' => base64_decode($attachment['content']),
            ];
        }

        try {
            $result = $mailgun->post($this->email->config->item('mailgun_msg'), $msg->getMessage(), $files);
        } catch (Exception $e) {
            return FALSE;
        }

        return $result;
    }

    public function get_mailgun($id)
    {
        if(!$id)
            return FALSE;
        $mailgun = new BasicMailgun('key-702419a5651d7c56de34422a6ca4904b');

        $queryString = array(

            'message-id'      => $id
        );

        # Make the call to the client.
        $result = $mailgun->get($this->email->config->item('mailgun_event'), $queryString);

        return $result;
    }

    public function checkIfVerifiedEmail($email)
    {
        return true;
    }

    public function getEmailInfo()
    {
        // TODO: Implement getEmailInfo() method.
    }

    public function parseCallbackMessage($payload)
    {
        // TODO: Implement parseCallbackMessage() method.
    }

//    /**
//     * @param array $signature
//     * @param null $api_key
//     * @return bool
//     */
//    protected function validateMailgun($signature, $api_key = null)
//    {
//        $timestamp = $signature['timestamp'];
//        $token = $signature['token'];
//        $signature = $signature['signature'];
//
//        //Concat timestamp and token values
//        if (empty($timestamp) || empty($token) || empty($signature)) {
//            return false;
//        }
//        $hmac = hash_hmac('sha256', $timestamp.$token, $api_key);
//
//        if (function_exists('hash_equals')) {
//            return hash_equals($hmac, $signature);
//        } else {
//            return $hmac === $signature;
//        }
//    }
//
//    public function handleMailgunWebhook($payload)
//    {
//        $signature = $payload['signature'];
//        $apiKey = getMailgunApiKey();
//
//        if (!$this->validateMailgun($signature, $apiKey)) {
//            return ['status' => false, 'msg' => 'Invalid signature!'];
//        }
//
//        $event_data = $payload['event-data'];
//        $message = $event_data['message'];
//        $messageId = $message['headers']['message-id'];
//        $sender = explode('@', $messageId);
//        $emailMessageId = '';
//        $tracking_data = [
//            'domain' => $sender[1],
//            'track_status' => $event_data['event'],
//            'recipients' => $event_data['recipient'],
//            'trackMessageId' => $messageId,
//            'emailMessageId' => $emailMessageId,
//            'mailHash' => md5($emailMessageId.$messageId),
//            'message' => json_encode($message),
//            'driver' => 'mailgun'
//        ];
//
//        if (!checkEmailTrackRowUpdate($tracking_data))
//            insertEmailTrackRow($tracking_data);
//        exit();
//    }
}
