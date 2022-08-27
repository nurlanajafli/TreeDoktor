<?php

use application\modules\estimates\models\Estimate;
use application\modules\mail\helpers\MailCheck;
class sendestimate extends CI_Driver implements JobsInterface
{
    var $payload, $CI = [];

    public function getPayload($data = NULL) {
        $data['user_id'] = request()->user()->id ?? 0;
        return $data;
    }

    function execute($job) {
        $this->CI =& get_instance();
        $this->payload = json_decode($job->job_payload);

        if(isset($this->payload->user_id)) {
            $user = \application\modules\user\models\User::find($this->payload->user_id);
            request()->merge(['user' => $user]);
            request()->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        $this->CI->load->library('Common/EstimateActions');
        $this->CI->estimateactions->setEstimateId($this->payload->estimate_id);
        $tmpFile = $this->CI->estimateactions->tmpPDF();
        $estimate = Estimate::with('user')->find($this->payload->estimate_id);
        $this->CI->estimateactions->clear();

        $from = (isset($this->payload->from) && $this->payload->from && $this->payload->from !== '') ? $this->payload->from : null;
        $from_name = (isset($this->payload->from_name) && $this->payload->from_name && $this->payload->from_name !== '') ? $this->payload->from_name : null;
        if(!$from){
            $email_check = (new MailCheck())->checkEmailIdentityStatus($estimate->user->user_email);
            if(!$email_check)
            {
                $domain_array = explode('@', $estimate->user->user_email);
                if(isset($domain_array[1]) && $domain_array[1])
                    $email_check = (new MailCheck())->checkEmailIdentityStatus($domain_array[1]);
            }

            if($email_check){
                $from = $estimate->user->user_email;
                $from_name = brand_name($estimate->estimate_brand_id);
            }
        }

        $from = ($from)?$from:config_item('account_email_address');
        $from_name = ($from_name)?$from_name:config_item('company_name_short');

        $this->CI->load->library('email');
        $this->CI->email->clear(TRUE);
        $this->CI->email->initialize(['mailtype' => 'html']);
        $this->CI->email->to($this->payload->to);
        $this->CI->email->from($from, $from_name);
        $this->CI->email->subject($this->payload->subject);

        if ($this->payload->cc && $this->payload->cc != '') {
            $this->CI->email->cc($this->payload->cc);
        }

        if ($this->payload->bcc && $this->payload->bcc != '') {
            $this->CI->email->bcc($this->payload->bcc);
        }

        $body = $this->payload->body;

        if(filesize($tmpFile) < config_item('default_pdf_size')
            && strlen(base64_encode(file_get_contents($tmpFile))) < config_item('default_pdf_size')){
            $this->CI->email->attach($tmpFile);
        }
        elseif(!empty($estimate)){
            $estimate_link = '<div style="text-align: center">';
            $href = base_url("payments/estimate/" . md5($estimate->estimate_no . $estimate->client_id));
            $estimate_link .= '<a href="' . $href . '" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';box-sizing:border-box;border-radius:3px;color:#fff;display:inline-block;text-decoration:none;background-color: #81BA53;border-top: 10px solid #81BA53;border-right: 18px solid #81BA53;border-bottom: 10px solid #81BA53;border-left: 18px solid #81BA53;font-size: 20px;" target="_blank" data-saferedirecturl="' . $href . '">View Estimate</a>';
            $estimate_link .= '</div>';
            $body .= $estimate_link;
        }
        if(isset($this->payload->unsubscribe_text) && !empty($this->payload->unsubscribe_text))
            $body .= $this->payload->unsubscribe_text;
        $this->CI->email->message($body);

        $send = $this->CI->email->send();

        if (is_array($send) && !array_key_exists('error', $send)) {

            $note_id = make_notes($estimate->client_id,
                'Estimate ' . $estimate->estimate_no . ' sent to "' . $this->payload->to . '".',
                'email',
                $estimate->lead_id,
                $this->CI->email
            );

            $entities = [
                ['entity' => 'estimate', 'id' => $estimate->estimate_id],
                ['entity' => 'client', 'id' => $estimate->client_id]
            ];
            $this->CI->email->setEmailEntities($entities);

            $dir = 'uploads/notes_files/' . $estimate->client_id .'/' . $note_id . '/';

            $pattern = "/<body>(.*?)<\/body>/is";
            preg_match($pattern, $body, $res);
            $note['text'] = $res[1];
            $note['subject'] = $this->payload->subject;
            $note['from'] = $from;
            $note['to'] = $this->payload->to;

            bucket_move($tmpFile, $dir . $estimate->estimate_no . '.pdf', ['ContentType' => 'application/pdf']);
            bucket_write_file($dir . 'Content.html', $this->CI->load->view('clients/note_file', $note, TRUE), ['ContentType' => 'text/html']);
            bucket_unlink_all('uploads/clients_files/' . $estimate->client_id . '/estimates/' . $estimate->estimate_no . '/tmp/');

            @unlink($tmpFile);

            return true;
        }
        @unlink($tmpFile);
    }
}
