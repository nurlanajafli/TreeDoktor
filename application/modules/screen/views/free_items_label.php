<?php foreach ($equipment as $item) : ?>
	<li class="label bg-danger ui-draggable addItem b-a" style="border-color: #000;text-shadow: 1px 1px #626262;background: <?php echo $item->group_color; ?>;"
	    data-item_id="<?php echo $item->item_id; ?>"><?php echo $item->item_name ?></li>
<?php endforeach; ?>