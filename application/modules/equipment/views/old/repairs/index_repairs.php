<?php $this->load->view('includes/header'); ?>
<link href="<?php echo base_url(); ?>/assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css" rel="stylesheet">
<!-- All clients display -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Repair Requests</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Repair Requests
			<form method="POST" class="pull-right">
				<button type="submit" name="pdf" value="1" class="btn btn-xs">PDF</button>
			</form>
			<a href="#addRepair" class="btn btn-success btn-xs pull-right m-r-sm" data-toggle="modal">Add</a>
			<select class="pull-right changeStatus form-control m-r-sm inline" style="width: 200px; height: 33px;">
				<option value="all">All</option>
				<option value="repaired" <?php if(isset($status) && $status == 'repaired') : ?>selected="selected"<?php endif; ?>>Repaired</option>
				<option value="not_repaired" <?php if(isset($status) && $status == 'not_repaired') : ?>selected="selected"<?php endif; ?>>Not repaired</option>
				<option value="on_hold" <?php if(isset($status) && $status == 'on_hold') : ?>selected="selected"<?php endif; ?>>On Hold</option>
				<option value="deleted" <?php if(isset($status) && $status == 'deleted') : ?>selected="selected"<?php endif; ?>>Deleted</option>
				<option value="not_repaired_on_hold" <?php if(isset($status) && $status == 'not_repaired_on_hold') : ?>selected="selected"<?php endif; ?>>Not Repaired/On Hold</option>
			</select>
			<div class="clear"></div>
			<?php $this->load->view('equipments/repairs/add_repair_form'); ?>
		</header>
		<?php $href = base_url() .  'equipments/' . $this->uri->segment(2) . '/'; ?>
		

		<div class="table-responsive">
			<table class="table table-striped b-t b-light" id="tbl_search_result">
				<thead>
				<tr>
					<th width="20px">ID</th>
					<th width="200px">
						<a href="<?php echo $href . $status; ?>/1/item_name/<?php echo isset($field) && $field == 'item_name' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
							Item Name
						</a>
					</th>
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
					<th>
						Status
					</th>
					<th>Pr</th>
					<th>Price</th>
					<th>Hours</th>
					<th>Counter</th>
					<th>
						<a href="<?php echo $href . $status; ?>/1/author/<?php echo isset($field) && $field == 'author' && $type == 'asc' ? 'desc' : 'asc' ?><?php echo $this->input->get('q') ? '?q=' . $this->input->get('q') : ''; ?>">
							Author
						</a>
					</th>
					<th>Repair Request</th>
					<th>Finished Comment</th>
					<th>Assigned to</th>
					<th width="130px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($repairs as $key=>$repair) : ?>
					<tr>
						<td>
							<?php echo $repair->repair_id; ?>
						</td>
						<td >
							<a href="<?php echo base_url('equipments/item_repairs') . '/' . $repair->item_id; ?>"><?php echo $repair->item_name; ?></a>
						</td>
