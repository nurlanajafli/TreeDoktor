<?php $this->load->view('includes/header'); ?>
<?php if($support) : ?>
	<script src="<?php echo base_url(); ?>assets/vendors/notebook/js/sortable/jquery.sortable.js"></script>
<?php endif; ?>
<section class="scrollable p-sides-15 ">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Users</li>
	</ul>
	
	<section class="panel panel-default">
		<header class="panel-heading" style="height:50px">Users
			
			<a href="#" role="button" title="Save Level List" class="btn btn-success btn-sm saveLevel" <?php if(!$support) : ?>disabled="disabled"<?php endif; ?>><i class="fa fa-save"></i><i
						class="fa fa-user"></i></a>
			
			<?php if (isAdmin() || $this->session->userdata('UM') == 1) { ?>
				<a href="user/user_add" role="button" class="btn btn-dark btn-sm pull-right"><i class="fa fa-plus"></i><i
						class="fa fa-user"></i></a>
			<?php } ?>
			
			<a class="btn btn-info btn-sm pull-right" type="button" style="margin-right: 6px;"
			   href="<?php echo base_url(); ?>user/user_pdf/<?php echo isset($status) ? $status : ''; ?>">
				<i class="fa fa-print"></i>
			</a>
			<div class="control-group pull-right" style="display: inline-block; padding-right: 9px;">
				<a href="#" class="support">
					<label class="control-label">
						<input type="checkbox" name="support" <?php if($support) : ?>checked="checked"<?php endif; ?>>
						Support 
					</label>
				</a>
			</div>
		</header>
		<!-- Data display -->
		<div class="m-bottom-10 p-sides-10">
			
				<div class="">
					<div class="text-center col-md-1">
						<strong>#</strong>
					</div>
					<div class="text-center col-md-1">
						<strong>Email</strong>
					</div>
					<div class="text-center col-md-3">
						<strong>Name</strong>
					</div>
					
					<div class="text-center col-md-1">
						<strong>Is Agent</strong>
					</div>
					<div class="text-center col-md-1">
						<strong>Is Support</strong>
					</div>
					<div class="text-center col-md-1">
						<strong>In Contact List</strong>
					</div>
					<div class="text-center col-md-3">
						<strong>Phone</strong>
					</div>
					<div class="text-center col-md-1">
						<strong>Ext.</strong>
					</div>
					
					
					<div class="clear"></div>
				</div>
				
				<?php
				if ($user_row) { ?>
					<ul class="list-group gutter list-group-lg list-group-sp <?php if($support) : ?>sortable<?php endif; ?>">
					<div style="height:20px;"></div>
					<?php foreach ($user_row as $key=>$row): 
						?>
						 
						<li class="list-group-item" data-id="<?php echo $row['id'];?>" style=" height: 40px;">
							
							<span class="pull-left col-md-1 text-center">
								<?php echo $key + 1; ?>
							</span>

							<div class="col-md-1 text-center">
								<?php echo $row['emailid']; ?>
							</div>

							<div class="col-md-3 text-center">
								<?php echo $row['firstname'] . ' ' . $row['lastname']; ?>
							</div>

							<div class="col-md-1 ">
								<label class="switch-mini">
									<input data-id="<?php echo $row['id']; ?>" name="agent" class="agent" type="checkbox" <?php if(isset($row['twilio_worker_id']) && $row['twilio_worker_id']) : ?>checked="checked"<?php endif; ?>>
									<span <?php if(isset($row['twilio_worker_agent']) && $row['twilio_worker_agent']) : ?>class="bg-warning"<?php endif; ?>></span>
								</label>
							</div>
							
							<div class="col-md-1 ">
								<label class="switch-mini">
									<input data-id="<?php echo $row['id']; ?>" type="checkbox" class="agent" name="support" <?php if($row['twilio_support']) : ?>checked="checked"<?php endif; ?>>
									<span></span>
								</label>
							</div>

							<div class="col-md-1 ">
								<label class="switch-mini">
									<input data-id="<?php echo $row['id']; ?>" name="list" class="agent" type="checkbox" <?php if(isset($row['twilio_user_list']) && $row['twilio_user_list']) : ?>checked="checked"<?php endif; ?>>
									<span></span>
								</label>
							</div>

							<div class="col-md-3 text-center">
								<?php if(isset($row['emp_phone']) && $row['emp_phone'] != '' && $row['emp_phone'] != NULL) : ?>
									<?php if($this->session->userdata('twilio_worker_id')) : ?>
										<a href="#" class="<?php if(!$this->uri->foundDisabled) : ?>createCall disabled<?php else : ?>createIframeCall<?php endif; ?>" data-number="<?php echo $row['emp_phone']; ?>">
									<?php endif; ?>
										<?php echo numberTo($row['emp_phone']); ?>
									<?php if($this->session->userdata('twilio_worker_id')) : ?>
										</a>
									<?php endif; ?>
								<?php else : ?>
									-
								<?php endif; ?>
							</div>

							<div class="col-md-1 text-center">
								<?php echo $row['extention_key']; ?>
							</div>

							

							<div class="clear"></div>

						  </li>
						
					
					<?php
					endforeach; ?>
				</ul>
				<?php } else {
					?>
					<div>
						<div class="col-md-12 text-center"><?php echo "No records found"; ?></div>
					</div>
					
				<?php } ?>
				
		</div>
		<!--/ Data Display-->

	</section>
</section>
		

	</section>
</section>
		
	
			<script>

				
				var support = '<?php echo json_encode($support); ?>';
				
				$('.sortable').sortable().bind('sortupdate', function() {
					$('.saveLevel').removeAttr('disabled');
					
					return false;
				});
				$(document).ready(function () {
					$('.saveLevel').click(function(){
						var obj = $(this);
						$(obj).attr('disabled', 'disabled');
						var list = $('.sortable li');
						var data = [];				
						$.each(list, function(key, val){
							data.push({'id' : $(val).attr('data-id')});
						});
						
						$.post(baseUrl + 'user/change_level', {data:data}, function (resp) {
							if(resp.status == 'ok')
							{
								$(obj).removeAttr('disabled');
							}
						}, 'json');
						return false;
					});
					
					$('.agent').on('change', function(){
						var name = $(this).attr("name")
						var id = $(this).data('id');
						var prop = $(this).prop('checked');
						var obj = $(this);
						$.post(baseUrl + 'user/edit_agent', {prop : prop, id : id, name : name}, function () {
						}, 'json');
						return false;
					});
					$('.support').click(function(){
						var val = $('.support').find('input:first').prop('checked');
						if(val)
							location.href = baseUrl + 'user/agents/support';
						else
							location.href = baseUrl + 'user/agents';
						
					});
				});
			</script>
			<?php $this->load->view('includes/footer'); ?>
