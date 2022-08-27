<?php $week_start_time = $weeks[0]['week_start_time']; $week_end_time = $weeks[1]['week_end_time']; ?>

<style>table{overflow: wrap;}</style>
<div style="position: absolute; left: 3mm; width: 100%;">
	<table width="100%" class="report-page-table" autosize="1" style="overflow: wrap">
		<thead>
			<tr>
				<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
					<?php if(isset($payrollWorkorders[date('Y-m-d', $i)]))  : ?>
					<th class="text-center bg-light b-r b-b" width="<?php echo (1040 / count($payrollWorkorders)); //countOk ?>px" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf; line-height: 12px!important;"><?php echo date('D d-M-y', $i); ?></th>
					<?php endif; ?>
				<?php endfor; ?>
			</tr>
		</thead>

		<tbody>
			<tr>
			<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
				<?php if(isset($payrollWorkorders[date('Y-m-d', $i)])) : ?>
				<td class="v-top">
					<?php if(isset($payrollWorkorders[date('Y-m-d', $i)])) : ?>
						<?php foreach($payrollWorkorders[date('Y-m-d', $i)] as $workorder) : ?>
							<?php $workorderDamages = 0; ?>
							<?php $strikeStyle = $workorder['count_other_finished'] ? 'text-decoration: line-through!important;' : ''; ?>
							<table width="100%" class="report-table" autosize="1" style="border: 2px solid #2e3e4e!important; overflow: wrap; ">
								<tr>
									<td class="text-center bg-dark" style="line-height: 14px!important;">
										<strong style="<?php echo $strikeStyle; ?>">
												<?php echo $workorder['lead_address']; ?>
												<a target="_blank" style="color: #9db1c5; <?php echo $strikeStyle; ?>" href="<?php echo base_url($workorder['estimate_no']); ?>">
													<?php echo $workorder['estimate_no'];?>
												</a>
										</strong>
											<?php if($workorder['totalForWork'] != round($workorder['scheduled_wo_price'], 2)) : ?>
												<img height="13px" src="<?php echo base_url('assets/img/question-32.png'); ?>">
											<?php endif; ?> 
											<?php if($workorder['count_other_finished']) : ?>
												<br>
												<strong class="text-danger">
													Finished: <?php echo $workorder['other_finished_dates']; ?>
												</strong>
											<?php endif; ?> 
										</strong>
									</td>
								</tr>
								<?php foreach($workorder['events'] as $key => $event) : ?>
								<tr>
									<td>
										<table width="100%" class="report-table" autosize="1" style="overflow: wrap; text-decoration: line-through!important;">
											<tr>
												<td class="text-center bg-light" style="line-height: 12px!important; <?php echo $strikeStyle; ?>">
													<strong>
														<?php echo date('d-M-y', $event['event_start']) . ' ' . date('H:i', $event['event_start']); ?> - <?php echo date('H:i', $event['event_end']); ?>
													</strong>
													<?php if(!$event['team']['team_closed']) : ?>
														 <img height="10px" src="<?php echo base_url('assets/img/warning-32.png'); ?>">
													<?php endif; ?>
												</td>
											</tr>
											<tr class="b-white">
												<td class="b-white" style="line-height: 13px!important; padding: 2px 3px; <?php echo $strikeStyle; ?>">
													<i><?php echo $event['team']['team_members']; ?></i>
													(<strong><?php echo money($event['event_price']); ?></strong>)
												</td>
											</tr>
											
											<?php if($event['team']['team_closed']) : ?>
											<tr class="b-white">
												<td class="b-white" style="line-height: 12px!important; padding: 2px 3px; <?php echo $strikeStyle; ?>">
													<?php if($event['team']['team_closed']) : ?>
														Man Hour Return - <strong><?php echo money($event['mhrReturn']); ?></strong>
													<?php endif; ?>
												</td>
											</tr>
											<?php endif; ?>
											
											<?php if($event['event_damage'] || $event['event_complain']) : ?>
											<tr class="b-white">
												<td class="b-white" style="line-height: 12px!important; padding: 2px 3px; <?php echo $strikeStyle; ?>">
													<table width="100%" class="report-table">
														<tr>
															<td class="text-center" style="line-height: 12px!important; padding: 2px 3px; <?php echo $strikeStyle; ?>">
																DM: <strong><?php echo money($event['event_damage']); $workorderDamages += $event['event_damage']; ?></strong>
															</td>
															<td class="text-center" style="line-height: 12px!important; padding: 2px 3px; <?php echo $strikeStyle; ?>">
																CM: <strong><?php echo money($event['event_complain']); ?></strong>	
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<?php endif; ?>
										</table>
									</td>
								</tr>
								<?php endforeach; ?>
								<tr>
									<td class="bg-dark text-center" style="line-height: 15px; <?php echo $strikeStyle; ?>">
										<span<?php if(round($workorder['totalForWork'], 2) != round($workorder['scheduled_wo_price'], 2)) : ?> class="text-danger"<?php endif; ?>>
											Total: 
											<strong>
												<?php echo money($workorder['totalForWork']); ?> / <?php echo money($workorder['scheduled_wo_price']); ?>
											</strong>
										</span><br>
										<span<?php if($workorderDamages) : ?> class="text-danger"<?php endif; ?>>
											AVG MHR: 
											<strong>
											<?php if(!floatval($workorder['scheduled_wo_time'])) $workorder['scheduled_wo_time'] = 1; ?>
												<?php echo money($workorder['totalMhrsReturn']); ?> / <?php echo money(round($workorder['scheduled_wo_price'] / $workorder['scheduled_wo_time'], 2)); ?>
											</strong>
										</span>
									</td>
									
								</tr>
							</table>
						<?php endforeach; ?>
					<?php endif; ?>
				</td>
				<?php endif; ?>
			<?php endfor; ?>
			</tr>
		</tbody>
	</table>
</div>
