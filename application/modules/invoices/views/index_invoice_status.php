<?php $this->load->view('includes/header'); ?>
<style>
    #sortable tr {
        cursor: move;
    }
</style>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Status</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Status
			<a href="#status-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
			   <div class="clear"></div>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>Status Name</th>
					<th>Default</th>
					<th>Paid</th>
					<th>Hold Back</th>
					<th>Sent</th>
					<th>Overdue</th>
					<th>Overpaid</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody id="sortable">
				<?php if(isset($statuses) && !empty($statuses)) : ?>
					<?php foreach ($statuses as $key=>$status) : ?>
						<tr data-status_id="<?php echo $status->invoice_status_id; ?>" <?php if (!$status->invoice_status_active) : ?> style="text-decoration: line-through;"<?php endif; ?> data-wo_status_id="<?php echo $status->invoice_status_id; ?>">
							<td><?php echo $status->invoice_status_name; ?></td>

                            <td><i class="fa fa-<?php if($status->default): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
                            <td><i class="fa fa-<?php if($status->completed): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
                            <td><i class="fa fa-<?php if($status->is_hold_backs): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
                            <td><i class="fa fa-<?php if($status->is_sent): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
                            <td><i class="fa fa-<?php if($status->is_overdue): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>
                            <td><i class="fa fa-<?php if($status->is_overpaid): ?>check text-success<?php else: ?>times text-danger <?php endif; ?>"></i></td>

							<td>
								<div id="status-<?php echo $status->invoice_status_id; ?>" class="modal fade" tabindex="-1" role="dialog"
									 aria-labelledby="myModalLabel" aria-hidden="true">
									<div class="modal-dialog">
										<div class="modal-content panel panel-default p-n">
											<header class="panel-heading">Edit Status <?php echo $status->invoice_status_name; ?></header>
											<div class="modal-body">
												<div class="form-horizontal">
													<div class="control-group">
														<label class="control-label">Name</label>

														<div class="controls">
															<input class="status_name form-control" type="text"
																   value="<?php echo $status->invoice_status_name; ?>"
																   style="background-color: #fff;">
														</div>
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button class="btn btn-success" data-save-status="<?php echo $status->invoice_status_id; ?>">
													<span class="btntext">Save</span>
													<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
														 style="display: none;width: 32px;" class="preloader">
												</button>
												<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
											</div>
										</div>
									</div>
								</div>

								<a class="btn btn-default btn-xs" href="#status-<?php echo $status->invoice_status_id; ?>" role="button"
								   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
								<?php if($status->invoice_status_id && !$status->protected) : ?>
								<a class="btn btn-xs btn-info deleteStatus" data-delete_id="<?php echo $status->invoice_status_id; ?>" data-active="<?php echo $status->invoice_status_active;?>"><i
										class="fa <?php if ($status->invoice_status_active) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<div id="status-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Status</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>
						<div class="controls">
							<input class="status_name form-control" type="text" value="" placeholder="Status Name">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-status="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	
	$(document).ready(function () {
		$('[data-save-status]').click(function () {
			var status_id = $(this).data('save-status');
			$(this).attr('disabled', 'disabled');
			$('#status-' + status_id + ' .modal-footer .btntext').hide();
			$('#status-' + status_id + ' .modal-footer .preloader').show();
			$('#status-' + status_id + ' .status_name').parents('.control-group').removeClass('incorrect');
			var status_name = $('#status-' + status_id).find('.status_name').val();
			
			if (!status_name) {
				$('#status-' + status_id + ' .status_name').addClass('incorrect');
				$('#status-' + status_id + ' .modal-footer .btntext').show();
				$('#status-' + status_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			
			$.post(baseUrl + 'invoices/ajax_save_invoice_status', {status_id : status_id, status_name : status_name}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteStatus').click(function () {
			var status_id = $(this).data('delete_id');
			var status = $(this).data('active');
			var active = 'inactive';
			if(status == 0)
			{
				active = 'active';
				status = 1;
			}
			else
				status = 0;
			if (confirm('Status will be ' + active + ', continue?')) {
				$.post(baseUrl + 'invoices/ajax_delete_invoice_status', {status_id: status_id, status: status}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});

        $('#sortable').sortable({
            update: function(e, ui) {
                let statuses_priority = [];
                $('#sortable tr').each(function () {
                    statuses_priority.push($(this).data('status_id'));
                });

                $.post(base_url + 'invoices/ajax_statuses_update', {statuses: statuses_priority});
            }
        }).disableSelection();
	});

</script>
<?php $this->load->view('includes/footer'); ?>
