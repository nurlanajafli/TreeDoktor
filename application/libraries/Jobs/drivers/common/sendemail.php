<?php

class sendemail extends CI_Driver implements JobsInterface
{
    public function getPayload($data = NULL) {

        if (
            !isset($data['subject'])
            || !isset($data['message'])
            || !isset($data['from'])
            || !isset($data['to'])
            || !filter_var($data['from'], FILTER_VALIDATE_EMAIL)
            || !filter_var($data['to'], FILTER_VALIDATE_EMAIL)
        ) {
            return FALSE;
        }

        return $data;
    }

    public function execute($job = NULL)
    {
        $CI =& get_instance();
        $CI->load->library('email');

        $payload = json_decode($job->job_payload);
        $config['mailtype'] = 'html';

        $CI->email->initialize($config);

        $CI->email->to($payload->to);
        $CI->email->from($payload->from, $payload->from_name ?? config_item('company_name_short'));
        $CI->email->subject($payload->subject);
        $CI->email->message($payload->message);

        $send = $CI->email->send();

        if (!is_array($send) || array_key_exists('error', $send)) {
            return FALSE;
        }

        if (isset($payload->client_id)) {
            $name = uniqid();
            $note_id = make_notes(
                $payload->client_id,
                'Sent email "' . $payload->subject . '"',
                'email',
                $payload->lead_id ?? null,
                $CI->email
            );
            $dir = 'uploads/notes_files/' . $payload->client_id . '/' . $note_id . '/';

            $pattern = "/<body>(.*?)<\/body>/is";
            preg_match($pattern, $payload->message, $res);
            $note['text'] = isset($res[1]) && $res[1] ? $res[1] : $payload->message;
            $note['from'] = $payload->from;
            $note['to'] = $payload->to;
            $note['subject'] = $payload->subject;

            bucket_write_file(
                $dir . $name . '.html',
                $CI->load->view('clients/note_file', $note, true),
                ['ContentType' => 'text/html']
            );
        }

        return TRUE;
    }
}
