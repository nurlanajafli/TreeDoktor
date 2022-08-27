<div style="display:<?php if(isset($docs) && !empty($docs)) : ?>block<?php else : ?>none<?php endif; ?>; padding-left: 30px;">
<p style="color: red;">This documents will be expired soon: </p>
<ul style=" color: red; list-style: none; padding:0;">
	<?php foreach($docs as $k=>$v) : ?>
		<li><?php echo $k+1 . ') ' . $v->us_name . ' (' . $v->us_exp. ')'?></li>
	<?php endforeach; ?>
</ul>
</div>
<strong class="panel-heading">Your daily hours:</strong>
<div class="responsive-table">
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
		<?php if(isset($userdata['emp_feild_worker']) && $userdata['emp_feild_worker']) : ?>
		<th>MHR</th>
		<th>DM</th>
		<th>CM</th>
		<?php endif; ?>
	</tr>
	</thead>
	<tbody>
	<?php $days = date("t", strtotime(date("{$year}-{$month}-01")));
	$totalForAWeek = $totalForAMonth = 0;
	for ($d = 1; $d <= $days; $d++) { 
		$d = ($d < 10) ? "0" . $d : $d;
		$day = date("{$year}-{$month}-{$d}");
		$trigger = TRUE; 
		
		if(date('w', strtotime($day)) == 1)
			$totalForAWeek = 0;
		if($d == 1)
			$totalForAWeek = $prevHours;
		if (strtotime($day) <= strtotime(date("Y-m-d"))) {
			?>

			<?php //$worked = array();?>
			<?php if (isset($emp_login_details[date("{$year}-{$month}-{$d}")])) {
				foreach ($emp_login_details[date("{$year}-{$month}-{$d}")] as $key => $time_details) {
					?>
					<tr>
						<td>
							<?php $dayname = date("D", strtotime($day)); ?>
							<?php echo date("M dS, D Y", strtotime($day)); ?>
						</td>

						<td><?php echo $time_details["login_time"]; ?></td>
						<td style="border-right: 1px solid #f1f1f1;"><?php echo $time_details["logout_time"]; ?></td>
						<?php if(isset($time_details['time_diff']) && $trigger) : ?>
						<td class="text-center v-middle" rowspan="<?php echo !empty($emp_login_details[$day]) ? count($emp_login_details[$day]) : '1';//countOk ?>" style="border-right: 1px solid #f1f1f1;">
							<?php if(!empty($emp_login_details[$day])) : ?>
								<?php if(isset($time_details['time_diff'])) : ?>
									<?php $totalForAWeek += $time_details['time_diff']; ?>
									<?php $totalForAMonth += $time_details['time_diff']; ?>
									<?php echo $time_details['time_diff'] . " hrs."; ?>
								<?php else : ?>
									'-'
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<?php endif; ?>
						<?php if(isset($bonuses[$day])) : ?>
						<td width="250" style="max-width: 250px;" rowspan="<?php echo !empty($emp_login_details[$day]) ? count($emp_login_details[$day]) : '1';//countOk ?>">
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
						<?php if(isset($userdata['emp_feild_worker']) && $userdata['emp_feild_worker']) : ?>
							<td>
								<?php echo mhrs_to_user($userdata['id'], $day); ?>
							</td>
							<td>
								<?php $damages = demages_complain($userdata['id'], $day); ?>
								<?php  echo $damages['event_damage'] ? $damages['event_damage'] : '-'; ?>
							</td>
							<td>
								<?php  echo $damages['event_complain'] ? $damages['event_complain'] : '-'; ?>
							</td>
						<?php endif; ?>
					</tr>

				<?php
				$trigger = FALSE;
				}
				
			} if(!date("w", strtotime($day))) {
				?>
				<tr>
					<td>
						<?php $dayname = date("D", strtotime($day)); ?>
						<?php echo date("M dS, D Y", strtotime($day)); ?>
					</td>
					<td colspan="<?php if(($totalForAWeek && (!date('w', strtotime($day)) || $d == $days)) || isset($payroll[$day])) : ?>2<?php else : ?>3<?php endif; ?>">No record found</td>
					
					<?php if(($totalForAWeek && (!date('w', strtotime($day)) || $d == $days)) || isset($payroll[$day])) : ?>
						<td class="text-center v-middle" style="border-left: 1px solid #f1f1f1;">
							<?php //$totalForAMonth += $totalForAWeek; ?>
							<?php if($totalForAWeek) : ?><strong>Total For A Week: <?php echo $totalForAWeek; ?>  hrs. </strong><?php endif; ?>
							<strong><?php if(isset($payroll[$day]) && !empty($payroll[$day])) : ?>Total for Paycheck: <?php echo $payroll[$day]->worked_payed; ?> hrs.<?php endif; ?></strong>
						</td>
					<?php endif; ?>
						
				</tr>
			<?php } ?>
			
		<?php } ?>

	<?php } ?>
