<!DOCTYPE html>
<html lang="en">
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
<body style="border: 0!important;">

<div style="margin: 10px 20px 20px 30px">
	<div class="">
		<div class="text-center"><strong>Users List</strong></div><br>
		<!-- Data display -->
<div class="row p-5"  style="margin-bottom:0px; padding-bottom:0; padding-top:0;" >
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table m-b-none b-a">
				<thead>
				<tr>
					<th width="10px" class="bg-light b-r b-b" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;" >#</th>
					<th  class="bg-light b-r b-b" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;" >&nbsp;</th>
					<th class="bg-light b-r b-b" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;" >Name</th>
					<th  class="bg-light b-r b-b" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf; text-align:center">Phone</th>
					<th class="bg-light b-r b-b" style="background: #f1f1f1;color: #717171; border-top: 1px solid #cfcfcf;" >Position</th>
				</tr>
				</thead>
				<tbody>
				<?php
                $users = $user_row->result();
				if ($users) {
					foreach ($users as $key=>$row):
						?>
						<tr>
							<td style="vertical-align: middle;"><?php echo $key + 1; ?></td>
							<td style="vertical-align: middle;">
								
							</td>
							<td style="vertical-align: middle;"><?php echo $row->firstname . ' ' . $row->lastname; ?></td>
							
							<td style="text-align:center; vertical-align: middle;">
								<?php if(isset($row->emp_phone) && $row->emp_phone != '' && $row->emp_phone != NULL) : ?>
									<?php echo numberTo($row->emp_phone); ?>
								<?php else : ?>
									-
								<?php endif; ?>
							
							</td>

							<td style="vertical-align: middle;"><?php echo isset($row->emp_position) ? $row->emp_position : '-'; ?></td>
						</tr>
					<?php
					endforeach;
				} else {
					?>
					<tr>
						<td colspan="10" class="text-danger"><?php echo "No records found"; ?></td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
		<!--/ Data Display-->

		<div class="address" style="margin-top: 60px;">
			<span class="green">ADDRESS:</span> <?php echo config_item('office_address'); ?>, <?php echo config_item('office_state'); ?> <?php echo config_item('office_zip'); ?>
			<span class="green">OFFICE: </span><?php echo config_item('office_phone_mask'); ?>
			<span class="green">WEB: </span><?php echo config_item('company_site_name_upper'); ?>
		</div>
	</div>
</div>
</body>
</html>
