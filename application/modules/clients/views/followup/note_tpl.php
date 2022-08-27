<ul style="list-style-position: inside; padding: 0;">
	<?php $variables = json_decode($fuData->fu_variables, TRUE) ?>
	<?php $noField = $fsConfig[$fuData->fu_module_name]['no_field_name']; ?>
	Follow Up For <strong><?php echo $variables['NO']; ?></strong>:
	<div class="m-t-sm"></div>
	<?php foreach ($to as $key => $value) : ?>
		<li>
			<strong><?php echo $fields[$key] ?></strong> — 
			<?php if($from->$key) : ?>
				<strong>From:</strong> "<u><?php echo $from->$key; ?></u>" 
			<?php endif; ?>
			<strong>To:</strong> "<u><?php echo $value; ?></u>"
		</li>
	<?php endforeach; ?>
</ul>