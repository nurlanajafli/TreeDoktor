<?php $this->load->view('includes/header'); ?>

<!-- All clients display -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Clients</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Clients
			<?php /*
			<a title="Client" href="#addStumpClient" role="button" class="btn btn-xs btn-success btn-mini pull-right" data-toggle="modal">
				<i class="fa fa-plus"></i><i class="fa fa-user"></i>
			</a>
			*/?>
		</header>
		<div class="table-responsive">
			<table class="table table-striped b-t b-light" id="tbl_search_result">
				<thead>
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($clients as $key=>$val) : ?>
					<tr>
						<td>
							<?php echo $key+1;?>
						</td>
						<td>
							<?php echo $val['cl_name'] . ' ' . $val['cl_lastname']; ?>
						</td>
						<td>
							<div id="client-<?php echo $val['cl_id']; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content panel panel-default p-n">
										<header class="panel-heading">Edit Client</header>
										<div class="modal-body">
											<div class="form-horizontal">
												<div class="control-group">
													<label class="control-label">Name</label>
													<div class="controls">
														<input class="form-control" type="text" name="name" style="background-color: #fff;" value="<?php echo $val['cl_name']; ?>">
														
														
													</div>
												</div>
											</div>
											<div class="form-horizontal">
												<div class="control-group">
													<label class="control-label">Lastname</label>
													<div class="controls">
														<input class="form-control" type="text" name="lastname" style="background-color: #fff;" value="<?php echo $val['cl_lastname']; ?>">
														
														
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button class="btn btn-success" data-save-client="<?php echo $val['cl_id']; ?>">
												<span class="btntext">Save</span>
												<img src="<?php echo base_url('assets/img/ajax-loader.gif'); ?>" style="display: none;width: 32px;" class="preloader">
											</button>
											<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
										</div>
									</div>
								</div>
							</div>
							<a class="btn btn-default btn-xs" title="Client Stumps List" href="<?php echo base_url('/stumps/client_stumps/' . $val['cl_id']); ?>"><i class="fa fa-list"></i></a>&nbsp;
							<a class="btn btn-default btn-xs" title="Client Stumps Map" href="<?php echo base_url('/stumps/stumps_mapper/new/' . $val['cl_id']); ?>"><i class="fa fa-globe"></i></a>&nbsp;
							<a class="btn btn-default btn-xs" title="My Client Stumps List" href="<?php echo base_url('/stumps/my_client_stumps/' . $val['cl_id']); ?>"><i class="fa fa-list"></i> <i class="fa fa-user"></i></a>&nbsp;
							<a class="btn btn-default btn-xs" title="My Client Stumps Map" href="<?php echo base_url('/stumps/my_mapper/new/' . $val['cl_id']); ?>"><i class="fa fa-globe"></i> <i class="fa fa-user"></i></a>
							<?php if($this->session->userdata('user_type') == "admin") :  ?>
								&nbsp;&nbsp;&nbsp;
								<a class="btn btn-default btn-xs" href="#client-<?php echo $val['cl_id']; ?>" role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
								<a class="btn btn-xs btn-danger deleteClient" data-delete_id="<?php echo $val['cl_id']; ?>"><i class="fa fa-trash-o"></i></a>
							<?php endif; ?>
						</td>
					</tr>
				
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
	</section>
</section>
<div id="addStumpClient" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add New Client</header>
			<form id="new_client" method="POST">
			<div class="p-10">
				<div class="control-group">
					<label class="control-label">Name</label>
					<div class="controls">
						<input class="form-control" type="text" name="name" style="background-color: #fff;">
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">Lastname</label>
					<div class="controls">
						<input class="form-control" type="text" name="lastname" style="background-color: #fff;">
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<div class="pull-right ">
					<button class="btn btn-success m-right-5" id="addClient">
					Add</button>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>

<script>
	$(document).on('click', '#addClient', function () {
		if ($(this).attr('disabled') == 'disabled')
			return false;
		var button = $(this);
		$(this).attr('disabled', 'disabled');
		var inputs = $('#new_client input');
		
		$.each(inputs, function( key, val ) {
			
			$(val).parent().parent().removeClass('has-error');
		});
		$.post(baseUrl + 'stumps/save_stump_client', $('#new_client').serialize(), function (resp) {
			
			if(resp.type == 'success')
			{
				location.href = baseUrl + 'stumps/profile_client/' + resp.id;
			}
			else
			{
				$.each(resp.field, function( key, val ) {
					$('#new_client input[name="'+ val +'"]').parent().parent().addClass('has-error');
				});
				$(button).removeAttr('disabled');
			}
				
			return false;
		}, "json");
		return false;
	});
	
	$(document).ready(function () {
		$('[data-save-client]').click(function () {
			
			var client_id = $(this).data('save-client');
			var inputs = $('#client-' + client_id + ' input');
			var button = $(this);
			$.each(inputs, function( key, val ) {
				
				$(val).parent().parent().removeClass('has-error');
			});

			
			$(button).attr('disabled', 'disabled');
			$('#client-' + client_id + ' .modal-footer .btntext').hide();
			$('#client-' + client_id + ' .modal-footer .preloader').show();
			$('#client-' + client_id + ' .category_name').parents('.control-group').removeClass('error');
			
			var name = $('#client-' + client_id).find('input[name="name"]').val();
			var lastname = $('#client-' + client_id).find('input[name="lastname"]').val();
			
			$.post(baseUrl + 'stumps/update_stump_client', {client_id : client_id, name : name, lastname : lastname}, function (resp) {
				if (resp.type == 'success')
					location.reload();
				else
				{
					$.each(resp.field, function( key, val ) {
						$('#stump-' + stump_id +' input[name="'+ val +'"]').parent().parent().addClass('has-error');
					});
					$(button).removeAttr('disabled');
				}
				return false;
			}, 'json');
			return false;
		});
		$('.deleteClient').click(function () {
			var id = $(this).data('delete_id');
			
			if (confirm('Are you sure?')) {
				
				$.post(baseUrl + 'stumps/ajax_delete_client', {id : id}, function (resp) {
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
