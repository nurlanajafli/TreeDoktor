<!DOCTYPE html>
<html lang="en" style=" margin-bottom: 0px!important;">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/payroll_pdf.css'); ?>">
</head>
<body style="border: 0!important; margin-bottom: 0px!important;">
<div style="">
	<div class="holder p_top_20">
		<img src="<?php echo base_url('assets/' . $this->config->item('company_dir') . '/print/header2.png'); ?>" width="100%" height="20%" class="p-top-20" style="margin-left: 0px;">
	</div>
	<div style="margin: 0 20px 0; width: 93%; border: 0;">
		<div class="panel-default p-n" style="max-height: 90%; overflow-y: auto; overflow-x: hidden; border-radius: 0; margin-bottom: 0; border: 0;">
			<div class="panel-heading" style="border-bottom: 0px;">
				<div class="h4 pull-right   bg-white" style="margin-bottom: 0;margin-left: -10px;">
					<table class="table" style="width: 55%;margin-bottom: 0px;">
						<tr>
							<td class="b-a bg-light" style="font-weight: bold; background: #f1f1f1;">
								Name
							</td>
							<td class="text-center b-a">
								<?php echo $employee->emp_name; ?>
							</td>
						</tr>
						<tr>
							<td class="b-a" style="font-weight: bold; background: #f1f1f1;">
								Payroll
							</td>
							<td class="text-center b-a p-n">
								<?php echo date('F d, Y', strtotime($payroll->payroll_start_date)); ?> - <?php echo date('F d, Y', strtotime($payroll->payroll_end_date)); ?>
							</td>
						</tr>
						<tr>
							<td class="b-a" style="font-weight: bold; background: #f1f1f1;">
								Address
							</td>
							<td class="text-center b-a" style="white-space: nowrap;padding: 0;">
								<?php $address = $employee->emp_address1 ? $employee->emp_address1 . ', ' : ''; ?>
								<?php $address .= $employee->emp_city ? $employee->emp_city . ', ' : ''; ?>
								<?php $address .= $employee->emp_state ? $employee->emp_state . ', ' : ''; ?>
								<?php $address = rtrim($address, ', '); ?>
								<?php echo $address; ?>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<?php $this->load->view('employees/payroll_employee_workspace'); ?>
		</div>
		
	</div>
</div>
<div class="address" style="position: absolute; bottom: 20px; right: 0; left: 0;">
    <?php echo $this->config->item('footer_pdf_address'); ?>
</div>
