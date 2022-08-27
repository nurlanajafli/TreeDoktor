<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients_cc_form.js?v=1.01"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/tinymce/tinymce.min.js"></script>
<!--Modals load-->
<?php $this->load->view('clients/client_information_update_modal'); ?>
<?php $this->load->view('invoices/modals/profile_change_status_modal'); ?>
<?php //$this->load->view('invoices/profile_qa_modal'); ?>
<!--/Modals load -->
<script>
    const CLIENT_NOTES = true;
    const NOTES_DATA = {
        client_id: <?php echo $estimate_data->client_id; ?>,
        lead_id: <?php echo $estimate_data->lead_id ?: null; ?>,
        client_only: false
    };

    $(document).ready(function () {
        $('.actionsList').on('click', function (event) {
            console.log('actionsList');
            $(this).parent().toggleClass('open');
        });
        $('body').on('click', function (e) {
            if (!$('.actionsDropdown').is(e.target)
                && $('.actionsDropdown').has(e.target).length === 0
                && $('.open').has(e.target).length === 0
                && !$(e.target).is('.modal.fade')
                && !$(e.target).parents('.modal.fade').length
            ) {
                $('.actionsDropdown').removeClass('open');
                $('.actionsDropdown').parent().removeClass('open');
            }
        });
        $(document).on('keyup', '#payment_amount', function () {
            let amount = Common.getAmount($(this).val()) || 0;
            let with_fee = amount + (amount * (cc_extra_fee / 100));
            $(this).parents('.form-group').find('#with_fee').text(Common.money(with_fee.toFixed(2)));
        });
    });
</script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('invoices'); ?>">Invoices</a></li>
		<li class="active">Profile - <?php echo $invoice_data->invoice_no; ?></li>
		<a href="#" class="btn btn-default btn-xs pull-right dk actionsList" style="margin-top: -3px;">Actions <span class="caret"></span></a>
        <section class="dropdown-menu aside-xl actionsDropdown" style="right: 0; left: auto;">
            <section class="panel bg-white">

                <?php $this->load->view('profile_actions_dropdown'); ?>

            </section>
        </section>
	</ul>
	<!-- Client information -->
	<?php $this->load->view('clients/client_information_display'); ?>
	<!-- /Client information ends -->

	<section class="media m-n">
		<!-- Invoice Options -->
		<?php $this->load->view('invoices/profile_invoice_options'); ?>
        <?php $this->load->view('clients/letters/client_letters_modal'); ?>
		<!-- /Invoice Options -->
		
		<!-- Estimate Data -->
		<?php $this->load->view('estimates/estimate_data_display'); ?>
		<!-- /Estimate Data -->
        <?php $this->load->view('clients/client_notes_form'); ?>

        <section class="panel panel-default p-n">
            <div id="client-notes"></div>
        </section>
        <?php $this->load->view('clients/notes/notes_tmp'); ?>

        <?php $this->load->view('clients/client_information_payment_modal'); ?>
    </section>
    <div id="card-block"></div>
	<div id="email-thanks" class="modal fade" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-hidden="false">
		<form data-type="ajax" data-callback="checkMessage" data-location="<?php echo current_url(); ?>" data-url="<?php echo base_url('clients/ajax_send_email'); ?>" class="modal-dialog" style="width: 900px;">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Email to <?php echo $client_data->client_name; ?></header>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="control-group">
							<label class="control-label">Email to <?php echo $client_data->client_name; ?></label>
							<div class="controls">
								<input class="email form-control" name="email" type="text"
								       value="<?php echo isset($client_contact['cc_email'])?trim(strtolower($client_contact['cc_email'])):''; ?>"
								       placeholder="Email to..." style="background-color: #fff;"/>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Email from </label>
							<div class="controls">
								<input class="fromEmail form-control" name="from_email" type="text"
								       value="<?php echo $estimate_data->user_email; ?>"
								       placeholder="Email from..." style="background-color: #fff;"/>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Email Subject</label>
							<div class="controls">
								<input class="subject form-control" name="subject" type="text"
								       value="<?php echo $thanks_text['email_template_title'];?>"
								       placeholder="Email Subject" style="background-color: #fff;"/>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Email Text</label>
							<div class="controls">
								<input id="estimate" type="hidden" name="estimate" value="<?php echo $estimate_data->estimate_id; ?>">
								<textarea id="template_text" name="text" class="form-control" value=""><?php echo trim(strip_tags($thanks_text['email_template_text'])); ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" type="submit">
						<span class="btntext" >Send</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
						     class="preloader">
					</button>
					<button class="btn" onclick="location.reload(); return false;">Close</button>
				</div>
			</div>
		</form>
	</div>

<script src="<?php echo base_url(); ?>assets/js/modules/invoices/invoices_status.js?v=1.0"></script>
<script type="text/javascript">
	var services = <?php echo json_encode($estimate_data->mdl_services_orm); ?>;
	$("document").ready(function () {
		Common.initTinyMCE('template_text_<?php echo $client_data->client_id; ?>');
    });
	
	function checkMessage(resp)
	{
		if(resp.status == 'error')
			alert(resp.message);
		return false;
	}
</script>

<?php $this->load->view('includes/footer'); ?>
