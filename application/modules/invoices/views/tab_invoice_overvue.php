<?php $totalOverdue = 0; ?>

<?php if ($invoices) : ?>
	<?php foreach ($invoices as $rows):
		$totalOverdue += $Overdue = get_interest_sum($rows->id);
		
		?>
		
		<tr>
			<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
			<td><?php echo $rows->client_address; ?><?php if($rows->client_address): ?>,&nbsp;<?php endif; ?><?php echo $rows->client_city; ?></td>
			<td>
				<a href="#" class="<?php if($rows->cc_phone == numberTo($rows->cc_phone)) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $rows->client_id; ?>" data-number="<?php echo substr($rows->cc_phone, 0, 10);?>">
					<?php echo numberTo($rows->cc_phone); ?>
				</a>
			</td>
			<td><?php echo $rows->emailid; ?></td>
			<td><?php echo $rows->invoice_no; ?></td>
            <td><?php echo money(round($rows->total, 2) ? round($rows->total, 2) : 0); ?></td>
            <td><?php echo money(round($rows->due, 2) ? round($rows->due, 2) : 0); ?></td>
<!--			<td>--><?php //echo date('Y-m-d', strtotime($rows->date_created)); ?><!--</td>-->
			<td><?php echo getDateTimeWithDate($rows->date_created, 'Y-m-d') ?></td>

				<td><?php echo money($Overdue); ?>
				</td>
			
			<td>
				<?php if ($rows->qa_id) : ?>
					<i class="fa fa-check"></i>
				<?php else : ?>
					<i class="fa fa-times"></i>
				<?php endif; ?>
			</td>
			
			
			<td>
				<?php echo anchor($rows->invoice_no . '/pdf', '<i class="fa fa-file"></i>', 'class="btn btn-xs btn-default"') ?>
				<?php echo anchor($rows->invoice_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
			</td>
		</tr>
	<?php  endforeach; ?>
<tr>
	<td colspan="9" style="text-align:right;"><strong>Total Overdue: </strong></td>
	<td><strong><?php echo money($totalOverdue); ?></strong></td>
	<td></td>
	<td></td>
</tr>
<?php  else : ?>
	<tr>
		<td colspan="11" style="color:#FF0000;">No record found</td>
	</tr>
<?php endif;  ?>
