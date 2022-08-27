<?php if ($data_available != "yes") { ?>
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Corporate Productivity:&nbsp; Overall</header>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Estimate Status</th>
				<th>Qty:</th>
				<th>Revenue/Loss</th>
				<th>Average</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Total:</td>
				<td><?php echo $corp_total_estimates; $corp_total_estimates = $corp_total_estimates ? $corp_total_estimates : 1; ?></td>
				<td><?php echo money($corp_revenue_total_estimates); ?></td>
				<td><?php $res = $corp_revenue_total_estimates / $corp_total_estimates;
					echo money(round($res, 2)); ?></td>
			</tr>
			<?php $confirmedSum = $declinedSum = 0; ?>
			<?php foreach($statuses as $status) :?>
			<tr>
				<?php if($status->est_status_confirmed) $confirmedSum += $corp_estimates[$status->est_status_id]; ?>
				<?php if($status->est_status_declined) $declinedSum += $corp_estimates[$status->est_status_id]; ?>
				<td><?php echo $status->est_status_name; ?>:</td>
				<td><?php echo $corp_estimates[$status->est_status_id]; ?></td>
				<td><?php echo money($corp_revenue_estimates[$status->est_status_id]); ?></td>
				<td></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<?php $res1 = (100 / $corp_total_estimates) * $confirmedSum;
		$corp_success_rate = $res1 ?>
		<?php $res2 = (100 / $corp_total_estimates) * $declinedSum;
		$corp_declined_rate = $res2 ?>
		<?php $corp_uncertanty_rate = 100 - $corp_success_rate - $corp_declined_rate; ?>


		<!--Graohical-->
		<div class="p-sides-10">
			<div class="progress m-t-sm progress-striped active">
				<div class="progress-bar progress-bar-success" style="width: <?php echo $corp_success_rate; ?>%;"></div>
				<div class="progress-bar progress-bar-warning" style="width: <?php echo $corp_uncertanty_rate; ?>%;"></div>
				<div class="progress-bar progress-bar-danger" style="width: <?php echo $corp_declined_rate; ?>%;"></div>
			</div>
		</div>

	</section>
<?php } ?>
