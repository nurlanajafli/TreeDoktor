<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active"><?php echo 'Internal payments'; ?></li>
    </ul>

    <?php $this->load->view('internalPayments/payments_info'); ?>

    <?php $this->load->view('clients/partials/payment_refund_modal'); ?>
    <?php $this->load->view('clients/partials/payment_details_modal'); ?>
</section>

<script>
    const isSystemUser = <?php echo isSystemUser() ? 'true' : 'false'; ?>;
</script>

<script src="<?php echo base_url('assets/js/modules/internalPayments/internal_payments.js?v=1.00'); ?>"></script>

<?php $this->load->view('includes/footer'); ?>
