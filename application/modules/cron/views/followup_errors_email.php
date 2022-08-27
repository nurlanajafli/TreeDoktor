<ul>
	Error List:
	<?php foreach ($errors as $key => $value) : ?>
		<?php 
			$periodicity = $value['job']->fs_every ? 'Every ' : 'After ';
			$periodicity .= $value['job']->fs_periodicity . ' Days ';
			$periodicity .= $value['job']->fs_time;
		?>
		<li>
			<?php echo $value['msg']; ?> - 
			<a href="<?php echo base_url('client/' . $value['job']->fu_client_id); ?>"><?php echo $value['variables']['NAME']; ?></a>, 
			<a href="<?php echo base_url($value['job']->fu_module_name . '/' . $value['job']->fu_action_name . '/' . $value['job']->fu_item_id); ?>"><?php echo $value['variables']['NO']; ?></a>, #<?php echo $value['job']->fs_id; ?> <?php echo $periodicity; ?>
		</li>
	<?php endforeach; ?>
</ul>