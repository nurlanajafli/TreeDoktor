<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Users Auth Log</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Users Auth Log
			<form method="POST" style="display: inline-block;">
				<select name="user" class="form-control m-l-md" style="width:200px;"
				        onchange="location.href = baseUrl + 'business_intelligence/activity/' + $(this).val()">
					<option value="all"<?php if ($currentuser == 'all') : ?> selected<?php endif; ?>>*All*</option>
					<option value="0"<?php if ($currentuser === '0') : ?> selected<?php endif; ?>>**Incorrect**</option>
					<?php foreach ($users as $user) : ?>
						<option
							value="<?php echo $user['id']; ?>"<?php if ($currentuser == $user['id']) : ?> selected<?php endif; ?>><?php echo $user['firstname'] . ' ' . $user['lastname']; ?></option>
					<?php endforeach; ?>
				</select>
			</form>
		</header>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th width="25%">User</th>
					<th width="25%">Login Date</th>
					<th width="25%">User IP</th>
					<!--<th width="25%">Other</th>-->
				</tr>
				</thead>
				<tbody>
				<?php if (!empty($logs)) : ?>
					<?php foreach ($logs as $log) : ?>
						<tr>
							<td>
								<?php echo ($log['firstname']) ? $log['firstname'] . ' ' . $log['lastname'] : '—'; ?>
							</td>
							<td>
<!--								--><?php //echo date('Y-m-d H:i:s', $log['log_time']); ?>
                                <?php echo getDateTimeWithTimestamp($log['log_time'], true) ?>
							</td>
							<td>
								<a href="http://www.ipaddresslocation.org/ip-address-locator.php?lookup=<?php echo $log['log_user_ip']; ?>"
								   target="_blank"><?php echo $log['log_user_ip']; ?></a>
							</td>
							<?php /*
							<td>
								<?php if ($log['log_data'] && $other = json_decode($log['log_data'])) : ?>
									<?php foreach ($other as $key => $val) : ?>
										<strong><?php echo $key; ?>:</strong> <?php echo $val; ?><br>
									<?php endforeach; ?>
								<?php endif; ?>
							</td>
							*/ ?>
						</tr>
					<?php endforeach; ?>
					<?php if (isset($pagging) && $pagging) : ?>
						<tr>
							<td colspan="4">
								<?php echo $pagging; ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php else : ?>
					<tr>
						<td colspan="3" style="text-align:center;">No Records Found</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>

<?php $this->load->view('includes/footer'); ?>
