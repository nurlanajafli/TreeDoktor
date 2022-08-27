<div id="tracking" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog" style="width: 1000px;">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Client Tracking</header>
			<table class="table table-striped">
				<tr>
					<th>Vehicle</th>
					<th>Leaving</th>
					<th>Arrive</th>
					<th>Finish</th>
					<th>Way</th>
					<th>Stop</th>
				</tr>
				<?php if(isset($tracking) && $tracking && !empty($tracking)) : ?>
					<?php foreach ($tracking as $car) : ?>
						<tr>
							<td><?php echo $car['vehicle']; ?></td>
							<td><?php echo isset($car['leaving_date']) ? $car['leaving_date'] : '-'; ?></td>
							<td><?php echo $car['start_date']; ?></td>
							<td><?php echo $car['end_date']; ?></td>
							<td><?php echo isset($car['way_time']) ? $car['way_time'] : '-'; ?></td>
							<td><?php echo $car['stop_time']; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr>
						<td colspan="6" style="color:#FF0000;">No record found</td>
					</tr>
				<?php endif; ?>
			</table>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>

