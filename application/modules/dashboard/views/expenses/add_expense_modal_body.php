
<ul class="nav nav-tabs" style="position: absolute;top: -40px;right: 0;">
	<li><a href="#event-expenses-form" data-toggle="tab">Form</a></li>
	<li class="active"><a href="#event-expenses-list" data-toggle="tab">List</a></li>
</ul>

<div class="tab-content">
	<div class="tab-pane" id="event-expenses-form">
		<form id="addExpense" method="POST">
			<?php if(isset($event_id) && $event_id): ?>
			<input type="hidden" name="expense_event_id" value="<?php echo $event_id; ?>">
			<input type="hidden" name="callback" value="get_event_expense_modal"> 
			<?php endif; ?>
			<div class="form-group">
				<label class="col-sm-3 control-label">Date</label>
				<div class="col-sm-9">
					<input type="text" name="expense_date" class="form-control datepicker-input" readonly data-date-format="yyyy-mm-dd" id="expenseDate" value="<?php echo $event_date; ?>">
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Type</label>
				<div class="col-sm-9">
					<select id="expenseType" name="expense_type" class="form-control">
						<option value="">Select Expense Type</option>
						<?php if(isset($expense_types) && count($expense_types)) : ?>
							<?php foreach($expense_types as $expense) : ?>
								<option value="<?php echo $expense->expense_type_id; ?>"><?php echo $expense->expense_name; ?></option>
							<?php endforeach; ?>
						<?php endif; ?>
					</select>
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="form-group" id="expenseItem" style="display: none;">
				<label class="col-sm-3 control-label">Select Item</label>
				<div class="col-sm-9">
					<select id="itemId" name="expense_item" class="expense_item form-control">
						<option value="">Select Expense Item</option>
					</select>
					<span class="help-inline text-danger"></span>
				</div>
			</div>

			<?php if(isset($employees_data) && count($employees_data)) : ?>
			<div class="form-group">
				<label class="col-sm-3 control-label">Select Employee</label>
				<div class="col-sm-9">
					<select name="expense_employee" class="expense_employee form-control">
						<option value="">Select Employee</option>
							<?php foreach($employees_data as $employee) : ?>
								<option value="<?php echo $employee['user_id']; ?>" <?php if($employee['team_leader_user_id']==$employee['user_id']): ?>selected="selected"<?php endif; ?>><?php echo $employee['emp_name']; ?></option>
							<?php endforeach; ?>
					</select>
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<?php endif; ?>
			<div class="form-group" id="expensePayment">
				<label class="col-sm-3 control-label">Select Payment</label>
				<div class="col-sm-9">
					<select id="paymentName" name="expense_payment" class="form-control">
						<option value="">Select Payment Type</option>
						<option value="Cash">Cash</option>
						<option value="CC">CC</option>
						<option value="Bank">Bank(Check/Debit/IT)</option>
					</select>
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Amount</label>
				<div class="col-sm-9">
					<input type="text" id="expenseAmount" name="expense_amount" class="form-control" value="">
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">No <?php echo config_item('tax_name') ? config_item('tax_name') : 'TAX'; ?> Expense</label>
				<div class="col-sm-9">
					<input type="checkbox" id="expenseHstAmount" name="expense_hst_amount" style="margin-top:10px" value="1">
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Description</label>
				<div class="col-sm-9">
					<textarea id="expenseDescipiton" name="expense_description" class="form-control"></textarea>
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label">Expense File</label>
				<div class="col-sm-9">
					<span class="btn btn-primary btn-file" id="file">Choose File
						<input type="file" name="file" class="btn-upload" id="fileToUploadExpense" class="btn-upload">
					</span>
					<span class="help-inline text-danger"></span>
				</div>
			</div>
			<div class="modal-footer p-right-0">
				<button class="btn btn-info" type="submit">
					<span class="btntext">Add expense</span>
					<img src="<?php echo base_url('assets/img/ajax-loader.gif'); ?>" style="display: none;width: 84px;" class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</form>
	</div>
	<div class="tab-pane active" id="event-expenses-list">
		<?php if(isset($event_expenses) && count($event_expenses)): ?>
			<table class="table">
				<tr>
					<th>Type</th>
					<th>Employee</th>
					<th>Payment</th>
					<th>Amount</th>
					<th class="text-center">Desc.</th>
					<th>Action</th>
				</tr>
			<?php foreach($event_expenses as $expense): ?>
				<tr>
					<td><?php echo element('expense_name', $expense, ''); ?></td>
					<th><?php echo element('emp_name', $expense, ''); ?></th>
					<th><?php echo element('expense_payment', $expense, ''); ?></th>
					<td><?php echo money(element('expense_amount', $expense, '')+element('expense_hst_amount', $expense, '')); ?></td>
					<td class="text-center">
                        <?php if(element('expense_description', $expense, '')) : ?>
                            <span class="badge badge-light th-sortable" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?php echo element('expense_description', $expense, ''); ?>" >
                                <i class="fa fa-info"></i>
                            </span>
                        <?php endif; ?>
                    </td>
					<td class="text-right">
						<form id="delete-expense-<?php echo element('expense_id', $expense, 0); ?>" data-type="ajax" data-global="false" data-url="<?php echo base_url('dashboard/ajax_delete_expense'); ?>" data-callback="delete_expense_row">
							<input type="hidden" name="expense_id" value="<?php echo element('expense_id', $expense, 0); ?>">
							<button type="submit" class="btn btn-danger btn-xs" data-pk="<?php echo element('expense_id', $expense, 0); ?>"><i class="fa fa-trash-o"></i></button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</table>
		<?php else: ?>
			<h5 class="text-center text-muted" style="height: 100px;">No expenses</h5>
		<?php endif; ?>
		<div class="modal-footer p-right-0">
			<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
	</div>
</div>


