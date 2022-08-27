<nav class="nav-primary animated fadeInRight">
    <ul class="nav">
        <li>
            <a title="Edit Status" href="#changeInvoiceStatus" role="button" data-toggle="modal">
                <i class="fa fa-pencil"><b class="bg-success"></b></i>
                Update Status
            </a>
        </li>
        <li>
            <?php echo anchor($invoice_data->invoice_no . '/pdf', '<i class="fa fa-file"><b class="bg-dark"></b></i>Download PDF', 'type="button"'); ?>
        </li>
            <?php //echo anchor('invoices/edit/'.$invoice_data->id, '<i class="icon-edit icon-white"></i>&nbsp;&nbsp;Edit Invoice&nbsp;&nbsp;', 'class="btn btn-danger btn-block type="button"'); ?>
        <li>
            <a href="#new_payment" role="button" data-toggle="modal">
                <i class="fa fa-credit-card"><b class="bg-success"></b></i>
                Add Payment
            </a>
        </li>

        <?php //if (isset($client_contact['cc_email']) && $client_contact['cc_email'] && filter_var($client_contact['cc_email'], FILTER_VALIDATE_EMAIL)): ?>
            <?php $sent = $invoice_data->default == 1 ? 'E-mail' : 'Re-send'; ?>
            <li>
                <a title="Sent PDF to Email" id="sendLink" href="#email-template-modal" data-callback="ClientsLetters.invoice_email_modal"
                   data-email_template_id="<?php echo $invoice_letter_template_id ?? 0; ?>" role="button" type="button" data-toggle="modal">
                    <i class="fa fa-inbox"><b class="bg-warning"></b></i>
                    <?php echo $sent; ?> Invoice
                </a>
           </li>
        <?php //endif; ?>

        <?php $this->load->view('includes/messages/actions_dropdown'); // SMS messages ?>

        <li>
            <a title="QA" href="#addInvoiceQA" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                <i class="fa fa-thumbs-up"><b class="bg-primary"></b></i>
                QA
            </a>
        </li>
        <li>
            <?php echo anchor('invoices/', '<i class="fa fa-times"><b class="bg-info"></b></i>Close'); ?>
        </li>
    </ul>
</nav>
