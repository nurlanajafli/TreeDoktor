<?php if (config_item('messenger')): ?>
    <?php
        $estimate_invoice_data = $estimate_data ?? $invoice_data ?? [];
        $brand_id = get_brand_id($estimate_invoice_data, $client_data ?? []);

        $messages = isset($messages) && is_array($messages) ? $messages : [];
        // for leads/map
        if (isset($sms) && is_object($sms) && sizeof($messages) === 0) {
            $messages[] = $sms;
        } else {
            $messages[] = (object) [
                'sms_id' => null,
                'sms_name' => 'Blank SMS',
                'sms_text' => ''
            ];
        }
    ?>

    <?php //if(isset($messages) && is_array($messages)  /*&& (isset($voices) && count($voices)) && $this->session->userdata('twilio_worker_id')*/) : ?>
        <?php foreach($messages as $key => $sms): ?>
            <div id="sms-<?php echo $sms->sms_id; ?>" class="modal fade" role="dialog" tabindex="-1"  aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content panel panel-default p-n">
                        <header class="panel-heading">Sms to <?php echo $client_data->client_name ?? ''; ?></header>
                        <div class="modal-body">
                            <div class="form-horizontal">
                                <div class="control-group">
                                    <label class="control-label">Sms to <?php echo $client_data->client_name ?? ''; ?></label>
                                    <div class="controls">
                                        <input class="client_number form-control" type="text"
                                               value="<?php echo $client_contact['cc_phone_clean'] ?? ''; ?>"
                                               placeholder="Sms to..." style="background-color: #fff;"/>
                                    </div>
                                </div>

                                <div class="control-group">
                                    <label class="control-label">Sms Text</label>
                                    <div class="controls">
                                        <?php $compiledText = ''; ?>
                                        <?php if (!empty($sms->sms_text)): ?>
                                            <?php $no = $invoice_data->invoice_no ?? $estimate_data->estimate_no ?? ''; ?>
                                            <?php $compiledText = trim(str_replace(
                                                ['[CCLINK]', '[SIGNATURELINK]', '[ESTIMATE_LINK]', '[ESTIMATE_ID]', '[ADDRESS]', '[AMOUNT]', '[DATE]', '[COMPANY_NAME]', '[COMPANY_EMAIL]', '[COMPANY_PHONE]', '[COMPANY_ADDRESS]', '[COMPANY_BILLING_NAME]', '[COMPANY_WEBSITE]'],
                                                [
                                                    isset($estimate_data) && isset($estimate_data->estimate_id) ?
                                                    config_item('payment_link') . 'payments/' . md5($estimate_data->estimate_no . $client_data->client_id??$estimate_data->client_id??$invoice_data->client_id) : '',
                                                    isset($estimate_data) && isset($estimate_data->estimate_id) ?
                                                        config_item('payment_link') . 'payments/estimate_signature/' . md5($estimate_data->estimate_id) : '',
                                                    isset($estimate_data) && isset($estimate_data->estimate_id) ?
                                                    config_item('payment_link') . 'payments/estimate/' . md5($estimate_data->estimate_no . $client_data->client_id??$estimate_data->client_id??$invoice_data->client_id) : '',
                                                    isset($estimate_data) && isset($estimate_data->estimate_id) ? $estimate_data->estimate_id : '',
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
                                                $sms->sms_text)
                                            ); ?>
                                            <?php $compiledText = trim(str_replace(['[NAME]', '[EMAIL]'],
                                                [$client_contact['cc_name'] ?? '[NAME]', $client_contact['cc_email'] ?? '[EMAIL]'], $compiledText
                                            )); ?>
                                        <?php endif; ?>
                                        <textarea data-text="<?php echo htmlspecialchars($compiledText); ?>" class="form-control sms_text"
                                                  style="height: 200px; resize: vertical !important; max-width: 558px;"><?php echo $compiledText; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-success addSMS" data-sms="<?php echo $sms->sms_id; ?>"
                                    data-client="<?php echo $client_data->client_name ?? ''; ?>"
                                    data-number="<?php echo $client_contact['cc_phone_clean'] ?? ''; ?>">
                                <span class="btntext">Send</span>
                                <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
                                     class="preloader">
                            </button>
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php //endif; ?>
    <script src="<?php echo base_url('assets/js/modules/clients/client_sms_modal.js?v=1.00'); ?>"></script>
<?php endif; ?>
