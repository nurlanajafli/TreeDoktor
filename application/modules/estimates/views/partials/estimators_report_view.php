<header class="panel-heading">Estimators MHR Return:</header>
<table class="table" id="tbl_Estimated">
	<thead>
		<tr>
			<th class="text-center">Name</th>
			<th class="text-center">AVG</th>
			<?php /*<th class="text-center">AVG Comission</th>*/ ?>
			<th class="text-center">Count Teams</th>
			<th class="text-center">Total MHRS</th>
			<th class="text-center">PR</th>
			<th class="text-center">Total</th>
			<th class="text-center">Damage</th>
			<th class="text-center">Complaint</th>
		</tr>
	</thead>
	<tbody>
	<?php if(isset($estimators_mhr_rate) && !empty($estimators_mhr_rate)) : ?>
		<?php $total = $total_mhrs = $total_teams = $total_profit = 0; ?>
		<?php foreach($estimators_mhr_rate as $key=>$report_row) : ?>
			<tr class="bg-<?php if($report_row->estimator_mhr_return < GOOD_MAN_HOURS_RETURN) : ?>danger<?php elseif($report_row->estimator_mhr_return > GOOD_MAN_HOURS_RETURN && $report_row->estimator_mhr_return < GREAT_MAN_HOURS_RETURN) : ?>warning<?php else : ?>success<?php endif; ?>">
				<td>
					<strong>
						<?php echo $report_row->firstname . ' ' . $report_row->lastname; ?>
					</strong>
				</td>
				<td class="text-center">
                    <?php echo money($report_row->estimator_mhr_return2); ?>
					<?php /* - 
					echo money($report_row->estimator_mhr_return);*/ ?>
				</td>
				<?php /*<td class="text-center">
					<?php echo money(isset($report_row->avg_finished) ? $report_row->avg_finished : 0);?> - <?php echo money(isset($report_row->avg_finished) ? $report_row->avg_finished : 0);?>
				</td>*/ ?>
				<td class="text-center">
					<?php echo $report_row->count_teams;?>
				</td>
				<td class="text-center">
					<?php echo $report_row->workorders_hours;?>
				</td>

				<td class="text-center">
                    <?php echo money(($report_row->estimator_mhr_return - 60) * $report_row->workorders_hours); ?>
				</td>
		
				<td class="text-center">
                    <?php echo money($report_row->total); ?>
				</td>
					
				<td class="text-center">
                    <?php echo money($report_row->total_damages); ?>
					<b>(<?php echo $report_row->count_team_damages; ?>)</b>
				</td>

				<td class="text-center">
                    <?php echo money($report_row->total_complains); ?>
					<b>(<?php echo $report_row->count_team_complains; ?>)</b>
				</td>

				<?php $total += $report_row->total; ?>
				<?php $total_mhrs += $report_row->workorders_hours; ?>
				<?php $total_teams += $report_row->count_teams; ?>
				<?php $total_profit += round(($report_row->estimator_mhr_return - 60) * $report_row->workorders_hours, 2); ?>
			</tr>
		<?php endforeach; ?>
			<tr class="bg-<?php if(($total / $total_mhrs) < 63) : ?>danger<?php elseif(($total / $total_mhrs) > 63 && ($total / $total_mhrs) < 70) : ?>warning<?php else : ?>success<?php endif; ?>">
				<td><strong>COMPANY TOTAL</strong></td>
				<td class="text-center">
                    <?php echo money(round(($total - element('damage_total', $total_damage_complain)) / $total_mhrs,
                        2)); ?>
						
				</td>

				<td class="text-center"><?php echo $total_teams; ?></td>
				<td class="text-center"><?php echo $total_mhrs; ?></td>
                <td class="text-center"><?php echo money($total_profit); ?></td>
                <td class="text-center"><?php echo money($total); ?></td>
                <td class="text-center"><?php echo money(element('damage_total', $total_damage_complain, 0)); ?></td>
                <td class="text-center"><?php echo money(element('complain_total', $total_damage_complain, 0)); ?></td>
			</tr>

	<?php else : ?>
		<tr>
			<td colspan="2">
				<p style="color:#FF0000;"> No record found</p>
			</td>
		</tr>

	<?php endif;  ?>
	</tbody>
</table>
