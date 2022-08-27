<div class="table-responsive">
	<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
		<thead>
		<tr>
			<th width="170px">Client Name</th>
			<th width="330px">Address</th>
			<th width="90px">Count<br>Contacts</th>
			<th width="100px">Last<br>Contact</th>
			<th width="105px">Date</th>
			<th width="150px">Estimator</th>
			<th width="100px">Status</th>
			<th width="85px">Action</th>
		</tr>
		</thead>
		<tbody>
		<?php if (!empty($working_estimates)) { ?>
			<?php foreach ($working_estimates as $days => $rows) { ?>
				<tr class="info">
				<td colspan="8" style="text-align:center;">
					<strong><?php echo $days; ?> Days:</strong>
				</td>
				<?php if (!empty($rows)) : ?>
					<?php foreach ($rows as $row) : ?>
						<tr>
							<td width="180"><?php echo anchor($row->client_id, $row->client_name); ?></td>
							<td><?php echo $row->lead_address . ",&nbsp;" . $row->lead_city . ",&nbsp;" . $row->lead_state . ",&nbsp;" . $row->lead_zip; ?></td>
							<td width="60"><?php echo $row->estimate_count_contact; ?></td>
<!--							<td width="75">--><?php //echo ($row->estimate_last_contact) ? date('Y-m-d', $row->estimate_last_contact) : ''; ?><!--</td>-->
							<td width="75"><?php echo ($row->estimate_last_contact) ? getDateTimeWithTimestamp($row->estimate_last_contact) : ''; ?></td>
<!--							<td width="105">--><?php //echo date('Y-m-d', $row->date_created); ?><!--</td>-->
							<td width="105"><?php echo getDateTimeWithTimestamp( $row->date_created); ?></td>
							<td width=""><?php echo $row->firstname . ' ' . $row->lastname; ?></td>
							<td width="100"><?php echo $row->status; ?></td>
							<td width="70">
								<?php echo anchor('estimates/edit/' . $row->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"'); ?>
								<?php echo anchor($row->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"'); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="8" style="color:#FF0000;">No record found</td>
					</tr>
				<?php endif; ?>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="8" style="color:#FF0000;">No record found</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
