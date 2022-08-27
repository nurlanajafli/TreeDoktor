<!--Profile Badge -->
<aside class="aside-lg bg-light lter b-r col-md-4 col-xs-12 col-sm-6 profile-badge">
	<section class="vbox">
		<section class="scrollable">
			<div class="wrapper">
				<div class="clearfix m-b">
					<a href="#" class="pull-left thumb m-r">
						<img src="<?php echo ($this->session->userdata('user_pic')/* && is_bucket_file(PICTURE_PATH . $this->session->userdata('user_pic'))*/) ? base_url(PICTURE_PATH) . $this->session->userdata('user_pic') : base_url('assets/pictures/avatar_default.jpg'); ?>">
					</a>

					<div class="" style="overflow: hidden;">
						<div class="h3 m-t-xs m-b-xs"
						     style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;"><?php echo($this->session->userdata('firstname')); ?>
							&nbsp;<?php echo($this->session->userdata('lastname')); ?></div>
						<small class="text-muted">Last
							Login:<br><?php echo getDateTimeWithDate($this->session->userdata('user_last_login'), 'Y-m-d H:i:s', true); ?></small>
					</div>
				</div>
				<?php /*<div class="panel wrapper panel-success">
					<div class="row">
						<div class="col-xs-12 text-center">
							<span class="m-b-xs h4 block"><?php echo $this->session->userdata('rate'); ?></span>
							<small class="text-muted">Rate</small>
						</div>
					</div>
				</div>*/ ?>
				<?php if($this->session->userdata('CL') || $this->session->userdata('user_type') == "admin") : ?>
				<div class="btn-group btn-group-justified m-b">
					<a href="<?php echo base_url('clients/new_client'); ?>" class="btn btn-success btn-rounded" style="font-size: 12.5px;">
						Add Client
					</a>
					<a href="<?php echo base_url('leads/map'); ?>" class="btn btn-dark btn-rounded" style="font-size: 12.5px; ">
						View Leads
					</a>
				</div>
				<?php endif; ?>
				<div class="btn-group btn-group-justified m-b">
					<a href="<?php echo base_url('schedule'); ?>" class="btn btn-info btn-rounded" style="font-size: 12.5px;">
						Crew Schedule
					</a>
					<a href="<?php echo base_url('schedule/office'); ?>" class="btn btn-danger btn-rounded" style="font-size: 12.5px; padding-left: 6px;">
						Office Schedule
					</a>
				</div>
				<div class="btn-group btn-group-justified m-b">
					<a href="<?php echo base_url('workorders'); ?>" class="btn btn-warning btn-rounded" style="font-size: 12.5px;">
						Workorders
					</a>
					<a href="<?php echo base_url('estimates/own'); ?>" class="btn btn-info btn-rounded" style="font-size: 12.5px; padding-left: 6px;">
						Own Estimates
					</a>
				</div>
				<?php if (($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1) && $employees_autologout && !empty($employees_autologout)) : ?>
					<section class="panel panel-default">
						<header class="panel-heading">
                            <ul class="nav nav-pills pull-right">
                                <li>
                                    <a href="#" class="panel-toggle text-muted active"><i class="fa fa-caret-down text-active"></i><i class="fa fa-caret-up text"></i></a>
                                </li>
                            </ul>
							<strong>
                                Auto Logout
                            </strong>
						</header>
                        <div class="panel-body clearfix collapse p-n">
                            <table class="table table-striped m-b-none">
                                <tbody id="empAutologout">
                                <?php foreach ($employees_autologout as $key => $employee) : ?>
                                    <tr>
                                        <td class="text-left" style="position: relative;">
										<a target="_blank" class="headline-dash__link" href="<?php echo base_url('employees/payroll/' . $employee->employee_id . '/' . $employee->worked_payroll_id); ?>">
                                                <?php echo $employee->emp_name . ' (' . getDateTimeWithDate($employee->worked_date, 'Y-m-d') . ')'; ?>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
					</section>
				<?php endif; ?>
				<?php if (($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1) && $logout_from_office && !empty($logout_from_office)) : ?>
					<section class="panel panel-default empOfficelogout">
						<header class="panel-heading">
                            <ul class="nav nav-pills pull-right">
                                <li>
                                    <a href="#" class="panel-toggle text-muted active"><i class="fa fa-caret-down text-active"></i><i class="fa fa-caret-up text"></i></a>
                                </li>
                            </ul>
                            <strong>Logout not from the office</strong>
						</header>
                        <div class="panel-body clearfix collapse p-n">
                            <table class="table table-striped m-b-none">
                                <tbody>
                                <?php foreach ($logout_from_office as $key => $employee) : ?>
                                    <tr>
                                        <td class="text-left" style="position: relative;">
                                            <?php if($employee->logout_lat !== NULL && $employee->logout_lat !== '0' && $employee->logout_lat !== 0) : ?>
                                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $employee->logout_lat; ?>,<?php echo $employee->logout_lon;?>" target="_blank">
                                            <?php elseif($employee->login_lat !== NULL && $employee->login_lat !== '0' && $employee->login_lat !== 0) : ?>
                                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $employee->login_lat; ?>,<?php echo $employee->login_lon;?>" target="_blank">
                                            <?php endif; ?>
                                                <?php echo $employee->name . ' (' . date(getDateFormat(), strtotime($employee->log_date)) . ')'; ?>
                                            </a>
                                            <input type="checkbox" name="position_check" class="pull-right" onclick="checkPos('<?php echo $employee->id; ?>', '<?php echo date(getDateFormat(), strtotime($employee->log_date)); ?>', $(this))">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
					</section>
				<?php endif; ?>

				<?php if($this->session->userdata('CL') == 1 || $this->session->userdata('CL') == 2 || $this->session->userdata('user_type') == "admin") : ?>
				<section class="panel panel-default">
					<header class="panel-heading">
                        <ul class="nav nav-pills pull-right">
                            <li>
                                <a href="#" class="panel-toggle text-muted active"><i class="fa fa-caret-down text-active"></i><i class="fa fa-caret-up text"></i></a>
                            </li>
                        </ul>
                        <strong>My Leads</strong>
					</header>
                    <div class="panel-body clearfix collapse p-n">
                        <table class="table table-striped m-b-none">
                            <?php if ($my_leads && !empty($my_leads)) : ?>
                                <?php foreach ($my_leads as $lead) : ?>
                                    <tr>
									<td style="text-align:left"><a class="headline-dash__link"
                                                                   href="<?php echo base_url($lead->lead_no); ?>"><?php echo $lead->lead_no; ?></a>
                                        <p>
                                            <?php echo $lead->client_name;?>, <?php echo $lead->lead_address;?>, <?php echo $lead->lead_city;?>, <?php echo $lead->lead_state;?><br>
                                            <a href="#"
                                                   class="<?php if($lead->cc_phone == numberTo($lead->cc_phone)): ?>text-danger<?php else: ?>createCall<?php endif;?>"
                                                   data-client-id="<?php echo $lead->client_id; ?>"
                                                   data-number="<?php echo substr($lead->cc_phone, 0, 10); ?>">
                                                    <?php echo numberTo($lead->cc_phone); ?>
                                            </a>
                                        </p>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td style="color:#FF0000;">No record found</td>
							</tr>
						<?php endif; ?>
					</table>
                    </div>
				</section>
			<?php endif; ?>
				
				<?php if($this->session->userdata('TSKS') || $this->session->userdata('user_type') == "admin") : ?>
				<section class="panel panel-default">
					<header class="panel-heading">
                        <ul class="nav nav-pills pull-right">
                            <li>
                                <a href="#" class="panel-toggle text-muted active"><i class="fa fa-caret-down text-active"></i><i class="fa fa-caret-up text"></i></a>
                            </li>
                        </ul>
                        <strong>My Tasks</strong>
					</header>
                    <div class="panel-body clearfix collapse p-n">
                        <table class="table table-striped m-b-none">
                            <?php if ($my_tasks && !empty($my_tasks)) : ?>
                                <?php foreach ($my_tasks as $task) : ?>
                                    <tr>
                                        <td>
                                            <div id="<?php echo $task['task_id']; ?>" class="modal fade overflow" tabindex="-1"
                                                     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content panel panel-default p-n">
                                                            <header class="panel-heading">Task
                                                                for&nbsp;<a target="_blank" href="<?php echo base_url($task['task_client_id']); ?>"><?php echo $task['client_name']; ?></a></header>
                                                            <div class="modal-body">
                                                                <div class="p-10">
                                                                    <span>Task Date: <?php echo getDateTimeWithDate($task['task_date_created'], 'Y-m-d'); ?></span><br>
                                                                    <form action="#">
                                                                        <span class="task_status">Task Status:
                                                                            <select name="task_status_change" class="task_status_change form-control">
                                                                                <option value="new" <?php if($task['task_status'] == 'new') : ?>selected="selected"<?php endif; ?>>
                                                                                    New
                                                                                </option>
                                                                                <option value="canceled" <?php if($task['task_status'] == 'canceled') : ?>selected="selected"<?php endif; ?>>
                                                                                    Canceled
                                                                                </option>
                                                                                <option value="done" <?php if($task['task_status'] == 'done') : ?>selected="selected"<?php endif; ?>>
                                                                                    Done
                                                                                </option>

                                                                            </select>
                                                                        </span><br>
                                                                        <textarea placeholder="This field is required..." name="new_status_desc" class="new_status_desc form-control" ></textarea><br>
    <!--																	<button class="pull-right btn btn-success submit" style="display:none; padding: 6px 12px;">-->
    <!--																		<span class="btntext">Save111</span>-->
    <!--																		<img src="--><?php //echo base_url("assets/img/ajax-loader.gif"); ?><!--" style="display: none;width: 32px;" class="preloader">-->
    <!--																	</button>-->
    <!--																	<button class="pull-right btn submit" style="display:none; padding: 6px 12px; margin-right:10px;">Close</button>-->

                                                                    </form><br><br>
                                                                    <?php if($task['ass_firstname']) : ?>
                                                                        <span>Task Date: <?php echo getDateTimeWithDate($task['task_date'], 'Y-m-d') . ' ' . getTimeWithDate($task['task_start'], 'H:i:s') . ' - ' .  getTimeWithDate($task['task_end'], 'H:i:s'); ?></span><br>
                                                                    <?php endif; ?>
                                                                    <span>Task Category: <?php echo ucfirst($task['category_name']); ?></span><br>
                                                                    <?php if($task['ass_firstname']) : ?>
                                                                    <span>Task Assigned To: <?php echo $task['ass_firstname'] . ' ' . $task['ass_lastname']; ?></span><br>
                                                                    <?php endif; ?>
                                                                    <span>Task Created By: <?php echo $task['firstname'] . ' ' . $task['lastname']; ?></span><br>

                                                                    <div class="line line-dashed line-lg"></div>
                                                                    <?php echo $task['task_desc']; ?>
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                                                <button class="btn btn-success submit" style="padding: 6px 12px; margin-left: 5px">
                                                                    <span class="btntext">Save</span>
                                                                    <img src="<?php echo base_url("assets/img/ajax-loader.gif"); ?>" style="display: none;width: 32px;" class="preloader">
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            <!--Triggers -->
										<a href="#<?php echo $task['task_id']; ?>" role="button" class="tasks-list-dash"
                                               data-toggle="modal"  style="text-align:center" ><?php echo $task['client_name'] ? $task['client_name'] : 'Office'; ?><br>
                                               <span style="font-size: 11px;">
                                                   <?php echo getDateTimeWithDate($task['task_date'], 'Y-m-d');?>
                                                   <?php echo getTimeWithDate($task['task_start'], 'H:i:s');?> - <?php echo getTimeWithDate($task['task_end'], 'H:i:s');?>
                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td style="color:#FF0000;">No record found</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
				</section>
				<?php endif; ?>

				<?php if($this->session->userdata('STP') != "3") : ?>
				<section class="panel panel-default">
					<header class="panel-heading">
                        <ul class="nav nav-pills pull-right">
                            <li>
                                <a href="#" class="panel-toggle text-muted active"><i class="fa fa-caret-down text-active"></i><i class="fa fa-caret-up text"></i></a>
                            </li>
                        </ul>
                        <strong>People Online</strong>
					</header>
                    <div class="panel-body clearfix collapse p-n">
                        <table class="table table-striped m-b-none">
                            <tbody id="empOnline">
                            <?php if ($employees_online) : ?>
                                <?php foreach ($employees_online as $key => $employee) : ?>
                                    <tr>
                                        <td class="text-left" style="position: relative;">
                                            <span class="empNumber"><?php echo ($key + 1) . ') '; ?></span>
                                            <?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1) : ?>
                                            <a href="#" class="employeeOnline"
                                               title="<?php echo $employee->emp_name; ?><button type=&quot;button&quot;
                                                   class=&quot;m-l-sm close pull-right&quot; data-dismiss=&quot;popover&quot;>
                                                   ×</button>"
                                               data-html="true" data-placement="top"
                                               data-content="<div class='text-center'>
                                                <input type='time' class='logOutTime span2 m-n' value='<?php echo date(getPHPTimeFormatWithOutSeconds()); ?>'>
                                                <a class='signOut btn btn-xs btn-info m-l-xs' title='Sign Out <?php echo $employee->emp_name; ?>'
                                                    data-login_id='<?php echo $employee->login_id; ?>'
                                                    data-employee_id='<?php echo ($employee->login_user_id) ? $employee->login_user_id : $employee->login_employee_id; ?>'>
                                                    <i class='fa fa-sign-out'></i>
                                                </a>
                                                </div>">
                                            <?php endif; ?>
                                            <?php $late = 0; ?>
                                            <span class="small
                                            <?php if(isset($employee->mdl_worked) && isset($employee->mdl_worked->worked_late) && $employee->mdl_worked->worked_late) :?>
                                             text-danger
                                             <?php $late = intval((strtotime($employee->mdl_worked->worked_start) - strtotime($employee->emp_start_time)) / 60 / 60) . ':' . str_pad(((strtotime($employee->mdl_worked->worked_start) - strtotime(date($employee->emp_start_time))) / 60 % 60), 2, '0', STR_PAD_LEFT); ?>
                                             <?php endif; ?>
                                             ">
                                                <?php echo $employee->emp_name . ' (' . date(getPHPTimeFormatWithOutSeconds(), strtotime($employee->login)) . ')'; ?>
                                                <?php if($late) : ?>
                                                    - <?php echo $late; ?>
                                                <?php endif; ?>
                                            </span>
                                            <?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1) : ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td style="color:#FF0000;">No record found</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
				</section>
				<?php endif; ?>
			</div>
		</section>
	</section>
