<?php $this->load->view('includes/header'); ?>
<!-- reports css -->
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/reports.css">

<!-- for select employee plugin -->
<script src="<?php echo base_url(); ?>assets/js/bootstrap-select.js"></script>

<!-- for timepicker -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/bootstrap-datetimepicker.js"></script>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
	<li class="active">Payroll</li>
</ul>
<!--
            <span style="color:red;"><?php echo validation_errors(); ?></span> -->


<section class="col-sm-12">
<section class="panel panel-default p-n">

	<div class="p-left-5">
		<div class="form-inline">
			<form name="frmSearchEmplouee" id="searcEmployee" method="POST" action="<?php echo base_url() . 'reports/payroll';?>">
				<input type="hidden" name="generate_pdf" id="generate_pdf" value=""/>
				<input type="hidden" name="get_data_by_date" id="get_data_by_date" value="<?php echo $curdate ?? '' ?>"/>
				<input type="hidden" name="which_week" id="which_week" value="this-week"/>
				<!-- SELECT EMPLOYEE -->
				<div class="form-group" style="margin: 10px;">
					<label for="id_select">Select Employee</label>
					<select id="id_select" class="form-control" name="selEmmployee" data-live-search="true">
						<option value=""></option>
						<?php if (!empty($employees)) { ?>
						<?php $status = NULL; ?>
						<?php foreach ($employees as $kk => $vv) { ?>
						<?php if ($vv["emp_status"] != $status) : ?>
						<?php $status = $vv["emp_status"]; ?>
						<?php if ($kk) : ?>
							</optgroup>
						<?php endif; ?>
						<optgroup label="<?php echo ucfirst($status); ?>">
							<?php endif; ?>
							<option value="<?php echo $vv["employee_id"] ?>"
							        <?php if (isset($employee_row["employee_id"]) && @$employee_row["employee_id"] == $vv["employee_id"]) { ?>selected<?php } ?>><?php echo ucwords($vv["emp_name"]) ?></option>
							<?php } ?>
							<?php } ?>
					</select>
					<button type="submit" id="btnsubmit" class="btn" style="margin-top: 0px; margin-left: 10px;">
						<i class="fa fa-book"></i>&nbsp;Get Report
					</button>
				</div>
			</form>
		</div>
</section>
<?php $style = ""; ?>

