<?php

namespace application\modules\clients\models;
use application\core\Database\EloquentModel;
use application\modules\estimates\models\Estimate;
use application\modules\workorders\models\Workorder;

class ClientLetter extends EloquentModel
{
    protected $table = 'email_templates';
    protected $primaryKey = 'email_template_id';
    //public $brand_id;

    const COLUMNS = [];
    const ENT_NAME = 'Letter';

    const CLIENT_LETTER_KEYWORDS = array(
        '[CUSTOMER_NAME]' => "Customer first and last name",
        '[COMPANY_NAME]'  => "Company name (from Brand settings)",
        '[COMPANY_EMAIL]' => 'Company email address (from Brand settings)',
        '[COMPANY_PHONE]' => 'Company phone number (from Brand settings)',
        '[COMPANY_ADDRESS]'  => 'Full company address (from Brand settings)',
        '[CUSTOMER_ADDRESS]' => 'Full customer address',
        '[JOB_ADDRESS]'     => 'Job site address',
        '[JOB_ADDRESS_CITY]'     => 'Job site city',
        '[COMPANY_WEBSITE]' => 'Company website address (from Brand settings)',
        '[CCLINK:"link"]'   => 'Link to receive payment. The name “link” can be edited',
        '[SIGNATURELINK:"link"]' => 'Link to receive customer signature. The name “link” can be edited',
        '[ESTIMATOR_NAME]'  => 'Estimator name',
        '[CUSTOMER_PHONE]'  => 'Customer phone number',
        '[SERVICES_LIST]'   => 'List of services in work (from the workorder)',
        '[DATE]' => 'Event date',
        '[TIME_AND_DATE]' => 'Event range time and date',
        /*
         * '[CONFIRMATION_LINK]',
         * */
        '[ESTIMATE_NUMBER]' => 'Estimate number',
        '[INVOICE_NUMBER]'  => 'Invoice number',
    );

    const CLIENT_LETTER_SPECIAL_KEYWORDS = array(
        '/\[CCLINK:"(.*?)"\]/is',
        '/\[SIGNATURELINK:"(.*?)"\]/is',
        '/\[QB_CCLINK(.*?)\]/is'
    );

    public static function compileLetter($letter, $brand_id, $data)
    {

        if (isset($data['task']) && $data['task'])
            return self::compileTaskLetter($letter, $brand_id, $data);

        if (isset($data['schedule_event']) && $data['schedule_event'])
            return self::compileScheduleEventLetter($letter, $brand_id, $data);

        return self::compileClientLetter($letter, $brand_id, $data);
    }

    public static function compileClientLetter($letter, $brand_id, $data) {
        $no = $data['estimate']->invoice->invoice_no ?? $data['workorder']->workorder_no ?? $data['estimate']->estimate_no ?? '';

        $replace_array = self::templateClientData($brand_id, $data);

        $letter = self::renderSubjectVars($letter, $replace_array);
        $qb_cc_link = '';
        if(isset($letter) && is_object($letter) && isset($data['estimate']->invoice) && !empty($data['estimate']->invoice)){
            $qb_cc_link = getQbInvoiceLinkForEmail($letter->email_template_text, $data['estimate']->invoice);
        }

        $replace_array['[NAME]'] = '<span class="_var_cc_name">' . (isset($data['client']) && isset($data['client']->primary_contact->cc_name) ? $data['client']->primary_contact->cc_name : 'Client') . '</span>';
        $replace_array['[CUSTOMER_NAME]'] = '<span class="_var_cc_name">' . (isset($data['client']) && isset($data['client']->primary_contact->cc_name) ? $data['client']->primary_contact->cc_name : 'Client') . '</span>';

        $special_replace_array = [
            (isset($no) && $no) ? '<a href="' . config_item('payment_link') . 'payments/' . md5($no . $data['client']->client_id??$data['estimate']->client_id) . '">$1</a>' : '',
            (isset($no) && $no) ? '<a href="' . config_item('payment_link') . 'payments/estimate_signature/' . md5($data['estimate']->estimate_id??$data['workorder']->estimate_id) . '">$1</a>' : '',
            $qb_cc_link
        ];

        $letter = self::renderBodyVars($letter, $replace_array, $special_replace_array, ClientLetter::CLIENT_LETTER_SPECIAL_KEYWORDS);

        return $letter;
    }

    public static function compileTaskLetter($letter, $brand_id, $data) {
        $CI = &get_instance();

        $replace_array = self::templateTaskData($brand_id, $data);
        $letter = self::renderSubjectVars($letter, $replace_array);

        $task_footer = calendar_button_tmp($letter, $data, $replace_array);

        $replace_array['[NAME]'] = '<span class="_var_cc_name">' . (isset($data['task']->client) && isset($data['task']->client->primary_contact->cc_name) ? $data['task']->client->primary_contact->cc_name : 'Client') . '</span>';
        $replace_array['[CUSTOMER_NAME]'] = '<span class="_var_cc_name">' . (isset($data['task']->client) && isset($data['task']->client->primary_contact->cc_name) ? $data['task']->client->primary_contact->cc_name : 'Client') . '</span>';

        $letter->email_template_text = task_to_calendar($task_footer, $letter->email_template_text);

        $letter = self::renderBodyVars($letter, $replace_array);

        return $letter;
    }

