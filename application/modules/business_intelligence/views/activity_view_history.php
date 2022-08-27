<?php $this->load->view('includes/header');?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Users History Log</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Users Auth Log
			<form method="POST" style="display: inline-block;">
				<select name="user" class="form-control m-l-md" style="width:200px;"
				        onchange="location.href = baseUrl + 'business_intelligence/history/' + $(this).val()">
					<option value="all"<?php if ($currentuser == 'all') : ?> selected<?php endif; ?>>*All*</option>
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
					<th width="160px">User</th>
					<th width="350px">URL</th>
					<th width="165px">Date</th>
					<th width="130px">User IP</th>
					<th>$_POST</th>
					<th>$_GET</th>
				</tr>
				</thead>
				<tbody>
				<?php if (!empty($logs)) : ?>
					<?php foreach ($logs as $log) : ?>
						<tr>
							<td>
								<small>
									<?php echo ($log['firstname']) ? $log['firstname'] . ' ' . $log['lastname'] : '—' ; ?>
								</small>
							</td>
							<td>
								<small>
									<?php echo base_url($log['log_url']); ?>
								</small>
							</td>
							<td>
								<small>
                                    <?php echo getDateTimeWithDate($log['log_date'], 'Y-m-d H:i:s', true) ?>
								</small>
							</td>
							<td>
								<a href="http://www.ipaddresslocation.org/ip-address-locator.php?lookup=<?php echo $log['log_user_ip']; ?>"
								   target="_blank"><?php echo $log['log_user_ip']; ?></a>
							</td>
							<td>
								<small>
									<?php if ($log['log_postdata'] && $other = json_decode($log['log_postdata'])) : ?>
										<?php foreach ($other as $key => $val) : ?>
											<strong><?php echo $key; ?>:</strong>
											<?php if(!is_array($val) && !is_object($val)) : ?>
												<?php echo htmlspecialchars($val); ?>
											<?php else : ?>
												<?php var_dump($val); ?>
											<?php endif; ?><br>
										<?php endforeach; ?>
									<?php else : ?>—
									<?php endif; ?>
								</small>
							</td>
							<td>
								<small>
									<?php if ($log['log_getdata'] && $other = json_decode($log['log_getdata'])) : ?>
										<?php foreach ($other as $key => $val) : ?>
											<strong><?php echo $key; ?>:</strong> <?php echo htmlspecialchars($val); ?><br>
										<?php endforeach; ?>
									<?php else : ?>—
									<?php endif; ?>
								</small>
							</td>
						</tr>
					<?php endforeach; ?>
					<?php if (isset($pagging) && $pagging) : ?>
						<tr>
							<td colspan="4">
								<?php echo $pagging; ?>
							</td>
							<td colspan="2"></td>
						</tr>
					<?php endif; ?>
				<?php else : ?>
					<tr>
						<td colspan="6" style="text-align:center;">No Records Found</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>

<?php $this->load->view('includes/footer'); ?>
