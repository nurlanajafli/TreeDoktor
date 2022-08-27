<?php $this->load->view('includes/header'); ?>
<link href="<?php echo base_url(); ?>/assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet">
<!-- Services -->

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('equipments'); ?>">Equipments</a></li>
		<li><a href="<?php echo base_url('equipments/details/' . $item[0]->group_id); ?>"><?php echo $item[0]->group_name; ?></a></li>
		<li class="active"><?php echo $item[0]->item_name; ?></li>
	</ul>
	
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">
			<div class="pull-left">
				<p>
				Item Name: "<?php echo $item[0]->item_name; ?>"<br>

				Item Serial: "<?php echo $item[0]->item_serial; ?>"<br>

				Item Description: "<?php if ($item[0]->item_description) :
					echo $item[0]->item_description;
				else :
					echo 'N/A';
				endif; ?>"
				<?php if(isset($kmrs) && $kmrs && $item[0]->item_gps_start_counter != NULL) : ?>
				<br>Item GPS Counter: <?php echo ($item[0]->item_gps_start_counter + $kmrs); ?> km
				<?php endif; ?>
				</p>
			</div>
			<a href="#addRepair" class="btn btn-success btn-xs pull-right" data-toggle="modal">Add</a>
			<select class="pull-right changeStatus form-control m-r-sm inline" style="width: 100px; margin-top: -6px;">
				<option value="all">All</option>
				<option value="repaired" <?php if(isset($status) && $status == 'repaired') : ?>selected="selected"<?php endif; ?>>Repaired</option>
				<option value="not_repaired" <?php if(isset($status) && $status == 'not_repaired') : ?>selected="selected"<?php endif; ?>>Not repaired</option>
				<option value="on_hold" <?php if(isset($status) && $status == 'on_hold') : ?>selected="selected"<?php endif; ?>>On Hold</option>
				<option value="deleted" <?php if(isset($status) && $status == 'deleted') : ?>selected="selected"<?php endif; ?>>Deleted</option>
			</select>
			<div class="clear"></div>
			<?php $this->load->view('equipments/repairs/add_repair_form'); ?>
		</header>
		<?php $href = base_url() .  'equipments/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/'; ?>
		<div class="tabbable p-10"> <!-- Only required for left/right tabs -->
			<ul class="nav nav-tabs" data-type="estimates" data-action="stop">
				<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab1">Services</a>
				</li>
				<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab2">Parts</a>
				</li>
				<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab3">Service Settings</a>
				</li>
				<li class="active">
					<!--<a href="#tab4" data-toggle="tab">Old Services</a>-->
					<a href="<?php echo base_url('equipments/item_repairs') . '/' . $item[0]->item_id ;?>" >Repairs</a>
				</li>
				<?php if(isset($item[0]->item_tracker_name) && $item[0]->item_tracker_name != '' && $item[0]->item_tracker_name != NULL) : ?>
					<li>
						<a href="<?php echo base_url('equipments/item_distance') . '/' . $item[0]->item_id ;?>" >Distance Report</a>
					</li>
				<?php endif; ?>
				<?php /*<li>
					<a href="<?php echo base_url('equipments/profile') . '/' . $item[0]->item_id; ?>#tab4">Files</a>
				</li>*/ ?>
			</ul>
		
			<div class="tab-content">
				<div class="table-responsive">
				<table class="table table-striped b-t b-light" id="tbl_search_result">
					<thead>
					<tr>
						<?php //<th width="200px">Item Name</th>?>
						<th width="20px">ID</th>
						<th>
							<a href="<?php echo $href . $status; ?>/1/date/<?php echo isset($field) && $field == 'date' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
								Date
							</a>
						</th>
						<th>
							<a href="<?php echo $href . $status; ?>/1/type/<?php echo isset($field) && $field == 'type' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
								Type
							</a>
						</th>
						<th>Status</th>
						<th>Pr</th>
						<th>Price</th>
						<th>Hours</th>
						<th>Counter</th>
						<th>
							<a href="<?php echo $href . $status; ?>/1/author/<?php echo isset($field) && $field == 'author' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
								Author
							</a>	
						</th>
						<th>First Comment</th>
						<th>Finished Comment</th>
						<th>Assigned to</th>
						<th width="130px">Action</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($repairs as $key=>$repair) : ?>
					<?php $row['repair'] = $repair; ?>
						<?php $this->load->view('equipments/repairs/repair_row', $row); ?>
						
							<div id="notes-<?php echo $repair->repair_id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<header class="panel-heading">
											<?php echo ucfirst($repair->repair_type); ?> Repair<br>
											
										</header>
										
										<!-- Client Files Header-->
										<div class="modal-body">
											<div class="control-group">
												
												<div class="control-group">
													<textarea data-item-id="<?php echo $repair->repair_item_id; ?>" data-id="<?php echo $repair->repair_id;?>" name="createRepairComment" class="form-control" placeholder="Comment"></textarea>
													
												</div>
												<a href="#" class="m-t-sm btn btn-success btn-mini pull-right createRepairComment">Send</a>
											</div>
											<div class="clear"></div>
											<div id="notes-list-<?php echo $repair->repair_id; ?>">
												<?php $this->load->view('equipments/repairs/item_repair_notes'); ?>
											</div>
										</div>
										<div class="modal-footer">
											<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
											
										</div>
									</div>
								</div>

							</div>
						
						
						<?php $this->load->view('equipments/repairs/edit_repair_modal'); ?>
						<!--Triggers -->
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php if($links) : ?>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-sm-5 text-right text-center-xs pull-right">
						<?php echo $links; ?>
					</div>
				</div>
			</footer>
			<?php endif; ?>
			</div>
		</div>
	</section>
	
	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">
			<div class="pull-left" style="width:100%">
				<p>
					Repair Comments
					
				</p>
			</div>
			<div class="control-group">
				
					<a href="#" class="m-t-sm btn btn-success btn-mini pull-right createRepairComment">Send</a>
				
				
					<textarea style="width:90%" data-item-id="<?php echo $item[0]->item_id; ?>" name="createRepairComment" class="form-control" placeholder="Comment"></textarea>
					
				
				
			</div>
			
		</header>
		<div class="tabbable p-10"> <!-- Only required for left/right tabs -->
			<div class="tab-content">
				<div class="table-responsive">
				
					
					<div id="main-notes-list">
						<?php if(isset($all_comments) && !empty($all_comments)) : ?>
							<?php $array['repair'] = new \stdClass();
							 $array['repair']->repair_notes = $all_comments; ?>
							<?php $this->load->view('equipments/repairs/item_repair_notes', $array); ?>
						<?php endif; ?>
					</div>
					
			</div>
			<?php if($links) : ?>
			<footer class="panel-footer">
				<div class="row">
					<div class="col-sm-5 text-right text-center-xs pull-right">
						<?php echo $links; ?>
					</div>
				</div>
			</footer>
			<?php endif; ?>
			</div>
		</div>
	</section>
	
	