<?php if(isset($userdata['emp_feild_worker']) && $userdata['emp_feild_worker']) : ?>
	<tr>
		<td colspan="3" class="text-right">
			<strong>AVE MHR (1M/3M/6M/12M):</strong>
		</td>
		<td colspan="4" class="text-right" width="100px">
		<?php $timestamp = time(); ?>
		$ <?php echo mhrs_to_user($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		 / 
		<?php $timestamp = strtotime(date("Y-m-01", $timestamp) . ' -2 months'); ?>
		$ <?php echo mhrs_to_user($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		 / 
		<?php $timestamp = strtotime(date("Y-m-01", $timestamp) . ' -3 months'); ?>
		$ <?php echo mhrs_to_user($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		 / 
		<?php $timestamp = strtotime(date("Y-m-01", $timestamp) . ' -6 months'); ?>
		$ <?php echo mhrs_to_user($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="text-right">
			<strong>AVE DM (1M/3M/6M/12M):</strong>
		</td>
		<td colspan="4" class="text-right" width="100px">
		<?php $timestamp = time(); ?>
		<?php $dmg = avg_dmg_cmp($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		<?php $timestamp = strtotime(date("Y-m-01", $timestamp) . ' -3 months'); ?>
		<?php $dmg3 = avg_dmg_cmp($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		<?php $timestamp = strtotime(date("Y-m-01", $timestamp) . ' -6 months'); ?>
		<?php $dmg6 = avg_dmg_cmp($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		<?php $timestamp = strtotime(date("Y-m-01", $timestamp) . ' -12 months'); ?>
		<?php $dmg12 = avg_dmg_cmp($userdata['id'], date("Y-m-01", $timestamp), date("Y-m-t", time())); ?>
		
		$ <?php echo round($dmg['avg_demage'], 2); ?>
		 / 
		$ <?php echo round($dmg3['avg_demage'], 2); ?>
		 / 
		$ <?php echo round($dmg6['avg_demage'], 2); ?>
		 / 
		$ <?php echo round($dmg12['avg_demage'], 2); ?>
		</td>
	</tr>
	<tr>
		<td colspan="3" class="text-right">
			<strong>AVE CM (1M/3M/6M/12M):</strong>
		</td>
		<td colspan="4" class="text-right" width="100px">
		$ <?php echo round($dmg['avg_complain'], 2); ?>
		 / 
		$ <?php echo round($dmg3['avg_complain'], 2); ?>
		 / 
		$ <?php echo round($dmg6['avg_complain'], 2); ?>
		 / 
		$ <?php echo round($dmg12['avg_complain'], 2); ?>
		</td>
	</tr>
<?php endif; ?>
<?php if($totalForAMonth && ($year . '-' . $month < date('Y-m') || date('j') == date('t'))) :  ?>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2"></td>
		<td class="text-center v-middle" style="border-left: 1px solid #f1f1f1;">
			<strong>Total For A Month: <?php echo $totalForAMonth; ?> hrs.</strong>
		</td>
	</tr>
<?php endif; ?>
	</tbody>
</table>
</div>