</aside>
<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('RPS_PR') == 1) : ?>
	<script>
		$(document).ready(function () {
			$('.submit').click(function(){
			var id = $(this).parents('.modal.fade.overflow').attr('id');
			if($(this).text() == 'Close')
			{
				$('#'+ id).find('.task_status_change').val('new');
				$('#'+ id).find('.new_status_desc').css('display', 'none');
				$('#'+ id).find('.submit').css('display', 'none');
			}
			else
			{
				status = $('#'+ id).find('.task_status_change').val();
				text = $('#'+ id).find('.new_status_desc').val();
				if(text == '')
				{
					alert('Description is required!');	
					return false;
				}
				else
				{
					$.post(baseUrl + 'tasks/ajax_change_status', {id : id, status : status, text : text}, function (resp) {
						if(resp.status == 'ok')
							location.reload();
						else
							alert(resp.msg);
					}, 'json');
				}
			}
			
			return false;
		});
			$(document).on('click', '.signOut', function () {
				if(confirm('Are you sure ' + $(this).attr('title') + ' ' + $(this).prev().val() + ' ?'))
				{
					var employee_id = $(this).data('employee_id');
					var login_id = $(this).data('login_id');
					var time = $(this).prev().val();
					var obj = $(this).parents('tr:first');
					$.post(baseUrl + 'dashboard/ajax_employee_logout', {login_id: login_id, time:time, employee_id: employee_id}, function (resp) {
						$(obj).fadeOut(function () {
							$(obj).remove();
							if(!$('.empNumber').length)
								$('#empOnline').html('<tr><td style="color:#FF0000;">No record found</td></tr>');
							else
							{
								$.each($('.empNumber'), function (key, val) {
									$(val).text((key + 1) + ') ');
								});
							}
						});
					});
					return false;
				}
			});
			$('.employeeOnline').popover();
			$('.employeeOnline').on('click', function (e) {
				$('.employeeOnline').not(this).popover('hide');
			});
		});
		$(document).on('change', '.task_status_change', function(){
			var id = $(this).parents('.modal.fade.overflow').attr('id');
			if($(this).val() == 'new')
			{
				$('#'+ id).find('.new_status_desc').css('display', 'none');
				$('#'+ id).find('.submit').css('display', 'none');
				
			}
			else
			{
				$('#'+ id).find('.new_status_desc').css('display', 'block');
				$('#'+ id).find('.submit').css('display', 'inline-block');
			}
			return false;
		});
		function checkPos(id, date, obj)
		{
			if($(obj).prop('checked') && confirm('Do you want to remove this logout not from the office?'))
			{
				$.post(baseUrl + 'dashboard/ajax_logout_not_office', {id:id, date:date}, function (resp) {
					if(resp.status == 'ok')
					{
						$(obj).parents('tr').remove();
						
						if(!$('.empOfficelogout').find('tr').length)
							$('.empOfficelogout').remove();
					}
				}, 'json');
			}
		}
	</script>
<?php endif; ?>
<!--/Profile Badge -->
