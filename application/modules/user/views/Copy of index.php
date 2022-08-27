<?php $this->load->view('includes/header'); ?>


	<div class="row">
		<div class="grid_12 rounded filled_white shadow overflow">

			<!-- Users header -->
			<div class="module-header">
				<div class="module-title">Users
					<?php if (isAdmin()) { ?>
						<a href="user/user_add" role="button" class="btn btn-mini btn-inverse pull-right" "><i
							class="icon-plus icon-white"></i><i class="icon-user icon-white"></i></a>
      <?php } ?>
				</div>
			</div>

			<!-- Data display -->
			<div class="m-bottom-10 p-sides-10">
				<table class="table table-hover">
					<thead>
					<tr>
						<th>Id</th>
						<th>Email</th>
						<th>Type</th>
						<th>First Name</th>
						<th>Last name</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody>
					<?php
					if ($user_row) {
						foreach ($user_row->result() as $row):
							?>
							<tr>
								<td><?php echo $row->id; ?></td>
								<td><?php echo $row->emailid; ?></td>
								<td><?php echo $row->user_type; ?></td>
								<td><?php echo $row->firstname; ?></td>
								<td><?php echo $row->lastname; ?></td>
								<th><?php if (isAdmin()) {
										echo anchor('user/user_update/' . $row->id, 'Edit', "class='btn btn-mini btn-success'"); ?> |
										<?php echo anchor('user/user_delete/' . $row->id, 'Delete', "class='btn btn-mini btn-danger'");
									} ?></th>
							</tr>
						<?php
						endforeach;
					} else {
						?>
						<tr>
							<td colspan="5"><?php echo "No records found"; ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			</div>
			<!--/ Data Display-->

		</div>
	</div>
<?php $this->load->view('includes/footer'); ?>