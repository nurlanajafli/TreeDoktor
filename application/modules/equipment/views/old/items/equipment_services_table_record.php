<?php if ($services && !empty($services)) : ?>
	<?php foreach ($services as $service) : ?>
		<?php if ($service['service_status'] == 'complete' && empty($history)) : ?>
			<tr class="info">
				<td colspan="7" style="text-align:center;"><strong>History</strong></td>
			</tr>
			<?php $history = TRUE; ?>
		<?php endif; ?>
		<tr data-service_id="<?php echo $service['service_id']; ?>"
		    data-item_id="<?php echo $service['service_item_id']; ?>"
		    data-service_type_id="<?php echo $service['service_type_id']; ?>">
			<td class="name">
				<?php if ($service['service_status'] == 'new') : ?>
					<a href="#" class="editService"
					   data-item_id="<?php echo $service['service_id']; ?>" data-service_type_id="<?php echo $service['service_type_id']; ?>"><?php echo $service['equipment_service_type']; ?></a>
				<?php else : ?>
					<?php echo $service['equipment_service_type']; ?>
				<?php endif; ?>
			</td>
			<td class="period"><?php echo $service['service_periodicity'] ? $service['service_periodicity'] : 'N/A'; ?></td>
			<td class="date"><?php echo $service['service_date'] ? date("Y-m-d", $service['service_date']) : 'N/A'; ?></td>
			<td class="next"><?php echo $service['service_next'] ? date("Y-m-d", $service['service_next']) : 'N/A'; ?></td>
			<td class="status"><?php echo $service['service_status']; ?></td>
			<td class="descr">
				<?php echo $service['service_description'] ? $service['service_description'] : 'N/A'; ?>
				<?php if ($service['service_status'] == 'complete') : ?>
					<br><strong>Service comment:</strong> <?php echo $service['service_comment']; ?>
				<?php endif; ?>
			</td>
			<td class="action" style="text-align:center;">
				<?php if ($service['service_status'] != 'complete') : ?>
					<a href="#" class="btn btn-danger btn-xs deleteService" style="margin-top:5px;"
					   data-service_id="<?php echo $service['service_id']; ?>"><i class="fa fa-trash-o icon-white"></i></a>
				<?php else : ?>
					<strong><?php echo $service['service_hrs'] ? $service['service_hrs'] : 0; ?></strong> hrs.
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
<?php else : ?>
	<tr>
		<td colspan="7" style="color:#FF0000;">No record found</td>
	</tr>
<?php endif; ?>
