<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/invoice_pdf.css" type="text/css" media="print">
</head>
<body>
<div class="holder p_top_20"><img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/header2.png" width="680"
                                  class="p-top-20"></div>
<div class="data">
	<!-- payroll overview date INFO -->
	<table cellpadding="0" cellspacing="0" border="0" class="client_table">
		<tr>
			<td>Week Starting:</td>
			<td><?php echo @date("m/d/Y", strtotime($weekdays["total_week_days"][0])); ?></td>
		</tr>
		<tr>
			<td>Week Ending:</td>
			<td><?php echo @date("m/d/Y", strtotime($next_weekdays["total_week_days"][6])); ?></td>
		</tr>

	</table>
	<!-- payroll overview date INFO -->
</div>

<div class="title">Overview Report</div>
<div class="data">
	<table cellpadding="0" cellspacing="0" border="0" class="invoice_table" width="100%">
		<thead>
		<tr>
			<th valign="top" style="border: 1px solid #DADADA"><strong>#</strong></th>
			<th class="top" style="border: 1px solid #DADADA"><strong>Name</strong></th>
			<th class="top" style="border: 1px solid #DADADA"><strong>Hours Worked</strong></th>
			<th class="top" style="border: 1px solid #DADADA"><strong>Hourly Rate</strong></th>
			<th class="top" style="border: 1px solid #DADADA"><strong>To Be Paid</strong></th>
			<th class="top" style="border: 1px solid #DADADA"><strong>Employee Type</strong></th>
			<th class="top" style="border: 1px solid #DADADA"><strong>Bonus</strong></th>
		</tr>
		</thead>
		<tbody>
					<?php $i = 1; ?>
					<?php $total_hours = 0; ?>
					<?php $total_to_pay = 0; ?>
					<?php if (!empty($emp_data)) : ?>
						<?php $type = 'employee'; ?>
						

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
									<td style="border: 1px solid #DADADA"><?php echo $employee["emp_hourly_rate"]; ?></td>
									<td style="border: 1px solid #DADADA">
										<?php echo money($employee['to_paid']); ?></td>
									<td style="border: 1px solid #DADADA">
										<?php echo ucfirst($employee["emp_type"]); ?></td>
									<td style="border: 1px solid #DADADA">
										&nbsp;</td>
									
								</tr>
								<?php $i++; ?>
								<?php unset($users[$key]); ?>
							<?php endforeach; ?>
							<tr>
								<td style="border: 1px solid #DADADA">&nbsp;</td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">Total hours for '<?php echo ucfirst($type); ?>s':</td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">
									<?php echo $total_hours_for_month[$type]; ?></td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">Total to pay for '<?php echo ucfirst($type); ?>s':</td>
								<td style="border: 1px solid #DADADA; font-weight: bold;">
									<?php echo money(getAmount($total_for_month[$type])); ?></td>
									<td style="border: 1px solid #DADADA">&nbsp;</td>
								<td style="border: 1px solid #DADADA">&nbsp;</td>
							</tr>
							<?php $total_to_pay += $total_for_month[$type]; ?>
							<?php $total_hours += $total_hours_for_month[$type]; ?>

						<?php endforeach; ?>						
					<?php endif; ?>
					<?php foreach($users as $key=>$user) : ?>
						<tr>
							<td style="border: 1px solid #DADADA"><?php echo $i; ?></td>
							<td style="border: 1px solid #DADADA"><?php echo $user->emp_name; ?></td>
							<td style="border: 1px solid #DADADA">0</td>
							<td style="border: 1px solid #DADADA"><?php echo $user->emp_hourly_rate; ?></td>
							<td style="border: 1px solid #DADADA"><?php echo money(0); ?></td>
							<td style="border: 1px solid #DADADA">
								<?php echo ucfirst($user->emp_type); ?></td>
							<td style="border: 1px solid #DADADA">
								&nbsp;</td>
						</tr>
						<?php $i++; ?>
					<?php endforeach; ?>
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
						</tr>
					</tbody>
	</table>
</div>
</body>
</html>
