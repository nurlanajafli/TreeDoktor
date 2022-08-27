<?php if(empty($files)): ?>
	<div class="text-center m-t-lg clearfix wrapper-lg animated fadeInRightBig" id="galleryLoading">
	<h1>Event Gallery</h1>
	<h3 class="text-muted">This gallery is empty.</h3>
	<h3><a href="#" class="btn btn-success" onclick="$('.dropzone').first().trigger('click')">Upload first photo</a></h3>
	</div>
<?php else: ?>
	<div id="gallery" style="display:none;">
	<?php foreach ($files as $key => $file): 
		$fileinfo = bucket_get_file_info($file);
	?>
		<?php if(is_bucket_file($file)): ?>
		<a href="<?php echo site_url($file); ?>">
		<img alt="<?php echo (count($fileinfo) && isset($fileinfo['name']))?$fileinfo['name']:''; ?>"
		     src="<?php echo site_url($file); ?>"
		     data-image="<?php echo site_url($file); ?>"
		     data-description="<?php echo (count($fileinfo) && isset($fileinfo['name']))?$fileinfo['name']:''; ?>"
		     style="display:none">
		</a>
		<?php else: ?>
			<a href="<?php echo site_url($file); ?>">
			<img alt="No file"
			     src="<?php echo site_url('/assets/img/nopic.jpg'); ?>"
			     data-image="<?php echo site_url('/assets/img/nopic.jpg'); ?>"
			     data-description="No file in bucket"
			     style="display:none">
			</a>

		<?php endif; ?>
	<?php endforeach; ?>
	</div>
<?php endif; ?>

