<!-- Issued Invoices -->

<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
	<thead>
	<tr>
		<th>Client Name</th>
		<th width="300px">Address</th>
		<th width="300px">Phone</th>
		<th width="105px">Invoice No</th>
		<th width="120px">Invoice Total</th>
		<th width="110px"><a href="#">Date</a></th>
		<th>QA</th>
		<th>Status</th>
		<th width="70px">Action</th>
	</tr>
	</thead>
	<tbody>
	<?php if ($overdue) { ?>
		<?php foreach ($overdue->result() as $rows) { //echo '<pre>';print_r($rows);
			;
			if (isset($rows->interest_status) && $rows->interest_status == 'Yes') {
				$overdue = $invoice_total; //if status yes remove overdue completely
			} else { //if status No show overdue
				if (isset($invoice_interest_data) && !empty($invoice_interest_data)) {
					$overdue_amt = $invoice_total;
					if ($invoice_interest_data[$rows->id]) {
						foreach ($invoice_interest_data[$rows->id] as $k => $v) {
							$interest = abs($v->rate / 100);
							$invoice_interst = $overdue_amt * $interest;
							$overdue_amt = $overdue_amt + $invoice_interst;
						}
						$overdue = $overdue_amt;
					} else {
						$overdue = $invoice_total;
					}

				} else {
					$overdue = $invoice_total;
				}

			}

			?>

			<tr>
				<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
				<td><?php echo $rows->client_address . ",&nbsp;" . $rows->client_city; ?></td>
				<td><?php echo $rows->cc_phone; ?></td>
				<td><?php echo $rows->invoice_no; ?></td>
				<td><?php echo money((float)$rows->total); ?></td>
				<td><?php echo date('Y-m-d', strtotime($rows->date_created)); ?></td>
				<td><?php

					if (isset($rows->interest_status) && $rows->interest_status == 'Yes') {
						echo money((float)$overdue);
					} else {
						echo money((float)($overdue - $rows->interest_rate));
					}
					?></td>
				<td>
					<?php if ($rows->qa_id) : ?>
						<i class="fa fa-check"></i>
					<?php else : ?>
						<i class="fa fa-times"></i>
					<?php endif; ?>
				</td>
				<td><?php echo $rows->in_status; ?></td>
				<td>
					<?php // echo anchor('invoices/edit/'.$rows->id,'<i class="icon-pencil"></i>', 'class="btn btn-mini"')?>
					<?php echo anchor($rows->invoice_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
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

<!-- /overdue Invoices end -->
