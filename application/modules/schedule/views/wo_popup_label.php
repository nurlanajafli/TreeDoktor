<?php foreach ($workorders as $workorder) : ?>
	<label class="btn btn-dark btn no-shadow m-b-xs m-l-xs label-wo" 
	       data-label-wo-id="<?php echo $workorder['id']; ?>"
	       data-label-wo-no="<?php echo $workorder['workorder_no']; ?>"
	       data-services="{}"
	       style="width:48%;overflow: hidden;text-overflow: ellipsis;"
	       data-toggle="popover" data-html="true" data-placement="bottom"
	       data-address="<?php echo $workorder['client_address']; ?>"
	       data-price="<?php echo money($workorder['sum_without_tax']); ?>"
	       data-time="<?php echo $workorder['total_time']; ?>"
	     >
		<input type="radio" name="options" id="option1" autocomplete="off">
		<?php $workorder['total_time'] = $workorder['total_time'] ? $workorder['total_time'] : 0; ?>
		<?php echo round($workorder['total_time'], 2) . ' hrs. - ' . money($workorder['sum_without_tax']) . ' - ' . $workorder['client_name'] . ', ' . $workorder['client_address']; ?>
	</label>
<?php endforeach; ?>
