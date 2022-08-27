<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Quality Assurance</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Quality Assurance
			<a href="#assurance-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>Assurance Name</th>
					<th>Type</th>
					<th width="60px">Rate</th>
					<th>Default Description</th>
					<th width="80px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php $qa_types = $this->config->item('qa_types');?>
				<?php foreach ($qa as $assurance) : ?>
					<tr<?php if (!$assurance->qa_status) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<td><?php echo $assurance->qa_name; ?></td>
						<td><?php echo (isset($qa_types) && isset($qa_types[$assurance->qa_type_int]))?ucfirst($qa_types[$assurance->qa_type_int]):'';  ?></td>
						<td><?php echo $assurance->qa_rate; ?></td>
						<td><?php echo $assurance->qa_description; ?></td>
						<td>
							<a class="btn btn-default btn-xs" href="#assurance-<?php echo $assurance->qa_id; ?>"
							   role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i
									class="fa fa-pencil"></i></a>
							<a class="btn btn-xs btn-info deleteAssurance"
							   data-delete_id="<?php echo $assurance->qa_id; ?>"><i
									class="fa <?php if ($assurance->qa_status) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i></a>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<?php foreach($qa as $assurance):  ?>
	<div id="assurance-<?php echo $assurance->qa_id; ?>" class="modal fade" tabindex="-1"
		 role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Edit
					Assurance <?php echo $assurance->qa_name; ?></header>
				<div class="modal-body">
					<div class="form-horizontal">
						<div class="control-group">
							<label class="control-label">Name</label>

							<div class="controls">
								<input class="qa_name form-control" type="text"
									   value="<?php echo $assurance->qa_name; ?>"
									   placeholder="Quality Assurance Name">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Type</label>

							<div class="controls">
								<select class="qa_type_int form-control">
									<?php foreach($qa_types as $k=>$v) :  ?>
										<option
											value="<?php echo $k; ?>" <?php if(intval($assurance->qa_type_int) === $k) : ?> selected <?php endif; ?>>
											<?php echo ucfirst($v);  ?>
										</option>
									<?php endforeach; ?>
									
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Rate</label>

							<div class="controls">
								<input class="qa_rate form-control" type="text"
									   value="<?php echo $assurance->qa_rate; ?>"
									   placeholder="Quality Assurance Rate">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Default Description</label>

							<div class="controls">
								<textarea class="qa_description form-control"
										  placeholder="Quality Assurance Description"
										  rows="5"><?php echo $assurance->qa_description; ?></textarea>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success" data-save-qa="<?php echo $assurance->qa_id; ?>">
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
<div id="assurance-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Quality Assurance</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="qa_name form-control" type="text" value=""
							       placeholder="Quality Assurance Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Type</label>

						<div class="controls">
							<select class="qa_type_int form-control">
								<?php foreach($qa_types as $k=>$v) : ?>
									<option
										value="<?php echo $k; ?>" >
										<?php echo ucfirst($v);  ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Rate</label>

						<div class="controls">
							<input class="qa_rate form-control" type="text" value=""
							       placeholder="Quality Assurance Rate">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Default Description</label>

						<div class="controls">
							<textarea class="qa_description form-control" placeholder="Quality Assurance Description"
							          rows="5"></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-qa="">
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
		$('[data-save-qa]').click(function () {
			var qa_id = $(this).data('save-qa');
			$(this).attr('disabled', 'disabled');
			$('#assurance-' + qa_id + ' .modal-footer .btntext').hide();
			$('#assurance-' + qa_id + ' .modal-footer .preloader').show();
			$('#assurance-' + qa_id + ' .qa_name').parents('.control-group').removeClass('error');
			var qa_name = $('#assurance-' + qa_id).find('.qa_name').val();
			var qa_description = $('#assurance-' + qa_id).find('.qa_description').val();
			var qa_type_int = $('#assurance-' + qa_id).find('.qa_type_int').val();
			var qa_rate = $('#assurance-' + qa_id).find('.qa_rate').val();
			if (!qa_name) {
				$('#assurance-' + qa_id + ' .qa_name').parents('.control-group').addClass('error');
				$('#assurance-' + qa_id + ' .modal-footer .btntext').show();
				$('#assurance-' + qa_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'qa/ajax_save_qa', {qa_id: qa_id, qa_name: qa_name, qa_rate: qa_rate, qa_type_int: qa_type_int, qa_description: qa_description}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteAssurance').click(function () {
			var qa_id = $(this).data('delete_id');
			if (confirm('Are you sure?')) {
				if ($(this).children().is('.fa-eye'))
					status = 1;
				$.post(baseUrl + 'qa/ajax_delete_qa', {qa_id: qa_id, status: status}, function (resp) {
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
