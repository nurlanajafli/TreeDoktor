<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/estimate_pdf.css" type="text/css" media="print">

</head>
<body>
<div class="holder p_top_20"><img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/header.png" width="680"
                                  class="p-top-20"></div>
<!-- Services -->
<?php if(isset($services)) : ?>
<div class="grid_12 rounded filled_white shadow overflow m-top-10">
	<div class="title">Actual Services</div>
	<div class="data">
		<table class="client_table" id="tbl_search_result" width="100%" border="1px">
			<thead>
			<tr>
				<th>Item Name</th>
				<th>Service Type</th>
				<th>Date</th>
				<th>Next</th>
				<th>Service Description</th>
				<th>Status</th>
			</tr>
			</thead>
			<tbody>
			<?php if (!empty($services)) : ?>
				<?php foreach ($services as $service) : ?>
					<tr>
						<td><?php echo $service['item_name']; ?></td>
						<td style="text-align:center"><?php echo $service['equipment_service_type']; ?></td>
						<td style="text-align:center"><?php echo date('Y-m-d', $service['service_date']); ?></td>
						<td style="text-align:center"><?php echo $service['service_next'] ? date('Y-m-d', $service['service_next']) : 'N/A'; ?></td>
						<td style="text-align:center"><?php echo $service['service_description']; ?></td>
						<td style="text-align:center">
							<?php if ($service['service_status'] == 'new') : ?>
								New
							<?php else : ?>
								<strong>Complete</strong>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>
<?php if(isset($past_due)) : ?>
<div class="grid_12 rounded filled_white shadow overflow m-top-10">
	<div class="title">Past Due</div>
	<div class="data">
		<table class="client_table" id="tbl_search_result" width="100%" border="1px">
			<thead>
			<tr>
				<th>Item Name</th>
				<th>Service Type</th>
				<th>Date</th>
				<th>Next</th>
				<th>Service Description</th>
				<th>Status</th>
			</tr>
			</thead>
			<tbody>
			<?php if (!empty($past_due)) : ?>
				<?php foreach ($past_due as $service) : ?>
					<tr>
						<td><?php echo $service['item_name']; ?></td>
						<td style="text-align:center"><?php echo $service['equipment_service_type']; ?></td>
						<td style="text-align:center"><?php echo date('Y-m-d', $service['service_date']); ?></td>
						<td style="text-align:center"><?php echo $service['service_next'] ? date('Y-m-d', $service['service_next']) : 'N/A'; ?></td>
						<td style="text-align:center"><?php echo $service['service_description']; ?></td>
						<td style="text-align:center">
							<?php if ($service['service_status'] == 'new') : ?>
								New
							<?php else : ?>
								<strong>Complete</strong>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>
<?php if(isset($past_services)) : ?>
<div class="grid_12 rounded filled_white shadow overflow m-top-10">
	<div class="title">Search</div>
	<div class="data">
		<table class="client_table" id="tbl_search_result" width="100%" border="1px">
			<thead>
			<tr>
				<th>Item Name</th>
				<th>Service Type</th>
				<th>Date</th>
				<th>Next</th>
				<th>Service Description</th>
				<th>Status</th>
			</tr>
			</thead>
			<tbody>
			<?php if (!empty($past_services)) : ?>
				<?php foreach ($past_services as $service) : ?>
					<tr>
						<td><?php echo $service['item_name']; ?></td>
						<td style="text-align:center"><?php echo $service['equipment_service_type']; ?></td>
						<td style="text-align:center"><?php echo date('Y-m-d', $service['service_date']); ?></td>
						<td style="text-align:center"><?php echo $service['service_next'] ? date('Y-m-d', $service['service_next']) : 'N/A'; ?></td>
						<td style="text-align:center"><?php echo $service['service_description']; ?></td>
						<td style="text-align:center">
							<?php if ($service['service_status'] == 'new') : ?>
								New
							<?php else : ?>
								<strong>Complete</strong>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="4" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>
</body>
</html>
