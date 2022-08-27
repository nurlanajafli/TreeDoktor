<!-- Display workorders -->
<div class="table-responsive">
	<table class="table table-striped b-t b-light m-n workordersTable" style=" ">
		<thead>
		<tr>
			<th>Client Name</th>
			<th>No</th>
			<th><a href="#">Date</a></th>
			<th>Address</th>
			<th>Phone</th>
			<th>Estimator</th>
			<th>Total</th>
			<th width="580px">Notes</th>
			<th width="80px" class="print-btns">Action</th>
		</tr>
		</thead>
		<tbody>
		<?php if ($workorders) { ?>
			<?php foreach ($workorders as $rows) {
				$if_priority = "";
				if ($rows['wo_priority'] == "Priority") {
					$if_priority = "alert-error";
				}
				?>
				<tr class="<?php echo $if_priority; ?>">
					<td width="200"><?php echo anchor( $rows['client_id'], $rows['client_name']); ?></td>
					<td width="100"><?php echo $rows['workorder_no']; ?></td>
					<td width="150"><?php echo getDateTimeWithDate($rows['date_created'], 'Y-m-d'); ?></td>
					<td width="250"><?php echo $rows['client_address'] . ",&nbsp;" . $rows['client_city']; ?></td>
					<td width="250">
						<a href="#" class="<?php if($rows['cc_phone'] == numberTo($rows['cc_phone'])) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $rows['client_id']; ?>" data-number="<?php echo substr($rows['cc_phone'], 0, 10);?>">
							<?php echo numberTo($rows['cc_phone']); ?>
						</a>
					</td>
					<td><?php echo $rows['firstname'] . ' ' . $rows['lastname']; ?></td>
					<td width="150"><?php echo money($rows['total_with_tax']); ?></td>
					<td width="580px"><textarea class="form-control wo-note" name="wo_office_notes" data-wo_id="<?php echo $rows['id']; ?>" placeholder="Ctrl+Enter"><?php echo htmlspecialchars($rows['wo_office_notes']); ?></textarea></td>
					<td width="70" class="print-btns">
						<?php //echo anchor('workorders/edit/' . $rows['id'], '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
						<?php echo anchor($rows['workorder_no'], '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
					</td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="7" style="color:#FF0000;">No record found</td>
				<td></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<!-- /Display workorders End-->
