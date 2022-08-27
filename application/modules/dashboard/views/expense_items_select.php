<select id="itemId" name="expense_item" class="expense_item form-control">
	<option value="">Select Item</option>
	<?php $groupName = NULL; ?>
	<?php foreach($items as $key => $item) : ?>

		<?php if($groupName != $item['group_name']) : ?>
			<optgroup label="<?php echo $item['group_name']; ?>">
		<?php endif; ?>

		<option value="<?php echo $item['item_id']; ?>"><?php echo $item['item_name']; ?></option>

		<?php if((isset($items[$key + 1]['group_name']) && $item['group_name'] != $items[$key + 1]['group_name']) || !isset($items[$key + 1]['group_name'])) : ?>
			</optgroup>
		<?php endif; ?>
		<?php $groupName = $item['group_name']; ?>
	<?php endforeach; ?>
</select>