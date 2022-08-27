<?php $this->load->view('includes/header'); ?>
<?php setlocale(LC_ALL, 'en_US.utf8', 'en_US'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Users Statistics</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Filter
			<div class="pull-right"  >
				<form id="dates" method="post" action="<?php echo base_url('business_intelligence/users_statistics'); ?>" class="input-append m-t-xs">
					<label>
						<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
                               value="<?php if (isset($from)) : echo getDateTimeWithDate($from, 'Y-m-d 00:00:00');
                               else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
					</label>
					— &nbsp;&nbsp;
					<label>
						<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
                               value="<?php if (isset($to)) : echo getDateTimeWithDate($to, 'Y-m-d 23:59:59');
                               else : echo date(getDateFormat()); endif; ?>">
					</label>
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
                    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
				</form>
				<script>
					$(document).ready(function () {
                        $('.datepicker').datepicker({format: $('#php-variable').val()});
					});
				</script>
			</div>
			<div class="clear"></div>
		</header>
	</section>
    <?php if(config_item('phone')) : ?>
	<section class="panel panel-default">
		<header class="panel-heading">Calls</header>
		<div class="table-responsive">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>User</th>
					<th>Incoming Calls / Duration</th>
					<th>Outgoing Calls / Duration</th>
					<th>All Calls / Duration</th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($calls && !empty($calls)) :
					foreach ($calls as $key => $val): ?>
							<tr class="">
								<td>
									<?php echo $val['username']; ?>
								</td>
								<td>
									<?php 
										  $hours = floor($val['income_duration']/3600);
										  $val['income_duration'] = $val['income_duration']-($hours*3600);
										  $minutes = floor($val['income_duration']/60); 
										  $val['income_duration'] = $val['income_duration'] - ($minutes*60);
									?>
									<?php echo intval($val['count_income_calls']) . ' call(s) / ' . sprintf("%02d", $hours) . ':' . sprintf("%02d", $minutes) . ':' . sprintf("%02d", $val['income_duration']); ?>
								</td>
								<td>
									<?php 
										  $hours = floor($val['outcome_duration']/3600);
										  $val['outcome_duration'] = $val['outcome_duration']-($hours*3600);
										  $minutes = floor($val['outcome_duration']/60); 
										  $val['outcome_duration'] = $val['outcome_duration'] - ($minutes*60);
									?>
									<?php echo intval($val['count_outcome_calls']) . ' call(s) / ' . sprintf("%02d", $hours) . ':' . sprintf("%02d", $minutes) . ':' . sprintf("%02d", $val['outcome_duration']); ?>
								</td>
								<td>
									<?php 
										  $hours = floor($val['duration']/3600);
										  $val['duration'] = $val['duration']-($hours*3600);
										  $minutes = floor($val['duration']/60); 
										  $val['duration'] = $val['duration'] - ($minutes*60);
									?>
									<?php echo $val['count_calls'] . ' call(s) / ' . sprintf("%02d", $hours) . ':' . sprintf("%02d",$minutes) . ':' . sprintf("%02d",$val['duration']); ?>
								</td>
							</tr>
						<?php endforeach; ?>
				<? else :
					?>
					<tr>
						<td colspan="4"><?php echo "No records found"; ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
    <?php endif; ?>
		<div class="row">
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">Created Leads</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($leads && !empty($leads)) :
								foreach ($leads as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_leads']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">Created Estimates</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($estimates && !empty($estimates)) :
								foreach ($estimates as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_estimates']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">Created Workorders</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($workorders && !empty($workorders)) :
								foreach ($workorders as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_workorders']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">Created Invoices</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($invoices && !empty($invoices)) :
								foreach ($invoices as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_invoices']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
		</div>
		<div class="row">
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">Sent Letters</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($letters && !empty($letters)) :
								foreach ($letters as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_letters']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">Created Clients</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($clients && !empty($clients)) :
								foreach ($clients as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_clients']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<?php else : ?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
			<section class="col-md-3">
				<section class="panel panel-default">
					<header class="panel-heading">User Links</header>
					<div class="table-responsive">
						<table class="table table-striped table-pulse">
							<thead>
								<tr>
									<th>User</th>
									<th class="text-center">Count</th>
								</tr>
							</thead>
							<tbody>
							<?php
							if ($refs && !empty($refs)) :
								foreach ($refs as $key => $val): ?>
										<tr class="">
											<td>
												<?php echo $val['username']; ?>
											</td>
											<td class="text-center">
												<?php echo $val['count_links']; ?>
											</td>
											 
										</tr>
									<?php endforeach; ?>
							<? else :
								?>
								<tr>
									<td colspan="2"><?php echo "No records found"; ?></td>
								</tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</section>
			</section>
		</div>
</section>
	
		<?php $this->load->view('includes/footer'); ?>
