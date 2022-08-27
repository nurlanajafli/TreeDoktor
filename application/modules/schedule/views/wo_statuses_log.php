<section class="panel panel-default p-n">
	<!-- Workorder Details Header -->
	<header class="panel-heading">Workorder Statuses History</header>

	<!-- Data Display -->
	<table class="table table-striped b-t bg-white m-n">
		<thead>
		<tr>
			<th>Status Name</th>
			<th style="width:30%">Changed</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				Estimate Confirmed
			</td>
			<td>
				<?php echo getDateTimeWithTimestamp($workorder_data->date_created);?>
			</td>
		</tr>
		<?php if (!empty($statuses)) : ?>
				<?php foreach($statuses as $status) : ?>
				<tr>
					<td>
						<?php echo $status['wo_status_name']; ?>
					</td>
					<td>
						<?php echo date(getDateFormat(), $status['status_date']); ?>
					</td>
				</tr>
				<?php endforeach;?>

		<?php endif;?>
		</tbody>
	</table>
</section>
