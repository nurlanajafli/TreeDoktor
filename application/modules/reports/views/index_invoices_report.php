<?php $this->load->view('includes/header'); ?>
<?php $this->load->view('_partials/invoice_report_filter');?>
    <a href="#" class="btn btn-lg btn-warning btn-rounded export-csv pull-right" title="Download CSV">
        <i class="fa fa-download"></i>
    </a>
<section class="panel invoice-report">
    <div class="report-range-body bg-light">
        <div class="control-label inline p-right-10 report-title">Report period</div>
        <div class="input-group report-range inline" data-url=""
             data-dates="<?php echo isset($dates) ? htmlspecialchars(json_encode($dates)) : ''; ?>">
            <i class="fa fa-calendar"></i>&nbsp;
            <span></span>
        </div>
    </div>
    <?php $this->load->view('_partials/invoice_report_table', ['invoices' => $invoices ?? []]);?>
</section>

<script src="<?php echo base_url('assets/js/modules/reports/invoice_report.js?v=1'); ?>"></script>
    <!-- reports css -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/reports.css">

<?php $this->load->view('includes/footer'); ?>