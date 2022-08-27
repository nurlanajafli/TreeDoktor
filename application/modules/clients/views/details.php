<?php $this->load->view('includes/header'); ?>

<link rel="stylesheet" href="<?php echo base_url('assets/css/modules/clients/client_details.css?v=1.01'); ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/datetimepicker/datetimepicker.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">

<!-- Edit Client Details Modal Loader -->
<?php $this->load->view('clients/client_information_update_modal'); ?>
<!-- End of Edit Customer Details Modal-->
<script>
    const CLIENT_NOTES = true;
    const NOTES_DATA = {
        client_id: <?php echo $client_data->client_id; ?>,
        lead_id: null,
        client_only: true
    };
    const itemsForSelect2 = <?php echo getCategoriesItemsForSelect2() ?: 'null'; ?>;
    const selectTagsEstimators = <?php echo json_encode($estimatorsList) ?: 'null'; ?>;
    var scheduledProcess = false;
    const defaultClientSelectedAddress = <?php echo json_encode($default_client_selected_address); ?>
</script>

<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="<?php echo base_url('clients'); ?>">Clients</a></li>
        <li class="active"><?php echo $client_data->client_name; ?></li>
        <a href="#" class="btn btn-default btn-xs pull-right dk actionsList" style="margin-top: -3px;">Actions <span class="caret"></span></a>
        <section class="dropdown-menu aside-xl actionsDropdown" style="right: 0; left: auto;">
            <section class="panel bg-white">
                <?php $this->load->view('profile_actions_dropdown'); ?>
            </section>
        </section>
    </ul>

    <!-- Client information display -->
    <?php $this->load->view('clients/client_information_display'); ?>

    <section class="media m-n">
        <?php $this->load->view('clients/client_billing_details'); ?>
        <section class="media-body">

            <?php $this->load->view('clients/client_information_files'); ?>
            <?php $this->load->view('clients/client_information_payment_modal');?>
            <?php $this->load->view('clients/client_information_payments'); ?>
            <?php $this->load->view('clients/client_notes_form'); ?>

            <section class="panel panel-default p-n">
                <div id="client-notes"></div>
            </section>
            <?php $this->load->view('clients/notes/notes_tmp'); ?>
        </section>
        <div id="card-block"></div>
    </section>

</section>

<?php //$this->load->view('appointment/appointment_ajax_forms'); ?>

<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/clients/clients.js?v=' . config_item('js_clients')); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/clients/clients_cc_form.js?v=1.01'); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/clients/client_details.js?v=1.03'); ?>"></script>
<script src="<?php echo base_url('assets/js/modules/leads/leads.js?v=1.21'); ?>"></script>

<?php $this->load->view('includes/footer'); ?>
