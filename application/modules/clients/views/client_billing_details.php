<section class="pull-right hidden-sm hidden-xs client-buttons" style="width:252px;">
	<?php if (isset($this->session->userdata["TSKS"]) && $this->session->userdata["TSKS"] == 0) { ?>
	<?php } else { ?>
		<?php $this->load->view('appointment/create_task_form'); ?>

	<?php } ?>
	<?php if (is_cl_permission_none()) { ?>
	<?php } else { ?>
		<a href="#new_lead" role="button" class="btn btn-info btn-block" data-toggle="modal">
            Create new lead
        </a>
	<?php } ?>

	<?php if (isset($this->session->userdata["CC"]) && $this->session->userdata["CC"] == 0) { ?>
	<?php } else { ?>
        <?php if(config_item('processing')) : ?>
            <a href="#billing_details" role="button" class="btn btn-warning btn-block" data-toggle="modal">
                Billing Details
            </a>
		<?php endif; ?>
        <?php if ($client_estimates && $client_estimates->num_rows()): ?>
			<a href="#new_payment" role="button" class="btn btn-success btn-block" data-toggle="modal">
                Add Payment
            </a>
		<?php endif; ?>
	<?php } ?>
    <div>
        <a class="btn btn-block btn-danger dropdown-toggle" style="overflow: hidden;text-overflow: ellipsis;" data-toggle="dropdown">
            Send Email to <?php echo $client_data->client_name; ?>
            <span class="caret" style="margin-left:5px;"></span>
        </a>
        <ul class="dropdown-menu btn-block" id="wo_status" data-type="workorders" style="max-height: 200px; overflow-y: auto;">
            <?php foreach($letters as $key=>$letter) : ?>
						<li><span  href="#email-template-modal" data-callback="ClientsLetters.client_letter_modal"
                   data-email_template_id="<?php echo $letter['email_template_id']; ?>"
                   data-toggle="modal" style="padding-right: 6px;padding-left: 6px;"><?php echo $letter['email_template_title']; ?> <span
                        class="badge bg-info"></span>
                            </span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php //endif; ?>
    <?php if((isset($client_contact['cc_phone_clean']) && $client_contact['cc_phone_clean']) && (isset($voices) && !empty($voices)) && $this->session->userdata('twilio_worker_id')) : ?>
        <?php if(config_item('phone')): ?>
            <div>
                <a class="btn btn-block btn-info dropdown-toggle" style="overflow: hidden;text-overflow: ellipsis;" data-toggle="dropdown">
                    Voice Message to <?php echo $client_data->client_name; ?>
                    <span class="caret" style="margin-left:5px;"></span>
                </a>

                <ul class="dropdown-menu" id="" style="max-height: 200px; overflow-y: scroll;">
                    <?php foreach($voices as $key => $voice): ?>
                        <li>
                            <span href="#" class="addCall" data-voice="<?php echo $voice->voice_id; ?>" data-number="<?php echo $client_contact['cc_phone_clean']; ?>"
                               style="padding-right: 6px;padding-left: 6px;"><?php echo $voice->voice_name; ?> <span class="badge bg-info"></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php $this->load->view('includes/messages/menu_button'); // SMS messages ?>

	<a href="<?php echo base_url('tree_inventory/scheme/'.$client_data->client_id); ?>" target="_blank" class="btn btn-info btn-block">
		<span class="fa fa-map-marker">&nbsp;</span>&nbsp;<span>Tree Inventory Map</span>
	</a>
	
</section>
<?php $this->load->view('clients/letters/client_letters_modal'); ?>

<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
