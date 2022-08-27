<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
interface_exists('MailDriverInterface',
    false) or require_once(APPPATH . '/libraries/MailDriver/MailDriverInterface.php');

use Aws\Exception\AwsException;
use Aws\Ses\Exception\SesException;
use Aws\Ses\SesClient;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Amazon Class
 */
class Amazon extends MY_Email implements MailDriverInterface
{
    public $CI;
    public $_driver = 'amazon';
    private $client;
    private $email;

    private $emailInfo;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config('email');
        $this->client = [];
        $this->emailInfo = [];
        $this->client = SesClient::factory(array(
            'region'  => AWS_SES_REGION,
            'version' => AWS_VERSION,
            'credentials' => array(
                'key'    => AWS_KEY,
                'secret' => AWS_SECRET_KEY,
            )
        ));
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email info: ['from', 'subject', 'recipients', 'cc', 'bcc']
     *
     * @return array
     */
    public function getEmailInfo()
    {
        return $this->emailInfo;
    }

    /**
     * Composes an email message and immediately queues it for sending.
     * @return bool|mixed|string|void|null
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function send($auto_clear = true)
    {
        if (empty($this->email)) {
            return [
                'toDb' => false,
                'error' => 'No email',
            ];
        }

        $headers = $this->email->_headers;
        $sender_email = explode(' <', $headers['From']);
        $fromName = str_replace('"', '', $sender_email[0]);
        $from = $originalFrom = str_replace('>', '', $sender_email[1]);
        $subject = str_replace(['=?'.$this->email->charset.'?Q?', '?=', '_'], ['', '', ' '], $headers['Subject']);
        $replyTo = null;

        if (($checkedFrom = $this->checkIfVerifiedEmail($from)) === false) {
            $checkedFrom = $from = $this->CI->config->item('company_dir') . '-no-reply@arbostar.com';
            $replyTo = $originalFrom;
            /*if ($this->CI->input->is_ajax_request()) {
                $status['type'] = 'error';
                $status['message'] = 'Oops! Unverified from address! Please use another one or verify this one.';
                die(json_encode($status));
            }

            return [
                'toDb' => false,
                'error' => 'Oops! Unverified from address! Please use another one or verify this one.'
            ];*/
        }

        if(
            strpos($from, 'yahoo.com') || strpos($from, 'aol.com')
        ) {
            $checkedFrom = $from = $this->CI->config->item('company_dir') . '-no-reply@arbostar.com';
            $replyTo = $originalFrom;
        }

        $this->emailInfo = [
            'from' => $from,
            'subject' => $subject
        ];

        $recipients = $this->email->_recipients;
        $this->emailInfo['recipients'] = is_array($recipients) ? implode(', ', $recipients) : $recipients;

        $mail = new PHPMailer;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = "binary";
        $mail->setFrom($checkedFrom, $fromName);

        $to = !is_array($recipients) ? explode(',', $recipients) : $recipients;
        foreach ($to as $value) {
            $mail->addAddress(trim($value));
        }

        $mail->AddCustomHeader("Precedence: bulk");
        $mail->AddCustomHeader("List-Unsubscribe: " . $checkedFrom);
        $mail->MessageID = "<" . md5('HELLO' . (idate("U") - 1000000000) . uniqid()) . $checkedFrom . '>';
        $mail->set('Subject', $subject);
        $mail->set('Body', $this->email->_body);
        $mail->set('AltBody', strip_tags($this->email->_body));
        // header to activate SNS events
        $mail->addCustomHeader('X-SES-CONFIGURATION-SET', config_item('amazon_ses_configuration_set') ?: config_item('company_dir'));

        if($replyTo) {
            $mail->addReplyTo($replyTo, $fromName);
        }

        if (isset($this->email->_headers['Cc'])) {
            $this->emailInfo['cc'] = $this->email->_headers['Cc'];
            $cc = explode(',', $this->email->_headers['Cc']);
            foreach ($cc as $value) {
                $mail->addCC(trim($value));
            }
        }
        elseif (!empty($this->email->_cc_array)) {
            $this->emailInfo['cc'] = implode(', ', $this->email->_cc_array);
            array_map(function ($cc) use ($mail) {
                $mail->addCC($cc);
            }, $this->email->_cc_array);
        }

        if (isset($this->email->_headers['Bcc'])) {
            $this->emailInfo['bcc'] = $this->email->_headers['Bcc'];
            $bcc = explode(',', $this->email->_headers['Bcc']);
            foreach ($bcc as $value) {
                $mail->addBCC(trim($value));
            }
        }
        elseif (!empty($this->email->_bcc_array)) {
            $this->emailInfo['bcc'] = implode(', ', $this->email->_bcc_array);
            array_map(function ($bcc) use ($mail) {
                $mail->addBCC($bcc);
            }, $this->email->_bcc_array);
        }

        if (isset($this->email->_attachments) && !empty($this->email->_attachments) && is_array($this->email->_attachments)) {
            foreach ($this->email->_attachments as $attachment){
                $mail->addAttachment($attachment['name'][0]);
            }
        }

        try {
            if (!$mail->preSend()) {
                return [
                    'toDb' => true,
                    'error' => $mail->ErrorInfo
                ];
            } else {
                $message = $mail->getSentMIMEMessage();
            }
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            throw new Exception($e->getMessage());
        }

        try {
            $result = $this->client->sendRawEmail([
                'RawMessage' => [
                    'Data' => $message
                ]
            ]);

            if (isset($this->email->_callback) && $this->email->_callback){
                return call_user_func($this->email->_callback, $result);
            }

            return [
                'toDb' => true,
                'messageId' => $result->get('MessageId')
            ];
        } catch (SesException $error) {
            throw new Exception($error->getAwsErrorMessage());
        }
    }

    /**
     * Returns only those for which status is success, deprecated for version 3
     * @param          $email
     * @param  string  $nextToken
     * @return bool
     */
    public function checkIfVerifiedEmail($email)
    {
        /*$this->CI->load->model('Mdl_amazon_identities_orm', 'amazon_identities');
        $existInDb = $this->CI->amazon_identities->get_by([
            'identity' => $email
        ]);

        if (! isset($existInDb)) {
            return false;
        }*/

        if ($result = $this->checkIfVerifiedIdentity($email)) {
            return $result;
        }

        $domain = explode('@', $email);
        if (empty($domain[0])) {
            return false;
        }

        if ($result = $this->checkIfVerifiedIdentity($domain[1], 'Domain')) {
            return $domain[0] . '@' . $result;
        }

        return false;
    }

    /**
     * Returns all statuses
     * @param  string  $identity
     * @param  string  $type  EmailAddress or Domain
     * @param  string  $nextToken
     * @return bool
     */
    public function checkIfVerifiedIdentity(string $identity, $type = "EmailAddress", $nextToken = '')
    {
        $formatting = [
            'IdentityType' => $type
        ];
        if (!empty($nextToken)) {
            $formatting['NextToken'] = $nextToken;
        }

        $result = $this->getVerifiedIdentities($formatting);
        $listIdentities = $result['Identities'];
        $nextToken = $result['NextToken'];

        $ident = $this->getIdentityVerificationAttributes([$identity]);
        if ($ident !== false
            && isset($ident['VerificationAttributes'][$identity])
            && $ident['VerificationAttributes'][$identity]['VerificationStatus'] == 'Success'
        ) {
            return $identity;
        }

        /*return false;*/

        foreach ($listIdentities as $listIdentity) {
            if (trim(strtolower($identity)) == strtolower($listIdentity)) {
                $ident = $this->getIdentityVerificationAttributes([$listIdentity]);
                if ($ident !== false
                    && isset($ident['VerificationAttributes'][$listIdentity])
                    && $ident['VerificationAttributes'][$listIdentity]['VerificationStatus'] == 'Success'
                ) {
                    return $listIdentity;
                } else {
                    return false;
                }
            }
        }

        /*if ($nextToken !== false && !empty($nextToken)) {
            $this->checkIfVerifiedIdentity($identity, $type, $nextToken);
        }
        return false;*/
    }

    /**
     * Adds an email address to the list of identities for Amazon SES account
     * @param  string $email
     * @return \Aws\Result
     * @throws Exception
     */
    public function verifyEmailIdentity($email) {
        try {
            return $this->client->verifyEmailIdentity(array(
                'EmailAddress' => $email,
            ));
        } catch (AwsException $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Adds a domain to the list of identities for your Amazon SES account in the current AWS Region and attempts to verify it
     * @param string $domain
     * @return array|\Aws\Result
     */
    public function verifyDomainIdentity($domain) {
        try {
            return $this->client->verifyDomainIdentity([
                'Domain' => $domain,
            ]);
        } catch (AwsException $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Returns a set of DKIM tokens for a domain identity.
     * @param string $domain
     * @return array|\Aws\Result
     */
    public function verifyDomainDkim($domain) {
        try {
            return $this->client->verifyDomainDkim([
                'Domain' => $domain,
            ]);
        } catch (AwsException $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Returns a list containing all of the identities (email addresses and domains)
     *
     * @param  array  $format
     * @return array|\Aws\Result
     */
    public function getVerifiedIdentities($format = []) {
        try {
            return $this->client->listIdentities($format);
        } catch (AwsException $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Get domain status and dkims
     * @param $identities
     * @return array|\Aws\Result
     */
    public function getIdentityDkimAttributes($identities) {
        try {
            return $this->client->getIdentityDkimAttributes([
                'Identities' => $identities
            ]);
        } catch (AwsException $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Get domain status and txt token
     * @param  array $identities
     * @return array|\Aws\Result
     */
    public function getIdentityVerificationAttributes($identities) {
        try {
            return $this->client->getIdentityVerificationAttributes([
                'Identities' => $identities
            ]);
        } catch (AwsException $e) {
            return false;
        }
    }

    /**
     * Deletes the specified identity (email address or domain) from the list of verified identities.
     *
     * @param  string  $identity
     * @return array|\Aws\Result
     */
    public function deleteIdentity($identity)
    {
        try {
            return $this->client->deleteIdentity(array(
                'Identity' => $identity,
            ));
        } catch (AwsException $e) {
            return ['status' => false, 'msg' => $e->getMessage()];
        }
    }

    public function getResultId($result = false) {
        if($result && is_a($result, 'Aws\Result')) {
            return $result->get('MessageId');
        }
        return false;
    }

    public function parseCallbackMessage($payload)
    {
        $payload = json_decode($payload, TRUE);

        if (!isset($payload['Type']) || !isset($payload['TopicArn']) || !isset($payload['Message'])) {
            exit('Wrong message format');
        }

        if ($payload['Type'] === 'SubscriptionConfirmation') {
            if(isset($payload['SubscribeURL']) && $payload['SubscribeURL']) {
                $xmlResponse = @file_get_contents($payload['SubscribeURL']);
                $response = $xmlResponse ? simplexml_load_string($xmlResponse) : false;
                if(isset($response->ConfirmSubscriptionResult) && isset($response->ConfirmSubscriptionResult->SubscriptionArn)) {
                    exit($response->ConfirmSubscriptionResult->SubscriptionArn);
                } else {
                    exit('Confirmation Error');
                }
            }
            exit('SubscribeURL Is Broken');
        }
        elseif ($payload['Type'] === 'Notification') {
            $message = json_decode($payload['Message'], true);

            if (!is_array($message) || !isset($message['eventType'])) {
                exit('Wrong message type');
            }

            $eventType = $message['eventType'];
            $eventDetails = $message[lcfirst($eventType)] ?? null;
            $eventDetails = $eventDetails && sizeof($eventDetails) ? json_encode($eventDetails) : null;
            $mailObject = $message['mail'];

            return [
                'messageId' => $mailObject['messageId'],
                'trackingId' => $payload['MessageId'],
                'status' => $this->getTrackStatusForAmazon($eventType),
                'details' => $eventDetails,
                'driver' => $this->_driver
            ];
        }

        exit('Not implemented.');
    }

    /**
     * get status corresponding db
     *
     * @param $status
     * @return string
     */
    protected function getTrackStatusForAmazon($status)
    {
        if ($status === 'Delivery') return 'delivered';
        elseif ($status === 'Send') return 'accepted';
        elseif ($status === 'Reject') return 'rejected';
        elseif ($status === 'Bounce') return 'bounce';
        elseif ($status === 'Complaint') return 'complained';
        elseif ($status === 'Click') return 'clicked';
        elseif ($status === 'Open') return 'opened';
        elseif ($status === 'Unsubscribe') return 'unsubscribed';
        else return 'accepted';
    }
}
