
	
		
	<div class="row">
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Other Stats</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td>Average Amount<br>Paid Per Day</td>
                        <td><?php echo money(round($avarage_payments_day, 2)); ?></td>
					</tr>
					<tr>
						<td>Average Amount<br>Paid Per Month</td>
                        <td><?php echo money(round($avarage_payments_month, 2)); ?></td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>

		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Likes</header>
				<table class="table table-striped table-pulse">
					<tbody>
						<tr>
							<td>Likes</td>
							<td style="text-align:center;"><?php echo $invoices_like; ?></td>
						</tr>
						<tr>
							<td>Dislikes</td>
							<td style="text-align:center;"><?php echo $invoices_dislike; ?></td>
						</tr>
					</tbody>
				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Leads Services Stats</header>
				<table class="table table-striped table-pulse">
					<thead>
					<tr>
						<th>Lead Service</th>
						<th>Count</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($blocks as $k=>$v) : ?>
						<tr>
							<td><?php echo $k; ?></td>
							<td><?php echo ($v['count']) ? money($v['count'], 2) : '-'; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					
				</table>
			</section>
		</section>
	</div>
	<div class="row">
		<section class="col-md-3">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Client Contacts Stat</header>
				<table class="table table-striped table-pulse">
					<thead>
					<tr>
						<th>Weekday</th>
						<th>Contacts</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($calls_stat as $call) : ?>
						<tr>
							<td><?php echo $call['weekday']; ?></td>
							<td><?php echo $call['count']; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</section>
		</section>

		<section class="col-md-3">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Client Payments Stat</header>
				<table class="table table-striped table-pulse">
					<thead>
					<tr>
						<th>Weekday</th>
						<th>Payments</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($payments_stat as $payment) : ?>
						<tr>
							<td><?php echo $payment['weekday']; ?></td>
							<td><?php echo $payment['count']; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</section>
		</section>

		<section class="col-md-3">
			<section class="panel panel-default p-n">
				<header class="panel-heading">New Clients Stat</header>
				<table class="table table-striped table-pulse">
					<thead>
					<tr>
						<th>Weekday</th>
						<th>Clients</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($clients_stat as $client) : ?>
						<tr>
							<td><?php echo $client['weekday']; ?></td>
							<td><?php echo $client['count']; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</section>
		</section>

		<section class="col-md-3">
			<section class="panel panel-default p-n">
				<header class="panel-heading">New Estimates Stat</header>
				<table class="table table-striped table-pulse">
					<thead>
					<tr>
						<th>Weekday</th>
						<th>Estimates</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($estimates_stat as $estimate) : ?>
						<tr>
							<td><?php echo $estimate['weekday']; ?></td>
							<td><?php echo $estimate['count']; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</section>
		</section>
	</div>