</section>
<script>
	
	function initEditable(selector) {
		if(!selector)
			selector = '';
		$(selector + '.solder').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			params: function (params) {
				params.status = '<?php echo $status; ?>';
				params.user_id = '<?php echo $user_id; ?>';
				return params;
			}
		});
		$(selector + '.type').editable({
			success: function(response, newValue) {
				response = $.parseJSON(response);
				if(response.id) {
					$('tr[data-id="' + response.id + '"]').replaceWith(response.html);
					initEditable('tr[data-id="' + response.id + '"] ');
				}
			},
			
		});


	}
	
	
	var item_id = <?php echo $item[0]->item_id; ?>;
	
	$(document).ready(function () {
		initEditable();
	});
	
	$(document).on('change', '.changeStatus', function () {
		var status = $(this).val();
		if(status == '')
			status = 'all';
		location.href = baseUrl + 'equipments/item_repairs/' + item_id + '/' + status;
		return false;
	});
	$(document).on('click', '.createRepairComment', function () {
		var obj = $(this);
		var text = $(this).parent().find('[name="createRepairComment"]').val();
		var id = $(this).parent().find('[name="createRepairComment"]').attr('data-id');
		var item_id = $(this).parent().find('[name="createRepairComment"]').attr('data-item-id');
		
		if(id == undefined)
			var list = $('#main-notes-list');
		else
			var list = $('#notes-list-' + id);
			
		if(text != '')
		{
			$.post(baseUrl + 'equipments/add_repair_note/', {text:text, id:id, item_id:item_id}, function(resp){
				if(resp.status == 'ok') {
					$(obj).parent().find('[name="createRepairComment"]').val('');
					$(list).html(resp.html);
				}
				return false;
			}, 'json');
		}
		return false;
	});
	
	$(document).on('click', '.deleteNote', function () {
		var obj = $(this);
		var id = $(this).attr('data-id');
		if(confirm('Are you sure?'))
		{
			$.post(baseUrl + 'equipments/delete_repair_note/', {id:id}, function (resp) {
				if (resp.status == 'ok') {
					$(obj).parent().parent().remove();
				}
				else
					alert('Something wrong! Please, try again later');
			}, 'json');
			return false;
		}
		
		return false;
	});
	
	$(document).on('click', '.edit_status', function () {
		var obj = $(this).closest('.modal-content');
		var id = $(this).parent().find('.repair_id').val();
		var old = $(this).parent().find('.old').val();
		var item_id = $(this).parent().find('.item_id').val();
		var status = $(obj).find('.changeNewStatus').val();
		var priority = $(obj).find('[name="priority"]:checked').val();
		var type = $(obj).find('[name="type"]:checked').val();
		var price = $(obj).find('.price').val();
		var hours = $(obj).find('.hours').val();
		var counter = $(obj).find('.counter').val();
		var repaired_by = $(obj).find('.repaired_by').val();
		var comment = $(obj).find('.commentSold').val();
		var firstComment = $(obj).find('.editRepairComment').val();
		
		$(obj).find('.price').parent().removeClass('has-error');
		$(obj).find('.hours').parent().removeClass('has-error');
		
		if(status == 'repaired')
		{
			if(!price)
				$(obj).find('.price').parent().addClass('has-error');
			if(!hours)
				$(obj).find('.hours').parent().addClass('has-error');
			if(!hours || !price)
				return false;
		}
		$.post(baseUrl + 'equipments/repair_update/', {id:id, status:status, priority:priority, type:type, repaired_by:repaired_by, price:price, item_id:item_id, hours:hours, counter:counter, firstComment:firstComment, comment:comment, old:old}, function (resp) {
			if (resp.status == 'ok') {
				location.reload();
			}
			else
			{
				if(confirm('Error. Try again'))
					location.reload();
			}
		}, 'json');
		return false;
	});
	$(document).on('click', '.deleteRepair', function () {
		var obj = $(this);
		var id = $(this).attr('data-id');
		if(confirm('Are you sure?'))
		{
			$.post(baseUrl + 'equipments/ajax_delete_repair', {id:id}, function (resp) {
				if (resp.status == 'ok') {
					$(obj).parent().parent().remove();
				}
				else
					alert('Something wrong! Please, try again later');
			}, 'json');
			return false;
		}
		
		return false;
	});
</script>
<?php $this->load->view('includes/footer'); ?> 
