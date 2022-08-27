<div style="position: absolute; left: 3mm; width: 100%;">
<style type="text/css">.day-report-column table td{ padding: 0 } </style>
<table width="100%" class="report-page-table">
	<tr>
		<th></th>
		<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?> 
			<?php if(date('N', $i)!= 7 || date('N', $week_start_time) == 7)  : ?>
			<th class="text-center bg-light b-r b-b" width="250px" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;"><?php echo date('D d-M-y', $i); ?></th>
			<?php endif; ?>
		<?php endfor; ?>
	</tr>
	<tr>
		<td></td>
		<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
			<?php if(date('N', $i) != 7 || date('N', $week_start_time) == 7) : ?>

		<td class="v-top" <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?> style="background-color: #f1f1f1" <?php endif; ?> >
			<?php if(isset($events[date('Y-m-d', $i)])) : ?>
				<?php foreach($events[date('Y-m-d', $i)] as $event) : ?>
				<table width="100%" class="report-table">
					<tr>
						<td class="text-center bg-light">
							<strong>
								<?php echo $event['emailid']; ?> <?php echo date('H:i', $event['event_start']); ?> - <?php echo date('H:i', $event['event_end']); ?>
							</strong>
						</td>
					</tr>
						<tr class="b-white">
						<td class="b-white">
							<?php echo $event['lead_address']; ?> 
						</td>
					</tr>
					<?php /*<tr class="b-white">
						<td class="b-white">
							<?php echo $event['planned_workorder_time']; ?> mhr.
						</td>
					</tr>*/ ?>
				</table>
				<?php endforeach; ?>
			<?php else : ?>
				<table width="100%" border="0" style="border: none!important;">
					<tr>
                        <td class="text-center" style="border: none!important;">
                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                &nbsp;
                            <?php else: ?>
                                N\A
                            <?php endif; ?>
                        </td>
                    </tr>
				</table>
			<?php endif; ?>
		</td>
			<?php endif; ?>
		<?php endfor; ?>
	</tr>

	<tr>
		<td></td>
		<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
			<?php if(date('N', $i) != 7 || date('N', $week_start_time) == 7) : ?>
				<td class="v-middle" <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?> style="background-color: #f1f1f1" <?php endif; ?>>
					<?php if(isset($worked_data[date('Y-m-d', $i)]->team)) : ?>
						<?php foreach($worked_data[date('Y-m-d', $i)]->team as $member) : ?>
							<table width="100%" class="report-table">
								<tr>
									<td class="text-center bg-light">
										<?php echo $member['emp_name']; ?>
									</td>
								</tr>
							</table>
						<?php endforeach; ?>
					<?php else : ?>
						<table width="100%" class="report-table" style="border: none!important;">
							<tr>
								<td class="text-center" style="border: none!important;">
                                    <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                        &nbsp;
                                    <?php else: ?>
                                        N\A
                                    <?php endif; ?>
								</td>
							</tr>
						</table>
					<?php endif; ?>
				</td>
			<?php endif; ?>
		<?php endfor; ?>
	</tr>

	<tr>
		<td style="padding: 0">
			<table  width="100%" style="border: none!important; line-height: 10px!important;">
				<tr><td style="padding: 0">&nbsp;</td></tr>
				<tr><td style="padding: 0">&nbsp;</td></tr>
				<tr><td style="padding: 0">&nbsp;</td></tr>
				<tr style="height: 60px; text-rotate:90; padding: 0"><td style="height: 60px">Travel</td></tr>
				<tr style="height: 60px; text-rotate:90; padding: 0"><td style="height: 60px">On Site</td></tr>
			</table>
		</td>
		<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
			<?php if(date('N', $i) != 7 || date('N', $week_start_time) == 7) : ?>
				<?php $teamDate = date('Y-m-d', $i + 4000); ?>
				<?php //if(isset($worked_data[$i]->team)) : ?>

					<td class="text-center v-top day-report-column" style="<?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>background-color: #f1f1f1; <?php endif; ?>padding: 0">
						<table class="report-table" width="100%" style="border: none!important; line-height: 1px!important; margin: 0">
							<tbody>
								<tr>
									<td width="33%" class="text-center" style="border-left: 0">
                                        <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                            &nbsp;
                                        <?php else: ?>
                                            EST
                                        <?php endif; ?>
									</td>
									<td width="33%" class="text-center" style="border-right: 0">
                                        <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                            &nbsp;
                                        <?php else: ?>
                                            ACT
                                        <?php endif; ?>
									</td>
									<?php /*<td width="33%" class="text-center" style="">
										%
									</td>*/?>
								</tr>
								<tr>
									<td class="text-center" style="white-space: nowrap;border-left: 0;">
										
										<span class="estimated-manhours">
										<?php if (isset($teams[$teamDate]['team_estimated_hours'])): ?>
                                            <?=$teams[$teamDate]['team_estimated_hours'] . ' mhr.'?>
                                        <?php else: ?>
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                N\A
                                            <?php endif; ?>
                                        <?php endif; ?>
										</span>
									</td>
									<td class="text-center" style="white-space: nowrap;border-right: 0">
										
										<span class="actual-manhours">
										<?php if (isset($teams[$teamDate]['team_man_hours'])): ?>
                                            <?=$teams[$teamDate]['team_man_hours'] . ' mhr.' ?>
                                        <?php else: ?>
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                N\A
                                            <?php endif; ?>
                                        <?php endif; ?>
										</span>
									</td>
									<?php /*<td class="text-center" style="padding-left: 2px;" valign="top">
										<span class="amount-productivity">-</span>
									</td>*/?>
								</tr>
								<tr>
									<td class="text-center" style="border-left: 0">
										<span class="estimated-per-hour">
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                <?php echo money((isset($teams[$teamDate]['team_estimated_amount']) && isset($teams[$teamDate]['team_estimated_hours']) && $teams[$teamDate]['team_estimated_hours']) ? round($teams[$teamDate]['team_estimated_amount'] / $teams[$teamDate]['team_estimated_hours'], 2) : 0); ?> / hr.
                                            <?php endif; ?>
										</span>
									</td>
									<td class="text-center" style="border-right: 0">
										<span class="actual-per-hour"><strong>
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                <?php echo money((isset($teams[$teamDate]['team_amount']) && isset($teams[$teamDate]['team_man_hours']) && $teams[$teamDate]['team_man_hours']) ? round($teams[$teamDate]['team_amount'] / $teams[$teamDate]['team_man_hours'], 2) : 0); ?> / hr.</strong>
                                            <?php endif; ?>
										</span>
									</td>
									<?php /*<td class="text-center" style="">
										<span class="per-hour-productivity">-%</span>
									</td>*/ ?>
								</tr>
								<tr style="height: 60px">
									<td class="text-center" style="height: 60px;border-left: 0">
										<span class="estimated-per-hour">
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                <?php echo (isset($events_totals[$teamDate]['planned_travel_time_total'])) ? $events_totals[$teamDate]['planned_travel_time_total'] : 0; ?> hr.
                                            <?php endif; ?>
										</span>
									</td>
									<td class="text-center" style="height: 60px;border-right: 0">
										<span class="actual-per-hour">
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                <strong><?php echo (isset($events_totals[$teamDate]['travel_time_total'])) ? round($events_totals[$teamDate]['travel_time_total']/3600, 2) : 0; ?> hr.</strong>
                                            <?php endif; ?>
										</span>
									</td>
									<?php /*<td class="text-center" style="">
										<span class="per-hour-productivity">-%</span>
									</td>*/ ?>
								</tr>
								<tr style="height: 60px">
									<td class="text-center" style="height: 60px;border-left: 0;">
										<span class="estimated-per-hour">
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                <?php echo (isset($events_totals[$teamDate]['planned_service_time_total'])) ? $events_totals[$teamDate]['planned_service_time_total'] : 0; ?>hr.
                                            <?php endif; ?>
										</span>
									</td>
									<td class="text-center" style="height: 60px;border-right: 0;">
										<span class="actual-per-hour">
                                            <?php if ($i < strtotime($date_range['start']) || $i > strtotime($date_range['end'])): ?>
                                                &nbsp;
                                            <?php else: ?>
                                                <strong><?php echo (isset($events_totals[$teamDate]['on_site_time_total'])) ? round($events_totals[$teamDate]['on_site_time_total']/3600, 2):0; ?> hr.</strong>
                                            <?php endif; ?>
										</span>
									</td>
									<?php /*<td class="text-center" style="">
										<span class="per-hour-productivity">-%</span>
									</td>*/ ?>
								</tr>
								
							</tbody>
						</table>
					</td>
				<?php //endif; ?>
			<?php endif; ?>
		<?php endfor; ?>
	</tr>
</table>
</div>
