<?php

use application\modules\mail\helpers\MailCheck;
class sendinvoice extends CI_Driver implements JobsInterface
{
    var $payload, $CI = [];

    public function getPayload($data = null)
    {
        $data['user_id'] = request()->user()->id ?? 0;
        return $data;
    }

    function execute($job)
    {
        $this->CI =& get_instance();
        $this->payload = json_decode($job->job_payload);

        if(isset($this->payload->user_id)) {
            $user = \application\modules\user\models\User::find($this->payload->user_id);
            request()->merge(['user' => $user]);
            request()->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        $this->CI->load->library('Common/InvoiceActions',  ['invoice_id' => $this->payload->invoice_id]);
        $this->CI->invoiceactions->setInvoiceId($this->payload->invoice_id);
        $file = $this->CI->invoiceactions->tmpPDF();
        $this->CI->invoiceactions->clear();

        $from = (isset($this->payload->from) && $this->payload->from && $this->payload->from !== '') ? $this->payload->from : null;
        $from_name = (isset($this->payload->from_name) && $this->payload->from_name && $this->payload->from_name !== '') ? $this->payload->from_name : null;
        if(!$from){
            $email_check = (new MailCheck())->checkEmailIdentityStatus($this->payload->estimate_data->user->user_email);
            if(!$email_check)
            {
                $domain_array = explode('@', $this->payload->estimate_data->user->user_email);
                if(isset($domain_array[1]) && $domain_array[1])
                    $email_check = (new MailCheck())->checkEmailIdentityStatus($domain_array[1]);
            }

            if($email_check){
                $from = $this->payload->estimate_data->user->user_email;
                $from_name = brand_name($this->payload->estimate_data->estimate_brand_id);
            }
        }

        $from = ($from)?$from:config_item('account_email_address');
        $from_name = ($from_name)?$from_name:config_item('company_name_short');

        $this->CI->load->library('email');
        $this->CI->email->clear(true);
        $config['mailtype'] = 'html';

        $toDomain = substr(strrchr($this->payload->to, "@"), 1);
        if (array_search($toDomain, $this->CI->config->item('smtp_domains')) !== false) {
            $config = $this->CI->config->item('smtp_mail');
            $from = $config['smtp_user'];
        }

        $this->CI->email->initialize($config);

        //checking if a file in not larger than default_pdf_size from the settings
        if (filesize($file) < config_item('default_pdf_size')
            && strlen(base64_encode(file_get_contents($file))) < config_item('default_pdf_size')) {
            $this->CI->email->attach($file);
        } else {
            $invoice_link = '<div style="text-align: center">';
            $href = base_url("payments/invoice/" . md5($this->payload->invoice_data->invoice_no . $this->payload->invoice_data->client_id));
            $invoice_link .= '<a href="' . $href . '" style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif,\'Apple Color Emoji\',\'Segoe UI Emoji\',\'Segoe UI Symbol\';box-sizing:border-box;border-radius:3px;color:#fff;display:inline-block;text-decoration:none;background-color: #81BA53;border-top: 10px solid #81BA53;border-right: 18px solid #81BA53;border-bottom: 10px solid #81BA53;border-left: 18px solid #81BA53;font-size: 20px;" target="_blank" data-saferedirecturl="' . $href . '">View Invoice</a>';
            $invoice_link .= '</div>';
            $this->payload->body .= $invoice_link;
        }

        $this->payload->body .= $this->CI->load->view('invoices/new_invoice_letter_likes', [
            'invoice_data' => $this->payload->invoice_data,
            'estimate_data' => $this->payload->estimate_data,
        ], true);

        $this->payload->body .= '<br><div style="text-align:center; font-size: 10px;"> If you no longer wish to receive these emails you may ' .
            '<a href="' . $this->CI->config->item('unsubscribe_link') . md5($this->payload->invoice_data->client_id) . '">unsubscribe</a> at any time.</div>';

        $this->CI->email->to($this->payload->to);
        if ($this->payload->cc && $this->payload->cc != '') {
            $this->CI->email->cc($this->payload->cc);
        }

        if ($this->payload->bcc && $this->payload->bcc != '') {
            $this->CI->email->bcc($this->payload->bcc);
        }

        $this->CI->email->from($from, $from_name);
        $this->CI->email->subject($this->payload->subject);
        $this->CI->email->message($this->payload->body);

        $send = $this->CI->email->send();

        if (is_array($send) && !array_key_exists('error', $send)) {
            $default_status = element('invoice_status_id', (array)$this->CI->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'default' => 1]), 0);
            $sent_status = element('invoice_status_id', (array)$this->CI->mdl_invoice_status->get_by(['invoice_status_active'=>1, 'is_sent' => 1]), 0);

            if ($this->payload->invoice_data->in_status == (int)$default_status) {
                $updated = $this->CI->mdl_invoices->update_invoice(['in_status' => (int)$sent_status], ['id' => $this->payload->invoice_data->id]);
                $insert = array('status_type' => 'invoice', 'status_item_id' => $this->payload->invoice_data->id, 'status_value' => $sent_status, 'status_date' => time());
                $this->CI->mdl_estimates->status_log($insert);
            }

            $entities = [
                ['entity' => 'invoice', 'id' => $this->payload->invoice_data->id],
                ['entity' => 'client', 'id' => $this->payload->invoice_data->client_id]
            ];
            $this->CI->email->setEmailEntities($entities);

            $note_id = make_notes(
                $this->payload->invoice_data->client_id,
                'Sent PDF of invoice ' . $this->payload->invoice_data->invoice_no . '.',
                'email',
                intval($this->payload->invoice_data->invoice_no)
            );

            $dir = 'uploads/notes_files/' . $this->payload->invoice_data->client_id .'/' . $note_id . '/';

            $pattern = "/<body>(.*?)<\/body>/is";
            preg_match($pattern, $this->payload->body, $res);
            $note = [
                'text' => isset($res[1]) ? $res[1] : $this->payload->body,
                'subject' => $this->payload->subject,
                'cc' => $this->payload->cc,
                'bcc' => $this->payload->bcc,
                'from' => $from,
                'to' => $this->payload->to
            ];
            $this->CI->mpdf->Output(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->payload->invoice_data->invoice_no . '.pdf', 'F');
            @unlink($file);
            bucket_move(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->payload->invoice_data->invoice_no . '.pdf', $dir . $this->payload->invoice_data->invoice_no . '.pdf', ['ContentType' => 'application/pdf']);
            @unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->payload->invoice_data->invoice_no . '.pdf');
            bucket_write_file($dir . 'Content.html', $this->CI->load->view('clients/note_file', $note, TRUE), ['ContentType' => 'text/html']);
            return true;
        }
        @unlink($file);
    }
}
