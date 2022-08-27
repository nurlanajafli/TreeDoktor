
<table class="table table-striped b-t b-light" id="tbl_search_result" border="1" cellspacing="0" cellpadding="5">
	<thead>
	<tr>
		<th width="20px">ID</th>
		<th width="200px">
			Item Name
		</th>
		<th>
			Date
		</th>
		<th>
			Type
		</th>
		<th>
			Status
		</th>
		<th>Pr</th>
		<th>Price</th>
		<th>Hours</th>
		<th>Counter</th>
		<th>
			Author
		</th>
		<th>Comment</th>
		<th>Assigned to</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($repairs as $key=>$repair) : ?>
		<tr>
			<td>
				<?php echo $repair->repair_id; ?>
			</td>
			<td ><?php echo $repair->item_name; ?></td>
<!--			<td>--><?php //echo date('Y-m-d', strtotime($repair->repair_date)); ?><!--</td>-->
			<td><?php echo getDateTimeWithDate($repair->repair_date, 'Y-m-d H:i:s');?></td>
			<td>
				<?php echo ucfirst($repair->repair_type); ?>
			</td>
			<td><?php echo mb_convert_case(str_replace('_', ' ', $repair->repair_status), MB_CASE_TITLE, "UTF-8"); ?></td>
			<td><?php echo $repair->repair_priority; ?></td>
			<td><?php echo money($repair->repair_price); ?></td>
			<td><?php echo $repair->repair_hours; ?></td>
			<td><?php echo $repair->repair_counter; ?></td>
			<td><?php echo $repair->author_name; ?></td>
			<td>
				<?php if(isset($repair->repair_notes) && !empty($repair->repair_notes)) : ?>
					<?php echo $repair->repair_notes[count($repair->repair_notes)-1]->equipment_note_text;//countOk ?>
				<?php else : ?>
					-
				<?php endif; ?>
			</td>
			<td>
				<?php echo $repair->repair_solder_id ? $repair->solder_name : ''; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>


