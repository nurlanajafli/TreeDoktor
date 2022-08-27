<?php if ($data_available == TRUE) { ?>
	
	<section class="panel panel-default p-n">
		<header class="panel-heading">
			Productivity:&nbsp;<?php echo $estimator_meta->firstname . " " . $estimator_meta->lastname; ?></header>
		<table class="table table-striped">
			<thead>
			<tr>
				<th width="200">Estimate Status</th>
				<th>Qty:</th>
				<th>Revenue/Loss</th>
				<th>$PR</th>
				<th>$CR</th>
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
				<td>
					<?php if($corp_revenue_total_estimates) : ?>
						<?php $res = (100 / $corp_revenue_total_estimates) * $revenue_total_estimates;
						echo round($res, 2) . "%"; ?>
					<?php else : ?>
						0%
					<?php endif; ?>
				</td>
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
	
</div>
<?php } ?>