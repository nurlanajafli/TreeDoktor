<section class="panel panel-default p-n">
	<header class="panel-heading">Corporate Calls Productivity:&nbsp; Overall</header>
	<div class="table-responsive">
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Caller name</th>
				<th>From</th>
				<th>To</th>
				<th>Count of Calls</th>
				<th>Daily Avarege</th>
			</tr>
			</thead>
			<tbody>
			<?php if (isset($users) && $users) : ?>
				<?php foreach ($users as $user) : ?>
					<tr>
						<td><?php echo $user['firstname']; ?> <?php echo $user['lastname']; ?></td>
						<td><?php echo $from; ?></td>
						<td><?php echo $to; ?></td>
						<td><?php echo $user['count']; ?></td>
						<td><?php echo round($user['count'] / ((strtotime($to) - strtotime($from)) / 86400), 1); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="5" style="color:#FF0000;">No record found</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</section>
