<?php


namespace application\modules\messaging\models;

use application\core\Database\EloquentModel;
use application\modules\estimates\models\Estimate;

class SmsTpl extends EloquentModel
{
    protected $table = 'sms_tpl';
    protected $primaryKey = 'sms_id';

    protected $fillable = [
        'sms_name',
        'sms_text',
        'user',
        'system_label'
    ];

    public function compileSmsTemplate(Estimate $estimate, SmsTpl $sms) {
        $compiledText = trim(
            str_replace(
                ['[CCLINK]', '[SIGNATURELINK]', '[ESTIMATE_LINK]', '[ESTIMATE_ID]', '[ADDRESS]', '[AMOUNT]', '[DATE]', '[COMPANY_NAME]', '[COMPANY_EMAIL]', '[COMPANY_PHONE]', '[COMPANY_ADDRESS]', '[COMPANY_BILLING_NAME]', '[COMPANY_WEBSITE]'],
                [
                    isset($estimate) && isset($estimate->estimate_id) ?
                        config_item('payment_link') . 'payments/' . md5($estimate->estimate_no . $estimate->client_id) : '',
                    isset($estimate) && isset($estimate->estimate_id) ?
                        config_item('payment_link') . 'payments/estimate_signature/' . md5($estimate->estimate_id) : '',
                    isset($estimate) && isset($estimate->estimate_id) ?
                        config_item('payment_link') . 'payments/estimate/' . md5($estimate->estimate_no . $estimate->client_id) : '',
                    isset($estimate) && isset($estimate->estimate_id) ? $estimate->estimate_id : '',
                    (isset($lead_data->lead_address) || isset($lead_data['lead_address'])) ?
                        ((array)$lead_data)['lead_address'] : ((isset($client_data->client_address) || isset($client_data['client_address'])) ?
                        ((array)$client_data)['client_address'] : '-'),
                    isset($amount) ? money($amount) : '[AMOUNT]',
                    $client_contact['event_date'] ?? '-',
                    (brand_name($brand_id))?brand_name($brand_id):$this->config->item('company_name_short'),
                    (brand_email($brand_id))?brand_email($brand_id):$this->config->item('account_email_address'),
                    (brand_phone($brand_id))?brand_phone($brand_id):$this->config->item('office_phone_mask'),
                    brand_address($brand_id,$this->config->item('office_address') . ', ' . $this->config->item('office_city') . ', ' . $this->config->item('office_zip')),
                    (brand_name($brand_id))?brand_name($brand_id):$this->config->item('company_name_long'),
                    $this->config->item('company_site')
                ],
                $sms->sms_text
            )
        );

        $compiledText = trim(
            str_replace(
                ['[NAME]', '[EMAIL]'],
                [$client_contact['cc_name'] ?? '[NAME]', $client_contact['cc_email'] ?? '[EMAIL]'],
                $compiledText
            )
        );

        return $compiledText;
    }
}