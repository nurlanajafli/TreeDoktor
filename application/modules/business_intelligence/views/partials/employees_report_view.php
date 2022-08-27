<header class="panel-heading">Employees MHR Return:</header>
<div class="table-responsive">
<table class="table" id="tbl_Estimated">
	<thead>
		<tr>
			<th class="text-center">Name</th>
			<th class="text-center">AVG</th>
			<th class="text-center">Count Days</th>
			<th class="text-center">Total MHRS</th>
			<th class="text-center">Total</th>
			<th class="text-center">Damage</th>
			<th class="text-center">Complaint</th>
		</tr>
	</thead>
	<tbody> <!--VERY_GREAT_MAN_HOURS_RETURN-->
		<?php if(isset($employees_mhr_return) && !empty($employees_mhr_return)) : ?>
			<?php $total = $total2 = $total_mhrs = $total_teams = 0; ?>
			
			<?php foreach($employees_mhr_return as $key=>$report_row) : ?>
				<?php $suffix = '_login'; ?>
				<?php $countPeoples = 1; ?>
				<?php if($report_row['total_mhrs'] != $report_row['team_hours_login']) : ?>
					<?php $suffix = ''; ?>
					<?php $countPeoples = $report_row['count_members']; ?>
				<?php endif; ?>
				
				<?php $style = 'class="bg-success"';?>
				<?php if($report_row['mhrs_return2' . $suffix] < GOOD_MAN_HOURS_RETURN) : ?>
					<?php $style = 'class="bg-danger"'; ?>
				<?php elseif($report_row['mhrs_return2' . $suffix] > GOOD_MAN_HOURS_RETURN && $report_row['mhrs_return2' . $suffix] < GREAT_MAN_HOURS_RETURN) : ?>
					<?php $style= 'class="bg-warning"'; ?>
				<?php elseif($report_row['mhrs_return2' . $suffix] > VERY_GREAT_MAN_HOURS_RETURN) : ?>
					<?php $style='style="background: linear-gradient(45deg,#277700 3%, #3bc63b 22%,#52b152 30%,#11e603 54%,#4ace04 72%,#277700 98%); color: #fff;"'; ?>
				<?php endif; ?>
				
				<tr <?php echo $style; ?>>
					<td>
						<strong><?php echo element('emp_name', $report_row, '-'); ?></strong>
					</td>
					<?php /*<td class="text-center">
						echo money( $report_row['mhrs_return2' . $suffix]);?>
					</td> */?>
					<td class="text-center">
                        <?php echo money($report_row['new_avg']); ?>
					</td>
					<td class="text-center">
						<?php echo $report_row['count_teams'];?>
					</td>
					<td class="text-center">
						<?php echo $report_row['total_mhrs'];// . $suffix] / ($countPeoples ? $countPeoples : 1), 2);?>
					</td>

					<td class="text-center">
                        <?php echo money(round($report_row['total'], 2)); ?>
					</td>

					<td class="text-center">
                        <?php echo money(element('damage_sum', $report_row, 0)); ?>
						<b>(<?php echo element('damage_count', $report_row, 0); ?>)</b>
					</td>

					<td class="text-center">
                        <?php echo money(element('complain_sum', $report_row, 0)); ?>
						<b>(<?php echo element('complain_count', $report_row, 0); ?>)</b>
					</td>

					<?php $total += $report_row['total']; ?>
					<?php //$total2 += $report_row['total2' . $suffix] / ($countPeoples ? $countPeoples : 1); ?>
					<?php $total_mhrs += $report_row['total_mhrs'];// . $suffix] / ($countPeoples ? $countPeoples : 1); ?>
					<?php $total_teams += $report_row['count_teams']; ?>
					
				</tr>
			<?php endforeach; ?>
			<?php 
			
			if(!$total_mhrs) 
				$total_mhrs = 1;
			?>
			<?php $style = 'class="bg-success"';?>
			<?php if(($total / $total_mhrs) < GOOD_MAN_HOURS_RETURN) : ?>
				<?php $style = 'class="bg-danger"'; ?>
			<?php elseif(($total / $total_mhrs) > GOOD_MAN_HOURS_RETURN && ($total / $total_mhrs) < GREAT_MAN_HOURS_RETURN) : ?>
				<?php $style= 'class="bg-warning"'; ?>
			<?php elseif(($total / $total_mhrs) > VERY_GREAT_MAN_HOURS_RETURN) : ?>
				<?php $style='style="background: linear-gradient(45deg,#277700 3%, #3bc63b 22%,#52b152 30%,#11e603 54%,#4ace04 72%,#277700 98%); color: #fff;"'; ?>
			<?php endif; ?>
			<tr <?php echo $style; ?>>
				<td><strong>COMPANY TOTAL</strong></td>
				<td class="text-center">

                    <?php echo money(round($total / $total_mhrs, 2)); ?>
				</td>
				<td class="text-center"><?php echo $total_teams; ?></td>
				<td class="text-center"><?php echo round($total_mhrs, 2); ?></td>
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
</div>
