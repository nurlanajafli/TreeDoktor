<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->

	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/payroll_pdf.css">

</head>
<body>
<div class="holder p_top_20"><img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/img/print/header2.png" width="100%" height="20%"
                                  class="p-top-20" style="margin-left: 0px;"></div>
<div style="margin-left: 82px; width: 100%;">


	<?php $style = ""; ?>

	<?php $style = "margin-left: 70px;"; ?>
	<?php if (!empty($employee_row)) { ?>
	<div class="row p-top-10">
		<div class="grid_12 filled_white rounded shadow">
			<div class="p-10">
				<!-- Client header -->
				<div class="module-header">
					<div class="module-title"><?php echo $page_title ?></div>
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
					<?php for ($e = 0; $e < 2; $e++) { ?>
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
									<div class="td-right" style="font-size: 11px;"><?php echo $week_label ?></div>
									<div class="td-right-with-bottom-border" style="font-size: 11px;">PD</div>
									<div class="td-normal" style="font-size: 11px;">Time In</div>
									<div class="td-normal" style="font-size: 11px;">Time Out</div>
									<div class="td-normal" style="font-size: 11px;">Bonuses</div>
									<div class="td-normal" style="font-size: 11px;">No Lunch</div>
									<div class="td-normal-without-bottom-border" style="font-size: 11px;">Worked</div>
								</div>
								<?php $total_per_week = 0.00; ?>
								<?php foreach ($weekdays_data as $w1kk => $w1vv) { ?>
									<?php $hightlight = ""; ?>
									<?php if ($w1vv == date("Y-m-d")) { ?>
										<?php $hightlight = "border: 1px solid green;"; ?>
									<?php } ?>
									<div class="week-inner-tbl-pdf">
										<div class="td-normal" style="font-size: 11px;">
											<!-- day name -->
											<?php echo date("D", strtotime($w1vv)); ?>&nbsp;
										</div>
										<div class="td-normal-blue" style="font-size: 11px;">

											<?php echo $w1vv ?>&nbsp; </div>
										<!-- first login time -->
										<div class="td-normal-value" style="font-size: 11px;">
											<?php  if(isset($emp_data[$w1vv][0]["login_time"])) : ?>

												<?php echo isset($emp_data[$w1vv][0]["login_time"]) ? date('H:i', strtotime($emp_data[$w1vv][0]["login_time"])) : "00.00" ?>
												&nbsp; 

											<?php elseif(isset($absences[strtotime($w1vv)])) : ?>
												- 
										
											<?php else : ?>

												"00.00"

											<?php endif; ?>
										</div>
										<?php $total_time_per_emp = 0.00; ?>
										<?php if (!empty($emp_data[$w1vv])) { ?>
											<?php foreach ($emp_data[$w1vv] as $ev1) { ?>
												<?php if($ev1['logout_time'] != '0000-00-00 00:00:00') { ?>
													<?php $total_time_per_emp = $total_time_per_emp + round(((strtotime($ev1['logout_time']) - strtotime($ev1['login_time'])) / 3600), 2); ?>
												<?php } ?>
											<?php } ?>
										<?php } ?>
										
										
										
										<!-- first logout time -->
										<div class="td-normal-value" style="font-size: 11px;">
											<?php if(isset($emp_data[$w1vv][count(@$emp_data[$w1vv]) - 1]["logout_time"])) : //countOk ?>
										
												
													<?php echo isset($emp_data[$w1vv][count(@$emp_data[$w1vv]) - 1]["logout_time"]) ? date('H:i', strtotime($emp_data[$w1vv][count($emp_data[$w1vv]) - 1]["logout_time"])) : "00:00" //countOk ?>
													&nbsp;
												
											<?php elseif(isset($absences[strtotime($w1vv)])) : ?>
													-
											
											<?php else : ?>
												
												"00:00"
												
											<?php endif; ?>
										</div>
										
										
										<div width="20%" class="td-normal-value" style="font-size: 11px; height: 16px;">
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
										
										<div class="td-normal-value" style="height: 16px;">
											<input type="checkbox" <?php if(date('Y-m-d') < $w1vv && !isset($emp_data[$w1vv][0]) ) : ?>disabled="disabled"<?php endif; ?> name="no_lunch" class="lunch" data-date="<?php echo $w1vv; ?>" <?php if(isset($emp_data[$w1vv][0]["no_lunch"]) && $emp_data[$w1vv][0]["no_lunch"] == TRUE) : ?>checked="checked"<?php endif; ?> value="1">
										</div>
										<?php if ($total_time_per_emp >= 5 && !$emp_data[$w1vv][0]["no_lunch"]) { ?>
											<?php $breaks = $breaks + 0.50; ?>
										<?php } ?>
										<div width="20%" class="td-normal-value" style="font-size: 11px; <?php if(isset($absences[strtotime($w1vv)])) : ?>color:red;<?php endif; ?>">
										    <?php if(isset($emp_data[$w1vv][0]["login_time"]) && $total_time_per_emp > 0) : ?>
												<?php if(!$emp_data[$w1vv][0]['no_lunch']) : ?>
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

									</div>
									<?php $total_per_week = $total_per_week + $total_time_per_emp; ?>
								<?php } // end foreach ?>
								<div class="week-outer-tbl">
									<div class="td-right" style="font-size: 11px;">&nbsp;</div>
									<div class="td-right-with-bottom-border" style="font-size: 11px;">&nbsp;</div>
									<div class="td-normal" style="font-size: 11px;">Total</div>
									<div class="td-normal" style="font-size: 11px;">Worked</div>
									<div class="td-normal"
									     style="font-size: 11px;"><?php echo $total_per_week ?> hrs.
									</div>
									<div class="td-normal" style="font-size: 11px;">&nbsp;</div>
									<div class="td-right" style="font-size: 11px;">&nbsp;</div>
								</div>
								<div style="clear: both;"></div>
							</div>
							<?php $total_time_two_weeks = $total_time_two_weeks + $total_per_week; ?>
						<?php } ?>
					<?php } //end foreach empdata ?>


					<?php if ($this->session->userdata('user_type') == "admin") : ?>
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
										<td> $ <?php echo @$employee_row["emp_hourly_rate"] ?>/hr</td>
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
										<td>
											$ <?php echo sprintf("%01.2f", $totalToPay); ?></td>
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
					<?php endif; ?>
					<?php if (!empty($emp_data)) { ?>
					<div class="row m-top-10">
						<div class="grid_12">
							<div class="p-10">

								<?php } ?>

							</div>
						</div>
					</div>
					<?php } // end if employee row data ?>


				</div>
				<!-- /span12 -->
			</div>

		</div>
</body>
</html>
