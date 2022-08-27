<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/workorder_pdf.css" type="text/css" media="print">
</head>
<body>
<div style="margin-left:10px;" class="workorder_number">
	<table width="100%" style="margin-bottom: 10px;">
		<tr>
			<td style="font-size: 16px;">
				<strong>
					<?php echo $track_name; ?>
				</strong>
				<?php if(isset($route_kms) && $route_kms) : echo ' - P:' . round($route_kms/1000, 2) . 'kms,'; endif; ?>
				<?php echo ' A:' . round($actual_distance/1000, 2) . 'kms'; ?>
				
			</td>
			<td align="right" style="font-size: 16px;">
				<strong>
					<?php echo date('d M, Y', $date); ?>
				</strong>
			</td>
		</tr>
	</table>
</div>



<div style="margin-left:10px; margin-right:10px;">
	<?php if(isset($items) && !empty($items)) : ?>
	<strong>TEAM:</strong>
		<?php foreach($items as $k=>$v) : ?>
			<?php if($v['item_id'] == $v['team_leader_user_id']) echo '* '; ?>
				<?php echo $v['name']; ?>
			<?php if($v['type'] == 'user' && $v['driver_id']) : ?>
				(<?php echo $v['driver_id']; ?>)
			<?php elseif($v['type'] == 'equipment' && $v['field_worker']) : ?>
				(<?php echo $v['field_worker']; ?>)
			<?php endif; ?>
			<?php if(isset($items[$k+1])) :?>
					,
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
		
	<?php if(isset($tools) && !empty($tools)) : ?>
		<br><strong>TOOLS:</strong>
		<?php $countTools = count($tools);?>//countOk
			<?php foreach($tools as $k => $tool) : ?>
				<?php echo $tool['item_name'];?>, 
			<?php unset($tools[$k]); ?>
			<?php if($k >= round($countTools / 2, 0, PHP_ROUND_HALF_UP) - 1) break; ?>
		<?php endforeach; ?>
			<?php foreach($tools as $k => $tool) : ?>
				<?php echo $tool['item_name'];?>
				<?php if(isset($tools[$k+1])) :?>
					,
				<?php endif; ?>
			<?php unset($tools[$k]); ?>
		<?php endforeach; ?>
		
	<?php endif; ?>
	<?php if(isset($events) && !empty($events)) : ?>
		<br><strong>JOBS:</strong>
		<?php $num = 1; ?>
		<?php foreach($events as $k=>$v) : ?>
			<?php echo $num++;?>)
			<?php echo $v['lead_address']; ?>, <?php echo $v['lead_zip']; ?>
			 - 
			 	P: <?php echo sprintf('%02d:%02d', (int) floatval($v['planned_service_time']), fmod(floatval($v['planned_service_time']), 1) * 60); ?>, 
			 	A: <?php echo sprintf('%02d:%02d', (int) round($v['actual_worked_time'] / 3600, 2), fmod(round($v['actual_worked_time'] / 3600, 2), 1) * 60); ?><?php if(isset($events[$k+1])) :?>;<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	
</div>

<?php if(isset($map_url) && $map_url) : ?>
<div style="text-align:center; margin-top:15px; width: 100%;">
	<img src="<?php echo $map_url; ?>" width="800px" height="420px"/>
</div>
<?php endif; ?>
<div style="text-align:center; margin-top:5px; width: 100%;">
	<?php /*<img src="<?php echo $map_url; ?>" width="800px" height="420px"/>*/ ?>
	<a href="<?php echo base_url('cron/equipmentMap/' . date('Y-m-d', $date) . '/' . $code); ?>" target="_blank">
		<img src="<?php echo 'data:image/png;base64,' . base64_encode(file_get_contents($map_url2)); ?>" width="800px" height="435px"/>
	</a>
</div>

