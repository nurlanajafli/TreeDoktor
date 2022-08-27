<?php /*
Service Name: <?php echo $service['equipment_service_type']; ?><br>
<div class="line line-dashed line-lg"></div>
Service Date: <?php echo $service['service_date'] ? date('Y-m-d', $service['service_date']) : 'N/A'; ?><br>
<?php if ($service['service_next']) : ?>
	<div class="line line-dashed line-lg"></div>
	Service Next: <?php echo date('Y-m-d', $service['service_next']); ?><br>
<?php endif; ?>
<div class="line line-dashed line-lg"></div>
*/?>
Service Status: <select id="" class="form-control serviceStatus">
	<option value="new"<?php if ($service['service_status'] == 'new') : ?> selected<?php endif; ?>>New</option>
	<option value="complete"<?php if ($service['service_status'] == 'complete') : ?> selected<?php endif; ?>>Complete
	</option>
</select>
<div class="line line-dashed line-lg"></div>
Service Comment:
<?php if ($service['service_status'] == 'new') : ?>
	<textarea class="serviceComment form-control" rows="2"><?php echo $service['service_comment']; ?></textarea>
<?php else : ?>
	<?php echo $service['service_comment']; ?>
<?php endif ?>
<div class="line line-dashed line-lg"></div>
Service hrs:
<?php if ($service['service_status'] == 'new') : ?>
	<input type="text" class="form-control serviceHrs"
	       value="<?php echo $service['service_hrs'] ? $service['service_hrs'] : 0; ?>">
<?php else : ?>
	<?php echo $service['service_hrs']; ?>
<?php endif ?>
