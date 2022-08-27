<?php

class send_error_notification extends CI_Driver implements JobsInterface
{
    public function getPayload($data = NULL) {

        if(
            !isset($data['subject']) || !isset($data['message']) || !isset($data['from']) || !isset($data['to']) ||
            !filter_var($data['from'], FILTER_VALIDATE_EMAIL))
            return FALSE;

        return $data;
    }

    public function execute($job = NULL)
    {
        /*$CI =& get_instance();
        $CI->load->library('email');


        $config = config_item('mailerDrivers')['smtp'];
        $config['mailtype'] = 'html';
        $config['driver'] = 'smtp';

        $CI->email->initialize($config);

        $CI->email->to($payload->to);
        
        $CI->email->from($config['smtp_user'], $config['smtp_user']);
        $CI->email->subject($payload->subject);
        $CI->email->message($payload->message);
        
        if (!$CI->email->send_smtp()){
            return;
        }*/

        $payload = json_decode($job->job_payload);

        slack_error_notification(
            $payload->subject,
            isset($payload->server->HTTP_HOST)?"https://".$payload->server->HTTP_HOST.$payload->server->REQUEST_URI:'',
            isset($payload->message_data->file) ? $payload->message_data->file : null,
            isset($payload->message_data->line) ? $payload->message_data->line : null,
            isset($payload->message_data->message) ? $payload->message_data->message : null,
            isset($payload->post) ? $payload->post : null
        );

        return TRUE;
    }
}
