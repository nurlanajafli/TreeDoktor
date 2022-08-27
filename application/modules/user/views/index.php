
<?php if(isset($pdf) && $pdf) :?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/payroll_pdf.css'); ?>">
</head>
<body style="border: 0!important;">

<div style="margin: 10px 20px 20px 30px">
	<div class="">
<?php else : ?>

<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15 ">
<?php endif; ?>


	<?php if(!isset($pdf)) : ?>
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Users</li>
	</ul>
	<section class="panel panel-default">
	<?php endif; ?>



		<header class="panel-heading">Users
			<?php if(!isset($pdf)) : ?>
				<select class="form-control m-l-md" style="width:200px; display: inline-block;" name="user"
						onchange="location.href = baseUrl + 'user/' + $(this).val()">
					<option value="active"<?php if ($status == 'active') : ?> selected<?php endif; ?>>Active</option>
					<option value="inactive"<?php if ($status == 'inactive') : ?> selected<?php endif; ?>>Inactive</option>
					<option value="dismissed"<?php if ($status == 'dismissed') : ?> selected<?php endif; ?>>Dismissed</option>
				</select>
			<?php endif; ?>
			
			<?php if (isAdmin() || $this->session->userdata('UM') == 1) { ?>
				<a data-toggle="modal" <?php /*href="user/user_add"*/?> href="#select-user-type" role="button" class="btn btn-dark pull-right"><i class="fa fa-plus"></i><i
						class="fa fa-user"></i></a>
				<?php $this->load->view('partials/select_user_type_modal'); ?>

				
			<?php } ?>
			<a class="btn btn-info btn-mini pull-right" type="button" style="margin-right: 6px;"
			   href="<?php echo base_url(); ?>user/user_pdf/<?php echo isset($status) ? $status : ''; ?>">
				<i class="fa fa-print"></i>
			</a>
			<?php if(config_item('phone') && isSystemUser() && isAdmin()): ?>
			<a href="<?php echo base_url(); ?>user/agents" role="button" class="btn btn-info pull-right" style="margin-right: 6px;">
				<i class="fa fa-user"></i><i class="fa fa-phone"></i>
			</a>
			<?php endif; ?>
			<?php /*
			<div class="control-group pull-right" style="display: inline-block; padding-right: 9px;">
				<a href="#" class="system">
					<label class="control-label">
						<input type="checkbox" name="system" <?php if($system) : ?>checked="checked"<?php endif; ?>>
						System 
					</label>
				</a>
			</div>
			*/ ?>
			 <div class="clear"></div>
		</header>

		<!-- Data display -->
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th width="10px">#</th>
					<th></th>
					<?php if(!isset($pdf)) :?>
						<th>Login</th> 
					<?php endif; ?>
					<th>Name</th>
					<?php if(!isset($pdf)) :?>
						<th>Position</th>
						<th style="text-align:center">Phone</th>
						<?php if(config_item('enable_twilio') && isAdmin() && isSystemUser()): ?>
						<th style="text-align:center">Ext.</th>
						<th>Agent</th>
						<?php endif; ?>
						<th>Contact List</th>
						<th>Worker Type</th>
						
						<th style="min-width:100px;text-align: center;">Action</th>
					<?php else: ?>
						<th style="text-align:center">Phone</th>
						<?php if(config_item('enable_twilio')): ?>
						<th style="text-align:center">Ext.</th>
						<?php endif; ?>
						<th>Position</th>
					<?php endif;?>
				</tr>
				</thead>
				<tbody>
				<?php
                $users = $user_row->result();
				if ($users) {
					foreach ($users as $key=>$row):
						?>
						<tr>
							<td style="vertical-align: middle;"><?php echo $key + 1; ?></td>
							<td style="vertical-align: middle;">
								<?php if(!isset($pdf)) :?>
									<?php /* <img class="pull-right" height="20"
										 src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|<?php echo str_replace('#', '', $row->color); ?>">
										 */ ?>
									<img class="pull-right"  
										 src="<?php echo mappin_svg($row->color, '&#9899;', FALSE, '#000');?>">
								<?php endif; ?>
							</td>
							<?php if(!isset($pdf)) :?>
								<td style="vertical-align: middle;">
									<?php if ($this->session->userdata('user_type') == "admin") : ?>
										<a href="<?php echo base_url('/user/loginAs/' . $row->id); ?>" title="Login As <?php echo $row->firstname . ' ' . $row->lastname; ?>" onclick="if(confirm('Are you sure you want to login as that person?')) return true; else return false;">
									<?php endif; ?>

									<?php echo $row->emailid; ?>

									<?php if ($this->session->userdata('user_type') == "admin") : ?>
										</a>
									<?php endif; ?>
								</td> 
							<?php endif; ?>
							<td style="vertical-align: middle;"><?php echo $row->firstname . ' ' . $row->lastname; ?></td>
							
							<?php if(!isset($pdf)) :?>
								<td style="vertical-align: middle;"><?php echo isset($row->emp_position) ? $row->emp_position : '-'; ?></td>
								<td style="text-align:center; vertical-align: middle;">
									<?php if(isset($row->emp_phone) && $row->emp_phone != '' && $row->emp_phone != NULL && $row->emp_phone != 0) : ?>
										<?php if($this->session->userdata('twilio_worker_id')) : ?>
											<a href="#" class="<?php if(!$this->uri->foundDisabled) : ?>createCall disabled<?php else : ?>createIframeCall<?php endif; ?>" data-number="<?php echo $row->emp_phone; ?>">
										<?php endif; ?>
											<?php echo numberTo($row->emp_phone); ?>
										<?php if($this->session->userdata('twilio_worker_id')) : ?>
											</a>
										<?php endif; ?>
										<?php /*
										<div class="checkbox pull-right" style="padding-left:0; margin-top:0;">
											<input class="duty" type="radio" title="Duty" name="duty" data-id="<?php echo $row->id; ?>" <?php if($row->duty) : ?>checked="checked"<?php endif; ?>>
										</div>
										*/ ?>
									<?php else : ?>
										-
									<?php endif; ?>
								
								</td>
								<?php if(config_item('enable_twilio') && is_admin() && isSystemUser()): ?>
									<td style="text-align: center; vertical-align: middle;">
										<?php echo $row->extention_key; ?>
									</td>
									
									<td style="vertical-align: middle;">
										<label class="switch-mini">
											<input data-id="<?php echo $row->id; ?>" name="agent" class="agent" type="checkbox" <?php if(isset($row->twilio_worker_id) && $row->twilio_worker_id) : ?>checked="checked"<?php endif; ?>>
											<span ></span>
										</label>
									</td>
								<?php endif; ?>

								<td style="  vertical-align: middle;">
									<label class="switch-mini">
										<input data-id="<?php echo $row->id; ?>" name="list" class="agent" type="checkbox" <?php if(isset($row->twilio_user_list) && $row->twilio_user_list) : ?>checked="checked"<?php endif; ?>>
										<span></span>
									</label>
								</td>
								<td style=" vertical-align: middle;">
									<div class="radio">
										<label class="radio-custom">
											<input class="wType" type="radio" title="Worker Type" name="wType<?php echo $row->id; ?>" value="1" data-id="<?php echo $row->id; ?>" <?php if($row->worker_type && $row->worker_type == 1) : ?>checked="checked"<?php endif; ?>>
											<i class="fa fa-circle-o"></i>Field
										</label>
									</div>
									<div class="radio">
										<label class="radio-custom"> 
											<input class="wType" type="radio" title="Worker Type" name="wType<?php echo $row->id; ?>" value="2" data-id="<?php echo $row->id; ?>" <?php if($row->worker_type && $row->worker_type == 2) : ?>checked="checked"<?php endif; ?>>
											<i class="fa fa-circle-o"></i>Support
										</label>
									</div>
									<?php /*
									<label class="checkbox" style="padding-left:0; margin-top:0;">
										<input class="wType" type="radio" title="Worker Type" name="wType<?php echo $row->id; ?>" value="1" data-id="<?php echo $row->id; ?>" <?php if($row->worker_type && $row->worker_type == 1) : ?>checked="checked"<?php endif; ?>>Field Worker
									</label>
									<label class="checkbox" style="padding-left:0; margin-top:0;">
										<input class="wType" type="radio" title="Worker Type" name="wType<?php echo $row->id; ?>" value="2" data-id="<?php echo $row->id; ?>" <?php if($row->worker_type && $row->worker_type == 2) : ?>checked="checked"<?php endif; ?>>Office Support
									</label>
									*/ ?>
								</td>
								
								<td style="vertical-align: middle; text-align: center;">
									<?php if($row->user_type == 'user') : ?>
										<?php echo anchor('user/user_update/' . $row->id, 'Edit', "class='btn btn-xs btn-success'"); ?>
										<?php //echo anchor('user/user_delete/' . $row->id, 'Delete', "class='btn btn-xs btn-danger'"); ?>
									<?php elseif($this->session->userdata('user_type') == "admin") : ?>
										<?php echo anchor('user/user_update/' . $row->id, 'Edit', "class='btn btn-xs btn-success'"); ?>
										<?php //echo anchor('user/user_delete/' . $row->id, 'Delete', "class='btn btn-xs btn-danger'"); ?>
									<?php endif; ?>
									
								</td>
							<?php else: ?>
								<td style="text-align:center; vertical-align: middle;">
									<?php if(isset($row->emp_phone) && $row->emp_phone != '' && $row->emp_phone != NULL) : ?>
										<?php echo numberTo($row->emp_phone); ?>
									<?php else : ?>
										-
									<?php endif; ?>
								
								</td>
								<?php if(config_item('enable_twilio')): ?>
									<td style="text-align: center; vertical-align: middle; ">
										<?php echo $row->extention_key; ?>
									</td>
								<?php endif; ?>

								<td style="vertical-align: middle;"><?php echo isset($row->emp_position) ? $row->emp_position : '-'; ?></td>
							<?php endif; ?>
						</tr>
					<?php
					endforeach;
				} else {
					?>
					<tr>
						<td colspan="10" class="text-danger"><?php echo "No records found"; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
		<!--/ Data Display-->
<?php if(!isset($pdf)) : ?>
	</section>
</section>
<?php endif; ?>		
		
<?php if(isset($pdf) && $pdf) : ?>
		<div class="address" style="margin-top: 60px;">
			<span class="green">ADDRESS:</span> <?php echo config_item('office_address'); ?>, <?php echo config_item('office_state'); ?> <?php echo config_item('office_zip'); ?>
			<span class="green">OFFICE: </span><?php echo config_item('office_phone_mask'); ?>
			<span class="green">WEB: </span><?php echo config_item('company_site_name_upper'); ?>
		</div>
	</div>
</div>
</body>
</html>
<?php else : ?>
	<script>
		var status = <?php echo json_encode($status); ?>;
		$(document).ready(function () {
			$('.agent').on('change', function(){
				var name = $(this).attr("name")
				var id = $(this).data('id');
				var prop = $(this).prop('checked');
				var obj = $(this);
				$.post(baseUrl + 'user/edit_agent', {prop : prop, id : id, name : name}, function () {
				}, 'json');
				return false;
			});
			$('.duty').on('change', function(){
				var id = $(this).data('id');
				
				$.post(baseUrl + 'user/chage_duty', {id : id}, function () {
				}, 'json');
				return false;
			});
			/*$('.wType').on('change', function(){
				var id = $(this).data('id');
				var val = $(this).val();
				
				$.post(baseUrl + 'user/chage_worker_type', {id : id, val:val}, function () {
				}, 'json');
				return false;
			});
			$('.system').click(function(){
				var val = $('.system').find('input:first').prop('checked');
				if(val)
					location.href = baseUrl + 'user/'+status+'/system';
				else
					location.href = baseUrl + 'user/'+status;
			});*/
		});
	</script>
	<?php $this->load->view('partials/change_worker_type_modal'); ?>
	<script src="<?php echo base_url(); ?>assets/js/modules/user/users.js"></script>
	<?php $this->load->view('includes/footer'); ?>
	
<?php endif; ?>
