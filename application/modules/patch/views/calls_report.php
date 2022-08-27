<table width="100%" border="1" cellpadding="1" cellspacing="0">
	<tr>
		<th align="center">SID</th>
		<th align="center">From</th>
		<th align="center">To</th>
		<th align="center">Date</th>
		<th align="center">Price</th>

		<!--<th>Parent SID</th>
		 <th>Parent From</th>
		<th>Parent To</th> -->
		<th>Parent Price</th>
		<th>Total Price</th>
	</tr>

	<?php $total = 0; ?>
	
	<?php foreach ($newCalls as $key => $value) : ?>
		<?php $total += isset($parents[$value['parent']]) ? $parents[$value['parent']]['price'] : 0; ?>
		<?php $total += $value['price']; ?>
		<tr>
			<td>
				<?php echo $value['sid'] ?>
			</td>
			<td>
				<?php echo $value['from'] ?>
			</td>
			<td>
				<?php echo $value['to'] ?>
			</td>
			<td width="170px">
				<?php echo $value['date'] ?>
			</td>
			<td>
				<?php echo $value['price'] ?><br>
				<?php //echo $total; ?>
			</td>

			<!-- <td>
				<?php if(isset($parents[$value['parent']])) echo $parents[$value['parent']]['sid']; ?>
			</td>
			<td>
				<?php if(isset($parents[$value['parent']])) echo $parents[$value['parent']]['from']; ?>
			</td>
			<td>
				<?php if(isset($parents[$value['parent']])) echo $parents[$value['parent']]['to']; ?>
			</td> -->
			<td>
				<?php if(isset($parents[$value['parent']])) echo $parents[$value['parent']]['price']; ?>
			</td>
			<td>
				<?php echo isset($parents[$value['parent']]) ? $value['price'] + $parents[$value['parent']]['price'] : $value['price']; ?>
			</td>
		</tr>
	<?php endforeach; ?>
		<tr colspan="6">
			<td><strong>Total</strong></td>
			<td><?php echo $total; ?></td>
		</tr>
</table>