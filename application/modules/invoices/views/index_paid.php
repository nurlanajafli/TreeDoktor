<!-- Paid Invoices -->

<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
	<thead>
	<tr>
		<th>Client Name</th>
		<th width="360px">Address</th>
		<th width="105px">Invoice No</th>
		<th width="120px">Invoice Total</th>
		<th width="120px"><a href="#">Date</a></th>
		<th>QA</th>
		<th>Like</th>
		<th>Status</th>
		<th width="70px">Action</th>
	</tr>
	</thead>
	<tbody>
	<?php if ($paid) { ?>
		<?php foreach ($paid->result() as $rows) {
			?>
			<tr>
				<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
				<td><?php echo $rows->client_address . ",&nbsp;" . $rows->client_city; ?></td>
				<td><?php echo $rows->invoice_no; ?></td>
				<td><?php echo money((float)$rows->total); ?></td>
				<td><?php echo date('Y-m-d', strtotime($rows->date_created)); ?></td>
				<td>
					<?php if ($rows->qa_id) : ?>
						<i class="fa fa-check"></i>
					<?php else : ?>
						<i class="fa fa-times"></i>
					<?php endif; ?>
				</td>
				<td>
					<?php if($rows->invoice_like === '1') : ?>
						<img src="<?php echo base_url('assets/img/up-sm.png'); ?>" height="15" class="m-l-xs">
					<?php elseif($rows->invoice_like === '0') : ?>
						<img src="<?php echo base_url('assets/img/down-sm.png'); ?>" height="15" class="m-l-xs">
					<?php endif; ?>
				</td>
				<td><?php echo $rows->in_status; ?></td>
				<td>
					<?php //echo anchor('invoices/edit/'.$rows->id,'<i class="icon-pencil"></i>', 'class="btn btn-mini"')?>
					<?php echo anchor($rows->invoice_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
				</td>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<tr>
			<td colspan="8" style="color:#FF0000;">No record found</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<!-- /Paid Invoices ends -->
