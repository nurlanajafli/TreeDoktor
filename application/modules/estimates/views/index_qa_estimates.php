<div class="table-responsive">
	<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
		<thead>
		<tr>
			<th width="200px">Client Name</th>
			<th width="380px">Address</th>
			<th width="90px">Count&nbsp;<br>Contacts</th>
			<th width="100px">Last&nbsp;&nbsp;&nbsp;<br>Contact</th>
			<th width="100px">Date</th>
			<th>QA</th>
			<th width="">Estimator</th>
			<th width="100px">Status</th>
			<th width="85px">Action</th>
		</tr>
		</thead>
		<tbody>
		<?php if ($qa_estimate) { ?>
			<?php foreach ($qa_estimate->result() as $rows) { ?>
				<tr>
					<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
					<td><?php echo $rows->lead_address . ",&nbsp;" . $rows->lead_city; ?></td>
					<td width="60"><?php echo $rows->estimate_count_contact; ?></td>
					<td width="75"><?php echo ($rows->estimate_last_contact) ? getDateTimeWithTimestamp($rows->estimate_last_contact) : ''; ?></td>
<!--					<td width="75">--><?php //echo date('Y-m-d', $rows->date_created); ?><!--</td>-->
					<td width="75"><?php echo getDateTimeWithTimestamp( $rows->date_created); ?></td>
					<td>
						<?php if ($rows->qa_id) : ?>
							<i class="fa fa-check"></i>
						<?php else : ?>
							<i class="fa fa-times"></i>
						<?php endif; ?>
					</td>
					<td width="100"><?php echo $rows->firstname . ' ' . $rows->lastname; ?></td>
					<td width="100"><?php echo $rows->status; ?></td>
					<td width="70">
						<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
						<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="9" style="color:#FF0000;">No record found</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
