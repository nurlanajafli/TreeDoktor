<div class="pull-left m-r-xs">

</div>
<p>
Item Name: <?php echo $row['item_name']; ?><br>

Item Serial: <?php echo $row['item_serial']; ?><br>

Item Description: <?php if ($row['item_description']) :
	echo $row['item_description'];
else :
	echo 'N/A';
endif; ?>
<br>
Hours counter: <?php echo $row['counter_hours'] ? $row['counter_hours'] : 'N/A'; ?><br>
Kilometers counter: <?php echo $row['counter_kilometers'] ? $row['counter_kilometers'] : 'N/A'; ?>
</p>
<br>
<? /*<div class="line line-dashed line-lg"></div>
Item Date: <?php if ($row['item_date']) {
	echo date('Y-m-d', $row['item_date']);
} else {
	echo 'N/A';
} ?><br>
*/ ?>
<?php    $path = 'uploads/equipments_files/' . $row['item_id'] . '/';
$files = bucketScanDir($path);
if ($files && !empty($files)) :
	sort($files, SORT_NATURAL);
	foreach ($files as $file) : ?>
		<div>
			<div class="line line-dashed line-lg"></div>
			<?PHP $ext = pathinfo($file, PATHINFO_EXTENSION); ?>
			File:
			<a <?php if ($ext == 'pdf' || $ext == 'PDF') : ?> target="_blank"<?php else : ?> data-lightbox="works"<?php endif; ?>
				href="<?php echo base_url('uploads/equipments_files/' . $row['item_id'] . '/' . $file); ?>"><?php echo $file; ?></a>
			<a class="btn btn-danger btn-xs pull-right delete_image" data-item_id="<?php echo $row['item_id']; ?>"
			   data-filename="<?php echo $file; ?>" aria-hidden="true"><i class="fa fa-trash-o"></i></a>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
<div class="line line-dashed line-lg"></div>
<span class="btn btn-primary btn-file">Choose File
			<input type="file" name="file" class="fileToUpload btn-upload"
			       id="fileToUpload_<?php echo $row['item_id']; ?>" data-item_id="<?php echo $row['item_id']; ?>">
		</span>
<img style="display:none;" class="preloader" src="<?php echo base_url('/assets/img/ajax-loader.gif'); ?>">
<div class="line line-dashed line-lg"></div>