    public static function compileScheduleEventLetter($letter, $brand_id, $data){

        $replace_array = self::templateScheduleEventData($brand_id, $data);

        $letter = self::renderSubjectVars($letter, $replace_array);
        $letter = self::renderBodyVars($letter, $replace_array);

        return $letter;
    }

    public static function templateClientData($brand_id, $data)
    {
        $no = $data['estimate']->invoice->invoice_no ?? $data['workorder']->workorder_no ?? $data['estimate']->estimate_no ?? '';
        $cc_link = $signature = '';
        if (isset($data['estimate'])) {
            $cc_link = (isset($no) && $no) ? '<a href="' . config_item('payment_link') . 'payments/' . md5($no . $data['client']->client_id??$data['estimate']->client_id) . '">link</a>' : '';
            $signature = (isset($no) && $no) ? '<a href="' . config_item('payment_link') . 'payments/estimate_signature/' . md5($data['estimate']->estimate_id??$data['workorder']->estimate_id) . '">link</a>' : '';
        }
        $replace_array = [
            '[NAME]'            =>  $data['client']->primary_contact->cc_name??'',
            '[CUSTOMER_NAME]'   =>  $data['client']->primary_contact->cc_name??'',
            '[COMPANY_NAME]'    =>  brand_name($brand_id),
            '[COMPANY_EMAIL]'   =>  brand_email($brand_id),
            '[COMPANY_PHONE]'   =>  brand_phone($brand_id),
            '[COMPANY_ADDRESS]' =>  brand_address($brand_id, config_item('office_address') . ', ' . config_item('office_city') . ', ' . config_item('office_zip')),
            '[ADDRESS]'         =>  isset($data['client']->client_id)?$data['client']->client_address:'',
            '[CUSTOMER_ADDRESS]'=>  isset($data['client']->client_id)?$data['client']->client_address:'',
            '[JOB_ADDRESS]'     =>  isset($data['estimate']->estimate_id)?$data['estimate']->lead->lead_address:'',
            '[JOB_ADDRESS_CITY]'     =>  isset($data['estimate']->estimate_id)?$data['estimate']->lead->lead_city:'',
            '[COMPANY_BILLING_NAME]'    =>  brand_name($brand_id, true),
            '[COMPANY_WEBSITE]'         =>  brand_site($brand_id),
            '[TEAM_SIGNATURE]'          =>  brand_team_signature($brand_id),
            '[SEO_EMAIL]'       =>  brand_email($brand_id),
            '[CCLINK]'          =>  $cc_link,
            '[SIGNATURELINK]'   =>  $signature,
            '[ESTIMATE_NUMBER]'     => $data['estimate']->estimate_no??'[ESTIMATE_NUMBER]',
            '[ESTIMATOR_NAME]'  =>  $data['estimate']->user->full_name??'',
            '[INVOICE_NUMBER]'      => $data['estimate']->invoice->invoice_no??'[INVOICE_NUMBER]',
        ];

        return $replace_array;
    }

    public static function templateTaskData($brand_id, $data) {

        $replace_array = [
            '[NAME]'            =>  $data['task']->client->primary_contact->cc_name??'',
            '[CUSTOMER_NAME]'   =>  $data['task']->client->primary_contact->cc_name??'',
            '[COMPANY_NAME]'    =>  brand_name($brand_id),
            '[COMPANY_EMAIL]'   =>  brand_email($brand_id),
            '[COMPANY_PHONE]'   =>  brand_phone($brand_id),
            '[COMPANY_ADDRESS]' =>  brand_address($brand_id, config_item('office_address') . ', ' . config_item('office_city') . ', ' . config_item('office_zip')),
            '[ADDRESS]'         =>  $data['task']->full_address,
            '[CUSTOMER_ADDRESS]'=>  $data['task']->full_address,
            '[JOB_ADDRESS]'     =>  $data['task']->full_address,
            '[JOB_ADDRESS_CITY]'=>  $data['task']->task_city,
            '[COMPANY_BILLING_NAME]'    =>  brand_name($brand_id, true),
            '[COMPANY_WEBSITE]' =>  brand_site($brand_id),
            '[TEAM_SIGNATURE]'  =>  brand_team_signature($brand_id),
            '[SEO_EMAIL]'       =>  brand_email($brand_id),
            '[ESTIMATOR_NAME]'  =>  $data['task']->user->full_name??'',
            '{address}'         =>  $data['task']->full_address,
            '[PHONE]'           =>  $data['task']->client->primary_contact->cc_phone_view??'',
            '[CUSTOMER_PHONE]'  =>  $data['task']->client->primary_contact->cc_phone_view??'',
        ];

        return $replace_array;
    }

