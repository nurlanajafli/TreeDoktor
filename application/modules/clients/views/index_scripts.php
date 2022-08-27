<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Scripts</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Scripts
			<a class="btn btn-success btn-xs pull-right" type="button" style="margin-top: -1px;"
			   href="#script-" role="button"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
				<i class="fa fa-plus"></i>
			</a>
		</header>
		
		<div id="script-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Script</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="script_name form-control" type="text" value="" placeholder="Script Name">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Text</label>

						<div class="controls">
							<textarea class="script_text form-control" type="text" value="" placeholder="Script Text"></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-script="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>

		<div class="m-bottom-10 p-sides-10">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Text</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($scripts) {
					foreach ($scripts as $key => $script):
						?>
						<tr>
							<td><?php echo $key+1; ?></td>
							<td><?php echo $script['script_name']; ?></td>
							<td><?php echo $script['script_text']; ?></td>
							<td>
								<div id="script-<?php echo $script['script_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
						     aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content panel panel-default p-n">
									<header class="panel-heading">Edit Script <?php echo $script['script_name']; ?></header>
									<div class="modal-body">
										<div class="form-horizontal">
											<div class="control-group">
												<label class="control-label">Name</label>

												<div class="controls">
													<input class="script_name form-control" type="text"
													       value="<?php echo $script['script_name']; ?>"
													       placeholder="Script Name" style="background-color: #fff;">
												</div>
											</div>
											<div class="control-group">
												<label class="control-label">Text</label>

												<div class="controls">
													<textarea class="script_text form-control" type="text"
													  placeholder="Script Text" style="background-color: #fff;"><?php echo $script['script_text']; ?></textarea>
												</div>
											</div>
										</div>
									</div>
									<div class="modal-footer">
										<button class="btn btn-success" data-save-script="<?php echo $script['script_id']; ?>">
											<span class="btntext">Save</span>
											<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
											     style="display: none;width: 32px;" class="preloader">
										</button>
										<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
									</div>
								</div>
							</div>
						</div>
								<a class="btn btn-xs btn-default" href="#script-<?php echo $script['script_id']; ?>" role="button" 
									data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
									&nbsp;
								<a class="btn btn-xs btn-danger deleteScript"
								   data-delete-id="<?php echo $script['script_id']; ?>">
									<i class="fa fa-trash-o"></i>
								</a>
							</td>
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

		</div>
		</div>
		<script type="text/javascript">
	
	$(document).ready(function(){
		
		$(document).on("click", '[data-save-script]', function(){
			
			var id = $(this).data('save-script');
			var name = $('#script-' + id + ' .script_name').val();
			var text = $('#script-' + id + ' .script_text').val();
			
			$(this).attr('disabled', 'disabled');
			$('#script-' + id + ' .modal-footer .btntext').hide();
			$('#script-' + id + ' .modal-footer .preloader').show();
			$('#script-' + id + ' .script_name').parents('.control-group').removeClass('error');
			
			if (!name) {
				$('#script-' + id + ' .script_name').parents('.control-group').addClass('error');
				$('#script-' + id + ' .modal-footer .btntext').show();
				$('#script-' + id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'clients/ajax_save_script', {id : id, name : name, text:text}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteScript').click(function () {
			var id = $(this).data('delete-id');
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'clients/ajax_delete_script', {id: id}, function (resp) {
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
