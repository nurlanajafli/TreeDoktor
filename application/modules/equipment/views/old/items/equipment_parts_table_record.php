<?php if ($parts && !empty($parts)) : ?>
	<?php foreach ($parts as $part) : ?>
		<tr data-part_id="<?php echo $part['part_id']; ?>" data-item_id="<?php echo $part['part_item_id']; ?>">
			<td class="name"><a href="#" class="editPart"
			                    data-item_id="<?php echo $part['part_id']; ?>"><?php echo $part['part_name']; ?></td>
			<td class="seller"><?php echo $part['part_seller'] ? $part['part_seller'] : 'N/A'; ?></td>
			<td class="price"><?php echo $part['part_price'] ? $part['part_price'] : 'N/A'; ?></td>
<!--			<td class="date">--><?php //echo $part['part_date'] ? date('Y-m-d', $part['part_date']) : 'N/A'; ?><!--</td>-->
			<td class="date"><?php echo $part['part_date'] ? getDateTimeWithTimestamp($part['part_date']) : 'N/A'; ?></td>
			<td class="action" style="text-align:center;">
				<a href="#" class="btn btn-danger btn-xs deletePart" style="margin-top:5px;"
				   data-part_id="<?php echo $part['part_id']; ?>"><i class="fa fa-trash-o"></i></a>
			</td>
		</tr>
	<?php endforeach; ?>
<?php else : ?>
	<tr>
		<td colspan="5" style="color:#FF0000;">No record found</td>
	</tr>
<?php endif; ?>
