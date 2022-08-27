<nav class="nav-primary animated fadeInRight">
    <ul class="nav">
        <li>
        <a role="button" target="_blank" href="<?php echo base_url('tree_inventory/scheme/'.$client_data->client_id); ?>">
            Tree Inventory Map<i class="fa fa-map-marker"><b class="bg-success"></b></i>
        </a>
        </li>
        <?php if (isset($this->session->userdata["TSKS"]) && $this->session->userdata["TSKS"] == 0) : ?>
        <?php else : ?>
            <li>
                <a href="#new_task" role="button" class="create_appointment_modal">
                    Create New Task
                    <i class="fa fa-tasks icon"><b class="bg-danger"></b></i>
                </a>
            </li>
        <?php endif; ?>

        <?php if (is_cl_permission_none()) : ?>
        <?php else : ?>
            <li>
                <a href="#new_lead" role="button" data-toggle="modal" data-backdrop="static"
                   data-keyboard="false">Create
                    New Lead
                    <i class="fa fa-bars icon"><b class="bg-success"></b></i>
                </a>
            </li>
        <?php endif; ?>

        <?php if (is_cl_permission_none()) : ?>
        <?php else : ?>
            <?php if(config_item('processing')) : ?>
            <li>
                <a href="#billing_details" role="button" data-toggle="modal" data-backdrop="static"
                   data-keyboard="false">
                    Billing Details
                    <i class="fa fa-credit-card icon"><b class="bg-warning"></b></i>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($client_estimates && $client_estimates->num_rows()) : ?>
                <li>
                    <a href="#new_payment" role="button" data-toggle="modal" data-backdrop="static"
                       data-keyboard="false">
                        <i class="fa fa-money icon"><b class="bg-info"></b></i>
                        Add Payment
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php /*<li>
            <a href="#getEmails" id="clientEmails" data-toggle="modal" data-backdrop="true" data-keyboard="true">
                <i class="fa fa-envelope-o"><b class="bg-dark"></b></i>
                E-mails History
            </a>
        </li>*/
        ?>

        <li>
            <a href="#">
                <i class="fa fa-mail-forward"><b class="bg-dark"></b></i>
                Send Email
                <span class="pull-right">
                    <i class="fa fa-angle-down text"></i>
                    <i class="fa fa-angle-up text-active"></i>
                </span>
            </a>
            <ul class="nav lt">
                <?php foreach ($letters as $key => $letter) : ?>
                    <li>
                        <a href="#email-template-modal" data-callback="ClientsLetters.client_letter_modal"
                           data-email_template_id="<?php echo $letter['email_template_id']; ?>"
                           data-toggle="modal">
                            <i class="fa fa-angle-right"></i>
                            <?php echo $letter['email_template_title']; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>

        <?php if ((isset($client_contact['cc_phone']) && $client_contact['cc_phone']) && (isset($voices) && !empty($voices)) && $this->session->userdata('twilio_worker_id')) : ?>
            <?php if(config_item('phone')) : ?>
                <li>
                    <a href="#">
                        <i class="fa fa-microphone"><b class="bg-dark"></b></i>
                        Send Voice Message
                        <span class="pull-right">
                            <i class="fa fa-angle-down text"></i>
                            <i class="fa fa-angle-up text-active"></i>
                        </span>
                    </a>
                    <ul class="nav lt">
                        <?php foreach ($voices as $key => $voice) : ?>
                            <li>
                                <a href="#" class="addCall1" data-voice="<?php echo $voice->voice_id; ?>"
                                   data-number="<?php echo isset($client_contact['cc_phone']) ? substr($client_contact['cc_phone'], 0, 10) : ''; ?>">
                                    <i class="fa fa-angle-right"></i>
                                    <?php echo $voice->voice_name; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php $this->load->view('includes/messages/actions_dropdown'); // SMS messages ?>
    </ul>
</nav>
