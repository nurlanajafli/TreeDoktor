<tr data-id="<?php echo $repair->repair_id; ?>">
	<?php /*<td >
		<a href="<?php echo base_url('equipments/profile') . '/' . $repair->item_id; ?>"><?php echo $repair->item_name; ?></a>
	</td>*/ ?>
	<td>
		<?php echo $repair->repair_id; ?>
	</td>
	<td>
<!--		--><?php //echo date('Y-m-d', strtotime($repair->repair_date)); ?>
		<?php echo getDateTimeWithDate($repair->repair_date, 'Y-m-d H:i:s'); ?>
	</td>
	<td>
		<a href="#" data-name="type" data-value="<?php echo $repair->repair_type; ?>" data-placement="right" data-type="select" data-source="[{value:'damage',text:'Damage'},{value:'repair',text:'Repair'},{value:'maintenance',text:'Maintenance'}]" data-pk="<?php echo $repair->repair_id; ?>" class="type" title="Type" data-url="<?php echo base_url('equipments/ajax_update_repair'); ?>"><?php echo ucfirst($repair->repair_type); ?></a>
	</td>
	<td><?php echo mb_convert_case(str_replace('_', ' ', $repair->repair_status), MB_CASE_TITLE, "UTF-8"); ?></td>
	<td><?php echo $repair->repair_priority; ?></td>
	<td><?php echo money($repair->repair_price); ?></td>
	<td><?php echo $repair->repair_hours; ?></td>
	<td><?php echo $repair->repair_counter; ?></td>
	<td><?php echo $repair->author_name; ?></td>
	<td>
		<?php if(isset($repair->repair_first_comment) && $repair->repair_first_comment) : ?>
			<?php echo $repair->repair_first_comment;?>
		<?php else : ?>
			-
		<?php endif; ?>
	</td>
	<td>
		<?php if(isset($repair->repair_finish_comment) && $repair->repair_finish_comment) : ?>
			<?php echo $repair->repair_finish_comment;?>
		<?php else : ?>
			-
		<?php endif; ?>
	</td>
	<td>
		<a href="#" data-name="solder_id" data-placement="left" data-type="select" data-source='<?php echo json_encode($solders); ?>' data-value="<?php echo $repair->repair_solder_id; ?>" class="solder" title="Assigned to" data-pk="<?php echo $repair->repair_id; ?>" data-url="<?php echo base_url('equipments/ajax_update_repair'); ?>"></a>
	<td>
		<?php if(isset($status) && $status != 'deleted') : ?>
			<a href="#notes-<?php echo $repair->repair_id; ?>" class="btn btn-default btn-xs" data-toggle="modal"><i class="fa fa-list-ul"></i></a>
			
			<a href="<?php echo base_url('equipments/repair_pdf') . '/' . $repair->repair_id; ?>" class="btn btn-default btn-xs "><i class="fa fa-file"></i></a>
			<a href="#edit-<?php echo $repair->repair_id; ?>" role="button" class="btn btn-default btn-xs " data-toggle="modal"><i class="fa fa-pencil"></i></a>
		<?php endif; ?>
		<a href="#" role="button" class="btn btn-default btn-xs deleteRepair btn-danger" data-id="<?php echo $repair->repair_id; ?>">
		<?php if(isset($status) && $status == 'deleted') : ?>
			<i class="fa fa-undo"></i>
		<?php else : ?>
			<i class="fa fa-trash-o"></i>
		<?php endif; ?>
		</a>
		
	</td>
</tr>
