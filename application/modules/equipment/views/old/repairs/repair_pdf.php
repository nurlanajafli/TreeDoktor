<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->


</head>
<body>

<div class="estimate_number">
	<div class="estimate_number_1">Reported by </div>

	<div class="">
		<strong>
			<?php echo($repair->author_name); ?>
<!--			--><?php //echo $repair->repair_date;?>
			<?php echo getDateTimeWithDate($repair->repair_date, 'Y-m-d H:i:s', true);?>
		</strong>
	</div>
	<div class="clearBoth"></div>
</div>


<div class="data">
	<!-- CLIENT INFO -->
	<table border="1" cellpadding="0" cellspacing="0" width="100%" >
		<tr>
			<td><strong>Item Group:</strong></td>
			<td align="center">
				<?php foreach($groups as $k=>$v) : ?>
					<?php if($v->group_id == $repair->group_id) : ?>
						<?php echo $v->group_name; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<td><strong>Item Name:</strong></td>
			<td align="center"><?php echo $repair->item_name; ?></td>
		</tr>
		<?php if(isset($repair->repair_notes) && !empty($repair->repair_notes)) : ?>
			<tr>
				<td><strong>Comment:</strong></td>
				<td align="center">
					<?php echo $repair->repair_notes[count($repair->repair_notes)-1]->equipment_note_text; //countOk ?>
				</td>
			</tr>
		<?php endif;?>
		<tr>
			<td><strong>Priority:</strong></td>
			<td align="center"> <?php echo $repair->repair_priority;?></td>
		</tr>
		<tr>
			<td><strong>Type:</strong></td>
			<td align="center"><?php echo ucfirst($repair->repair_type); ?></td>
		</tr>
		<tr>
			<td><strong>Status:</strong></td>
			<td align="center"><?php echo ucfirst(str_replace('_', ' ', $repair->repair_status)) ; ?></td>
		</tr>
		<?php if($repair->repair_solder_id) : ?>
		<tr>
			<td><strong>Assigned to:</strong></td>
			<td align="center"><?php echo $repair->solder_name ; ?></td>
		</tr>
		<?php endif; ?>
		<?php if($repair->repair_status == 'repaired') : ?>
		<tr>
			<td><strong>Price:</strong></td>
			<td align="center"><?php if($repair->repair_price) : echo money($repair->repair_price); else: echo '-'; endif; ?></td>	
		</tr>
		<tr>
			<td><strong>Hours:</strong></td>
			<td align="center"><?php if($repair->repair_hours) : echo $repair->repair_hours; else : echo '-'; endif; ?></td>
		</tr>
		<?php endif; ?>
	</table>
	<!-- CLIENT INFO -->
</div>


</body>
</html>
