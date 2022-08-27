<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Expense Types</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Expense Types
			<a href="#expense-" class="btn btn-xs btn-success pull-right" role="button" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
			<div class="clear"></div>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th>Expense Name</th>
					<th>Items Groups</th>
					<th>Protected</th>
					<th width="80px">Action</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($expenses as $key=>$expense) : ?>
					<tr <?php if (!$expense->expense_status) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<td><?php echo $expense->expense_name; ?></td>
						<td>
							<?php $groups = NULL; ?>
							<?php if(isset($expenses_groups[$expense->expense_type_id])) : ?>
								<?php foreach($expenses_groups[$expense->expense_type_id] as $expGroup) : ?>
									<?php $groups .= $expGroup['group_name'] . ', '; ?>
								<?php endforeach; ?>
							<?php endif; ?>
							<?php echo $groups ? rtrim($groups, ', ') : '—'; ?>
						</td>
						<td>
							<?php if ($expense->protected) : ?>
								<i class="fa fa-check text-danger"></i>
							<?php endif; ?>
						</td>
						<td> 
							<a class="btn btn-default btn-xs" href="#expense-<?php echo $expense->expense_type_id; ?>" role="button"
							   data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
							<?php if($expense->expense_type_id && !$expense->protected) : ?>
							<a class="btn btn-xs btn-info deleteExpense" data-delete_id="<?php echo $expense->expense_type_id; ?>" data-status="<?php echo $expense->expense_status; ?>">
								<i class="fa <?php if ($expense->expense_status) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i>
							</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<?php foreach ($expenses as $key=>$expense) : ?>
<?php $groups = NULL; ?>
<?php if(isset($expenses_groups[$expense->expense_type_id])) : ?>
	<?php foreach($expenses_groups[$expense->expense_type_id] as $expGroup) : ?>
		<?php $groups .= $expGroup['group_name'] . ', '; ?>
	<?php endforeach; ?>
<?php endif; ?>
<div id="expense-<?php echo $expense->expense_type_id; ?>" class="modal fade" tabindex="-1" role="dialog"  aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Edit Expense <?php echo $expense->expense_name; ?></header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="expense_name form-control" type="text"
								   value="<?php echo $expense->expense_name; ?>"
								   placeholder="Expense Name" style="background-color: #fff;">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Items Groups</label>
						<div class="controls">
							<?php foreach($items_groups as $group) : ?>
								<?php $checked = ''; ?>
								<?php if(isset($expenses_groups[$expense->expense_type_id])) : ?>
									<?php foreach($expenses_groups[$expense->expense_type_id] as $expGroup) : ?>
										<?php if($group->group_id == $expGroup['group_id']) $checked = ' checked'; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							<label class="checkbox-inline m-r-sm m-l-none">
								<input <?php echo $checked; ?> type="checkbox" class="expense_group" value="<?php echo $group->group_id; ?>">
								<?php echo $group->group_name; ?>
							</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-expense="<?php echo $expense->expense_type_id; ?>">
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

<div id="expense-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Expense</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Name</label>

						<div class="controls">
							<input class="expense_name form-control" type="text"
							       value=""
							       placeholder="Expense Name" style="background-color: #fff;">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Items Groups</label>
						<div class="controls">
							<?php foreach($items_groups as $group) : ?>
								<label class="checkbox-inline m-r-sm m-l-none">
									<input type="checkbox" class="expense_group" value="<?php echo $group->group_id; ?>">
									<?php echo $group->group_name; ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-expense="">
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
		$('[data-save-expense]').click(function () {
			var expense_id = $(this).data('save-expense');
			$(this).attr('disabled', 'disabled');
			$('#expense-' + expense_id + ' .modal-footer .btntext').hide();
			$('#expense-' + expense_id + ' .modal-footer .preloader').show();
			$('#expense-' + expense_id + ' .expense_name').parents('.control-group').removeClass('error');
			var expense_name = $('#expense-' + expense_id).find('.expense_name').val();
			var expense_groups = [];
			$.each($('#expense-' + expense_id).find('.expense_group'), function(key, val){
				if($(val).is(':checked'))
					expense_groups.push($(val).val());
			});
			if (!expense_name) {
				$('#expense-' + expense_id + ' .expense_name').parents('.control-group').addClass('error');
				$('#expense-' + expense_id + ' .modal-footer .btntext').show();
				$('#expense-' + expense_id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'reports/ajax_save_expense', {expense_id : expense_id, expense_groups : expense_groups, expense_name : expense_name}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteExpense').click(function () {
			var expense_id = $(this).data('delete_id');
			var status = $(this).data('status');
			if (confirm('Are you sure?')) {
				if(!status)
					status = 1;
				else
					status = 0;
				$.post(baseUrl + 'reports/ajax_delete_expense', {expense_id:expense_id,status:status}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
	});
$('[type="radio"]').change(function(){
	if(!$(this).parents('tr:first').find('input[type="text"]').length)
		return false;
	var name = $(this).attr('name');
	$(this).parents('td:first').find('input[type="text"]').removeAttr('disabled');
	$(this).parents('tr:first').find('input[type="radio"][name="' + name + '"]').not(':checked').parents('td:first').find('input[type="text"]').attr('disabled', 'disabled');
});
</script>
<?php $this->load->view('includes/footer'); ?>
