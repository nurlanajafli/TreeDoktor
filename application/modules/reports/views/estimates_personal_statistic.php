<?php if ($data_available == TRUE) { ?>
	<div class="clearfix"></div>
	<div class="row">
	<section class="col-sm-7">
		<section class="panel panel-default p-n">
			<header class="panel-heading">
				Productivity:&nbsp;<?php echo $estimator_meta->firstname . " " . $estimator_meta->lastname; ?></header>
			<table class="table table-striped">
				<thead>
				<tr>
					<th width="200">Estimate Status</th>
					<th>Qty:</th>
					<th>Revenue/Loss</th>
					<th><?php echo get_currency(); ?> PR</th>
					<th><?php echo get_currency(); ?> CR</th>
					<th>Average</th>
				</tr>
				</thead>
				<tbody>

				<tr>
					<td>Total:</td>
					<td><?php echo $total_estimates; ?></td>
					<td><?php echo money($revenue_total_estimates); ?></td>
					<td><?php $res = ($revenue_total_estimates) ? ((100 / $revenue_total_estimates) * $revenue_total_estimates) : 0;
						echo round($res, 2) . "%"; ?></td>
					<td><?php $res = (100 / $corp_revenue_total_estimates) * $revenue_total_estimates;
						echo round($res, 2) . "%"; ?></td>
					<td><?php $res = $total_estimates ? ($revenue_total_estimates / $total_estimates) : 0;
						echo money(round($res, 2)); ?></td>
				</tr>
				<?php $confirmedSum = $declinedSum = 0; ?>
				<?php foreach($statuses as $status) :?>
					<tr>
						<?php if($status->est_status_confirmed) $confirmedSum += $personal_estimates[$status->est_status_id]; ?>
						<?php if($status->est_status_declined) $declinedSum += $personal_estimates[$status->est_status_id]; ?>
						<td><?php echo $status->est_status_name; ?>:</td>
						<td><?php echo $personal_estimates[$status->est_status_id]; ?></td>
						<td><?php echo money($revenue_personal_estimates[$status->est_status_id]); ?></td>
						<td>
							<?php $res = ($revenue_personal_estimates[$status->est_status_id]) ? ((100 / $revenue_total_estimates) * $revenue_personal_estimates[$status->est_status_id]) : 0;
							echo round($res, 2) . "%"; ?>
						</td>
						<td>
							<?php $corp_revenue_estimates[$status->est_status_id] = $corp_revenue_estimates[$status->est_status_id] ? $corp_revenue_estimates[$status->est_status_id] : 1;
							$res = (100 / $corp_revenue_estimates[$status->est_status_id]) * $revenue_personal_estimates[$status->est_status_id];
							echo round($res, 2) . "%";?>
						</td>
						<td>
							<?php 
							if($personal_estimates[$status->est_status_id])
								$res = $revenue_personal_estimates[$status->est_status_id] / $personal_estimates[$status->est_status_id];
							else
								$res = 0;
							echo money(round($res, 2)); ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php $res1 = $total_estimates ? ((100 / $total_estimates) * $confirmedSum) : 0;
			$success_rate = $res1 ?>
			<?php $res2 = $total_estimates ? ((100 / $total_estimates) * $declinedSum) : 0;
			$declined_rate = $res2 ?>
			<?php $uncertanty_rate = 100 - $success_rate - $declined_rate; ?>

			<!--Graohical-->
			<div class="border-top filled_dark_grey p-xs">
				<div class="progress m-t-sm progress-striped active m-b-sm">
					<div class="progress-bar progress-bar-success" style="width: <?php echo $success_rate; ?>%;"></div>
					<div class="progress-bar progress-bar-warning"
					     style="width: <?php echo $uncertanty_rate; ?>%;"></div>
					<div class="progress-bar progress-bar-danger" style="width: <?php echo $declined_rate; ?>%;"></div>
				</div>
			</div>
		</section>
	</section>

	<!-- Daily and weekly averages -->

	<section class="col-sm-5">
		<section class="panel panel-default p-n">
			<header class="panel-heading">Daily and weekly KPI
				for <?php echo $estimator_meta->firstname . " " . $estimator_meta->lastname; ?></header>

			<!-- Getting the days -->
			<?php echo form_open(base_url() . "reports/estimates") ?>
			<div class="border-bottom m-top-5">
				<div class="p-10">
					<div class="form-inline">
						<input type="hidden" id="user_id" value="<?php echo $estimator_id; ?>">
						<label>From:</label>
						<input type="text" class="form-control m-l-sm" style="width:100px;"
						       value="<?php if (isset($from_date_val) && $from_date_val != "") {
							       echo $from_date_val;
						       } ?>" placeholder="Pick a date" id="dp1" name="from_date">
						<label class="p-left-10">To:</label>
						<input type="text" class="form-control m-l-sm" style="width:100px;"
						       value="<?php if (isset($to_date_val) && $to_date_val != "") {
							       echo $to_date_val;
						       } ?>" placeholder="Pick a date" id="dp2" name="to_date">
						<span class="p-left-10"><input type="submit" name="save" value="&nbsp;&nbsp;View&nbsp;&nbsp;"
						                               class="btn btn-success"/></span>
					</div>
				</div>
			</div>
			<?php form_close(); ?>
			<!-- /Getting the days -->

			<!-- Display Data -->
			<?php if ($dates_available == FALSE){
				echo "**Please select Date above";
			}else {
			?>

			<!--Totals-->
			<div class="clear b-t">
				<div class="col-md-4 h-62 b-r text-center">Total
					<div class="h2"><?php echo $total_estimates_date; ?></div>
				</div>
				<div class="col-md-4 h-62 text-center">Opportunity
					<div class="h2"><?php echo money($revenue_total_estimates_date); ?></div>
				</div>
				<div class="col-md-4 h-62 b-l text-center">Working Days
					<div class="h2"><?php echo round($period_days_val, 0); ?></div>
				</div>
			</div>
			<!--/Totals-->

			<!--Confirmed-->
			<div class="bg-success clear">
				<div class="col-md-4 h-62 b-r text-center">Confirmed
					<div class="h2"><?php echo $confirmed_estimates_date; ?></div>
				</div>
				<div class="col-md-4 h-62 text-center">Revenue:
					<div class="h2"><?php echo money($revenue_confirmed_estimates_date); ?></div>
				</div>
				<div class="col-md-4 h-62 b-l text-center">Approval Rate
					<div class="h2"><?php if ($revenue_total_estimates_date != 0) {
							$res = 100 / $revenue_total_estimates_date * $revenue_confirmed_estimates_date;
							echo round($res, 2) . "%";
						} else {
							echo "N/A";
						} ?></div>
				</div>
			</div>
			<!--/Confirmed-->

			<!--Declined-->
			<div class="bg-danger clear">
				<div class="col-md-4 h-62 b-r text-center">Declined
					<div class="h2"><?php echo $declined_estimates_date; ?></div>
				</div>
				<div class="col-md-4 h-62  text-center">Lost Opportunity:
					<div class="h2"><?php echo money($revenue_declined_estimates_date); ?></div>
				</div>
				<div class="col-md-4 h-62 b-l text-center">Rejection Rate:
					<div class="h2"><?php if ($revenue_total_estimates_date != 0) {
							$res = 100 / $revenue_total_estimates_date * $revenue_declined_estimates_date;
							echo round($res, 2) . "%";
						} else {
							echo "N/A";
						} ?></div>
				</div>
			</div>
			<div class="bg-info clear">
				<div class="col-md-4 h-62 b-r text-center">Daily average:
					<div class="h2"><?php if ($total_estimates_date > 0) {
							$res = $total_estimates_date / $period_days_val;
							echo round($res, 0);
						} else {
							echo "None";
						} ?></div>
				</div>
				<div class="col-md-4 h-62 text-center">Commission(3%):
					<div class="h2"><?php $res = ($revenue_confirmed_estimates_date / 100) * 3;
						echo money($res); ?></div>
				</div>
				<div class="col-md-4 h-62 b-l text-center">Reach-out Rate:
					<div class="h2"><?php if ($revenue_total_estimates_date != 0) {
							$res = 100 / $revenue_total_estimates_date * ($revenue_confirmed_estimates_date + $revenue_declined_estimates_date);
							echo round($res, 2) . "%";
						} else {
							echo "N/A";
						} ?></div>
				</div>
			</div>
			<div class="bg-warning clear">
					<div class="col-md-4 h-62 b-r text-center">SE
						<div class="h2"><?php echo $se = $total_estimates_date * 5; ?></div>
					</div>
					<div class="col-md-4 h-62 text-center">CSE 
						<div class="h2">
						<?php if(isset($estimate_counts_contact)) : ?>
							<?php echo round($estimate_counts_contact / $se * 100, 2); ?>
						<?php else :?>
								0
						<?php endif;?>	
						%</div>
					</div>
					<div class="col-md-4 h-62 b-l text-center">EE
						<div class="h2">
							<?php if(isset($estimator_counts_contact)) : ?>
								<?php echo round($estimator_counts_contact / $se * 100, 2); ?>
							<?php else :?>
								0
							<?php endif;?>	
						%</div>
					</div>
				</div>
				<!--Sales Engagement-->
				
				<?php } ?>
		</section>
		<!-- /Display Data -->

	</section>
	<div class="clearfix"></div>
