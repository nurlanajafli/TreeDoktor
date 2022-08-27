<?php

class newsletters_sender extends CI_Driver implements JobsInterface
{
    public function getPayload($data = NULL)
    {
        return true;
    }

    public function execute($job = NULL)
    {
        $CI =& get_instance();
        $CI->load->library('email');
        $CI->load->model('mdl_clients');

        $nls = $CI->mdl_clients->get_nl([
            'nl_status IS NULL' => null,
            'nl_mailgun_status' => 'queued',
            'nl_date >=' => date('Y-m-d 00:00:00', strtotime("-1 days")),
        ]);

        foreach ($nls as $key => $nl) {
            $brand_id = get_brand_id([], $nl);

            $CI->email->clear(TRUE);
            $CI->email->initialize(['mailtype' => 'html']);
            $CI->email->to($nl->nl_to);
            $CI->email->from($nl->nl_from ?? brand_email($brand_id), brand_name($brand_id));
            $CI->email->message($nl->nl_text);
            $CI->email->subject($nl->nl_subject);

            $CI->email->_callback = function ($result = false) use ($nl, $CI) {
                $CI->mdl_clients->update_nl($nl->nl_id, [
                    'nl_mailgun_status' => 'in_progress',
                    'nl_status' => 1,
                    'nl_mailgun_id' => $CI->email->getResultId($result)
                ]);
            };

            $send = $CI->email->send();

            if(is_array($send) && !array_key_exists('error', $send)) {
                return TRUE;
            }
        }

        return TRUE;
    }
}