<!--						<td>--><?php //echo date('Y-m-d', strtotime($repair->repair_date)); ?><!--</td>-->
						<td><?php echo getDateTimeWithDate($repair->repair_date, 'Y-m-d H:i:s'); ?></td>
						<td>
							<a href="#" data-name="type" data-value="<?php echo $repair->repair_type; ?>" data-placement="right" data-type="select" data-source="[{value:'damage',text:'Damage'},{value:'repair',text:'Repair'},{value:'maintenance',text:'Maintenance'}]" data-pk="<?php echo $repair->repair_id; ?>" class="type" title="Type" data-url="<?php echo base_url('equipments/ajax_update_repair'); ?>"><?php echo ucfirst($repair->repair_type); ?></a>
						</td>
						<td><?php echo mb_convert_case(str_replace('_', ' ', $repair->repair_status), MB_CASE_TITLE, "UTF-8"); ?></td>
						<td><?php echo $repair->repair_priority; ?></td>
						<td><?php echo money($repair->repair_price); ?></td>
						<td><?php echo $repair->repair_hours; ?></td>
						<td><?php echo $repair->repair_counter; ?></td>
						<td><?php echo $repair->author_name; ?></td>
						<td>
							<?php if(isset($repair->repair_first_comment) && $repair->repair_first_comment) : ?>
								<?php echo $repair->repair_first_comment;?>
							<?php else : ?>
								-
							<?php endif; ?>
						</td>
						<td>
							<?php if(isset($repair->repair_finish_comment) && $repair->repair_finish_comment) : ?>
								<?php echo $repair->repair_finish_comment;?>
							<?php else : ?>
								-
							<?php endif; ?>
						</td>
						<td>
							<a href="#" data-name="solder_id" data-placement="left" data-type="select" data-source="<?php echo htmlspecialchars(json_encode($solders)); ?>" data-value="<?php echo $repair->repair_solder_id; ?>" class="solder" title="Assigned to" data-pk="<?php echo $repair->repair_id; ?>" data-url="<?php echo base_url('equipments/ajax_update_repair'); ?>"></a>
						</td>
						<td style="width: 135px;">
							<?php if(isset($status) && $status != 'deleted') : ?>
								<a href="#notes-<?php echo $repair->repair_id; ?>" class="btn btn-default btn-xs" data-toggle="modal"><i class="fa fa-list-ul"></i></a>
								<a href="<?php echo base_url('equipments/repair_pdf') . '/' . $repair->repair_id; ?>" class="btn btn-default btn-xs "><i class="fa fa-file"></i></a>
								<a href="#edit-<?php echo $repair->repair_id; ?>" role="button" class="btn btn-default btn-xs " data-toggle="modal"><i class="fa fa-pencil"></i></a>
							<?php endif;?>
								<a href="#" role="button" class="btn btn-default btn-xs deleteRepair btn-danger" data-id="<?php echo $repair->repair_id; ?>">
									<?php if(isset($status) && $status == 'deleted') : ?>
										<i class="fa fa-undo"></i>
									<?php else : ?>
										<i class="fa fa-trash-o"></i>
									<?php endif; ?>
								</a>
						</td>
						
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-sm-5 text-right text-center-xs pull-right">
					<?php echo $links; ?>
				</div>
			</div>
		</footer>
	</section>
</section>
<?php foreach ($repairs as $key=>$repair) : ?>
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
							<textarea data-id="<?php echo $repair->repair_id;?>" name="createRepairComment" class="form-control" placeholder="Comment"></textarea>
							
						</div>
						<a href="#" class="m-t-sm btn btn-success btn-mini pull-right createRepairComment">Send</a>
					</div>
					<div class="clear"></div>
					<div id="notes-list-<?php echo $repair->repair_id; ?>">
						<?php $row['repair'] = $repair; ?>
						<?php $this->load->view('equipments/repairs/item_repair_notes', $row); ?>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
					
				</div>
			</div>
		</div>

	</div>
	<?php $this->load->view('equipments/repairs/edit_repair_modal'); ?>
<?php endforeach; ?>
<script type="text/javascript">
	var uriSegment = '<?php echo $this->uri->segment(2);?>';
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
				//params.status = '<?php //echo $status; ?>';
				//params.user_id = '<?php //echo $user_id; ?>';
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

	$(document).on('change', '.changeStatus', function () {
		var status = $(this).val();
		if(status == '')
			status = 'all';
		location.href = baseUrl + 'equipments/' + uriSegment + '/' + status;
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
		var firstComment = $(obj).find('.editRepairComment').val();
		var comment = $(obj).find('.commentSold').val();
		
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
		$.post(baseUrl + 'equipments/repair_update/', {id:id, status:status, firstComment:firstComment, priority:priority, type:type, repaired_by:repaired_by, price:price, item_id:item_id, hours:hours, counter:counter, comment:comment, old:old}, function (resp) {
			
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
	$(document).ready(function () {
		initEditable();
	});
</script>
<?php $this->load->view('includes/footer'); ?>
