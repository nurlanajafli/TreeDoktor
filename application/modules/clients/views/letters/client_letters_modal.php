
<div id="email-template-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content panel panel-default p-n" id="email-template-modal-body"></div>
    </div>
</div>

<script type="text/x-jsrender" id="email-template-modal-body-tmp">
    <?php $this->load->view('clients/letters/client_letters_modal_body'); ?>
</script>

<script type="text/x-jsrender" id="estimate-pdf-modal-body-tmp">
    <?php $this->load->view('clients/letters/estimate_pdf_modal_body'); ?>
</script>

<script type="text/x-jsrender" id="partial-invoice-modal-body-tmp">
    <?php $this->load->view('clients/letters/partial_invoice_modal_body'); ?>
</script>

<script type="text/x-jsrender" id="invoice-email-modal-body-tmp">
    <?php $this->load->view('clients/letters/invoice_email_modal_body'); ?>
</script>

<script type="text/x-jsrender" id="schedule-appointment-email-modal-body-tmp">
    <?php $this->load->view('clients/letters/schedule_appointment_email_modal_body'); ?>
</script>

<script type="text/x-jsrender" id="crews-schedule-letters-modal-body-tmp">
    <?php $this->load->view('clients/letters/crews_schedule_letters_modal_body'); ?>
</script>

<?php $this->load->view('clients/letters/get_email_template_form', [
    'callback'=>'ClientsLetters.client_letter_modal'
]); ?>

<script>
    var estimate_total_due = '<?php echo (isset($estimate_data) && $estimate_data->total_due)?$estimate_data->total_due:0; ?>';
</script>

<?php $this->load->view('clients/client_sms_modal'); ?>
