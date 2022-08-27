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
	<table class="table" width="95%" style="margin-left: 50px;">
		<thead>
			<tr>
				<th width="200px">Employee Name</th>
				<th width="480px">Bonuses</th>
				<th width="100px">Percents</th>
				<th width="100px">Amount</th>
			</tr>
		</thead>
		<tbody>
		<?php $total = 0; ?>
		<?php foreach($data as $key => $val) : ?>
			<?php if($val['bonus_sum']) : ?>
				<tr>
					<td align="center" valign="top"><?php echo $val['employee']->emp_name; ?></td>
					<td align="center">
						<table class="table" width="100%">
						<?php $counter = 0; ?>
						<?php foreach($val['bonuses'] as $date => $bonusSum) : ?>
							<?php if($counter == 0) : ?>
								<tr>
							<?php endif; ?>
								<td width="33%"><?php echo money($bonusSum); ?> - <?php echo $date; ?></td>
								<?php $counter++; ?>
							<?php if($counter == 3) : ?>
								<?php $counter = 0; ?>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
						</table>
					</td>x
					<td align="center" valign="top"><?php echo $val['bonuses_percents']; ?>%</td>
					<td align="center" valign="top"><?php echo money($val['bonus_sum']); ?></td>
					<?php $total += $val['bonus_sum'];?>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		<tr>
			<td></td>
			<td align="center"><strong>TOTAL:</strong></td>
			<td align="center"><strong><?php echo money($total); ?></strong></td>
		</tr>
		</tbody>
	</table>
</body>
</html>
