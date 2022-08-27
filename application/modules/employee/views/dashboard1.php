<?php $this->load->view('includes/header'); ?>
<style>
	.first-letter:first-letter{text-transform:uppercase}
</style>
<body><input type="hidden" id="empid" value="<?php echo $emp_id ?>"/>
<input type="hidden" id="empname" value="<?php echo $emp_name ?>"/>

<!-- Alerts -->
<div class="p-10">
	<div class="alert alert-dismissable alert-danger" id="error" style="display:none;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<strong>Oh snap!</strong> <span class="alert-text"></span>
	</div>
</div>

<style>
	.error{border: 2px solid red!important;}
</style>
<section class="scrollable p-sides-15">
	<section class="col-md-12">

		<!-- Daily stats -->
		<section class="col-md-8 panel panel-default p-n m-t-sm">

			<!-- Tracker header header -->
			<header class="panel-heading"><span id="username"></span> - Dashboard</header>

			<div class="m-10">
				<div class="form-inline">
					<form name="frmgetmonth" id="frmgetmonth" method="POST" action="">
						<!--<label for="dpMonths"></label>-->

						<!-- SELECT Month and Year -->
						<div class="input-group date col-md-4 pull-left" id="dpMonths" data-date="<?php echo $cdate; ?>"
						     data-date-format="mm/yyyy" data-date-viewmode="years" data-date-minviewmode="months">
							<input class="picker form-control" size="16" type="text" value="<?php echo $cdate; ?>"
							       readonly="" name="monthyear" id="monthyear">
							<span class="input-group-addon add-on"><i class="fa fa-calendar"></i></span>
						</div>
						<button type="button" id="btngetreport" class="btn pull-left"
						        style="margin-top: 0px; margin-left: 10px;">
							<i class="fa fa-book"></i>&nbsp;Get Report
						</button>
						<div class="clear"></div>
					</form>
				</div>
			</div>
			<div id="monthreport"></div>

		</section>
		<!-- /Daily stats -->

		<!-- Logged hours -->
		<section class="col-md-3 panel panel-default p-n m-l-sm m-t-sm">
			<header class="panel-heading">Today:</header>
			<div class="p-10">
				<strong>Login:</strong><span id="logintime"><?php echo $login_time; ?></span></br>
				<strong>Logout:</strong><span id="logouttime"><?php echo $logout_time; ?></span></br>
				<strong>Total:</strong><span id="timediff"><?php echo @$time_diff; ?></span>
			</div>
		</section>
		<!-- Logged hours -->

		<!-- Start/Stop time -->
		<?php /* $timer_class = "start"; ?>
		<?php $button_caption = "Start"; ?>
		<?php $timer_running = "none"; ?>
		<?php if ($login == true && $logout == false) { ?>
			<?php $timer_class = "running"; ?>
			<?php $timer_running = "display";
			$class = 'filled_green'; ?>
			<?php $button_caption = "Stop"; ?>
		<?php } */?>
		<input type="hidden" name="login_rec_id" id="login_rec_id" value="<?php echo $login_rec_id ?>"/>
		<section class="pull-left">
			<section class="col-md-2 panel filled_blue panel-default p-n m-l-sm timer" id="show_timer"
			         style="display:none;">

				<!-- entry button -->
				<div class="start click" id="start" href="#" style="<?php if ($logout == FALSE) {
					echo 'display:none;';
				} ?>" <?php if ($logout == FALSE) {
					echo 'disabled="disabled"';
				} ?>></div>

			</section>
			<!--  /Start -->
			<section class="col-md-2 panel filled_green panel-default p-n m-l-sm timer" id="show_timer1"
			         style='display:none;'>
				<!-- <input type="hidden" name="login_rec_id" id="login_rec_id" value="<?php echo $login_rec_id ?>" />-->
				<!-- entry button -->
				<div class="running click" id="stop" href="#startTimeModal" data-toggle="modal"
				     style=" <?php if ($logout == TRUE) {
					     echo 'display:none;';
				     } ?>" <?php if ($logout == TRUE) {
					echo 'disabled="disabled"';
				} ?>></div>
			</section>
		</section>
		<!--  Stop time -->

	</section>
	<!------Report-------------->
	<div id="reportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<form id="eventReport" class="form-horizontal" method="POST">
				<div class="modal-body">
				<?php if(isset($events) && !empty($events)) : ?>
					<?php foreach($events as $key => $event) : ?>
					<div class="event">
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Workorder:</label>
							<div class="col-sm-7">
								<p class="form-control-static">
									<strong>
										<?php echo $event['workorder_no']; ?> - 
										<?php echo $event['lead_address']; ?>
									</strong>
								</p>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
							<input type="hidden" name="workorder" value="<?php echo $event['workorder_no'] . '-' . $event['lead_address']; ?>">
						</div>
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Work(start/finish):</label>
							<div class="col-sm-4">
								<select class="form-control begin hrs p-n" style="width: 68px; display:inline-block;" name="event_start_hours[<?php echo $event['id'];?>]">
									<option value=""></option>
									
									<?php for($i=0; $i <= 23; $i++) : ?>
										<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo date('h A', strtotime(date('Y-m-d ' . $i . ':00:00'))); ?></option>
									<?php endfor;?>
								</select> : 
								<select class="form-control begin min" style="width: 68px; display:inline-block;" name="event_start_min[<?php echo $event['id'];?>]">
									<option value=""></option>
									<?php for($i=0; $i < 60; $i+=5) : ?>
										<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
									<?php endfor;?>
								</select>
								
							</div>
							<div class="col-sm-4">
								<select class="form-control finish hrs p-n" style="width: 68px; display:inline-block;" name="event_finish_hours[<?php echo $event['id'];?>]">
									<option value=""></option>

									<?php for($i=0; $i <= 23; $i++) : ?>
										<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo date('h A', strtotime(date('Y-m-d ' . $i . ':00:00'))); ?></option>
									<?php endfor;?>
								</select> : 
								<select class="form-control finish min text-center" style="width: 68px; display:inline-block;" name="event_finish_min[<?php echo $event['id'];?>]">
									<option value=""></option>
									<?php for($i=0; $i < 60; $i+=5) : ?>
										<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></option>
									<?php endfor;?>
								</select>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Finished:</label>
							<div class="col-sm-7">
								<div class="radio">
								  <label>
									<input type="radio" class="status" name="status[<?php echo $event['id'];?>]" value="finished">
									Yes
								  </label>
								</div>
								<div class="radio">
								  <label>
									<input type="radio" class="status" name="status[<?php echo $event['id'];?>]" value="unfinished">
									No
								  </label>
								</div>
								<input type="hidden" name="wo_id[<?php echo $event['id'];?>]" value="<?php echo $events[$key]['event_wo_id']; ?>">
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none payment" style="display:none;">
							<label class="col-sm-4 control-label">Payment:</label>
							<div class="col-sm-7">
								<div class="radio">
								  <label>
									<input type="radio" class="payment" name="payment[<?php echo $event['id'];?>]" value="yes">
									Yes
								  </label>
								</div>
								<div class="radio">
								  <label>
									<input type="radio" class="payment" name="payment[<?php echo $event['id'];?>]" value="no">
									No
								  </label>
								</div>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none paymentSum" style="display:none;">
							<label class="col-sm-4 control-label">Payment Type:</label>
							<div class="col-sm-7">
								<div class="radio">
								  <label>
									<input type="radio" class="payment_type" name="payment_type[<?php echo $event['id'];?>]" value="Cash">
									Cash
								  </label>
								</div>
								<div class="radio">
								  <label>
									<input type="radio" class="payment_type" name="payment_type[<?php echo $event['id'];?>]" value="Check">
									Check
								  </label>
								</div>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none paymentSum" style="display:none;">
							<label class="col-sm-4 control-label">Payment Amount:</label>
							<div class="col-sm-7">
								<input type="text" disabled class="finished form-control" name="payment_amount[<?php echo $event['id'];?>]">
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						
						<div class="form-group m-b-none finishDescription" style="display:none;">
							<label class="col-sm-4 control-label">Work Remaining:</label>
							<div class="col-sm-7">
								<textarea class="unfinished form-control" disabled name="work_description[<?php echo $event['id'];?>]" ></textarea>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Damage:</label>
							<div class="col-sm-7">
								<div class="radio">
								  <label>
									<input type="radio" class="damage" name="damage[<?php echo $event['id'];?>]" value="yes">
									Yes
								  </label>
								</div>
								<div class="radio">
								  <label>
									<input type="radio" class="damage" name="damage[<?php echo $event['id'];?>]" value="no">
									No
								  </label>
								</div>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none dmgDescription" style="display:none;">
							<label class="col-sm-4 control-label">Damage Description:</label>
							<div class="col-sm-7">
								<textarea class="form-control dmgText" disabled name="demage_description[<?php echo $event['id'];?>]" ></textarea>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none" >
							<label class="col-sm-4 control-label">Event Description:</label>
							<div class="col-sm-7">
								<textarea class="form-control eventText" name="event_description[<?php echo $event['id'];?>]" ></textarea>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
					</div>
					<?php endforeach; ?>
				<?php endif; ?>
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Expenses:</label>
							<div class="col-sm-7">
								<div class="radio">
								  <label>
									<input type="radio" class="expenses" name="expenses" value="yes">
									Yes
								  </label>
								</div>
								<div class="radio">
								  <label>
									<input type="radio" class="expenses" name="expenses" value="no">
									No
								  </label>
								</div>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none expensesDesc" style="display:none;">
							<label class="col-sm-4 control-label">Expenses Description:</label>
							<div class="col-sm-7">
								<textarea class="form-control expensesText" disabled name="expenses_description" ></textarea>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Malfunctions Equipment:</label>
							<div class="col-sm-7">
								<div class="radio">
								  <label>
									<input type="radio" class="fail" name="malfunctions_equipment" value="yes">
									Yes
								  </label>
								</div>
								<div class="radio">
								  <label>
									<input type="radio" class="fail" name="malfunctions_equipment" value="no">
									No
								  </label>
								</div>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						<div class="form-group m-b-none failDesc" style="display:none;">
							<label class="col-sm-4 control-label">Malfunctions Description:</label>
							<div class="col-sm-7">
								<textarea class="form-control failText" disabled name="malfunctions_description" ></textarea>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
					<?php if(isset($team_members) && !empty($team_members)) : ?>
					<?php foreach($team_members as $key => $member) : ?>
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Finished Time<br><strong><?php echo $member['emp_name']; ?></strong>:</label>
							<div class="col-sm-7">
								<input type="time" class="form-control" name="logout_time[<?php echo $member['employee_id']; ?>]" class="">
							</div>
							<?php if($key + 1 != count($team_members)) : //countOk ?>
								<div class="clear"></div>
								<div class="line line-dashed line-lg"></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
					<?php if(isset($team_id)) : ?>
						<input type="hidden" name="team_id" value="<?php echo $team_id; ?>">
					<?php endif;?>
					
				</div>
				<div class="modal-footer">
					<input type="submit" name="submit" value="Preview" class="btn btn-info" id="submit">
				</div>
				</form>
			</div>
		</div>
	</div>
	
	<!------End Report-------------->
	
	<!-- /Time tracker -->
	<div id="startTimeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<h5 class="p-bottom-20">Time Information</h5>
				</div>
				<div class="p-bottom-20 " id="time_str" style=" margin-left: 25px; margin-top: -21px;">
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true" id='empSignout'>Sign Out</button>
				</div>
			</div>
		</div>
	</div>
	
	<!--Estimator Report Modal-->
	<div id="estReportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Estimator Report</header>
				<div class="modal-body">
					<form id="estimateReport" class="form-horizontal" method="POST">
						
						
							<input type="hidden" name="log_id" value="<?php echo $login_rec_id; ?>">
						
						<div class="form-group m-b-none">
							<label class="col-sm-4 control-label">Comment: </label>
							<div class="col-sm-8">
								<textarea class="form-control" name="comment" ><?php echo isset($est_report) ? $est_report : ''; ?></textarea>
							</div>
							<div class="clear"></div>
							<div class="line line-dashed line-lg"></div>
						</div>
						
					</form>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true" >Close</button>
					<button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id='saveEstReport'>Confirm</button>
				</div>
			</div>
		</div>
	</div>
	<!--End Estimator Report Modal-->
	
	<!--Confirm Modal-->
	<div id="resultReportModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Report Preview</header>
				<div class="modal-body">
					<div id="resultData"></div>
				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true" id='cancelReport'>Edit</button>
					<button class="btn btn-success" data-dismiss="modal" aria-hidden="true" id='saveReport'>Confirm</button>
				</div>
			</div>
		</div>
	</div>
	<!--End Confirm Modal-->
	<script> var _BASE_PATH = '<?php echo base_url(); ?>'; </script>
	<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
	<script>
		var show_web_cam = '<?php echo @$show_web_cam ?>';
		var estimator = <?php echo isset($estimator) && !empty($estimator) && $estimator['emp_field_estimator'] == true ? json_encode($estimator) : '[]'; ?>;
		var teams = <?php echo isset($teams) && !empty($teams) ? json_encode($teams) : '[]'; ?>;
		var _BASE_URL = '<?php echo base_url(); ?>';
		var _DISABLE_TIMER_BUTTON = '<?php echo _DISABLE_TIMER_BUTTON; ?>';
		$(document).ready(function(){
			$("#username").html($("#empname").val());
			$('#dpMonths').datepicker();
			$(".click").click(function () {
				id = 'start';
				if($(this).attr('id') == 'start')
					id = 'stop';
				$(id).removeAttr("disabled");
				$(id).show();
				//$(this).hide();
				//console.log(id); return false;
				// alert($(this).attr('disabled'));
				
				if ($(this).attr('disabled') != 'disabled') {
					if($(this).attr('id') == 'start')
						start();
					else
					{
						if($(teams).length)
						{
							$('#reportModal').modal().show();
							return false;
						}
						if($(estimator).length)
						{
							$('#estReportModal').modal().show();
							return false;
						}
						$(this).hide();
						$('#start').removeAttr("disabled");
						$('#start').show();
						$('#stop').hide();
						
						stop();
					}
				}
			});
			if ($('#stop').attr('disabled') != 'disabled') {
				$("#start").hide();
			} else {
				$("#start").hide();
			}
			$("#btngetreport").click(function () {
				get_monthly_report();
			});
			$('#saveEstReport').click(function(){
				var fields = [];
				var fields = $("input[data-required='required']");
				$("#estimateReport input[data-required='required']").removeClass("error");
				var trigger = true;
				$.each(fields, function(key,val){
					if($(val).val() == '' || $(val).val() == '00:00')
					{
						$(fields[key]).addClass('error');
						trigger = false;
					}
				});
				if(trigger)
				{
					$.post(baseUrl + 'report/ajax_save_report', $('#estimateReport').serialize(), function (resp) {
						if(resp.status == 'ok')
						{
							estimator = [];
							$('#estReportModal').modal('hide');
							$('#stop').click();
						}
						return false;
					}, 'json');
				}
				return false;
			});
			$('#cancelReport').click(function(){
				$('#resultReportModal').modal('hide');
				$('#reportModal').modal().show();
				return false;
			});
			$("#emp_logout").on("click", function () {
				logout();
			});
			$("#empSignout").on("click", function () {
				if ($('#stop').attr('disabled') == 'disabled') {
					logout();
				}

			});
			$('.status').change(function(){
				if($(this).val() == 'finished')
				{
					$(this).parents('.event:first').find('.payment').slideDown();
					$(this).parents('.event:first').find('.timeToFinish').slideUp();
					$(this).parents('.event:first').find('.finishDescription').slideUp();
					$(this).parents('.event:first').find('.timeToFinish').find('.unfinished').val('').attr('disabled', 'disabled');
					$(this).parents('.event:first').find('.finishDescription').find('.unfinished').val('').val('').attr('disabled', 'disabled');
				}
				else
				{
					$(this).parents('.event:first').find('.payment').slideUp();
					$(this).parents('.event:first').find('.paymentSum').slideUp();
					$(this).parents('.event:first').find('.paymentSum').find('.finished').val('').attr('disabled', 'disabled');
					$(this).parents('.event:first').find('.payment').find('.payment').prop('checked', false);
					$(this).parents('.event:first').find('.timeToFinish').find('.unfinished').removeAttr('disabled');
					$(this).parents('.event:first').find('.timeToFinish').slideDown();
					$(this).parents('.event:first').find('.finishDescription').find('.unfinished').removeAttr('disabled');
					$(this).parents('.event:first').find('.finishDescription').slideDown();
				}
				return false;
			});
			$('.payment').change(function(){
				if($(this).val() == 'yes')
				{
					$(this).parents('.event:first').find('.paymentSum').slideDown();
					$(this).parents('.event:first').find('.paymentSum').find('.finished').removeAttr('disabled');
				}
				else
				{
					$(this).parents('.event:first').find('.paymentSum').slideUp();
					$(this).parents('.event:first').find('.paymentSum').find('.finished').val('').attr('disabled', 'disabled');
				}
				return false;
			});
			$('.damage').change(function(){
				if($(this).val() == 'yes')
				{
					$(this).parents('.event:first').find('.dmgDescription').slideDown();
					$(this).parents('.event:first').find('.dmgDescription').find('.dmgText').removeAttr('disabled');
				}
				else
				{
					$(this).parents('.event:first').find('.dmgDescription').slideUp();
					$(this).parents('.event:first').find('.dmgDescription').find('.dmgText').val('').attr('disabled', 'disabled');
				}
			});
			$('.fail').change(function(){
				
				if($(this).val() == 'yes')
				{
					$(this).parents('#eventReport').find('.failDesc').slideDown();
					$(this).parents('#eventReport').find('.failDesc').find('.failText').removeAttr('disabled');
				}
				else
				{
					$(this).parents('#eventReport').find('.failDesc').slideUp();
					$(this).parents('#eventReport').find('.failDesc').find('.failText').val('').attr('disabled', 'disabled');
				}
			});
			$('.expenses').change(function(){
				
				if($(this).val() == 'yes')
				{
					$(this).parents('#eventReport').find('.expensesDesc').slideDown();
					$(this).parents('#eventReport').find('.expensesDesc').find('.expensesText').removeAttr('disabled');
				}
				else
				{
					$(this).parents('#eventReport').find('.expensesDesc').slideUp();
					$(this).parents('#eventReport').find('.expensesDesc').find('.expensesText').val('').attr('disabled', 'disabled');
				}
			});
			$("#eventReport").on("submit", function(){
				var formValid = true;
				var startTime = null;
				var finishTime = null;
				$('#eventReport').find('div.form-group').removeClass('has-error');
				var inputs = $('#eventReport').find('textarea:visible, input:visible, select:visible').not('[type="submit"]');
				$.each(inputs, function(key, val){
					var inputName = $(val).attr('name');
					var inputType = $(val).attr('type');
					if(!inputType && !$('[name="' + inputName + '"]').val())
					{
						$(val).parents('div.form-group:first').addClass('has-error');
						formValid = false;
					}
					if(inputType == 'radio' && !$('[name="' + inputName + '"]').is(':checked'))
					{
						$(val).parents('div.form-group:first').addClass('has-error');
						formValid = false;
					}
					if(inputType && inputType != 'radio' && !$('[name="' + inputName + '"]').val())
					{
						$(val).parents('div.form-group:first').addClass('has-error');
						formValid = false;
					}
					if($(val).hasClass('hrs'))
						var seconds = 3600;
					if($(val).hasClass('min'))
						var seconds = 60;
					if($(val).hasClass('begin'))
						startTime += $(val).val() * seconds;
					if($(val).hasClass('finish'))
						finishTime += $(val).val() * seconds;
					if($(val).hasClass('status'))
					{
						if(startTime > finishTime)
						{
							$(val).parents('div.form-group:first').prev().addClass('has-error');
							formValid = false;
						}
						finishTime = null;
						startTime = null;
					}
						
				});
				if(formValid)
				{
					$('#reportModal').modal().hide();
					var data = $('#eventReport').serializeArray();
					var html = '';
					$.each(data, function(key, value){ 
						name = $.trim(value['name'].replace(/[^A-Za-zА-Яа-яЁё]/g, " "));
						if(name == 'logout time')
							name = $('#reportModal').find('input[name="' + value['name'] + '"]').parents('.form-group:first').find('label.control-label').html().replace(":", "");
						if((name == 'workorder' && key) || name == 'malfunctions equipment')
							html= html + '<br><br>';
						if(name == 'team id')
							return false;
						if(name == 'workorder')
							value['value'] = '<strong>' + value['value'] + '</strong>';
						if(name == 'event start hours' || name == 'event finish hours')
						{
							name = name.replace(' hours', '');
							value['value'] += ':' + data[key + 1]['value'];
						}
						divider = '';
						if(data[key + 1]['name'] != 'team_id' && data[key + 1]['name'] != 'workorder' && data[key + 1]['name'] != 'malfunctions_equipment')
							divider = '<div class="line line-dashed line-lg"></div>';
						if(name != 'event start min' && name != 'event finish min' && name != 'wo id')
						{	
							html = html + '<div class="form-group m-b-none"><label class="col-sm-4 control-label first-letter">' + name + ':</label><div class="col-sm-8 first-letter">' + value['value'] + '</div><div class="clear"></div>' + divider + '</div>';
						}
					});
					$("#resultData").html(html);
					$('#resultReportModal').modal().show();
					return false;
				}
				else
				{
					$('#reportModal').animate({
						scrollTop: $('#reportModal').scrollTop() + $('#reportModal').find('.has-error:first').offset().top
					},'slow');
				}
				return false;
			});
			$('#saveReport').click(function(){
				$.post(baseUrl + 'employee/ajax_save_report', $('#eventReport').serialize(), function (resp) {
					if(resp.status == 'ok')
					{
						teams = [];
						$('#resultReportModal').modal('hide');
						$('#stop').click();
					}
					return false;
				}, 'json');
				return false;
			});
		});
	</script>
	
	<?php /* <script> 
		$('#show_timer1').hide();
		$('#show_timer').hide();
		var show_web_cam = '<?php echo @$show_web_cam ?>';
		var estimator = <?php echo isset($estimator) && count($estimator) && $estimator['emp_field_estimator'] == true ? json_encode($estimator) : '[]'; ?>;
		var teams = <?php echo isset($teams) && count($teams) ? json_encode($teams) : '[]'; ?>;
		var _BASE_URL = '<?php echo base_url(); ?>';
		var _DISABLE_TIMER_BUTTON = '<?php echo _DISABLE_TIMER_BUTTON; ?>';
		$("document").ready(function () {
			$("#username").html($("#empname").val());
			$('#dpMonths').datepicker();
			$('#drop1').on("click", function () {
				if ($(this).next().attr("aria-labelledby") == "drop1") {
					$(this).next().show();
				}
			});
			
			
			
			$(document).mouseup(function (e) {
				var container = $(".dropdown");

				if (!container.is(e.target) // if the target of the click isn't the container...
					&& container.has(e.target).length === 0) // ... nor a descendant of the container
				{
					//$("#emp_logout").parent().parent().hide();
				}
			});
			get_monthly_report();
			//$("#drop1").next().remove();


			$("#entry-button").click(function () {
				$('#running-button').removeAttr("disabled");
				$('#running-button').show();
				$('#entry-button').hide();
				// alert($(this).attr('disabled'));
				if ($(this).attr('disabled') != 'disabled') {
					toggletimer($(this));
				}
			});
			if ($('#running-button').attr('disabled') != 'disabled') {
				$("#entry-button").hide();
			} else {
				$("#running-button").hide();
			}
			

			


			
			
			
		}); 
	</script> */ ?>
	<?php $this->load->view('includes/footer'); ?>
