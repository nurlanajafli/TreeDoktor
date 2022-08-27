<?php $this->load->view('includes/header'); ?>

	<!-- Database Title -->
	<div class="row">
		<div class="grid_12 rounded filled_white shadow overflow">

			<!-- Database header -->
			<div class="module-header">
				<div class="module-title">Database Backup</div>
			</div>

			<!-- Database lateset files -->
			<div class="filled_grey p-top-10 p-sides-15">

				<table class="table table-hover" id="tbl_Estimated">
					<thead>
					<tr>
						<th>File Name</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td>Current</td>
						<td><?php echo anchor('administration/db_backup', 'Download'); ?></td>
					</tr>
					<?php if (!empty($latest_files)) {
						foreach ($latest_files as $k => $v) {
							?>
							<tr>
								<td><?php echo $k; ?></td>
								<td><?php echo anchor('administration/downloadFile/' . $k, 'Download'); ?></td>

							</tr>
						<?php
						}
					}?>

					</tbody>
				</table>

			</div>
		</div>
	</div>
	<!-- /Invoices Title ends-->




<?php $this->load->view('includes/footer'); ?>