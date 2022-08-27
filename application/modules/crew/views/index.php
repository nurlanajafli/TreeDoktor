<?php $this->load->view('includes/header'); ?>


	<div class="row">
		<div class="grid_12 rounded filled_white shadow overflow">

			<!-- Users header -->
			<div class="module-header">
				<div class="module-title">Crews
					<?php if (isAdmin()) { ?>
						<a href="crew/crew_add" role="button" class="btn btn-mini btn-inverse pull-right"><i
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
						<th>Crew Name</th>
						<th>Crew_color</th>
						<th>Date</th>
						<th>Action</th>
					</tr>
					</thead>
					<tbody>
					<?php
					if ($crew_row) {
						foreach ($crew_row->result() as $row):
							?>
							<tr>
								<td><?php echo $row->crew_id; ?></td>
								<td><?php echo $row->crew_name; ?></td>
								<td><?php echo $row->crew_color; ?></td>
								<td><?php echo $row->create_date; ?></td>
								<th><?php if (isAdmin()) {
										echo anchor('crew/crew_update/' . $row->crew_id, 'Edit', "class='btn btn-mini btn-success'"); ?>
										<?php /* echo anchor('crew/crew_delete/'.$row->crew_id, 'Delete', "class='btn btn-mini btn-danger'");*/
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