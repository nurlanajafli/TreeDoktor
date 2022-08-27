<?php $this->load->view('includes/header'); ?>
<style type="text/css">
	.select2-container{display: block!important;height: auto!important;}
</style>
<script src="<?php echo base_url(); ?>assets/js/jquery.inputmask.bundle.js"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/select2/select2.min.js'); ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/vendors/notebook/js/select2/select2.css'); ?>">
<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/vendors/notebook/js/select2/theme.css'); ?>">
<section class="scrollable p-sides-15 ">

	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Users</li>
	</ul>
		<section class="col-md-6">
			<section class="panel panel-default">
				<header class="panel-heading">Numbers
					<a title="Number" href="#newNumber" role="button" class="btn btn-xs btn-success btn-mini pull-right" data-toggle="modal">
						<i class="fa fa-plus"></i><i class="fa fa-user"></i>
					</a>
				</header>
				<!-- Data display -->
				<div class="m-bottom-10 p-sides-10 table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th width="70">#</th>
								<th>Name</th>
								<th>Number</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody class="numbersList">
						<?php if(isset($numbers) && !empty($numbers)) : ?>
							<?php foreach ($numbers as $key=>$row) :  ?>
								<tr data-id="<?php echo $row->nb_id; ?>">
									<td>
										<?php echo $key + 1; ?>
									</td>
									<td><?php echo $row->nb_name; ?></td>
									<td>
										<?php if($this->session->userdata('twilio_worker_id')) : ?>
											<a href="#" class="<?php if(!$this->uri->foundDisabled) : ?>createCall disabled<?php else : ?>createIframeCall<?php endif; ?>" data-number="<?php echo $row->nb_number; ?>">
										<?php endif; ?>
											<?php echo numberTo($row->nb_number); ?>
										<?php if($this->session->userdata('twilio_worker_id')) : ?>
											</a>
										<?php endif; ?>
									</td>
									<td>
										<a href="#" class="deleteNumber btn btn-danger btn-xs print-btns" style="padding: 0 5px;margin-top: 5px;">
											<i class="fa fa-trash-o"></i>
										</a>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="5"><?php echo "No records found"; ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</section>
		<section class="col-md-6">
			<section class="panel panel-default">
				<header class="panel-heading">
					Users
                    <?php if(config_item('messenger')) : ?>
					<a href="#sendSMS" role="button" data-toggle="modal" class="btn btn-default btn-xs pull-right">Send SMS</a>
                    <?php endif; ?>
				</header>
				<div class="m-bottom-10 p-sides-10">
					<table class="table table-hover">
					<thead>
						<tr>
							<th width="70">#</th>
							<th>Name</th>
							<th>Number</th>
                            <?php if(config_item('messenger')) : ?>
							<th width="80" class="th-sortable text-left">All <input type="checkbox" class="selectAll"></th>
                            <?php endif; ?>
						</tr>
					</thead>
					<tbody>
					<?php if(isset($users) && $users) : ?>
						<?php foreach ($users->result() as $key=>$row) : ?>
							<tr>
								<td>
									<?php echo $key + 1; ?>
								</td>
								<td><?php echo $row->firstname . ' ' . $row->lastname; ?></td>
								<td>
									<?php if(isset($row->emp_phone) && $row->emp_phone != '' && $row->emp_phone != NULL) : ?>
										<?php if($this->session->userdata('twilio_worker_id')) : ?>
											<a href="#" class="<?php if(!$this->uri->foundDisabled) : ?>createCall disabled<?php else : ?>createIframeCall<?php endif; ?>" data-number="<?php echo $row->emp_phone; ?>">
										<?php endif; ?>
											<?php echo numberTo($row->emp_phone); ?>
										<?php if($this->session->userdata('twilio_worker_id')) : ?>
											</a>
										<?php endif; ?>
									<?php else : ?>
										-
									<?php endif; ?>
								
								</td>
                                <?php if (config_item('messenger')) : ?>
								<td class="text-center">
									<?php if(isset($row->emp_phone) && $row->emp_phone != '' && $row->emp_phone != NULL && $row->emp_phone != '0') : ?>
										<input type="checkbox" class="check-contact" value="<?php echo $row->id; ?>">
									<?php endif; ?>
								</td>
                                <?php endif; ?>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5"><?php echo "No records found"; ?></td>
						</tr>
					<?php endif; ?>
					</tbody>
					</table>
				</div>
			</section>
		</section>
		<!--/ Data Display-->