    public static function templateScheduleEventData($brand_id, $data){

        $CI = &get_instance();
        $event_services = $CI->load->view('clients/letters/partials/services', ['schedule_event'=>$data['schedule_event']], true);

        $link = 'http://confirm.' . config_item('company_site_name') . '/confirm/' . md5($data['schedule_event']->workorder->workorder_no . $data['schedule_event']->workorder->id . $data['schedule_event']->workorder->wo_status);
        $link_html = '<a href="' . $link . '">' . $link . '</a>';

        $replace_array = [
            '[NAME]'            =>  $data['schedule_event']->workorder->estimate->client->primary_contact->cc_name??'',
            '[CUSTOMER_NAME]'   =>  $data['schedule_event']->workorder->estimate->client->primary_contact->cc_name??'',
            '[COMPANY_NAME]'    =>  brand_name($brand_id),
            '[COMPANY_EMAIL]'   =>  brand_email($brand_id),
            '[COMPANY_PHONE]'   =>  brand_phone($brand_id),
            '[COMPANY_ADDRESS]' =>  brand_address($brand_id, config_item('office_address') . ', ' . config_item('office_city') . ', ' . config_item('office_zip')),
            '[ADDRESS]'         =>  $data['schedule_event']->workorder->estimate->lead->lead_address??'',
            '[CUSTOMER_ADDRESS]'=>  $data['schedule_event']->workorder->estimate->lead->lead_address??'',
            '[JOB_ADDRESS]'     =>  $data['schedule_event']->workorder->estimate->lead->lead_address??'',
            '[JOB_ADDRESS_CITY]'=>  $data['schedule_event']->workorder->estimate->lead->lead_city??'',
            '[COMPANY_BILLING_NAME]'    =>  brand_name($brand_id, true),
            '[COMPANY_WEBSITE]' =>  brand_site($brand_id),
            '[TEAM_SIGNATURE]'  =>  brand_team_signature($brand_id),
            '[SEO_EMAIL]'       =>  brand_email($brand_id),
            '[ESTIMATOR_NAME]'  =>  $data['schedule_event']->workorder->estimate->user->full_name??'',
            '[PHONE]'           =>  $data['schedule_event']->workorder->estimate->client->primary_contact->cc_phone_view??'',
            '[CUSTOMER_PHONE]'  =>  $data['schedule_event']->workorder->estimate->client->primary_contact->cc_phone_view??'',
            '[SERVICES LIST]'   =>  $event_services,
            '[SERVICES_LIST]'   =>  $event_services,
            '[DATE]'            =>  $data['schedule_event']->event_date,
            '[TIME]'            =>  $data['schedule_event']->event_time_interval_string,
            '[TIME AND DATE]'   =>  $data['schedule_event']->event_time_interval_string,
            '[TIME_AND_DATE]'   =>  $data['schedule_event']->event_time_interval_string,
            '[CONFIRMATION_LINK]'   =>  $link_html
        ];

        return $replace_array;
    }

    public static function renderSubjectVars($letter, $data){
        $keywords = array_keys($data);
        $values = array_values($data);
        $letter->email_template_title = str_replace(
            $keywords,
            $values,
            $letter->email_template_title
        );

        return $letter;
    }

    public static function renderBodyVars($letter, $data, $special_data=[], $special_keywords=[]){
        $keywords = array_keys($data);
        $values = array_values($data);

        $letter->email_template_text = str_replace(
            $keywords,
            $values,
            $letter->email_template_text
        );

        if ($special_data){
            $letter->email_template_text = preg_replace(
                $special_keywords,
                $special_data,
                $letter->email_template_text
            );
        }

        return $letter;
    }

    public static function parseCustomTemplates($estimate_id, $text, $brand_id, $workorder_id=false)
    {
        $letter = (object) ['email_template_text' => $text, 'email_template_title'=>''];
        $data = [];

        if($workorder_id){
            $data['workorder'] = Workorder::with(['estimate.user', 'estimate.client.primary_contact'])->find($workorder_id);
            $data['estimate'] = [];
            $data['client'] = $data['workorder']->client;
        }

        if($estimate_id) {
            $data['estimate'] = Estimate::with(['client.primary_contact', 'user', 'lead', 'invoice'])->find((int)$estimate_id);
            $data['client'] = $data['estimate']->client;
        }

        if(!isset($data['workorder']) && !isset($data['estimate']))
            return $text;

        $letter = self::compileLetter($letter, $brand_id, $data);

        return $letter->email_template_text;
    }

}
