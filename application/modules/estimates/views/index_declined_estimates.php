<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
	<thead>
	<tr>
		<th><a href="#" class="sort" data-status="decline_no_follow" data-field="client_name" data-type="ASC">
			Client Name<i class="fa fa-filter desc"></i></a></th>
		<th><a href="#" class="sort" data-status="decline_no_follow" data-field="lead_address" data-type="ASC">
			Address<i class="fa fa-filter desc"></i></a></th>
		<th><a href="#" class="sort" data-status="decline_no_follow" data-field="date_created" data-type="ASC">
			Date<i class="fa fa-filter desc"></i></a></th>
		<th width="200px">Estimator</th>
		<th width="200px">Status</th>
		<th width="85px">Action</th>
	</tr>
	</thead>
	<tbody>
	<?php if ($declined_estimate) { ?>
		<?php foreach ($declined_estimate->result() as $rows) { ?>
			<tr>
				<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
				<td><?php echo $rows->lead_address . ",&nbsp;" . $rows->lead_city; ?></td>
				<td><?php echo date('Y-m-d', $rows->date_created); ?></td>
				<td width="100"><?php echo $rows->firstname . ' ' . $rows->lastname; ?></td>
				<td width="100"><?php echo $rows->status; ?></td>
				<td width="70">
					<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
					<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
				</td>
			</tr>
		<?php } ?>
		<?php if ($declined_estimate_links) : ?>
			<tr>
				<td colspan="5">
					<div class="pull-right p-sides-10"><?php echo $declined_estimate_links; ?></div>
					</div>
			</tr>
		<?php endif; ?>
	<?php } else { ?>
		<tr>
			<td colspan="6" style="color:#FF0000;">No record found</td>
		</tr>
	<?php } ?>
	</tbody>
</table>
