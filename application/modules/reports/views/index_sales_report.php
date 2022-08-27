<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
    window.filterTags = <?php echo \application\modules\clients\models\Tag::all()->each(function($item, $key) {
        $item->text = $item->name;
        $item->id = $item->tag_id;
    })->toJson(); ?>;
</script>
<script src="<?php echo base_url('assets/js/modules/reports/sales.js') ?>?v=1.03"></script>
<section class="scrollable p-sides-15 scrollable-y-auto">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Sales Report</li>
	</ul>

	<div class="row">
        <?php $this->load->view('_partials/sales_report_filters'); ?>
        <?php $this->load->view('_partials/sales_report_results'); ?>
	</div>
</section>

<?php $this->load->view('includes/footer'); ?>