<?php if (!empty($employee_row)) { ?>
<section class="col-sm-12 panel panel-default p-n">
<!-- Client header -->
<header class="panel-heading"><?php echo $page_title ?? '' ?></header>
<div class="top-buttons">
	<input type="hidden" name="pre-week-val" id="pre-week-val" value="<?php echo $weekdays["last_week_date"] ?? ''; ?>"/>
	<input type="button" value="<< Previous Weeks" id="pre-week" class="btn"/>
	<input type="hidden" name="this-week-val" id="this-week-val" value="<?php echo date("Y-m-d"); ?>"/>
	<input type="button" value="Current Weeks" id="this-week" class="btn"/>
	<input type="hidden" name="next-week-val" id="next-week-val"
	       value="<?php echo $next_weekdays["next_week_date"] ?? ''; ?>"/>
	<input type="button" value="Next Weeks >>" id="next-week" class="btn"/>
</div>


<div class="week-outer">
	<div class="top-blcks">
		<div class="wrapper">
			<div class="labelt">Employee:</div>
			<div class="value"><?php echo @$employee_row["emp_name"] ?></div>
			<div style="clear: both"></div>
		</div>

		<!--<div class="wrapper">
			<div class="labelt">Notes:</div>
			<div class="value">&nbsp;</div>
		</div>-->
		<div style="clear: both"></div>
	</div>
	<?php // if(!empty($emp_data)) { ?>
	<?php $total_time_two_weeks = 0.00; ?>
	<?php $breaks = 0.00; ?>
	<?php $bonusesSum = 0; ?>
	<?php $totalToPay = 0; ?>
	<?php for ($e = 0;
	$e < 2;
	$e++) { ?>
	<?php if ($e == 0) { ?>
		<?php $weekdays_data = $weekdays["total_week_days"]; ?>
		<?php $week_label = "WK1"; ?>
	<?php } else { ?>
		<?php $weekdays_data = $next_weekdays["total_week_days"]; ?>
		<?php $week_label = "WK2"; ?>
	<?php } ?>
	<?php if (!empty($weekdays_data)) { ?>
	<div class="week-days-heading">
		<div class="labelt">Week Starting:</div>
		<div class="value"><?php echo @date("m/d/Y", strtotime($weekdays_data[0])); ?></div>
		<div style="clear: both"></div>
		<div class="labelt">Week Ending:</div>
		<div class="value"><?php echo @date("m/d/Y", strtotime($weekdays_data[6])); ?></div>
		<div style="clear: both"></div>
	</div>
	<div style="clear: both;"></div>
	<div class="week-wrapper">
		<div class="week-outer-tbl">
			<div class="td-right"><?php echo $week_label ?></div>
			<div class="td-right-with-bottom-border">PD</div>
			<div class="td-normal">Time In</div>
			<div class="td-normal">Time Out</div>
			<div class="td-normal">Bonuses</div>
			<div class="td-normal">No Lunch</div>
			<div class="td-normal">Worked</div>
			<?php if(isAdmin()) : ?><div class="td-normal">Hourly Rate</div><?php endif; ?>
			<div class="td-normal">Teams</div>
		</div>
		<?php $total_per_week = 0.00; ?>
		<?php foreach ($weekdays_data as $w1kk => $w1vv) { ?>
		<?php $hightlight = ""; ?>
		<?php if ($w1vv == date("Y-m-d")) { ?>
			<?php $hightlight = "border: 1px solid green;"; ?>
		<?php } ?>
		<div class="week-inner-tbl">
			<div class="td-normal">
				<!-- day name -->
				<?php echo date("D", strtotime($w1vv)); ?>&nbsp;
			</div>
			<div class="td-normal-blue">
				<!-- date -->
				<?php echo $w1vv ?>&nbsp; </div>
			<!-- first login time -->
			<div class="td-normal-value">
				<?php  if(isset($emp_data[$w1vv][0]["login_time"])) : ?>
					<a href="#emp_time_details_<?php echo strtotime($w1vv) ?>" class="edit-time" role="button"
					   class="btn btn-success btn-block" data-toggle="modal" data-backdrop="static" data-keyboard="false">
							<?php echo isset($emp_data[$w1vv][0]["login_time"]) ? date('H:i', strtotime($emp_data[$w1vv][0]["login_time"])) : "00.00" ?>
							&nbsp; 
					</a>
				<?php elseif(isset($absences[strtotime($w1vv)])) : ?>
					- 
			
				<?php else : ?>
				<a href="#emp_time_details_<?php echo strtotime($w1vv) ?>" class="edit-time" role="button"
					   class="btn btn-success btn-block" data-toggle="modal" data-backdrop="static" data-keyboard="false">
					"00.00"
				</a>
				<?php endif; ?>
			</div>
			<?php $total_time_per_emp = 0; ?>
			<?php if (!empty($emp_data[$w1vv])) { ?>
				<?php foreach ($emp_data[$w1vv] as $ev1) { ?>
					<?php if($ev1['logout_time'] != '0000-00-00 00:00:00') {?>
					<?php $total_time_per_emp = $total_time_per_emp + round(((strtotime($ev1['logout_time']) - strtotime($ev1['login_time'])) / 3600), 2); ?>
				<?php } } ?>
			<?php } ?>
			<!-- first logout time -->
			<div class="td-normal-value">
				<?php if(isset($emp_data[$w1vv][count(@$emp_data[$w1vv]) - 1]["logout_time"])) : //countOk ?>
					<a href="#emp_time_details_<?php echo strtotime($w1vv) ?>" class="edit-time" role="button"
					   class="btn btn-success btn-block" data-toggle="modal" data-backdrop="static" data-keyboard="false">
						<?php echo isset($emp_data[$w1vv][count(@$emp_data[$w1vv]) - 1]["logout_time"]) ? date('H:i', strtotime($emp_data[$w1vv][count($emp_data[$w1vv]) - 1]["logout_time"])) : "00:00" //countOk ?>
						&nbsp;
					</a>
				<?php elseif(isset($absences[strtotime($w1vv)])) : ?>
						-
				
				<?php else : ?>
					<a href="#emp_time_details_<?php echo strtotime($w1vv) ?>" class="edit-time" role="button"
					   class="btn btn-success btn-block" data-toggle="modal" data-backdrop="static" data-keyboard="false">
					"00:00"
					</a>
				<?php endif; ?>
			</div>
			<?php if ($total_time_per_emp >= 5 && !$emp_data[$w1vv][0]["no_lunch"]) { ?>
				<?php $breaks = $breaks + 0.50; ?>
			<?php } ?>
			<div class="td-normal-value" style="max-height: 19px;">
				<?php $workedToday = ($total_time_per_emp >= 5 && !$emp_data[$w1vv][0]["no_lunch"]) ? ($total_time_per_emp - 0.5) : $total_time_per_emp; ?>
				<?php $totalToPay += (isset($emp_data[$w1vv][0]["employee_hourly_rate"])) ? round($workedToday * $emp_data[$w1vv][0]["employee_hourly_rate"], 2) : 0; ?>
				<?php  if(isset($emp_data[$w1vv]) && isset($bonuses[$w1vv])) : ?>
					<?php $bonus = round(($workedToday * $emp_data[$w1vv][0]["employee_hourly_rate"]) * ($bonuses[$w1vv] / 100), 2); ?>
					<?php $bonusesSum += $bonus; ?>
					<?php echo money($bonus); ?>
				<?php else : ?>
					<?php echo money(0); ?>
				<?php endif; ?>
			</div>
			<div class="td-normal-value" style="max-height: 19px;">
				<input type="checkbox" <?php if(date('Y-m-d') < $w1vv && !isset($emp_data[$w1vv][0]) ) : ?>disabled="disabled"<?php endif; ?> name="no_lunch" class="lunch" data-date="<?php echo $w1vv; ?>" <?php if(isset($emp_data[$w1vv][0]["no_lunch"]) && $emp_data[$w1vv][0]["no_lunch"] == TRUE) : ?>checked="checked"<?php endif; ?> value="1">
			</div>

			<div width="20%" class="td-normal-value" <?php if(isset($absences[strtotime($w1vv)])) : ?>style="color:red;"<?php endif; ?>>
				<?php if(isset($emp_data[$w1vv][0]["login_time"]) && $total_time_per_emp > 0) : ?>
					<?php if(!$emp_data[$w1vv][0]['no_lunch'] && $total_time_per_emp >= 5) : ?>
						<?php $total_time_per_emp = $total_time_per_emp - 0.5; ?>
					<?php endif;  ?>
					<?php echo $total_time_per_emp; ?> hrs.
				<?php elseif(isset($absences[strtotime($w1vv)])) : ?>
					<?php echo  $absences[strtotime($w1vv)]['reason_name']; ?>
				<?php else : ?>
					0 hrs.
					<?php //echo $total_time_per_emp; ?> 
				<?php endif; ?>
			</div>
			<?php if(isAdmin()) : ?><!------------------------------------------------------------------------------------------------------------->
				<div width="20%" class="td-normal-value">
					<a href="#emp_time_details_<?php echo strtotime($w1vv) ?>" class="edit-time" role="button"
					   class="btn btn-success btn-block" data-toggle="modal" data-backdrop="static" data-keyboard="false">
					
					
					<?php if(isset($emp_data[$w1vv][0]["employee_hourly_rate"])) : ?>
						$ <?php echo $emp_data[$w1vv][0]['employee_hourly_rate']; ?> 
					<?php else : ?>
						$ 0 
					<?php endif; ?>
					</a>
				</div>
			<?php endif; ?>
			<div width="20%" class="td-normal-value">
				
				
				<?php if(isset($teams[$w1vv])) : ?>
					<?php foreach($teams[$w1vv] as $key=>$emp) : ?>
						<?php if($emp['employee_id'] != $employee_row['employee_id']) : ?>
							<a href="<?php echo base_url() . 'reports/payroll/' . $emp['employee_id'] . '/' . ($get_data_by_date ?? '') . '/' . ($which_week ?? ''); ?>" target="_blank" class="team_emp" role="button"
								class="btn btn-success btn-block"  data-backdrop="static" data-keyboard="false">
									<?php echo $emp['emp_name']; ?>
							</a><br>
						<?php endif; ?>
					<?php endforeach; ?> 
				<?php else : ?>
					-
				<?php endif; ?>
				</a>
			</div>
			<!-- Employee Login Details Popup Window Code Start -->
			<div id="emp_time_details_<?php echo strtotime($w1vv); ?>" class="modal fade" tabindex="-1" role="dialog"
			     aria-labelledby="myModalLabel" aria-hidden="false" style="color: #000;">
				<div class="modal-dialog">
					<div class="modal-content panel panel-default p-n">
						<header class="panel-heading">Time Login Details - <?php echo $w1vv ?><span
								class="btn btn-success btn-xs pull-right addEmpRow" data-date="<?php echo $w1vv; ?>"
								data-emp_id="<?php echo $employee_row["employee_id"]; ?>"><i
									class="fa fa-plus"></i></span></header>
						<div class="modal-body">
							<table class="table table-bordered table-hover tablesorter">
								<thead>
								<tr>
									<th class="header">Login In Time</th>
									<th class="header">Login Out Time</th>
									<?php if(isAdmin()) : ?>
										<th class="header">Hourly Rate</th>
									<?php endif; ?>
									<th class="header">Time Diff</th>
									<th class="header">Photos</th>
									<th class="header">Action</th>
								</tr>
								</thead>
								<tbody class="emp_data">
								<!-- login details popup -->
								<?php if (!empty($emp_data[$w1vv])) { ?>
									<?php $total_time_diff = 0.00; ?>
									<?php foreach ($emp_data[$w1vv] as $emp_time_details_key => $emp_time_details_val) { ?>
										<?php $total_time_diff = $total_time_diff + $emp_time_details_val["time_diff"]; ?>
										<tr>
											<td id="in-time-<?php echo $emp_time_details_val["id"]; ?>"
											    title="<?php echo $emp_time_details_val["login_time"] ?>">
												<?php if (!empty($emp_time_details_val["login_image"])) { ?>
													<a href="#" class="fancybox1">
														<img style="display: none;"
														     src="<?php echo @$emp_time_details_val["login_image"] ?>">
													</a>
												<?php } ?>
												<input type="time"
												       id="inp-login-time-<?php echo $emp_time_details_val["id"]; ?>"
												       value="<?php echo isset($emp_time_details_val["login_time"]) ? date('H:i', strtotime($emp_time_details_val["login_time"])) : "00.00" ?>"
												       class="span2">
											</td>
											<td id="out-time-<?php echo $emp_time_details_val["id"]; ?>"
											    title="<?php echo $emp_time_details_val["logout_time"] ?>">
												<?php if (!empty($emp_time_details_val["logout_image"])) { ?>
													<a href="#" class="fancybox1">
														<img style="display: none;"
														     src="<?php echo @$emp_time_details_val["logout_image"] ?>">
													</a>
												<?php } ?>
												<input type="time"
												       id="inp-logout-time-<?php echo $emp_time_details_val["id"]; ?>"
												       value="<?php echo isset($emp_time_details_val["logout_time"]) && $emp_time_details_val["logout_time"] != '0000-00-00 00:00:00' ? date('H:i', strtotime($emp_time_details_val["logout_time"])) : "" ?>"
												       class="span2">
											</td>
											<?php if(isAdmin()) : ?>
												<td id="hourly-rate-<?php echo $emp_time_details_val["id"]; ?>"
													title="<?php echo $emp_time_details_val["employee_hourly_rate"] ?>">
													
													$ <input type="text"
														   id="inp-hourly-rate-<?php echo $emp_time_details_val["id"]; ?>"
														   value="<?php echo $emp_time_details_val["employee_hourly_rate"] ?>"
														   class="span2" style="width:50px">
												</td>
											<?php endif; ?>
											<td><?php echo $emp_time_details_val["time_diff"] ?> hrs.</td>
											<td>
												<?php if (isset($emp_data[$w1vv][$emp_time_details_key]["login_image"]) && $emp_data[$w1vv][$emp_time_details_key]["login_image"]) : ?>
													<a data-lightbox="works"
													   href="<?php echo @$emp_data[$w1vv][$emp_time_details_key]["login_image"]; ?>">
														<img width="40px"
														     src="<?php echo @$emp_data[$w1vv][$emp_time_details_key]["login_image"]; ?>">
													</a>
												<?php endif; ?>
												<?php if (isset($emp_data[$w1vv][$emp_time_details_key]["logout_image"]) && $emp_data[$w1vv][$emp_time_details_key]["logout_image"]) : ?>
													<a data-lightbox="works"
													   href="<?php echo @$emp_data[$w1vv][$emp_time_details_key]["logout_image"]; ?>">
														<img width="40px"
														     src="<?php echo @$emp_data[$w1vv][$emp_time_details_key]["logout_image"]; ?>">
													</a>
												<?php endif; ?>
											</td>
											<td width="80px">
												<input type="hidden" name="inp-emp-id"
												       id="inp-emp-id-<?php echo $emp_time_details_val["id"]; ?>"
												       value="<?php echo $emp_time_details_val["employee_id"]; ?>">
												<input type="hidden" name="inp-login-date"
												       id="inp-login-date-<?php echo $emp_time_details_val["id"]; ?>"
												       value="<?php echo $w1vv; ?>">
												<input type="hidden" name="inp-id"
												       id="inp-id-<?php echo $emp_time_details_val["id"]; ?>"
												       value="<?php echo $emp_time_details_val["id"]; ?>">
												<a href="#" class="btn btn-xs btn-success save_login_time"
												   id="<?php echo $emp_time_details_val["id"]; ?>" title="Save"><i
														class="fa fa-edit"></i></a>
												<a href="#" class="btn btn-xs btn-danger delete-emp-details"
												   id="<?php echo $emp_time_details_val["id"]; ?>" title="Delete"><i
														class="fa fa-trash-o"></i></a>
											</td>
										</tr>
									<?php } ?>
									<tr>
										<th class="header">&nbsp;</th>
										
										<th class="header">Total Time</th>
										<th class="header"><?php echo $total_time_diff ?> hrs.</th>
										<?php if(isAdmin()) : ?><th class="header">&nbsp;</th><?php endif; ?>
										<th class="header">&nbsp;</th>
										<th class="header">&nbsp;</th>
									</tr>
								<?php } else { ?>
								<tr>
									<td colspan="<?php if(isAdmin()) : ?>6<?php else : ?>5<?php endif;?>" class="text-error" align="center">No time details found.
						</div>
						<?php } ?>
						<!-- login details popup ends -->
						</tbody>
						</table>
					</div>
					<div class="modal-footer">
						<div class="pull-right">
							<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<? /*<div class="emp-time-details" id="emp_time_details_<?php echo strtotime($w1vv); ?>">
													<h3>Time Login Details - <?php echo $w1vv ?></h3>
													<div class="add-emp-details" style="cursor: pointer; float: left" id="<?php echo $w1vv; ?>"><img src="<?php echo base_url(); ?>assets/img/list2-add.png" /></div>
													<div class="close-emp-details" id="close-time-details-<?php echo strtotime($w1vv); ?>"><img src="<?php echo base_url(); ?>assets/img/close.png" /></div>
													<div style="clear: both"></div>
													<div class="table-responsive">
													
												  </div>		
												</div>*/
		?>
		<!-- Employee Login Details Popup Window Code Ends -->

	</div>
<?php $total_per_week = $total_per_week + $total_time_per_emp; ?>
<?php } // end foreach ?>
	<div class="week-outer-tbl">
		<div class="td-right">&nbsp;</div>
		<?php if(isAdmin()) : ?><div class="td-right-with-bottom-border">&nbsp;</div><?php endif; ?>
		<div class="td-right-with-bottom-border">&nbsp;</div>
		<div class="td-normal">Total Hours</div>
		<div class="td-normal">Worked</div>
		<div class="td-right-with-bottom-border"><?php echo $total_per_week ?> hrs.</div>
		<div class="td-normal">&nbsp;</div>
		<div class="td-normal">&nbsp;</div>
		<div class="td-normal-without-bottom-border">&nbsp;</div>
	</div>
	<div style="clear: both;"></div>
</div>
<?php $total_time_two_weeks = $total_time_two_weeks + $total_per_week; ?>
<?php } ?>
<?php } //end foreach empdata ?>


<?php if (isAdmin()) : ?>
	<div class="row1">
		<div class="grid_12 filled_white" style="width: 50%; margin-top: 10px;">
			<div class="p-10">
				<table width="70%" class="summary-tbl">
					<tr>
						<td width="40%">Total for Two Weeks:</td>
						<td class="td-normal" width="30%"><?php echo $total_time_two_weeks ?> hrs</td>
					</tr>
					<tr>
						<td>Hourly Rate:</td>
						<td><?php echo money(isset($employee_row["emp_hourly_rate"])?$employee_row["emp_hourly_rate"]:0); ?>/hr</td>
					</tr>
					<tr>
						<td>Late:</td>
						<td></td>
					</tr>
					<tr>
						<td>Day of Payday:</td>
						<td><?php echo $next_weekdays["total_week_days"][6]; ?></td>
					</tr>
					<tr>
						<td>Total Pay:</td>
						<td><?php echo money(getAmount($totalToPay)); ?></td>
					</tr>
					<?php /*<tr>
						<td>Payroll Bonuses:</td>
						<td>
							$ <?php echo sprintf("%01.2f", $bonusesSum); ?></td>
					</tr>
					<tr>
						<td>Collected Bonuses:<br>
						(<?php echo $bonusesDates['from']; ?> - <?php echo $bonusesDates['to']; ?>)
						</td>
						<td>
							$ <?php echo sprintf("%01.2f", $collectedBonuses); ?>
						</td>
					</tr>*/ ?>
				</table>
			</div>
		</div>
	</div>


<?php if (!empty($employee_row)) { ?>
<div class="row m-top-10">
	<div class="grid_12">
		<div class="p-10">
			<div style="margin: 20px; float: right;"><input type="button" value="Generate PDF" id="btn_generate_pdf"
			                                                class="btn"/></div>

			<?php } ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php } // end if employee row data ?>