<?php if (config_item('messenger')) : ?>
<div id="sendSMS" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Send SMS</header>
			<form id="newNumberForm" data-type="ajax" data-url="<?php echo base_url('notebook/ajax_send_sms'); ?>" method="POST" class="p-10" action="" data-callback="sendSuccess">
				<div class="control-group m-b-xs">
					<label class="control-label">User List</label>
					<div class="controls">
						<select name="userlist[]" multiple="multiple" id="select2-users" style="width: 100%;">
							<?php foreach ($users->result() as $key=>$row) : ?>
								<option value="<?php echo $row->id; ?>"><?php echo $row->firstname . ' ' . $row->lastname; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="control-group m-b-xs">
					<label class="control-label">Message</label>
					<div class="controls">
						<textarea class="form-control" name="sms_body"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<div class="pull-right ">
						<button class="btn btn-success m-right-5" id="sendSMS">
						Send</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
<div id="newNumber" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add New Number</header>
			<form id="newNumberForm" data-type="ajax" data-url="<?php echo base_url('notebook/save_number'); ?>" method="POST" class="p-10" action="" data-callback="saveSuccess">
				<div class="control-group m-b-xs">
					<label class="control-label">Name</label>
					<div class="controls">
						<input class="form-control" type="text" name="name" title="Name" data-original-title="Name">
					</div>
				</div>
				<div class="control-group m-b-xs">
					<label class="control-label">Number (Format: <?php echo str_replace('9', 'X', config_item('phone_inputmask')); ?>)</label>
					<div class="controls">
						<input class="form-control phoneNumber" type="text" name="number" title="Number" data-original-title="Number">
					</div>
				</div>
				<div class="modal-footer">
					<div class="pull-right ">
						<button class="btn btn-success m-right-5" id="addNumber">
						Add</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	var iframe = <?php echo intval($this->uri->foundDisabled); ?>;
	function saveSuccess(resp)
	{
		var className = 'createCall';
		if(iframe)
			className = 'createIframeCall';
		$('#processing-modal').modal();
		if($('.numbersList tr td').length == 1)
			$('.numbersList tr').remove();
		var Link = '<tr data-id="' + resp.id + '"><td>' + ($('.numbersList tr').length + 1) + '</td><td>'+ resp.name +'</td><td><a href="#" class="'+ className +'" data-number="'+ resp.number +'">'+ resp.tel +'</a></td><td><a class="deleteNumber btn btn-danger btn-xs print-btns" style="padding: 0 5px;margin-top: 5px;"><i class="fa fa-trash-o"></i></a></td></tr>';
		$('.numbersList').append(Link);
		$('#newNumberForm')[0].reset();
		$('#newNumber').modal('hide');
		return false;
	}
	function sendSuccess(resp)
	{
		$('#sendSMS').modal('hide');
		successMessage(resp.msg);
		$('[name="sms_body"]').val('');
		$('.check-contact').prop('checked', false);
		return false;
	}
	$(document).ready(function () {
		$('.phoneNumber').inputmask(PHONE_NUMBER_MASK);
		$(document).on("click", '.deleteNumber', function () {
			var obj = $(this).parents('tr:first'); 
			var id = $(obj).data('id');
			if (confirm('Are you sure?')) {
				
				$.post(baseUrl + 'notebook/ajax_delete_number', {id : id}, function (resp) {
					if (resp.status == 'ok') {
						$(obj).remove();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
		$(document).on('click', '.selectAll', function(){
			if($('.check-contact').prop('checked'))
				$('.check-contact').prop('checked', false);
			else
				$('.check-contact').prop('checked', true);
		});
		$('#sendSMS').on('shown.bs.modal', function (e) {
			var selected = [];
			$.each($('.check-contact:checked'), function(key, val){
				selected.push($(val).val());
			});
			$("#select2-users").val(selected);
			$("#select2-users").select2();
		})
		$("#select2-users").select2();
	});
</script>
<?php $this->load->view('includes/footer'); ?>
