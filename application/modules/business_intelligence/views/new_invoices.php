<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<?php $sum_total_due_invoices = $sum_total_invoices = 0; ?>
 <style>
      html, body, #map-canvas {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
      #panel {
        position: absolute;
        top: 0px;
        right: 0;
		padding-top: 2px;
      }
    </style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">Invoices</li>
	</ul>
	<section class="panel">
		<header class="panel-heading bg-info lt no-border">
            <div class="clearfix">
				<a href="#" class="pull-left thumb avatar filled_dark_grey b-3x m-r"  style="width:64px; height:64px;">
					<img src="<?php echo base_url('assets/img/estimates.png'); ?>" class="img-circle">
				</a>
				<div id="reportrange" class="pull-right m" style="background: #fff; cursor: pointer; color: #555; padding: 5px 10px; border: 1px solid #ccc;">
					<i class="fa fa-calendar"></i>&nbsp;
					<span></span> <i class="fa fa-caret-down"></i>
				</div>
				<div>
					<div class="h3 m-t-xs m-b-xs text-white">
						<span class="">Invoices Report</span>
					</div>
				</div>   
			</div>
        </header>
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
				<tr>
					<th>Invoice Status</th>
					<th>Qty</th>
					<th>Total Services</th>
					<th>% Outstanding</th>
				</tr>
				</thead>
				<tbody>
					<?php $total_invoices = $sum_service = 0; ?>
					<?php if(isset($invoices_statuses) && !empty($invoices_statuses) && isset($total) && isset($total['sales']) && $total['sales']) : ?>
						<?php foreach($invoices_statuses as $k=>$v) : ?>
							<tr>
								<td><?php echo $v->invoice_status_name; ?> Invoices:</td>
								<td><?php echo isset($invoice_by_statuses[$v->invoice_status_id]) ? $invoice_by_statuses[$v->invoice_status_id]['quantity'] : 0; ?></td>
                                <td><?php echo isset($invoice_by_statuses[$v->invoice_status_id]) ? money($invoice_by_statuses[$v->invoice_status_id]['sales']) : money(0); ?></td>
								<td><?php echo isset($total['sales']) ? round($invoice_by_statuses[$v->invoice_status_id]['sales'] * 100 / $total['sales'], 2) . ' %' : 0; ?></td>
							</tr>
						<?php endforeach;?>					
						<tr>
							<td><strong>Total:</strong></td>
							<td><strong><?php echo $total['quantity']; ?></strong></td>
							<td><strong><?php echo money($total['sales']); ?></strong></td>
							<td><strong>100 %</strong></td>
						</tr>
					<?php else : ?>
						<tr>
							<td><strong style="color:#FF0000;"> No record found</strong></td>
						</tr>
					<?php endif;?>
				</tbody>
			</table>
		</div>
	</section>
		<script>
            $(function() {

                var start = moment('<?php echo isset($from_date) ? $from_date : ''; ?>');
                var end = moment('<?php echo isset($to_date) ? $to_date : ''; ?>');
                var allTime = '<?php echo ($all_time_from) ? $all_time_from : ''; ?>';
                var allFrom = '<?php echo isset($all_time_from) ? $all_time_from : ''; ?>';
                var allTo = '<?php echo isset($all_time_to) ? $all_time_to : ''; ?>';

                if(!start)
                    start = moment().startOf('month');
                if(!end)
                    end = moment().endOf('month');

                function cb(start, end) {
                    $('#reportrange span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
                    if(parseInt(allTime))
                        $('#reportrange span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
                    location.href = baseUrl + 'business_intelligence/new_invoices_report/' + start.format('YYYY-MM-DD') + '/' + end.format('YYYY-MM-DD');
                }

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                        'All Time': [moment(allFrom), moment(allTo)]
                    }
                }, cb);

                $('#reportrange span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
            });
		</script>

	
</section>
<?php $this->load->view('includes/footer'); ?>
