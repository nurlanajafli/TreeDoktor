<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.pie.min.js'); ?>"></script>
			  <style type="text/css">
				#flotcontainer, #flotcontainer2, #flotcontainer3{
					width: 460px;
					height: 450px;
					display: inline-block;
				}
				.pieLabel{font-weight: bold;}
				.plot-label{color: inherit!important;}
				.legendLabel{text-align: left;padding-left: 5px;}
				</style>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Expenses</li>
	</ul>
	<!--Top Menu -->
	<section class="panel panel-default p-n">
		<header class="panel-heading">Expenses Reports
			<?php if($this->session->userdata('user_type') == "admin" || $this->session->userdata('EXP') == 1) : ?>
				<div class="btn-group pull-right">
					<a href="#add_expense" class="btn btn-info" role="button" data-toggle="modal">
						Add Expense
					</a>
				</div>
				<div class="clear"></div>
			<?php endif; ?>
		</header>
		<div class="p-left-5">
			<div class="form-inline">
				<div class="p-10">
					<form id="dates" method="post" action="<?php echo base_url('reports/expenses'); ?>"
					      class="input-append m-t-xs">
<!--                        value="--><?php //if ($from) : echo $from;
//                        else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?><!--">-->

						<label>From:&nbsp;&nbsp;&nbsp;
							<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
                                   value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d');
                                   else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
						</label>
						<label>To:&nbsp;&nbsp;&nbsp;
							<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
							       value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d');
							       else : echo date(getDateFormat()); endif; ?>">
						</label>
						<label>Created:&nbsp;&nbsp;&nbsp;
							<select class="form-control date-input-client user">
								<option value="">Select User</option>
								<?php foreach($users as $user) : ?>
									<option value="<?php echo $user->id; ?>"><?php echo $user->firstname . ' ' . $user->lastname; ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label>Employee:&nbsp;&nbsp;&nbsp;
							<select class="form-control date-input-client employee">
								<option value="">Select Employee</option>
								<?php foreach($employees as $employee) : ?>
									<option value="<?php echo $employee->employee_id; ?>"><?php echo $employee->emp_name; ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<span id="date_submit" type="submit" class="btn btn-info pull-right" style="width:114px;">GO!</span>
					</form>
                    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
				</div>
				<script>
					$(document).ready(function () {
						// $('.datepicker').datepicker({format: 'yyyy-mm-dd'});
                        $('.datepicker').datepicker({format: $('#php-variable').val()});
					});
				</script>
			</div>
		</div>
		<div class="plots text-center">
			<div id="flotcontainer" class="plotContainer"></div>
		</div>
	</section>
	<section class="panel panel-default p-n expenses-table">
		
		<?php $this->load->view('expenses_table'); ?>
		
	</section>
	<?php if($this->session->userdata('user_type') == "admin" || $this->session->userdata('EXP') == 1) : ?>
		<?php $this->load->view('dashboard/add_expense_modal'); ?>
	<?php endif; ?>
	<script>
		var expense_id = '';
		var group_id = '';
		var item_id = '';
		var expenses = <?php echo json_encode($donut); ?>;
		function donut(groups, items) { 
				
				var group_data = [];
				var item_data = [];
				var data = [];
			   $.each(expenses, function (key, val) {
					var sumExp = val.sum;
					if(!sumExp)
						sumExp = "0";
					data.push({
					  label: '<a href="#" class="plot-label" data-expense_id="' + val.expense_type_id + '">' + val.expense_name + '<span class="pull-right m-l-xs">' + Common.money(sumExp) + '</span><div class="clear"></div></a>',
					  data: sumExp
					});
				});
				
				if($(groups) && $(groups).length)
				{
					
					$.each(groups, function (key, val) {
					
					var sumExp = val.summ;
					if(!sumExp)
						sumExp = "0";
					group_data.push({
					  label: '<a href="#" class="plot-label" data-group_id="' + val.group_id + '">' + val.group_name + '<span class="pull-right m-l-xs">' + Common.money(sumExp) + '</span><div class="clear"></div></a>',
					  data: sumExp
					});
				});
				}
				if($(items) && $(items).length)
				{
					$.each(items, function (key, val) {
					var sumExp = val.summ;
					if(!sumExp)
						sumExp = "0";
					item_data.push({
					  label: '<a href="#" class="plot-label" data-item_id="' + val.item_id + '">' + val.item_code + '<span class="pull-right m-l-xs">' + Common.money(sumExp) + '</span><div class="clear"></div></a>',
					  data: sumExp
					});
				});
				}
				var options = {
						series: {
							pie: {
								show: true,
								innerRadius: 0.3,
								label:{                        
									radius: 0.6,
								}
							}
						},
						legend: {
							show: true
						},
						grid: {
							hoverable: true,
							clickable: true
						}
					 };
				var sum = 0;
				$.plot($("#flotcontainer"), data, options);
				$.each($('#flotcontainer .plot-label span'), function(key, val){
					sum += parseFloat($(val).text().replace(Common.get_currency(), '').replaceAll(',',''));
				});
			    $('#flotcontainer .legend').find('table tbody').append('<tr><td colspan="2" class="text-right"><a href="#" class="plot-label" data-expense_id=""><strong>Total:' + Common.money(sum) + '</strong></a></td></tr>')
				if($(groups) && $(groups).length)
				{
					$('#flotcontainer2').remove();
					$('#flotcontainer3').remove();
					$('.plots').append('<div id="flotcontainer2" class="plotContainer"></div>');
					$.plot($("#flotcontainer2"), group_data, options);  
				}
				else
				{
					$('#flotcontainer2').remove();
				}
				if($(items) && $(items).length)
				{
					$('#flotcontainer3').remove();
					$('.plots').append('<div id="flotcontainer3" class="plotContainer"></div>');
					$.plot($("#flotcontainer3"), item_data, options);  
				}
				else
				{
					$('#flotcontainer3').remove();
				}
		}
		donut();
		$(document).ready(function(){

		    $(document).on('click', '.editExpenseForm', function () {
                var expense_id = $(this).data('expense_id');
                $.post(baseUrl + 'reports/ajax_get_edit_expense_form', {expense_id: expense_id}, function (data) {
                    if (data.status == 'ok') {
                        $('body').append(data.html);
                        $('#edit_expense-' + expense_id).modal();
                        $('#edit_expense-' + expense_id).find('.datepicker-input').datepicker({format: $('#php-variable').val()});
                    }
                }, 'json');
            });
			$(document).on("plotclick", ".plotContainer", function(event, pos, obj){
					var from = $('#dates .from').val();
					var to = $('#dates .to').val();
					var user_id = $('#dates .user').val();
					var emp_id = $('#dates .employee').val();
					if(typeof(obj.series) != 'undefined' && $(obj.series.label).data('expense_id'))
					{
						expense_id = $(obj.series.label).data('expense_id');
						group_id = '';
						item_id = '';
					}
					if(typeof(obj.series) != 'undefined' && $(obj.series.label).data('group_id'))
					{
						group_id = $(obj.series.label).data('group_id');
						item_id = '';
					}
					if(typeof(obj.series) != 'undefined' && $(obj.series.label).data('item_id'))
					{
						item_id = $(obj.series.label).data('item_id');
					}
					
					if(expense_id === '0')
					{
						$('#dates .user').attr('disabled', 'disabled');
						$('#dates .employee').attr('disabled', 'disabled');
					}
					else
					{
						$('#dates .user').removeAttr('disabled', 'disabled');
						$('#dates .employee').removeAttr('disabled', 'disabled');
					}
					
					$.post(baseUrl + 'reports/ajax_get_expense', {from:from, to:to, expense_id:expense_id, group_id:group_id, item_id:item_id, user_id:user_id, emp_id:emp_id}, function(resp){
						if(resp.status = 'ok')
						{
							var items = false;
							var html = resp.html;
							$('.expenses-table').html(html);
							if(resp.items)
								items = resp.items;
							groups = resp.groups;
							donut(groups, items);
							$('.datepicker-input').datepicker();
						}
					}, 'json');
				});
			   $(document).on("click", ".plot-label", function(){
					var from = $('#dates .from').val();
					var to = $('#dates .to').val();
				    var user_id = $('#dates .user').val();
				    var emp_id = $('#dates .employee').val();
				    if(!$(this).attr('data-expense_id') && !$(this).attr('data-group_id') && !$(this).attr('data-item_id'))
				    {
					    expense_id = '';
					    group_id = '';
					    item_id = '';
				    }
					if($(this).attr('data-expense_id'))
					{
						expense_id = $(this).attr('data-expense_id');
						group_id = '';
						item_id = '';
					}
					if($(this).attr('data-group_id'))
					{
						group_id = $(this).attr('data-group_id');
						item_id = '';
					}
					if($(this).attr('data-item_id'))
					{
						item_id = $(this).attr('data-item_id');
					}
					
					if(expense_id === '0')
					{
						$('#dates .user').attr('disabled', 'disabled');
						$('#dates .employee').attr('disabled', 'disabled');
					}
					else
					{
						$('#dates .user').removeAttr('disabled', 'disabled');
						$('#dates .employee').removeAttr('disabled', 'disabled');
					}
					
					$.post(baseUrl + 'reports/ajax_get_expense', {from:from, to:to, expense_id:expense_id, group_id:group_id, item_id:item_id, user_id:user_id, emp_id:emp_id}, function(resp){
						if(resp.status = 'ok')
						{
							var items = false;
							var html = resp.html;
							$('.expenses-table').html(html);
							if(resp.items)
								items = resp.items;
							groups = resp.groups;
							donut(groups, items);
							$('.datepicker-input').datepicker();
						}
					}, 'json');
				});
			$(document).on("click", '#date_submit', function(){
				//$('#processing-modal').modal();
				var from = $('#dates .from').val();
				var to = $('#dates .to').val();
				var user_id = $('#dates .user').val();
				var emp_id = $('#dates .employee').val();
				
				if(expense_id === '0')
				{
					$('#dates .user').attr('disabled', 'disabled');
					$('#dates .employee').attr('disabled', 'disabled');
				}
				else
				{
					$('#dates .user').removeAttr('disabled', 'disabled');
					$('#dates .employee').removeAttr('disabled', 'disabled');
				}
				
				$.ajax({
					type: 'POST',
					url: baseUrl + 'reports/ajax_get_expense',
					data: {from:from, to:to, expense_id:expense_id, group_id:group_id, item_id:item_id, user_id:user_id, emp_id:emp_id},
					//global: false,
					success: function(resp){
						if(resp.status = 'ok')
						{
							var groups = false;
							var items = false;
							if(resp.groups)
								groups = resp.groups;
							if(resp.items)
								items = resp.items;
							var html = resp.html;
							$('.expenses-table').html(html);
							expenses = resp.donut;
							donut(groups, items);
							$('.modal-backdrop.in').remove();
							$('.datepicker-input').datepicker();
							//$('#processing-modal').modal('hide');
						}
					},
					dataType: 'json'
				}).fail(function( jqXHR, textStatus, errorThrown ) {console.log(jqXHR);});
			});
				
			$(document).on("keypress keyup blur", '.expense_amount', function (event) {
				if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
					event.preventDefault();
				}
			});
			$(document).on('change', '.expense_type', function(){
				var expId = parseInt($(this).val());
				var expense_id = $(this).parents('.modal:first').data('expid');
				var obj = $(this).parents('[data-expid="' + expense_id + '"]:first');
				if(!expId)
				{
					$(obj).find('.expense_type optgroup').remove();
					$(obj).find('.expense_item').slideUp();
				}
				else
				{
					
					$.post(baseUrl + 'dashboard/ajax_get_expense_type_items', {expense_id : expId}, function(resp){
						if(resp.status = 'ok')
						{
							console.log($(obj));
							$(obj).find('.expense_item').replaceWith(resp.html);
							if(resp.count_items && !$(obj).find('.expenseItem').is(':visible'))
								$(obj).find('.expense_item').parents('.expenseItem:first').slideDown();
							if(!resp.count_items && $(obj).find('.expenseItem').is(':visible'))
								$(obj).find('.expense_item').parents('.expenseItem:first').slideUp();
						}
					}, 'json');
				}
				return false;
			});
			$(document).on('submit', 'form.editExpense', function (event) {
				//$('#processing-modal').modal();
				$(this).find('.btntext').hide();
				$(this).find('.preloader').show();
				$(this).find('.preloader').parent().attr('disabled', 'disabled');
				var $form = $(this);
				var formData = new FormData(this);
				//$('#edit_expense-' + $('[data-expid]:visible').data('expid')).modal('hide');
				$.ajax({
					type: $form.attr('method'),
					//global: false,
					url: baseUrl + 'dashboard/ajax_add_expense',
					data: formData,
					mimeType: "multipart/form-data",
					contentType: false,
					dataType: 'json',
					cache: false,
					processData: false,
					success: function (data, status) {
						$form.find('.error.form-group .help-inline').html('');
						$form.find('.control-group.error').removeClass('error');
						$form.find('.form-group.error').removeClass('error');
						if (data.status == 'error') {
							$form.find('.btntext').show();
							$form.find('.preloader').hide();
							$form.find('.preloader').parent().removeAttr('disabled');
							delete data.status;
							$.each(data, function (key, val) {
								if (val) {
									$form.find('[name="' + key + '"]').parent().parent().addClass('error');
									$form.find('[name="' + key + '"]').next().html(val);
								}
							});
							$('#processing-modal').modal('hide');
							return false;
						}
						if (data.status == 'ok') {
                            $('#expenseRow-' + data.id).replaceWith(data.html);
                            $('#edit_expense-' + data.id).modal('hide');
                            setTimeout(function(){
                                $('#edit_expense-' + data.id).remove();
                                $('[data-toggle="tooltip"]').tooltip();
                            }, 200);
							return false;
						}
					}
				});
				event.preventDefault();
			});
			$(document).on('click', '.deleteExp', function(){
				var expense_id = $(this).data('expense_id');
				var obj = $(this);
				if(confirm('Are you sure?'))
				{
					$.post(baseUrl + 'dashboard/ajax_delete_expense', {expense_id : expense_id}, function(resp){
						if(resp.status == 'ok')
							$(obj).parents('tr:first').remove();
					}, 'json');
				}
			});
		});
	</script>
<?php $this->load->view('includes/footer'); ?>
