<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('info'); ?>">Tree Info</a></li>
		<li class="active"><?php echo $tree->tree_common_name ?></li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading"><?php echo $tree->tree_common_name ?></header>
		<div class="media m-b">
			<div class="pull-left col-md-4 text-center">
				<?php $files = bucketScanDir('uploads/trees_files/' . $tree->tree_id); ?>
				<?php if ($files) : ?>
					<?php foreach ($files as $key => $file) : ?>
						<a href="<?php echo base_url('uploads/trees_files/' . $tree->tree_id . '/' . $file); ?>"
						   data-lightbox="works">
							<img src="<?php echo base_url('uploads/trees_files/' . $tree->tree_id . '/' . $file); ?>"
							     data-alt="<?php echo $tree->tree_common_name; ?>"<?php if ($key) : ?> class="m-t"<?php endif; ?>>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<div class="media-body">
				<h1>
					<strong><?php echo $tree->tree_common_name; ?></strong>&nbsp;&nbsp;&nbsp;<?php echo $tree->tree_scientific_name; ?>
				</h1>

				<div class="details m-t-lg">
					<?php $details = json_decode($tree->tree_data); ?>
					<?php foreach ($details as $key => $val) : ?>
						<h4><strong><?php echo $key; ?></strong></h4>
						<p><?php echo $val; ?></p>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
