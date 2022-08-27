<div id="add_expense"  class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add Expense</header>
			
			<div class="modal-body form-horizontal"></div>
			
		</div>
	</div>
</div>
<input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
<script>
	$(document).ready(function(){
		$('#add_expense').on('show.bs.modal', function(e){
			if (e.namespace === 'bs.modal') {
				var event_id = e.relatedTarget.dataset.pk;
				$.post(baseUrl + 'dashboard/get_event_expense_modal', {event_id : event_id}, function(resp){
					if(resp.status = 'ok')
						$('#add_expense .modal-body').html(resp.html);
                        $('[data-toggle="tooltip"]').tooltip();
						//$('#add_expense .datepicker-input').datepicker({format: $('#php-variable').val()});
				}, 'json');
			}
		});
		
		$(document).delegate('#expenseAmount', "keypress keyup blur", function (event) {
			if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
				event.preventDefault();
			}
		});
		$(document).delegate('#expenseType', 'change', function(){
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
		$(document).delegate('form#addExpense', 'submit', function (event) {
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
						//$('#addExpense').modal('hide');
						//location.reload();
						$('#add_expense .modal-body').html(data.html);
                        $('[data-toggle="tooltip"]').tooltip();
						
						if(data.event_id!=undefined && data.expense_amount_sum!=undefined){
							$('#addExpense'+'-'+data.event_id).html(data.expense_amount_sum);
						}

						return false;
					}
				}
			});
			event.preventDefault();
		});
	});
	window.delete_expense_row = function(response){
		$('#delete-expense-'+response.expense_id).closest('tr').remove();
		if(response.expense_amount_sum!=undefined)
			$('#addExpense'+'-'+response.expense.expense_event_id).html(response.expense_amount_sum);
	}
</script>
