<div style="position: absolute; left: 3mm; width: 100%;">
<table width="100%" class="report-page-table">
	<thead>
		<tr>
			<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
				<?php if(date('N', $i)!= 7 || date('N', $week_start_time) == 7)  : ?>
				<th class="text-center bg-light b-r b-b" width="250px" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf; line-height: 12px!important;"><?php echo date('D d-M-y', $i); ?></th>
				<?php endif; ?>
			<?php endfor; ?>
		</tr>
	</thead>
	<tbody>
		<tr>
			<?php for($i = $week_start_time; $i <= $week_end_time; $i += 86400) : ?>
				<?php if(date('N', $i) != 7 || date('N', $week_start_time) == 7) : ?>
			<td class="v-top">
				<?php //$totalMore = $totalLess = 0; ?>
				<?php if(isset($events[date('Y-m-d', $i)])) : ?>
					<?php foreach($events[date('Y-m-d', $i)] as $event) : ?>
					<table width="100%" class="report-table">
						<tr>
							<td class="text-center bg-light" style="line-height: 12px!important;">
								<strong>
									<?php echo $event['emailid']; ?> <?php echo date('H:i', $event['event_start']); ?> - <?php echo date('H:i', $event['event_end']); ?>
								</strong>
							</td>
						</tr>
						<tr class="b-white">
							<td class="b-white" style="line-height: 12px!important; padding: 2px 3px;">
								<?php echo $event['lead_address']; ?> - <strong><?php echo money($event['event_price']); ?></strong>
							</td>
						</tr>
						<?php if(isset($teams[$event['id']])) : ?>
						<tr class="b-white">
							<td class="b-white" style="line-height: 12px!important; padding: 2px 3px;">
								<i><?php echo $teams[$event['id']]['team_members']; ?></i>
							</td>
						</tr>
						<?php if($teams[$event['id']]['team_closed']) : ?>
						<tr class="b-white">
							<td class="b-white" style="line-height: 12px!important; padding: 2px 3px;">
								<?php if($teams[$event['id']]['team_closed']) : ?>
									<?php $mhrReturn = round($teams[$event['id']]['team_amount'] / $teams[$event['id']]['team_man_hours'], 2); ?>
									<?php if($mhrReturn >= 67) $totalMore[$i] = (isset($totalMore[$i])) ? $totalMore[$i] + $event['event_price'] : $event['event_price']; ?>
									<?php if($mhrReturn < 67) $totalLess[$i] = (isset($totalLess[$i])) ? $totalLess[$i] + $event['event_price'] : $event['event_price']; ?>
									<?php echo money($teams[$event['id']]['team_amount']); ?> / <?php echo $teams[$event['id']]['team_man_hours']; ?> mhrs. = <?php echo money($mhrReturn); ?>
								<?php endif; ?>
							</td>
						</tr>
						<?php endif; ?>
						<?php endif; ?>
					</table>
					<?php endforeach; ?>
				<?php else : ?>
					<table width="100%" border="0" style="border: none!important;">
						<tr><td class="text-center" style="border: none!important;">N\A</td></tr>
					</table>
				<?php endif; ?>
			</td>
				<?php endif; ?>
			<?php endfor; ?>
		</tr>
	</tbody>
</table>
</div>