</div><!-- /span12 -->
</div>

<?php if (!empty($employee_row)) { ?>
	<!-- div to add/edit timelog through ajax starts -->
	<div class="add-time-detials">
		<h3></h3>
		<input type="hidden" name="inp-emp-id" id="inp-emp-id" value="<?php echo @$employee_row["employee_id"] ?>"/>
		<input type="hidden" name="inp-login-date" id="inp-login-date"/>
		<input type="hidden" name="inp-hourly-rate" id="inp-hourly-rate"
		       value="<?php echo @$employee_row["emp_hourly_rate"] ?>"/>
		<input type="hidden" name="inp-id" id="inp-id"/>

		<div><label for="inp-login-time">Login Time</label>

			<div id="intime-timepicker" class="input-append date">
				<input data-format="hh:mm" type="text" name="inp-login-time" id="inp-login-time">
					<span class="add-on">
					  <i data-time-icon="icon-time" data-date-icon="icon-calendar">
					  </i>
					</span>
			</div>
		</div>
		<div><label for="inp-logout-time">Logout Time</label>

			<div id="outtime-timepicker" class="input-append date">
				<input data-format="hh:mm" type="text" name="inp-logout-time" id="inp-logout-time">
					<span class="add-on">
					  <i data-time-icon="icon-time" data-date-icon="icon-calendar">
					  </i>
					</span>
			</div>
		</div>
		<div><input type="button" name="save_login_time" id="save_login_time" class="btn" value="Submit"/> &nbsp;
			<input type="button" name="close_login_time" id="close_login_time" class="btn" value="Cancel"/>
		</div>
	</div>
	</div>
	<!-- div to add/edit timelog through ajax ends -->
<?php } ?>

<script src="<?php echo base_url(); ?>assets/js/jquery.mask.min.js" type="text/javascript"></script>
<script type="text/javascript">
	var emp_id = <?php echo $employee_row['employee_id']; ?>;
	var _BASE_URL = '<?php echo base_url(); ?>';
	$(window).on('load', function () {
		var which_week = '<?php echo $which_week ?>';
		$(".emp-time-details").drags();
		$(".add-time-detials").drags();
	});
	$('.lunch').change(function(){
		date = $(this).attr('data-date');
		lunch  = +$(this).is(':checked');
		$.post(baseUrl + 'reports/update_lunch', {date : date, emp_id : emp_id, lunch : lunch}, function (resp) {
			if(resp.status == 'ok')
			{
				alert(resp.msg);
				location.reload();
			}
			else
				alert(resp.msg);
			
		}, 'json');
		return false;
	});
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js"></script>
<?php $this->load->view('includes/footer'); ?>
