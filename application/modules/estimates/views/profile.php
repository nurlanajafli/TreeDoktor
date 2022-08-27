<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients_cc_form.js?v=1.01"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/estimates/estimate_copy.js?v=1.13"></script>

<!--Modal-->
<?php if (!empty($invoice_data) && $invoice_data && !empty($invoice_data)) : ?>
	<?php $this->load->view('invoices/profile_qa_modal'); ?>
<?php endif; ?>
<?php require_once('templates/estimate_copy_form.php'); // Modal Form for copy ?>
<!--/Modal-->
<section class="scrollable p-sides-15">
<script>
    const CLIENT_NOTES = true;
    const search_by_clients=<?php echo $search_by_clients[0]['id'];?>;
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
    });
</script>
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
		<li class="active">Profile - <?php echo $estimate_data->estimate_no; ?></li>
		<a href="#" class="btn btn-default btn-xs pull-right dk actionsList" style="margin-top: -3px;">Actions <span class="caret"></span></a>
        <section class="dropdown-menu aside-xl actionsDropdown" style="right: 0; left: auto;">
            <section class="panel bg-white">

                <?php $this->load->view('profile_actions_dropdown'); ?>

            </section>
        </section>
	</ul>

	<!-- /Client information display -->
	<?php $this->load->view('clients/client_information_display'); ?>
	<?php $this->load->view('clients/client_information_payment_modal'); ?>
	<!-- /Client information display -->

	<section class="media m-n">
		<!--Estimate Options -->

        <?php $this->load->view('estimates/estimate_options_display'); ?>

		<?php $this->load->view('clients/letters/client_letters_modal'); ?>

		<?php //$this->load->view('estimates/sent_confirmed_email'); ?>
		<!--/Estimate Options -->
		<!-- /Client information display -->
		<?php $this->load->view('estimates/estimate_data_display'); ?>
		<!-- /Client information display -->
	</section>

	<!-- Project Requirements Display -->
	<?php $this->load->view('estimates/estimate_project_requirements'); ?>

	<!-- /Project Requirements Display End-->
	<?php $this->load->view('clients/client_notes_form'); ?>

    <section class="panel panel-default p-n">
        <div id="client-notes"></div>
    </section>
    <?php $this->load->view('clients/notes/notes_tmp'); ?>


	
	<?php $this->load->view('estimates/profile_update_estimate_status_modal'); ?>
	<?php $this->load->view('clients/client_information_update_modal'); ?>
	<?php $this->load->view('estimates/profile_add_new_call_modal'); ?>

	<?php $this->load->view('estimates/profile_scripting'); ?>
    <div id="card-block"></div>
	<?php $this->load->view('includes/footer'); ?>

