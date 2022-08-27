<div id="edit_expense-<?php echo $expense['expense_id']; ?>" data-expid="<?php echo $expense['expense_id']; ?>" class="modal fade" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Edit Expense</header>
			<form class="editExpense" method="POST">
				<div class="modal-body form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Date</label>
						<div class="col-sm-9">
							<input type="text" name="expense_date" class="form-control datepicker-input" readonly
							       data-date-format="yyyy-mm-dd" value="<?php echo date(getDateFormat(), $expense['expense_date']); ?>">
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Type</label>
						<div class="col-sm-9">
							<select name="expense_type" class="form-control expense_type">
								<option value="">Select Expense Type</option>
								<?php foreach($expense_types as $type) : ?>
									<option value="<?php echo $type->expense_type_id; ?>"<?php if($type->expense_type_id == $expense['expense_type_id']) :?> selected<?php endif; ?>>
										<?php echo $type->expense_name; ?>
									</option>
								<?php endforeach; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group expenseItem"<?php if(!isset($items[$expense['expense_type_id']])) : ?> style="display: none;"<?php endif; ?>>
						<label class="col-sm-3 control-label">Select Item</label>
						<div class="col-sm-9">
							<select name="expense_item" class="expense_item form-control">
								<option value="">Select Item</option>
								<?php if(isset($items[$expense['expense_type_id']])) : ?>
									<?php $groupName = NULL; ?>
									<?php foreach($items[$expense['expense_type_id']] as $key => $item) : ?>

										<?php if($groupName != $item['group_name']) : ?>
											<optgroup label="<?php echo $item['group_name']; ?>">
										<?php endif; ?>

										<option value="<?php echo $item['item_id']; ?>"<?php if($expense['expense_item_id'] == $item['item_id']) : ?> selected<?php endif; ?>>
											<?php echo $item['item_name']; ?>
										</option>

										<?php if((isset($items[$expense['expense_type_id']][$key + 1]['group_name']) && $item['group_name'] != $items[$expense['expense_type_id']][$key + 1]['group_name']) || !isset($items[$expense['expense_type_id']][$key + 1]['group_name'])) : ?>
											</optgroup>
										<?php endif; ?>
										<?php $groupName = $item['group_name']; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Select Employee</label>
						<div class="col-sm-9">
							<select name="expense_employee" class="expense_employee form-control">
								<option value="">Select Employee</option>
								<?php if(isset($employees) && count($employees)) : ?>
									<?php foreach($employees as $employee) : ?>
										<option value="<?php echo $employee->id; ?>"<?php if($employee->id == $expense['expense_user_id']) : ?> selected<?php endif; ?>>
											<?php echo $employee->firstname . ' ' . $employee->lastname; ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Select Payment</label>
						<div class="col-sm-9">
							<select name="expense_payment" class="form-control">
								<option <?php if($expense['expense_payment'] == 'Cash') : ?>selected<?php endif;?> value="Cash">Cash</option>
								<option <?php if($expense['expense_payment'] == 'CC') : ?>selected<?php endif;?> value="CC">CC</option>
								<option <?php if($expense['expense_payment'] == 'Bank') : ?>selected<?php endif;?> value="Bank">Bank(Check/Debit/IT)</option>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Tax</label>
                        <div class="col-sm-9">
                            <select name="tax_select" id="editTax" class="form-control">
                                <?php foreach ($allTaxes as $tax) : ?>
                                    <option value="<?php echo $tax['name'] ?>" <?php if ($tax['text'] == $expenseTax['text']) : ?> selected <?php endif; ?> > <?php echo $tax['text'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-inline text-danger"></span>
                            <input type="hidden" name="tax_text" id="editTaxHidden" value="<?php echo $expenseTax['text'] ?>">
                            <input type="hidden" name="tax_value" id="editTaxValue" value="<?php echo $expenseTax['value'] ?>">
                        </div>
                    </div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Amount</label>
						<div class="col-sm-9">
							<input type="text" name="expense_amount" class="expense_amount form-control" value="<?php echo $expense['expense_amount'] + $expense['expense_hst_amount']; ?>">
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label taxNoLabel">No <?php echo config_item('tax_name') ? config_item('tax_name') : 'Tax'; ?> Expense</label>
						<div class="col-sm-9">
							<input type="checkbox" id="expenseHstAmount" name="expense_hst_amount" style="margin-top:10px"<?php if($expense['expense_hst_amount'] == '0.00') : ?>checked="checked"<?php endif; ?> value="1">
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Description</label>
						<div class="col-sm-9">
							<textarea name="expense_description" class="form-control"><?php echo $expense['expense_description']; ?></textarea>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Expense File</label>
						<div class="col-sm-9">
							<span class="btn btn-primary btn-file">Choose File
								<input type="file" name="file" class="btn-upload" class="btn-upload">
							</span>
							<span class="help-inline text-danger"></span>
							<?php if(is_bucket_file('uploads/expenses_files/' . $expense['expense_file'])) : ?>
                                <div class="js-expense-file-container" style="display: inline-block">
                                    <a href="<?php echo base_url('uploads/expenses_files/' . $expense['expense_file']); ?>" target="_blank"
                                       class="btn btn-sm btn-success">
                                        <i class="fa fa-file"></i>
                                    </a>

                                    <button type='button' class="btn btn-xs btn-danger v-top js-delete-expense-file" data-expense-file-path="<?= 'uploads/expenses_files/' . $expense['expense_file']?>">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </div>
							<?php endif; ?>
						</div>
					</div>
					<div class="modal-footer">
						<input name="expense_id" type="hidden" value="<?php echo $expense['expense_id']; ?>">
						<button class="btn btn-info" type="submit">
							<span class="btntext">save</span>
							<img src="<?php echo base_url('assets/img/ajax-loader.gif'); ?>" style="display: none;width: 84px;" class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
    $(document).off('click', '.js-delete-expense-file');
    $(document).on('click', '.js-delete-expense-file', function() {
      if(! confirm('Are you sure?')) {
         return;
      }

      let filePath = $(this).attr('data-expense-file-path');
      
      $.post(baseUrl + 'reports/ajax_delete_expense_file', {file_path : filePath}, function (resp) {
        if (resp.status == 'ok') {
          $(`[data-expense-file-path="${filePath}"]`).closest('.js-expense-file-container').remove();
        }
        return false;
      }, 'json');
    });

    $('.taxNoLabel').text('No ' + $('#editTax').val() + ' Expense');
    $('#editTax').on('change', function () {
        $('.taxNoLabel').text('No ' + $('#editTax').val() + ' Expense');
        $('#editTaxHidden').val($('#editTax option:selected').text());
    })
</script>
