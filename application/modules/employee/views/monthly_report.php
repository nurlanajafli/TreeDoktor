<strong class="panel-heading">Your daily hours:</strong>

<table class="table table-hover m-t-sm" id="tbl_Estimated">
	<thead>
	<tr>
		<th>Date</th>
		<th>Start</th>
		<th>Finish</th>
		<th>Total</th>
		<?php if(isset($bonuses) && !empty($bonuses) && $bonuses) : ?>
		<th>Bonuses</th>
		<?php endif; ?>
	</tr>
	</thead>
	<tbody>
	<?php $days = date("t", strtotime(date("{$year}-{$month}-01")));

	for ($d = 1; $d <= $days; $d++) {
		$d = ($d < 10) ? "0" . $d : $d;
		$day = date("{$year}-{$month}-{$d}");
		$trigger = TRUE;
		if (strtotime($day) <= strtotime(date("Y-m-d"))) {
			?>

			<?php //$worked = array();?>
			<? if (isset($emp_login_details[date("{$year}-{$month}-{$d}")])) {
				foreach ($emp_login_details[date("{$year}-{$month}-{$d}")] as $key => $time_details) {
					?>
					<tr>
						<td>
							<?php $dayname = date("D", strtotime($day)); ?>
							<? echo date("M dS, D Y", strtotime($day)); ?>
						</td>

						<td><?php echo $time_details["login_time"]; ?></td>
						<td style="border-right: 1px solid #f1f1f1;"><?php echo $time_details["logout_time"]; ?></td>
						<?php if(isset($time_details['time_diff']) && $trigger) : ?>
						<td class="text-center v-middle" rowspan="<?php echo !empty($emp_login_details[$day]) ? count($emp_login_details[$day]) : '1'; //countOk ?>" style="border-right: 1px solid #f1f1f1;">
							<?php if(!empty($emp_login_details[$day])) : ?>
								<?php echo isset($time_details['time_diff']) ? $time_details['time_diff'] . " hrs." : '-'; ?>
							<?php endif; ?>
						</td>
						<?php endif; ?>
						<?php if(isset($bonuses[$day])) : ?>
						<td width="250" style="max-width: 250px;" rowspan="<?php echo !empty($emp_login_details[$day]) ? count($emp_login_details[$day]) : '1'; //countOk ?>">
								<?php $dailyPercents = 0; ?>
								<?php foreach($bonuses[$day] as $bonus) : ?>
									<label class="label bg-<?php if($bonus['bonus_amount'] > 0) : ?>success<?php else : ?>danger<?php endif; ?> text-xs" title="<?php echo $bonus['bonus_description'] ? $bonus['bonus_description'] : $bonus['bonus_title']; ?>"
									       style="display: block;float: left;clear: both; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">
										<?php if($bonus['bonus_amount'] > 0) : ?>+<?php endif; ?><?php echo $bonus['bonus_amount']; ?>% -
										<?php echo $bonus['bonus_title']; $dailyPercents += $bonus['bonus_amount']; ?>
									</label>
									<br>
								<?php endforeach; ?>
								<?php if(/***/date('m', strtotime($day)) >= 5 && date('m', strtotime($day)) <= 11 && /***/$dailyPercents && isset($time_details['hourly_rate'])) : ?>
									<span class="text-success"><?php echo money(round(($worked[$day]->worked_hours - $worked[$day]->worked_lunch)  * $time_details['hourly_rate'] * $dailyPercents / 100, 2)); ?></span>
								<?php endif; ?>
								<?php unset($bonuses[$day]); $bonused[$day] = TRUE; ?>
						</td>
						<?php elseif(isset($bonuses) && !empty($bonuses) && $bonuses && !isset($bonuses[$day])) : ?>
						<td></td>
						<?php endif;  ?>
					</tr>

				<?php
				$trigger = FALSE;
				}
				
			} else {
				?>
				<tr>
					<td>
						<?php $dayname = date("D", strtotime($day)); ?>
						<? echo date("M dS, D Y", strtotime($day)); ?>
					</td>
					<td colspan="4">No record found</td>
				</tr>
			<?php } ?>


		<?php } ?>

	<?php } ?>
	</tbody>
</table> 
