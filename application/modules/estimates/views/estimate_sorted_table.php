<?php if ($new_estimate) { ?>
	<?php foreach ($new_estimate->result() as $rows) { ?>
		<tr>
			<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
			<td><?php echo $rows->client_address . ",&nbsp;" . $rows->client_city; ?></td>
			<?php if(isset($rows->status) && $rows->status != 'Decline - Project on hold' && $rows->status != 'Decline - No follow up') : ?><td width="60"><?php echo $rows->estimate_count_contact; ?></td><?php endif; ?>
			<?php if(isset($rows->status) && $rows->status != 'Decline - Project on hold' && $rows->status != 'Decline - No follow up') : ?><td width="100"><?php echo ($rows->estimate_last_contact) ? date('Y-m-d', $rows->estimate_last_contact) : ''; ?></td><?php endif; ?>
			<td width="100"><?php echo date('Y-m-d', $rows->date_created); ?></td>
			<td width="100"><?php echo $rows->firstname . ' ' . $rows->lastname; ?></td>
			<td><?php echo $rows->status; ?></td>
			<td width="70">
				<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
				<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
			</td>
		</tr>
	<?php } ?>
	<?php if ($pending_estimate_links) : ?>
		<tr>
			<td colspan="7">
				<div class="pull-right p-sides-10"><?php echo $pending_estimate_links; ?></div>
				</div>
		</tr>
	<?php endif; ?>
	<?php if ($sent_estimate_links) : ?>
		<tr>
			<td colspan="7">
				<div class="pull-right p-sides-10"><?php echo $sent_estimate_links; ?></div>
				</div>
		</tr>
	<?php endif; ?>
	<?php if ($new_estimate_links) : ?>
		<tr>
			<td colspan="7">
				<div class="pull-right p-sides-10"><?php echo $new_estimate_links; ?></div>
				</div>
		</tr>
	<?php endif; ?>
<?php } else { ?>
	<tr>
		<td colspan="6" style="color:#FF0000;">No record found</td>
	</tr>
<?php } ?>
