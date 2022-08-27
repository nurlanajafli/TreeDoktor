<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
	<thead>
	<tr>
		<th width="200px"><a href="#" class="sort" data-status="new" data-field="client_name" data-type="ASC">
			Client Name<i class="fa fa-filter desc"></i></a></th>
		<th width="350px"><a href="#" class="sort" data-status="new" data-field="lead_address" data-type="ASC">
			Address<i class="fa fa-filter desc"></i></a></th>
		<th width="90px"><a href="#" class="sort" data-status="new" data-field="estimate_count_contact" data-type="ASC">Count&nbsp;<i
					class="fa fa-filter desc"></i><br>Contacts</a></th>
		<th width="100px"><a href="#" class="sort" data-status="new" data-field="estimate_last_contact" data-type="ASC">Last&nbsp;&nbsp;&nbsp;<i
					class="fa fa-filter desc"></i><br>Contact</a></th>
		<th width="110px"><a href="#" class="sort" data-status="new" data-field="date_created" data-type="ASC">Date&nbsp;<i
					class="fa fa-filter desc"></i></th>
		<th width="130px">Estimator</th>
		<th width="100px">Status</th>
		<th width="85px">Action</th>
	</tr>
	</thead>
	<tbody>
	<?php if ($new_estimate) { ?>
		<?php foreach ($new_estimate->result() as $rows) { ?>
			<tr>
				<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
				<td><?php echo $rows->lead_address . ",&nbsp;" . $rows->lead_city; ?></td>
				<td width="60"><?php echo $rows->estimate_count_contact; ?></td>
				<td width="100"><?php echo ($rows->estimate_last_contact) ? date('Y-m-d', $rows->estimate_last_contact) : ''; ?></td>
				<td width="100"><?php echo date('Y-m-d', $rows->date_created); ?></td>
				<td width="100"><?php echo $rows->firstname . ' ' . $rows->lastname; ?></td>
				<td><?php echo $rows->status; ?></td>
				<td width="70">
					<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
					<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
				</td>
			</tr>
		<?php } ?>
		<?php if ($new_estimate_links) : ?>
			<tr>
				<td colspan="8">
					<div class="pull-right p-sides-10"><?php echo $new_estimate_links; ?></div>
                </td>
			</tr>
		<?php endif; ?>
	<?php } else { ?>
		<tr>
			<td colspan="8" style="color:#FF0000;">No record found</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
