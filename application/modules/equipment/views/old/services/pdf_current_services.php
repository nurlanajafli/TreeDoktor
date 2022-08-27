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

<body style="padding: 20px;">
<style>
	@page *{

	}
</style>
<?php
/*
$services = array();
$dates = array();
foreach($months_services as $key => $service)
{

	$sdate = $service['service_created'];
	$kms = intval($service['report_counter_kilometers_value']);
	$hrs = intval($service['report_counter_hours_value']);
	if($service['diff'] >= $service['service_period_months'])
	{
		for($i = $service['diff']; $i >= $service['service_period_months']; $i -= $service['service_period_months'])
		{
			$key = count($services);
			$sdate = date('Y-m-d', strtotime("+" . $service['service_period_months'] . " months", strtotime($sdate)));
			$services[$key]['date'] = $sdate;
			$services[$key]['item_name'] = $service['item_name'];
			$services[$key]['equipment_service_type'] = $service['equipment_service_type'];
			$services[$key]['prev_hours'] = $service['service_period_hours'] ? $hrs : '—';
			$services[$key]['next_hours'] = $service['service_period_hours'] ? ($hrs + $service['service_period_hours']) : '—';
			$hrs += $service['service_period_hours'];
		
			$services[$key]['prev_kilometers'] = $service['service_period_kilometers'] ? $kms : '—';
			
			$services[$key]['next_kilometers'] = $service['service_period_kilometers'] ? ($kms + $service['service_period_kilometers']) : '—';
			$kms += $service['service_period_kilometers'];
			break;
		}
	}
}
/*
foreach ($services as $key => $row) {
	$dates[] = $row['date'];
}
if(count($dates) && count($services))
	array_multisort($dates, SORT_ASC, $services);
*/
?>

	<header class="panel-heading text-center" style="border-top: none; font-weight: bold;">
		Required Services To <?php echo !empty($date) ? date('Y-m', strtotime($date)) : date('Y-m'); ?>
	</header>
	<div style="padding: 20px;">
		<table class="table" id="servicesList">
			<thead>
			<tr>
				<th align="center" width="30">#</th>
				<th align="center" >Service Type</th>
				<th align="center">Item Name</th>
				<th align="center">Next Counter Value</th> 
				<th align="center" width="100">Actual Km/H On Date Of Service</th>
				<th align="center" width="100">Done</th>
				<th align="center" width="100">Postponed</th>
				<th align="center" width="300">Note</th>
			</tr>
			</thead>
			<tbody>
			<?php if(!empty($months_services)) : ?>
			
			<?php foreach($months_services as $key => $service) : ?>
				<?php $kms = isset($service['complete_reports'][0]) ? intval($service['complete_reports'][0]['report_counter_kilometers_value'] + $service['service_period_kilometers']) : 0;

					$style = '';
					if($service['curr_service_date'] < date('Y-m-d'))
						$style = 'background-color: #e0e0e0;';
				?>

				<tr>
					<td style="<?php echo $style; ?>"><?php echo ($key+1); ?></td>
					<td style="<?php echo $style; ?>"><?php echo $service['equipment_service_type']; ?></td>
					<td style="<?php echo $style; ?>"><?php echo $service['item_name']; ?></td>

					<?php if($service['group_id'] != 18) : ?>
						<td align="center" style="<?php echo $style; ?>">
							<?php echo $service['service_period_kilometers'] ? $kms : '—'; ?>
						</td>
					<?php else : ?>
						<td align="center" style="<?php echo $style; ?>">
							—
						</td>
					<?php endif; ?>

					<td align="center" style="<?php echo $style; ?>"> </td>
					<td align="center" style="<?php echo $style; ?>"> </td>
					<td align="center" style="<?php echo $style; ?>"> </td>
					<td align="center" style="<?php echo $style; ?>"> </td>

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
</body>
</html>
