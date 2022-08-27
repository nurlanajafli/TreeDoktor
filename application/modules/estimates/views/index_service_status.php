<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Service Status</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Status
			<a href="#status-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<table class="table">
			<thead>
			<tr>
				<th>Status Name</th>
				<th width="80px">Action</th>
			</tr>
			</thead>
			<tbody class="">
			<?php foreach ($statuses as $key=>$status) : ?>
				<tr data-status_id="<?php echo $status['services_status_id']; ?>">
					<td><?php echo $status['services_status_name']; ?></td>
					<td>
						<div id="status-<?php echo $status['services_status_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
						     aria-labelledby="myModalLabel" aria-hidden="true">
							<form data-type="ajax" data-url="<?php echo base_url('estimates/ajax_save_service_status'); ?>" data-location="<?php echo current_url(); ?>" class="saveForm">
								<div class="modal-dialog">
									<div class="modal-content panel panel-default p-n">
										<header class="panel-heading">Edit Status <?php echo $status['services_status_name']; ?></header>
										<div class="modal-body">
											<div class="form-horizontal">
												<div class="control-group">
													<label class="control-label">Name</label>

													<div class="controls">
														<input type="hidden" value="<?php echo $status['services_status_id']; ?>" name="status_id">
														<input name="status_name" class="status_name form-control" type="text"
															   value="<?php echo $status['services_status_name']; ?>"
															   placeholder="Crew Name" style="background-color: #fff;">
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="submit" class="btn btn-success">Save</button>
											<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
										</div>
									</div>
								</div>
							</form>
						</div>

                        <?php if($status['services_status_id'] > 2) : ?>
                            <a class="btn btn-default btn-xs" href="#status-<?php echo $status['services_status_id']; ?>" role="button"
						        data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
						    <a class="btn btn-xs btn-danger deleteStatus" data-delete_id="<?php echo $status['services_status_id']; ?>"><i
								class="fa fa-trash-o"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</section>
</section>
<div id="status-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form data-type="ajax" data-url="<?php echo base_url('estimates/ajax_save_service_status'); ?>" data-location="<?php echo current_url(); ?>" class="saveForm">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Add Status</header>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="control-group">
							<label class="control-label">Name</label>

							<div class="controls">
								<input name="status_name" class="status_name form-control" type="text"
									   value=""
									   placeholder="Name Status" style="background-color: #fff;">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success">Save</button>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
	$(document).ready(function () {

		$('.deleteStatus').click(function () {
			var status_id = $(this).data('delete_id');
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'estimates/ajax_service_delete_status', {status_id: status_id}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
	});

</script>
<?php $this->load->view('includes/footer'); ?>
