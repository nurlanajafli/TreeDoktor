<?php foreach ($workorders as $workorder) : ?>
	<label class="btn btn-dark btn no-shadow m-b-xs m-l-xs label-wo"
	       data-label-wo-id="<?php echo $workorder['id']; ?>"
	       data-label-wo-no="<?php echo $workorder['workorder_no']; ?>"
	       style="width:48%;overflow: hidden;text-overflow: ellipsis;"
	       data-toggle="popover" data-html="true" data-placement="bottom"
	       data-address="<?php echo $workorder['client_address']; ?>"
	       data-price="<?php echo money($workorder['total']); ?>"
	       data-time="<?php echo $workorder['total_time']; ?>"
	       data-original-title="<button type=&quot;button&quot; class=&quot;close pull-right&quot; data-dismiss=&quot;popover&quot;>×</button>&nbsp;">
		<input type="radio" name="options" id="option1" autocomplete="off">
		<?php $workorder['total_time'] = $workorder['total_time'] ? $workorder['total_time'] : 0; ?>
		<?php echo $workorder['total_time'] . ' hrs. - ' . money($workorder['total']) . ' - ' . $workorder['client_address']; ?>
	</label>
<?php endforeach; ?>