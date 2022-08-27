<?php


class sendworkorders extends CI_Driver implements JobsInterface
{
    var $payload, $CI = [];

    public function getPayload($data = null)
    {
        $data['user_id'] = request()->user()->id;
        return $data;
    }

    function execute($job)
    {
        $this->CI =& get_instance();
        $this->payload = json_decode($job->job_payload);
        $from_email = $this->payload->from;

        $this->CI->load->library('Common/WorkorderActions',  ['workorder_id' => $this->payload->workorder->id]);
        $this->CI->workorderactions->setWorkorderId($this->payload->workorder->id);
        $file = $this->CI->workorderactions->showPDF('F');

        $toDomain = substr(strrchr($this->payload->to, "@"), 1);
        if (array_search($toDomain, $this->CI->config->item('smtp_domains')) !== false) {
            $config = $this->CI->config->item('smtp_mail');
            $note['from'] = $from_email = $config['smtp_user'];
        }

        $config['mailtype'] = 'html';
        $this->CI->load->library('email');
        $this->CI->email->clear(true);
        $this->CI->email->initialize($config);
        $this->CI->email->attach($file);
        $this->CI->email->to($this->payload->to);

        $this->CI->email->from($from_email, $this->CI->config->item('company_name_short'));
        $this->CI->email->subject($this->payload->subject);

        $this->CI->email->message($this->payload->body);
        $status['type'] = 'success';
        $status['message'] = 'Email sent. Thanks';

        $send = $this->CI->email->send();

        if (!is_array($send) || array_key_exists('error', $send)) {
            @unlink($file);
            return false;
        }

        $entities = [
            ['entity' => 'workorder', 'id' => $this->payload->workorder->id],
            ['entity' => 'client', 'id' => $this->payload->workorder->client_id]
        ];
        $this->CI->email->setEmailEntities($entities);

        @unlink($file);
        return true;
    }
}
