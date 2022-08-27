<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Database Backups</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Database Backups
			<a class="pull-right" href="<?php echo base_url('administration/db_backup'); ?>">Backup Now</a>
		</header>
		<table class="table table-hover">
			<thead>
			<tr>
				<th>Filename</th>
				<th>Filesize</th>
				<th width="170px">Date</th>
			</tr>
			</thead>
			<tbody>
			<?php if (isset($files) && !empty($files)) : ?>
				<?php foreach ($files as $file) : ?>
					<tr>
						<td>
							<a href="<?php echo base_url('administration/download/' . $file); ?>"><?php echo $file; ?></a>
						</td>
						<td>
							<?php echo (round((filesize(/*FCPATH . */'docs/' . $file) / 1024 / 1024), 2)) . " MB"; ?>
						</td>
						<td>
							<?php $info = get_file_info(/*FCPATH . */'docs/' . $file);
							echo date('Y-m-d H:i:s', $info['date']); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			</tbody>
		</table>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
