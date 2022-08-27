<?php $pdfFiles = isset($workorder_data) && $workorder_data->wo_pdf_files ? json_decode($workorder_data->wo_pdf_files) : []; ?>
<?php $pdfFiles = isset($invoice_data) && $invoice_data->invoice_pdf_files ? json_decode($invoice_data->invoice_pdf_files) : $pdfFiles; ?>

<?php if (empty($schedule) && !isset($unsortable)) : ?>
<script src="<?php echo base_url('assets/js/ajaxfileupload.js'); ?>"></script>
<?php endif; ?>
<style>
	.btn-upload, .est-upload  {
		position: absolute;
		z-index: 2;
		top: 0;
		left: 0;
		filter: alpha(opacity=0);
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
		opacity: 0;
		background-color: transparent;
		color: transparent;
	}

	.btn-file, .est-file {
		position: relative;
		overflow: hidden;
	}
</style>
<!-- Display Workorder Details -->
<div class="row">
    <div class="col-xs-12 col-md-8">
        <?php $this->load->view('partials/workorder_details_block', ['pdfFiles' => $pdfFiles]); ?>
    </div>
    <div class="col-sm-12 col-md-4 p-right-20 p-left-0">
        <?php $this->load->view('partials/profitability_block'); ?>
    </div>
</div>
<!-- /Display Workorder Details ends-->
