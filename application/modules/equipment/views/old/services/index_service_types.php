<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Service Types</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Service Types
			<a class="btn btn-success btn-xs pull-right" type="button" style="margin-top: -1px;"
			   href="#type-" role="button"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
				<i class="fa fa-plus"></i>
			</a>
			<div class="clear"></div>
		</header>
		
		<div id="type-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content panel panel-default p-n">
					<header class="panel-heading">Create Service Type</header>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="control-group">
								<label class="control-label">Type Name</label>

								<div class="controls">
									<input class="type_name form-control" type="text" value="" placeholder="Type Name">
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Description</label>

								<div class="controls">
									<textarea class="type_desc form-control" type="text" value="" placeholder="Type Description"></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" data-save-type="">
							<span class="btntext">Save</span>
							<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
								 class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</div>
		</div>

		<div class="m-bottom-10 p-sides-10 table-responsive">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>#</th>
					<th>Type Name</th>
					<th>Description</th>
					<th width="100px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($types) {
					foreach ($types as $key => $type):
						?>
						<tr>
							<td><?php echo $key+1; ?></td>
							<td><?php echo $type['equipment_service_type']; ?></td>
							<td><?php echo $type['equipment_service_desc']; ?></td>
							<td>
								<a class="btn btn-xs btn-default" href="#type-<?php echo $type['equipment_service_id']; ?>" role="button" 
									data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
									&nbsp;
								<a class="btn btn-xs btn-danger deleteType"
								   data-delete-id="<?php echo $type['equipment_service_id']; ?>">
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

	</section>
</section>
<?php if ($types) : ?>
	<?php foreach ($types as $key => $type): ?>
	<div id="type-<?php echo $type['equipment_service_id']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content panel panel-default p-n">
					<header class="panel-heading">Edit Service Type <?php echo $type['equipment_service_type']; ?></header>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="control-group">
								<label class="control-label">Type Name</label>

								<div class="controls">
									<input class="type_name form-control" type="text"
										   value="<?php echo $type['equipment_service_type']; ?>"
										   placeholder="Type Name" style="background-color: #fff;">
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Description</label>

								<div class="controls">
									<textarea class="type_desc form-control" type="text"
									  placeholder="Type Desc" style="background-color: #fff;"><?php echo $type['equipment_service_desc']; ?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" data-save-type="<?php echo $type['equipment_service_id']; ?>">
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

		<script type="text/javascript">
	
	$(document).ready(function(){
		
		$(document).on("click", '[data-save-type]', function(){
			
			var id = $(this).data('save-type');
			var name = $('#type-' + id + ' .type_name').val();
			var text = $('#type-' + id + ' .type_desc').val();
			
			$(this).attr('disabled', 'disabled');
			$('#type-' + id + ' .modal-footer .btntext').hide();
			$('#type-' + id + ' .modal-footer .preloader').show();
			$('#type-' + id + ' .type_name').parents('.control-group').removeClass('error');
			
			if (!name) {
				$('#type-' + id + ' .type_name').parents('.control-group').addClass('error');
				$('#type-' + id + ' .modal-footer .btntext').show();
				$('#type-' + id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'equipments/ajax_save_service_type', {id : id, name : name, text:text}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteType').click(function () {
			var id = $(this).data('delete-id');
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'equipments/ajax_delete_service_type', {id: id}, function (resp) {
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