</div>
<?php } ?>

<!--Datepicker: -->
<script>
	$(function () {
		window.prettyPrint && prettyPrint();
		$('#dp1').datepicker({
			format: 'yyyy-mm-dd'
		});
		$('#dp2').datepicker({
			format: 'yyyy-mm-dd'
		});
		$('#dp3').datepicker();
		$('#dp3').datepicker();
		$('#dpYears').datepicker();
		$('#dpMonths').datepicker();


		var startDate = new Date(2012, 1, 20);
		var endDate = new Date(2012, 1, 25);
		$('#dp4').datepicker()
			.on('changeDate', function (ev) {
				if (ev.date.valueOf() > endDate.valueOf()) {
					$('#alert').show().find('strong').text('The start date can not be greater then the end date');
				} else {
					$('#alert').hide();
					startDate = new Date(ev.date);
					$('#startDate').text($('#dp4').data('date'));
				}
				$('#dp4').datepicker('hide');
			});
		$('#dp5').datepicker()
			.on('changeDate', function (ev) {
				if (ev.date.valueOf() < startDate.valueOf()) {
					$('#alert').show().find('strong').text('The end date can not be less then the start date');
				} else {
					$('#alert').hide();
					endDate = new Date(ev.date);
					$('#endDate').text($('#dp5').data('date'));
				}
				$('#dp5').datepicker('hide');
			});

		// disabling dates
		var nowTemp = new Date();
		var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

		var checkin = $('#dpd1').datepicker({
			onRender: function (date) {
				return date.valueOf() < now.valueOf() ? 'disabled' : '';
			}
		}).on('changeDate',function (ev) {
			if (ev.date.valueOf() > checkout.date.valueOf()) {
				var newDate = new Date(ev.date)
				newDate.setDate(newDate.getDate() + 1);
				checkout.setValue(newDate);
			}
			checkin.hide();
			$('#dpd2')[0].focus();
		}).data('datepicker');
		var checkout = $('#dpd2').datepicker({
			onRender: function (date) {
				return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
			}
		}).on('changeDate',function (ev) {
			checkout.hide();
		}).data('datepicker');
	});
</script>
