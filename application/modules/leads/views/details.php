<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.css" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/theme.css" type="text/css" />
<link href="<?php echo base_url('assets/vendors/notebook/js/datetimepicker/datetimepicker.css'); ?>" rel="stylesheet">
<link rel="stylesheet" href="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.css'); ?>">

<style type="text/css">
    .m-bottom-0{ margin-bottom: 0; }
    .datepicker-inline{ font-size: 21px; width: 277px; }
    .hour, .minute{
        color: #333;
        font-weight: 600;
    }
</style>

<!-- Edit Client Details Modal Loader -->
<?php $this->load->view('clients/client_information_update_modal'); ?>
<!-- End of Edit Customer Details Modal-->
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="<?php echo base_url('leads'); ?>">Leads</a></li>
        <li class="active">Edit - <?php echo $row['lead_no']; ?></li>
    </ul>
    <!-- Client profile -->
    <?php $this->load->view('clients/client_information_display'); ?>
    <!-- /Client profile -->
    <?php echo form_open('leads/update_lead', ['class' => 'editLeadForm']); ?>
        <!-- Lead Date and Status -->
        <section class="col-md-12 panel panel-default p-n">
            <header class="panel-heading">Lead Details</header>
            <div class="panel-body">
                <?php $this->load->view('clients/partials/edit_lead_modal');?>
            </div>
        </section>
        <!-- Submit Values -->
        <section class="col-md-12 m-top-10 m-bottom-20">
            <div class="m-10 pull-right">
                <?php echo anchor('client/' . $row['client_id'], 'Close', 'class="btn btn-info"'); ?>
                <?php echo form_submit('submit', 'Update', "class='btn btn-success'"); ?>
            </div>
        </section>
        <!-- /Submit Values -->
    <?php echo form_close() ?>

    <?php $this->load->view('clients/appointment/appointment_ajax_forms'); ?>
    <?php $this->load->view('clients/appointment/schedule_appointment_modal'); ?>
</section>

<script>
    const itemsForSelect2 = <?php echo getCategoriesItemsForSelect2() ?: 'null'; ?>;
</script>

<script src="<?php echo base_url(); ?>assets/js/modules/clients/clients.js?v=<?php echo config_item('js_clients'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/select2/select2.min.js"></script>
<script src="<?php echo base_url('assets/js/libs/dropzone/min/dropzone.min.js'); ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/modules/leads/leads.js?v=1.21"></script>

<?php $this->load->view('includes/footer'); ?>

