<!DOCTYPE html>
<html>
<head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>
</head>
<body>
 
<table style="width: 50%;margin: 0 auto;">
	<caption>Equipment Worked Days By 2018</caption>
	<thead>
	<tr>
		<th>Name</th>
		<th>Days</th> 
	</tr>
	</thead>
	<tbody>
	<?php foreach ($equipments as $k=>$v) { ?>
		<tr>
			<td><?php echo $v->item_code; ?></td>
			<td><?php echo $v->days; ?></td> <?php /* if($k == 1) : ?><?php var_dump($data[$v->item_code]); die; ?><?php endif; */ ?>
			
		</tr>
	<?php } ?>
	</tbody>
</table><br>
<table style="width: 50%;margin: 0 auto;">
	<caption>Worked Hours By 2018(Crews)</caption>
	<thead>
	<tr>
		<th>Name</th>
		<th>Hrs</th> 
	</tr>
	</thead>
	<tbody>
	<?php $totalHrs = $totalUsers = 0; ?>
	<?php foreach ($crews as $k=>$v) { ?>
		<tr>
			<td><?php echo $v->dates; ?> </td>
			 <td><?php echo $v->avg_hours_day; ?> hrs</td>
			 <?php $totalUsers += $v->users; ?>
			 <?php $totalHrs += $v->team_man_hours; ?> 
		</tr>
	<?php } ?>
		<tr>
			<td><strong>AVG:</strong></td>
			<td><strong><?php echo round($totalHrs / $totalUsers, 2); ?> hrs</strong></td>
		</tr>
	</tbody>
</table>
	

</body>
</html>
