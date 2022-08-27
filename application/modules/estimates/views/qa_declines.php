<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.pie.min.js'); ?>"></script>
<style type="text/css">
				#flotcontainer{
					width: 100%;
					height: 450px;
					display: inline-block;
				}
				.pieLabel{font-weight: bold;}
				.plot-label{color: inherit!important;}
				.legendLabel{text-align: left;padding-left: 5px;}
				</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
		<li class="active">Decline Reasons</li>
	</ul>


		<section class="panel panel-default">
			<header class="panel-heading">Decline Reasons
				<div class="pull-right" >
					<form id="dates" method="post" action="<?php echo base_url('estimates/declines'); ?>" class="input-append m-t-xs">
						<label>
							<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
                                value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d');
                                else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
						</label>
						— 
						<label>
							<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
                                   value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d');
                                   else : echo date(getDateFormat()); endif; ?>">
						</label>
						<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					</form>
				</div>
				<div class="clear"></div>
			</header>
			<div style="height:300px; color:#fff;display:table;width: 100%;" class="text-center">
				<?php if(!isset($declined)) : ?>
				<div style="display:table-cell; vertical-align: middle; color: #000; font-size: 50px;">NO DATA</div>
				<?php else : ?>
					<?php /* foreach($statuses as $key=>$status) : ?>
					<?php if(isset($status->mdl_est_reason) && count($status->mdl_est_reason)) : ?>
						<?php foreach($status->mdl_est_reason as $jkey=>$reason) : ?>
							<?php if(isset($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) && count($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))])) : ?>
								<div style="display: table-row;">
									<div class="progress-bar-<?php if($status->est_status_declined) : echo 'danger'; endif;?> b-t" style="height:<?php echo count($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) / $count_estimates * 100;?>%; display:table-cell; vertical-align: middle; "><?php echo round(count($declined[mb_strtolower(str_replace($symbols, '_', $reason->reason_name))]) / $count_estimates * 100, 2) . '% '  . $reason->reason_name; ?></div>
								</div>
							<?php endif;?>
						<?php endforeach; ?>
					<?php endif;?>
					<?php endforeach; */?>
					<div class="plots text-center">
						<div id="flotcontainer" class="plotContainer"></div>
					</div>
				<?php endif; ?>
			</div>
		</section>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</section>
<script> 
 
	var statuses = <?php echo isset($declined) && !empty($declined) ? json_encode($declined) : '[]'; ?>;
	var data = [];
	var all = <?php echo $count_estimates; ?>;
	$(document).ready(function () {
        $('.datepicker').datepicker({format: $('#php-variable').val()});

		$.each(statuses, function (key, val) {
			var colors = '#';
			var letters = '0123456789ABCDEF';
			for (var i = 0; i < 6; i++) {
				colors += letters[Math.floor(Math.random() * 16)];
			  }
			var perc = val.length !== 0 ? (val.length / all * 100).toFixed(2) + '%' : '';
			data.push({
			  label: perc + ' ' + key.replace(/_/g," ").toUpperCase()    + ' (' + val.length + ')',
			  data: val.length,
			  color: colors
			});
		});
		if(data.length)
		{
			var options = {
					series: {
						pie: {
							show: true,
							innerRadius: 0.3,
							label:{                        
								radius: 0.6,
							}
						}
					},
					legend: {
						show: true
					},
					grid: {
						hoverable: false,
						clickable: false
					}
				 };
			var sum = 0;
			$.plot($("#flotcontainer"), data, options);
		}
	});
</script>
<?php $this->load->view('includes/footer'); ?>
