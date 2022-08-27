<input type="hidden" name="ev_event_id" value="<?php echo $event_id; ?>">
<input type="hidden" name="ev_estimate_id" value="<?php echo $estimate_data->estimate_id; ?>">
<input type="hidden" name="ev_team_id" value="<?php echo $event['team_id']; ?>">

<div class="row">
	<hr>
	<div class="col-md-12"><h3 class="text-center m-top-5">HAZARDS</h3></div>
</div>

<?php foreach(config_item('hazards') as $key => $row): ?>
	<div class="row">
	<?php foreach($row as $col_key=>$col_data): ?>
	<div class="col-md-4 col-sm-6 hazards-container">
		<h5><strong><?php echo $col_data['label']; ?></strong></h5>
		
		<?php foreach($col_data['data'] as $checkbox_key=>$checkbox_row): ?>
		<div class="checkbox">
			<label class="checkbox-custom">
				<input type="checkbox" name="hazards[]" value="<?php echo $checkbox_row; ?>"<?php /*checked="checked"*/ ?>>
				<i class="fa fa-fw fa-square-o"></i>
				<?php echo $checkbox_row; ?>
			</label>
		</div>
		<?php endforeach; ?>
		<textarea class="form-control" name="hazards_text_<?php echo $key; ?>_<?php echo $col_key; ?>"></textarea>
	</div>
	<?php endforeach; ?>
	</div>
<?php endforeach; ?>

<div class="row">
	<hr>
	<div class="col-md-12"><h3 class="text-center m-top-5">CONTROLS</h3></div>
</div>
<?php foreach(config_item('controls') as $key => $row): ?>
	<div class="row">
	<?php foreach($row as $col_key=>$col_data): ?>
	<div class="col-md-4 col-sm-6 controls-container">
		<h5><strong><?php echo $col_data['label']; ?></strong></h5>
		
		<?php foreach($col_data['data'] as $checkbox_key=>$checkbox_row): ?>
		<div class="checkbox">
			<label class="checkbox-custom">
				<input type="checkbox" value="<?php echo $checkbox_row; ?>" name="controls[]" <?php /*checked="checked"*/ ?>>
				<i class="fa fa-fw fa-square-o"></i>
				<?php echo $checkbox_row; ?>
			</label>
		</div>
		<?php endforeach; ?>
		<textarea class="form-control" name="controls_text_<?php echo $key; ?>_<?php echo $col_key; ?>"></textarea>
	</div>
	<?php endforeach; ?>
	</div>
<?php endforeach; ?>
