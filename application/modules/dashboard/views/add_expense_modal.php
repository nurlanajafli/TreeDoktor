<div id="add_expense"  class="modal fade" tabindex="-1" role="dialog"
     aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add Expense</header>
			<form id="addExpense" method="POST">
				<div class="modal-body form-horizontal">
					<div class="form-group">
						<label class="col-sm-3 control-label">Date</label>
						<div class="col-sm-9">
<!--							<input type="text" name="expense_date" class="form-control datepicker-input" readonly data-date-format="yyyy-mm-dd" id="expenseDate" value="--><?php //echo date('Y-m-d'); ?><!--">-->
							<input type="text" name="expense_date" class="form-control datepicker-input" readonly data-date-format="yyyy-mm-dd" id="expenseDate" value="<?php echo date(getDateFormat()); ?>" style="cursor: pointer">
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-3 control-label">Type</label>
						<div class="col-sm-9">
							<select id="expenseType" name="expense_type" class="form-control">
								<option value="">Select Expense Type</option>
								<?php if(isset($expenses_data) && !empty($expenses_data)) : ?>
									<?php foreach($expenses_data as $expense) : ?>
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
					<div class="form-group">
						<label class="col-sm-3 control-label">Select Employee</label>
						<div class="col-sm-9">
							<select name="expense_employee" class="expense_employee form-control">
								<option value="">Select Employee</option>
								<?php if(isset($employees_data) && !empty($employees_data)) : ?>
									<?php foreach($employees_data as $employee) : ?>
										<option value="<?php echo $employee->employee_id; ?>"><?php echo $employee->emp_name; ?></option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
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
                        <label class="col-sm-3 control-label">Tax</label>
                        <div class="col-sm-9">
                            <select id="tax" class="form-control">
                                <?php foreach ($allTaxes as $tax) : ?>
                                    <option value="<?php echo $tax['name'] ?>" <?php if ($tax['text'] == $defaultTax['text']) : ?> selected <?php endif; ?> > <?php echo $tax['text'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="help-inline text-danger"></span>
                            <input type="hidden" name="tax_text" id="taxHidden" value="<?php echo $defaultTax['text'] ?>">
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
						<label class="col-sm-3 control-label taxNoLabel">No <?php echo getDefaultTax()['name'] ?: 'TAX'; ?> Expense</label>
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
								<input type="file" name="file" class="btn-upload" id="fileToUpload" class="btn-upload">
							</span>
							<span class="help-inline text-danger"></span>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-info" type="submit">
							<span class="btntext">Add expense</span>
							<img src="<?php echo base_url('assets/img/ajax-loader.gif'); ?>" style="display: none;width: 84px;" class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
<script>
	$(document).ready(function(){
        $('.datepicker-input').datepicker({format: $('#php-variable').val()});
		$(document).on("keypress keyup blur", '#expenseAmount', function (event) {
			if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
				event.preventDefault();
			}
		});
		$(document).on('change', '#expenseType', function(){
			var expId = parseInt($(this).val());
			if(!expId)
			{
				$('#itemId optgroup').remove();
				$('#expenseItem').slideUp();
			}
			else
			{
				$.post(baseUrl + 'dashboard/ajax_get_expense_type_items', {expense_id : expId}, function(resp){
					if(resp.status = 'ok')
					{
						$('#itemId').replaceWith(resp.html);
						if(resp.count_items && !$('#expenseItem').is(':visible'))
							$('#expenseItem').slideDown();
						if(!resp.count_items && $('#expenseItem').is(':visible'))
							$('#expenseItem').slideUp();
					}
				}, 'json');
			}
			return false;
		});
		$('form#addExpense').submit(function (event) {
			$('form#addExpense .btntext').hide();
			$('form#addExpense .preloader').show();
			$('form#addExpense .preloader').parent().attr('disabled', 'disabled');
			var $form = $(this);
			var formData = new FormData(this);
			$.ajax({
				type: $form.attr('method'),
				url: baseUrl + 'dashboard/ajax_add_expense',
				data: formData,
				mimeType: "multipart/form-data",
				contentType: false,
				dataType: 'json',
				cache: false,
				processData: false,
				success: function (data, status) {
				    console.log(data);
					$('.error.form-group .help-inline').html('');
					$('.control-group.error').removeClass('error');
					$('.form-group.error').removeClass('error');
					if (data.status == 'error') {
						$('form#addExpense .btntext').show();
						$('form#addExpense .preloader').hide();
						$('form#addExpense .preloader').parent().removeAttr('disabled');
						delete data.status;
						$.each(data, function (key, val) {
							if (val) {
								$('[name="' + key + '"]').parent().parent().addClass('error');
								$('[name="' + key + '"]').next().html(val);
							}
						});
						return false;
					}
					if (data.status == 'ok') {
						$('#addExpense').modal('hide');
						location.reload();
						return false;
					}
				}
			});
			event.preventDefault();
		});
		$('#tax').on('change', function () {
            $('.taxNoLabel').text('No ' + $('#tax').val() + ' Expense');
            $('#taxHidden').val($('#tax option:selected').text());
        })
	});
</script>
