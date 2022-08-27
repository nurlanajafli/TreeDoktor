
<?php if (isset($estimate_qa) && $estimate_qa && !empty($estimate_qa)) : ?>
    <?php  $qa_types = $this->config->item('qa_types'); ?>
	<?php foreach ($estimate_qa as $qa) : ?>
		<tr>
			<td><?php echo date(getDateFormat(), $qa['qa_date']); ?></td>
			<td><?php echo $qa_types[$qa['qa_type_int']] ?? 'N/A'; ?></td>
			<td><?php echo $qa['qa_name']; ?></td>
			<td><?php echo $qa['qa_message']; ?></td>
		</tr>
	<?php endforeach; ?>
<?php else : ?>
	<tr>
		<td colspan="4" style="color:#FF0000;">No record found</td>
	</tr>
<?php endif; ?>
