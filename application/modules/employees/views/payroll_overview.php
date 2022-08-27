<?php $this->load->view('includes/header'); ?>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/reports.css">

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
		<li class="active">Payroll Overview</li>
	</ul>
	<span style="color:red;"><?php echo validation_errors(); ?></span>
	<section class="col-sm-12 panel panel-default p-n">
		<div class="p-left-5">
			<div class="form-inline text-center">
				<form name="frmSearchEmplouee" id="searcEmployee" method="POST" action="">
					<input type="hidden" name="generate_pdf" id="generate_pdf" value=""/>
					<input type="hidden" name="curdate" id="curdate" value="<?php echo $curdate; ?>"/>
					<input type="submit" name="btnsubmit" id="btnsubmit" value="Submit" style="visibility: hidden;"/>
				</form>
				<div class="form-group m-t-n-md">
					<div class="top-buttons">
						<input type="hidden" name="pre-week-val" id="pre-week-val"
						       value="<?php echo $weekdays["last_week_date"]; ?>"/>
						<input type="button" value="<< Previous Weeks" id="pre-week" class="btn"/>
						<input type="hidden" name="this-week-val" id="this-week-val"
						       value="<?php echo date("Y-m-d"); ?>"/>
						<input type="button" value="Current Weeks" id="this-week" class="btn"/>
						<input type="hidden" name="next-week-val" id="next-week-val"
						       value="<?php echo $next_weekdays["next_week_date"]; ?>"/>
						<input type="button" value="Next Weeks >>" id="next-week" class="btn"/>
					</div>
				</div>
			</div>
		</div>
	</section>


	<section class="col-sm-12 panel panel-default p-n">
		<div class="p-10">
			<div class="week-days-heading">
				<div class="labelt">Week Starting:</div>
				<div class="value-black"><?php echo @date("m/d/Y", strtotime($weekdays["total_week_days"][0])); ?></div>
				<div style="clear: both"></div>
				<div class="labelt">Week Ending:</div>
				<div
					class="value-black"><?php echo @date("m/d/Y", strtotime($next_weekdays["total_week_days"][6])); ?></div>
				<div style="clear: both"></div>
			</div>
			<div style="clear: both"></div>
		</div>
	</section>
	<section class="col-sm-12 panel panel-default p-n">
		<div class="p-10">
			<h2>Overview Report</h2>

			<div class="table-responsive">
				<table class="table table-hover tablesorter" style="border: 1px solid #DADADA">
					<thead>
					<tr>
						<th class="header" style="border: 1px solid #DADADA">#</th>
						<th class="header" style="border: 1px solid #DADADA">Name</th>
						<th class="header" style="border: 1px solid #DADADA">Hours Worked</th>
						<?php if ($this->session->userdata('user_type') == "admin") : ?>
							<th class="header" style="border: 1px solid #DADADA">Hourly Rate</th>
							<th class="header" style="border: 1px solid #DADADA">To Be Paid</th>
						<?php endif; ?>
						<th class="header" style="border: 1px solid #DADADA">Employee Type</th>
						<th class="header" style="border: 1px solid #DADADA">Bonus</th>
						<th class="header" style="border: 1px solid #DADADA">Report</th>
					</tr>
					</thead>
					<tbody>
					<?php $i = 1; ?>
					<?php $total_hours = 0; ?>
					<?php $total_to_pay = 0; ?>
					<?php if (!empty($emp_data)) : ?>
						<?php $type = 'employee'; ?>
						<tr style="display: none;"><td></td></tr>

						<?php foreach ($emp_data as $type => $employees) : ?>
							<?php $total_for_month[$type] = 0.0; ?>
							<?php $total_hours_for_month[$type] = 0.0; ?>
							<?php foreach ($employees as $key => $employee) : ?>
								<?php $total_hours_for_month[$type] = $total_hours_for_month[$type] + $employee['worked']; ?>
								<?php $total_for_month[$type] = $total_for_month[$type] + $employee["to_paid"]; ?>
								<tr>
									<td style="border: 1px solid #DADADA"><?php echo $i; ?></td>
									<td style="border: 1px solid #DADADA"><?php echo $employee["emp_name"] ?></td>
									<td style="border: 1px solid #DADADA"><?php echo $employee["worked"]; ?></td>
									<?php if ($this->session->userdata('user_type') == "admin") : ?>
										<td style="border: 1px solid #DADADA"><?php echo $employee['worked'] ? round($employee["to_paid"] / $employee['worked'], 2) : $employee["emp_hourly_rate"];//$employee["emp_hourly_rate"]; ?></td>
										<td style="border: 1px solid #DADADA">
											<?php echo money($employee['to_paid']); ?></td>
									<?php endif; ?>
									<td style="border: 1px solid #DADADA">
										<?php echo ucfirst($employee["emp_type"]); ?></td>
									<td style="border: 1px solid #DADADA">
										&nbsp;</td>
									<td class="employee_pdf" style="border: 1px solid #DADADA">
										<form name="frmSearchEmplouee-<?php echo $key; ?>" id="searcEmployee-<?php echo $key; ?>" method="POST" action="<?php echo base_Url('reports/payroll'); ?>">
											<input type="hidden" name="selEmmployee" value="<?php echo $key; ?>"/>
											<input type="hidden" name="generate_pdf" value="1"/>
											<input type="hidden" name="get_data_by_date" value="<?php echo $curdate ?>"/>
											<input type="hidden" name="which_week" value="this-week"/>

											<button type="submit" class="btn btn-xs" style="margin-top: 0px; margin-left: 10px;">
												<i class="fa fa-book"></i>
											</button>
										</form>
									</td>
								</tr>
								<?php $i++; ?>
								<?php unset($users[$key]); ?>
							<?php endforeach; ?>
							<?php if ($this->session->userdata('user_type') == "admin") : ?>
							<tr>
								<td style="border: 1px solid #DADADA">&nbsp;</td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">Total hours for '<?php echo ucfirst($type); ?>':</td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">
									<?php echo $total_hours_for_month[$type]; ?></td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">Total to pay for '<?php echo ucfirst($type); ?>':</td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">
									<?php echo money(getAmount($total_for_month[$type])); ?></td>
									<td style="border: 1px solid #DADADA">&nbsp;</td>
								<td style="border: 1px solid #DADADA">&nbsp;</td>
								<td style="border: 1px solid #DADADA">&nbsp;</td>
							</tr>
							<?php endif; ?>
							<?php $total_to_pay += $total_for_month[$type]; ?>
							<?php $total_hours += $total_hours_for_month[$type]; ?>

						<?php endforeach; ?>						
					<?php endif; ?>
					<?php foreach($users as $key=>$user) : ?>
						<tr>
							<td style="border: 1px solid #DADADA"><?php echo $i; ?></td>
							<td style="border: 1px solid #DADADA"><?php echo $user->emp_name; ?></td>
							<td style="border: 1px solid #DADADA">0</td>
							<?php if ($this->session->userdata('user_type') == "admin") : ?>
								<td style="border: 1px solid #DADADA"><?php echo $user->emp_hourly_rate; ?></td>
								<td style="border: 1px solid #DADADA"><?php money(0); ?></td>
							<?php endif; ?>
							<td style="border: 1px solid #DADADA">
								<?php echo ucfirst($user->emp_type); ?></td>
							<td style="border: 1px solid #DADADA">
								&nbsp;</td>
							<td style="border: 1px solid #DADADA">
								&nbsp;</td>
						</tr>
						<?php $i++; ?>
					<?php endforeach; ?>
					<?php if ($this->session->userdata('user_type') == "admin") : ?>
						<tr>
							<td style="border: 1px solid #DADADA">&nbsp;</td>
							<td style="border: 1px solid #DADADA; font-weight: bold;">Total hours:</td>
							<td style="border: 1px solid #DADADA; font-weight: bold;">
								<?php echo $total_hours; ?></td>
							<td style="border: 1px solid #DADADA; font-weight: bold;">Total to pay:</td>
							<td style="border: 1px solid #DADADA; font-weight: bold;">
								<?php echo money(getAmount($total_to_pay)); ?></td>
								<td style="border: 1px solid #DADADA">&nbsp;</td>
							<td style="border: 1px solid #DADADA">&nbsp;</td>
							<td style="border: 1px solid #DADADA">&nbsp;</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
			</div>
			<?php if (!empty($emp_data)) { // comes in normail view not in pdf ?>
				<div style="margin: 20px; float: right;"><input type="button" value="Generate PDF" id="btn_generate_pdf"
				                                                class="btn"/></div>
			<?php } ?>
		</div>
	</section>
</section>
<script type="text/javascript">
	$(window).on('load', function () {
		$("#btn_generate_pdf").click(function () {
			$("#generate_pdf").val(1);
			$("#searcEmployee").submit();
		});

		$("#pre-week").click(function () {
			$("#generate_pdf").val(0);
			$("#curdate").val($("#pre-week-val").val());
			$("#btnsubmit").click();
		});

		$("#this-week").click(function () {
			$("#generate_pdf").val(0);
			$("#curdate").val($("#this-week-val").val());
			$("#btnsubmit").click();
		});

		$("#next-week").click(function () {
			$("#generate_pdf").val(0);
			$("#curdate").val($("#next-week-val").val());
			$("#btnsubmit").click();
		});
		
	});
</script>
<?php $this->load->view('includes/footer'); ?>
