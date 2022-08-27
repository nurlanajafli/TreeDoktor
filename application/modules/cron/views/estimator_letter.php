<table class="table tsble-striped" border="1px" cellspacing="0">
	<thead>
		<tr>
			<th align="center">Estimate No</th>
			<th align="center">Estimate Address</th>
			<th align="center">Time for Work</th>
			<th align="center">Estimator Time</th>
		</tr>
	</thead>
	<tbody>
		
		<tr>
			
			<td align="center">
				<?php echo $number; ?>
			</td>
			<td align="center">
				<?php echo $address; ?>
			</td>
			<td align="center">
			<?php
				$hours = floor($timing/3600);
				$minutes = ($timing/3600 - $hours)*60;
				echo $hours . ':' . str_pad(floor($minutes), 2, '0', STR_PAD_LEFT);
			?>
			
			</td>
			<td align="center">
				<?php
					$hours = floor($time_estimator);
					$minutes = ($time_estimator - $hours)*60;
					echo $hours . ':' . str_pad(floor($minutes), 2, '0', STR_PAD_LEFT);
				?>
			</td>
		</tr>
	</tbody>
</table>
