<nav class="nav-primary animated fadeInRight">
    <ul class="nav">
        <li>
            <a title="Call" href="#addEstimateCall" role="button" data-toggle="modal">
                <i class="fa fa-headphones"><b class="bg-primary"></b></i>
                Contact
            </a>
        </li>
        <li>
            <a title="Update Estimate" href="#changeEstimateStatus" role="button" data-toggle="modal">
                <i class="fa fa-pencil"><b class="bg-success"></b></i>
                Update Status
            </a>
        </li>
        <li>
            <?php echo anchor($estimate_data->estimate_no . '/pdf', '<i class="fa fa-file"><b class="bg-dark"></b></i>Download Estimate PDF', ' type="button"'); ?>
        </li>
        <?php //if (/*!intval($client_data->client_unsubscribe) && */isset($client_contact['cc_email']) && $client_contact['cc_email'] && filter_var($client_contact['cc_email'], FILTER_VALIDATE_EMAIL)): ?>
        <li>
            <a title="Email Estimate with PDF" <?php /*href="#sentEstimatePdfToEmail"*/ ?> href="#email-template-modal"
               data-callback="ClientsLetters.estimate_pdf_letter_modal"
               data-email_template_id="<?php echo $estimate_pdf_letter_template_id ?? 0; ?>" role="button"  type="button" data-toggle="modal">
                <i class="fa fa-mail-forward"><b class="bg-warning"></b></i>
                <?php if($estimate_data->est_status_default == 1): ?>
                    Email Estimate with PDF
                <?php else: ?>
                    Re-send Estimate PDF
                <?php endif; ?>
           </a>
        </li>
        <?php //endif; ?>

        <?php $this->load->view('includes/messages/actions_dropdown'); // SMS messages ?>

        <?php if ($client_estimates && $client_estimates->num_rows()) : ?>
            <li>
                <a href="#new_payment" role="button" data-toggle="modal">
                    <i class="fa fa-credit-card"><b class="bg-success"></b></i>
                    Add Payment
                </a>
            </li>
        <?php endif; ?>
        <li>
            <?php echo anchor('estimates/edit/' . $estimate_data->estimate_id, '<i class="fa fa-edit"><b class="bg-danger"></b></i>Edit Estimate', ' type="button"'); ?>
        </li>
        <?php if (!empty($invoice_data) && $invoice_data && !empty($invoice_data)): ?>
            <li>
                <a title="QA" href="#addInvoiceQA" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                    <i class="fa fa-thumbs-up"><b class="bg-primary"></b></i>
                    QA
                </a>
            </li>
        <?php endif; ?>
        <li>
            <?php echo anchor('estimates/', '<i class="fa fa-times"><b class="bg-info"></b></i>Close', ''); ?>
        </li>
    </ul>
</nav>
