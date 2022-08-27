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
	<caption>Equipment Stats By 2018</caption>
	<thead>
	<tr>
		<th>#</th>
		<th>Name</th>
		<th>Time</th> 
		<th>Distanse</th>
		<th>AVG Time</th>
		<th>AVG Distanse</th>
		<th>Days</th>
		<th>Schedule Days</th>
	</tr>
	</thead>
	<tbody>
	<?php if(isset($data) && !empty($data)) : ?><?php $count = 1; ?>
	<?php foreach ($data as $k=>$v) {  ?>
		<tr>
			<td><?php echo $count; ?></td> 
			<td><?php echo $v['name']; ?></td> 
			 
				<td><?php echo sprintf('%02d:%02d', (int) round($v['time'] / 3600, 2), fmod(round($v['time'] / 3600, 2), 1) * 60); ?> hrs.</td>
				<td><?php echo $v['distance']; ?> kms.</td>
				<td><?php echo sprintf('%02d:%02d', (int) round($v['time'] / $v['days'] / 3600, 2), fmod(round($v['time'] /  $v['days'] / 3600, 2), 1) * 60);  ?> .hrs</td>
				<td><?php echo round(($v['distance'] / $v['days']), 2);   ?> kms.  </td>
				<td><?php echo $v['days']; ?></td>
				<td><?php echo $v['schedule_days']; ?></td>
			
		</tr>
		<?php $count++; ?>
	<?php } ?>
	<?php endif; ?>
	</tbody>
</table>
	

</body>
</html>
