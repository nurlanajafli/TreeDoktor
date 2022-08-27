<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Reasons Of Absence</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Reasons Of Absence
			<a href="#reason-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
			   <div class="clear"></div>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>Reason Name</th>
					<th width="120px">Reason Limit</th>
					<th width="80px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php if($reasons && !empty($reasons)) : ?>
					<?php foreach ($reasons as $key=>$reason) : ?>
						<tr<?php if (!$reason->reason_status) : ?> style="text-decoration: line-through;"<?php endif; ?>>
							<td><?php echo $reason->reason_name; ?></td>
							<td class="text-center"><?php echo $reason->reason_limit ? $reason->reason_limit : '—'; ?></td>
							<td>
								<a class="btn btn-default btn-xs" href="#reason-<?php echo $reason->reason_id; ?>" role="button"
								   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
								<?php if($reason->reason_id) : ?>
								<a class="btn btn-xs btn-info deleteReason" data-delete_id="<?php echo $reason->reason_id; ?>" data-status="<?php echo $reason->reason_status; ?>">
									<i class="fa <?php if ($reason->reason_status) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i>
								</a>
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
<?php if($reasons && !empty($reasons)) : ?>
	<?php foreach ($reasons as $key=>$reason) : ?>
	<div id="reason-<?php echo $reason->reason_id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Edit Reason <?php echo $reason->reason_name; ?></header>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="control-group">
							<label class="control-label">Name</label>

							<div class="controls">
								<input class="reason_name form-control" type="text"
									   value="<?php echo $reason->reason_name; ?>"
									   placeholder="Reason Name" style="background-color: #fff;">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Limit Per Year</label>
							<div class="controls">
								<input class="form-control reason_limit" type="text"
									   value="<?php echo $reason->reason_limit; ?>"
									   placeholder="Reason Limit">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Our Reason</label>
							<div class="controls">
								<input class="form-control reason_company" type="text"
									   value="<?php echo $reason->reason_company; ?>"
									   placeholder="Our Reason">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" data-save-reason="<?php echo $reason->reason_id; ?>">
						<span class="btntext">Save</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
							 style="display: none;width: 32px;" class="preloader">
					</button>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
<?php endif; ?>

<div id="reason-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Reason</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="reason_name form-control" type="text"
							       value=""
							       placeholder="Reason Name" style="background-color: #fff;">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Limit Per Year</label>
						<div class="controls">
							<input class="form-control reason_limit" type="text"
							       value=""
							       placeholder="Reason Limit">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Our Reason </label>
						<div class="controls">
							<input class="form-control reason_company" type="text"
							       value=""
							       placeholder="Our Reason">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-reason="">
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
		$('[data-save-reason]').click(function () {
			var reason_id = $(this).data('save-reason');
			$(this).attr('disabled', 'disabled');
			$('#reason-' + reason_id + ' .modal-footer .btntext').hide();
			$('#reason-' + reason_id + ' .modal-footer .preloader').show();
			$('#reason-' + reason_id + ' .reason_name').parents('.control-group').removeClass('error');
			var reason_name = $('#reason-' + reason_id).find('.reason_name').val();
			var reason_limit = $('#reason-' + reason_id).find('.reason_limit').val();
			var reason_company = $('#reason-' + reason_id).find('.reason_company').val();
			if (!reason_name) {
				$('#reason-' + reason_id + ' .reason_name').parents('.control-group').addClass('error');
				$('#reason-' + reason_id + ' .modal-footer .btntext').show();
				$('#reason-' + reason_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'employees/ajax_save_reason', {reason_id : reason_id, reason_limit : reason_limit, reason_name : reason_name, reason_company : reason_company}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteReason').click(function () {
			var reason_id = $(this).data('delete_id');
			var status = $(this).data('status');
			if (confirm('Are you sure, you want to delete the reason?')) {
				if(!status)
					status = 1;
				else
					status = 0;
				$.post(baseUrl + 'employees/ajax_delete_reason', {reason_id:reason_id,status:status}, function (resp) {
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
