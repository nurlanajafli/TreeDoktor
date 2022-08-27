<table id="counter_table" cellpadding="0" cellspacing="0" border="0" class="table table-striped m-b-none p-10">

	<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
	<tr>
		<td align="left" width="33%" class="border-right p-top-10">Schedule Reports</td>
		<td align="left" width="20%" class="p-top-10 p-sides-5" colspan="2">
			<?php if(empty($events)) : ?>
				0
			<?php else: ?>
				<div class="btn-group clear" style="display: block;overflow: visible;">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #8ec165;">
						<strong id="reportsCounter">
							<?php echo $countEventsReport; ?>
						</strong>
					</a>
					<ul class="dropdown-menu on animated fadeInRight eventsList" style="max-height: 300px;overflow-y: scroll;overflow-x: hidden;right: 0px;">
						
						<?php foreach($events as $key => $command) : ?>
							
							<li data-leader_id="<?php echo ($command[0]['team_leader_user_id']) ? $command[0]['team_leader_user_id'] : $command[0]['team_leader_id']; ?>">
								<a href="#reportInfoModal" data-id="<?php echo $command[0]['team_id']; ?>" class="reportInfo" data-toggle="modal" data-backdrop="static" data-keyboard="false" style="display: inline-block;  width: 100%;">
									<?php echo $command[0]['crew_name']; ?>
									(<?php echo $command[0]['leader_name'];  ?>)
									<?php echo date('<\b><\u>'.getDateFormat().'</\u></\b>', $command[0]['event_start']); ?>
								</a>
							</li>
							
						<?php endforeach; ?>

						<?php /*foreach($events as $key => $command) : ?>
							
							<li data-leader_id="<?php echo ($command[0]['team_leader_user_id']) ? $command[0]['team_leader_user_id'] : $command[0]['team_leader_id']; ?>">
								<a href="#commandInfo-<?php echo $command[0]['team_id']; ?>" class="reportInfo" data-toggle="modal" data-backdrop="static" data-keyboard="false" style="display: inline-block;  width: 100%;">
									<?php echo $command[0]['crew_name']; ?>
									(<?php echo $command[0]['leader_name'];  ?>)
									<?php echo date('<\b><\u>Y-m-d</\u></\b>', $command[0]['event_start']); ?>
								</a>
							</li>
							
						<?php endforeach;*/ ?>
						
					</ul>
				</div>
				<?php /*foreach($events as $command) : ?>
					<?php $event['command'] = $command; ?>
					<?php $this->load->view('dashboard/event_info_reports', $event); ?>
				<?php endforeach;*/ ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>
	<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
	<tr>
		<td align="left" width="33%" class="border-right p-top-10">Estimator Reports</td>
		<td align="left" width="20%" class="p-top-10 p-sides-5" colspan="2">
			<?php if(!isset($reports) || empty($reports)) : ?>
				0
			<?php else: ?>
				<div class="btn-group clear" style="display: block;overflow: visible;">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="color: #8ec165;">
						<strong id="estimatorCounter">
						<?php echo isset($reports) && !empty($reports) ? count($reports) : 0;//countOk ?>
						</strong>
					</a>

					<ul class="dropdown-menu on animated fadeInRight eventsList" style="max-height: 300px;overflow-y: scroll;overflow-x: hidden;right: 0px;">
						<?php foreach($reports as $key => $report) : ?>
							<li data-report_id="<?php echo $report['report_id']; ?>">
								<a href="#reportInfo-<?php echo $report['report_id']; ?>" class="reportInfo" data-toggle="modal" data-backdrop="static" data-keyboard="false" style="display: inline-block;  width: 100%;">
									<?php echo $report['emp_name']; ?>
									<b><?php echo $report['report_date']; ?></b>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php foreach($reports as $report) : $report['report'] = $report;?>
					<?php $this->load->view('profile_estimator_report', $report); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</td>
	</tr>

	<?php endif; ?>


<tr class="border-right">
	<td align="left" width="33%" class="border-right p-top-10">Clients</td>
	<td align="left" width="20%" class="p-top-10 p-sides-5"><?php echo $total_clients; ?></td>
	<?php 
	$file_success = "text-error";
	$plus = "-";
	if ($today_clients != "0"):
		$file_success = "text-success";
		$plus = "+";
	endif; 
	?>
	<td align="left" width="45%" class="p-top-10 p-sides-5 <?php echo $file_success ?>">
		<div class="btn-group clear" style="display: block;overflow: visible;">
			<?php echo $plus;
			echo $today_clients ? anchor('dashboard#', "<strong>" . $today_clients . "</strong>", 'style="color:#8ec165"  class="dropdown-toggle" data-toggle="dropdown"') : "<strong>" . $today_clients . "</strong>"; ?>
			<?php if ($today_clients_records) : ?>
				<ul class="dropdown-menu animated fadeInRight client-dropdown">
					<?php foreach ($today_clients_records as $client) : ?>
						<li>
							<span class="inline">
                                <a href="<?php echo base_url($client->client_id) ?>"><?php echo $client->client_name; ?></a>
                            </span>
							<span class="pull-right">
								<?php if(isset($client->estimator) && $client->estimator) :?>
									<?php echo mb_strtoupper($client->estimator); ?>
								<?php elseif(isset($client->refferal) && $client->refferal) : ?>
									<?php echo mb_strtoupper($client->refferal); ?>
								<?php elseif(isset($references[$client->lead_reffered_by])) : ?>
									<?php echo mb_strtoupper($references[$client->lead_reffered_by]);?>
								<?php endif;  ?>
							</span>
							<div class="clear"></div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</td>
</tr>
<tr class="border-right">
	<td align="left" width="33%" class="border-right p-top-5">Leads</td>
	<td align="left" width="20%" class="p-top-5 p-sides-5"><?php echo $total_leads ?></td>
	<?php 
	$file_success = "text-error";
	$plus = "-";
	if ($today_leads != "0"):
		$file_success = "text-success";
		$plus = "+";
	endif;
	?>
	<td align="left" width="45%" class="p-top-5 p-sides-5 <?php echo $file_success ?>">
		<div class="btn-group clear" style="display: block;overflow: visible;">
			<?php echo $plus;
			echo $today_count_leads ? anchor('dashboard#', "<strong>" . $today_count_leads . "</strong>", 'style="color:#8ec165"  class="dropdown-toggle" data-toggle="dropdown"') : "<strong>" . $today_count_leads . "</strong>"; ?>
			<?php $newClients = TRUE; ?>
			<?php if ($today_leads_records) : ?>
				<ul class="dropdown-menu animated fadeInRight ">
					<?php foreach($today_leads as $personal) : ?>
						<li>
							<a style="text-decoration:none">
                                <strong>
                                    <?php echo user_fullname($personal, 'N/A'); ?>
                                </strong>: <?php echo $personal['count']; ?> lead(s)
							</a>
						</li>
					<?php endforeach; ?>
					<li class="divider"></li>
					
					<?php foreach ($today_leads_records as $key => $lead) : ?>
						<?php if($newClients && $lead->client_date_created == $lead->lead_date_created) : ?>
							<?php if($key) : ?><li class="divider"></li><?php endif; ?>
							<li><span style="display: block;color: #000;text-align: center;font-weight: bold;">New Clients</span></li>
							<?php $newClients = false; ?>
						<?php endif; ?>
						<li><?php echo anchor($lead->client_id, $lead->lead_no . ' - ' . $lead->lead_created_by); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</td>
</tr>
<tr class="border-right">
	<td colspan="3" style="padding-left:0; padding-right: 0;">
		<div class="product_pulse_div">
			
			<?php if(isset($today_leads_cate) && !empty($today_leads_cate)) : ?>
				<table class="table table-striped table-pulse m-b-none">
					<thead>
					<tr>
						<th width="33%">Leads Status</th>
						<th width="20%">QTY:</th>
						<th width="45%"></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($today_leads_cate as $row) : ?>
						<tr>
							<td><?php echo $row['status']; ?>:</td>
							<td><?php 
								if ($row['count']):
									echo '+' . $row['count'];
								else:
									echo '-' . $row['count'];
								endif; 
								?>
							</td>
							<td></td>
						</tr>
					<?php endforeach; ?>
					
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</td>
</tr>
<tr class="border-right">
	<td align="left" width="33%" class="border-right p-top-5">Estimated</td>
	<td align="left" width="20%" class="p-top-5 p-sides-5"><?php echo $total_estimates ?></td>
	<?php 
	$file_success = "text-error";
	$plus = "-";
	if ($today_estimates != "0"):
		$file_success = "text-success";
		$plus = "+";
	endif; 
	?>
	<td align="left" width="45%" class="p-top-5 p-sides-5 <?php echo $file_success ?>">
		<div class="btn-group pull-left clear" style="display: inline-block;overflow: visible;">
			<?php echo $plus;
			echo !empty($today_estimates_records) ? anchor('dashboard#', "<strong>" . count($today_estimates_records) . "</strong>", 'style="color:#8ec165"  class="dropdown-toggle" data-toggle="dropdown"') : "<strong>" . count($today_estimates_records) . "</strong>";//countOk ?>
			<?php if ($today_estimates_records || $today_nogo_leads_records) : ?>
				<ul class="dropdown-menu animated fadeInRight ">
					<?php foreach($today_estimates as $personal) : ?>
						<li>
							<a style="text-decoration:none">
                                <strong>
                                    <?php echo user_fullname($personal); ?>
                                </strong>: <?php echo $personal['count']; ?>
                                estimate(s) - <?php echo money($personal['estimated_sum']); ?>
							</a>
						</li>
					<?php endforeach; ?>
					<li class="divider"></li>
					<?php foreach ($today_estimates_records as $estimate) : ?>
						<li>
							<a href="<?php echo base_url($estimate->estimate_no); ?>">
								<?php echo $estimate->estimate_no; ?>
								<?php if ($estimate->firstname && $estimate->lastname) : ?>
									<small> - <?php echo money($estimate->sum_without_tax); ?> (<?php echo $estimate->firstname . " " . $estimate->lastname; ?>)</small>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php $symbols = array(' - ', ' ','-'); ?>
		<?php $sum = 0; ?>
		<?php foreach($statuses as $key=>$val) : ?>
			<?php $sum += $estimates['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates']; ?>
		<?php endforeach; ?>
		<strong class="pull-right"><?php echo money($sum); ?></strong>
	</td>
</tr>
<tr class="border-right">
	<td align="left" width="33%" class="border-right p-top-5">NoGo</td>
	<td align="left" width="20%" class="p-top-5 p-sides-5"></td>
	<?php 
	$file_success = "text-error";
	$plus = "-";
	if (!empty($today_nogo_leads_records)):
		$file_success = "text-success";
		$plus = "+";
	endif;
	?>
	<td align="left" width="45%" class="p-top-5 p-sides-5 <?php echo $file_success ?>">
		<div class="btn-group pull-left clear" style="display: inline-block;overflow: visible;">
				<?php echo $plus;
				echo !empty($today_nogo_leads_records) ? anchor('dashboard#', "<strong>" . count($today_nogo_leads_records) . "</strong>", 'style="color:#8ec165"  class="dropdown-toggle" data-toggle="dropdown"') : "<strong>" . count($today_nogo_leads_records) . "</strong>";//countOk ?>
				<?php if ($today_nogo_leads_records || $today_nogo_leads_records) : ?>
					<ul class="dropdown-menu animated fadeInRight " style="    left: -200px;">
						<?php if($today_nogo_leads_records && !empty($today_nogo_leads_records)) : ?>
							<?php foreach($today_nogo_leads_records as $lead) : ?>
								<li>
									<a href="<?php echo base_url($lead['lead_no']); ?>">
										<span>
											<?php echo $lead['lead_no']; ?><?php if ($lead['firstname'] && $lead['lastname']) : ?><small>(<?php echo $lead['firstname'] . " " . $lead['lastname']; ?>)</small><?php endif; ?><br><strong><?php echo mb_convert_case($lead['reason_name'], MB_CASE_UPPER, "UTF-8"); ?></strong>
										</span>
									</a>
								</li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php $symbols = array(' - ', ' ','-'); ?>
			<?php $sum = 0; ?>
			<?php foreach($statuses as $key=>$val) : ?>
				<?php $sum += $estimates['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates']; ?>
			<?php endforeach; ?>
			<strong class="pull-right"><?php echo money((30 * count($today_nogo_leads_records)));//countOk ?></strong>
		</td>
</tr>
<tr class="border-right">
	<td colspan="3" style="padding-left:0; padding-right: 0;">
		<div class="product_pulse_div">
			<table class="table table-striped table-pulse m-b-none">
				<thead>
				<tr>
					<th width="33%">Estimate Status</th>
					<th width="20%">QTY:</th>
					<th width="45%">Revenue/Loss</th>
				</tr>
				</thead>
				<tbody>
				<?php $symbols = array(' - ', ' ','-'); ?>
				<?php foreach($statuses as $key=>$val) : ?>
				<tr>
					<td><?php echo $val->est_status_name; ?>:</td>
					<td><?php echo $estimates['corp_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates']; ?></td>
					<td><?php echo money($estimates['corp_revenue_' . mb_strtolower(str_replace($symbols, '_', $val->est_status_name)) . '_estimates']); ?></td>
				</tr>
				<?php endforeach; ?>
				
				</tbody>
			</table>
		</div>
	</td>
</tr>
<tr class="border-right">
	<td align="left" width="33%" class="border-right p-top-5">Workorders</td>
	<td align="left" width="20%" class=" p-top-5 p-sides-5">
		<?= $total_workorders ?>
	</td>
	<?php 
	$file_success = "text-error";
	$plus = "-";
	if ($today_workorders != "0"):
		$file_success = "text-success";
		$plus = "+";
	endif;
	?>
	<td align="left" width="45%" class=" p-top-5 p-sides-5 <?php echo $file_success ?>">
		<div class="btn-group clear pull-left" style="display: inline-block;overflow: visible;">
			<?php echo $plus;
			echo $today_count_workorders ? anchor('dashboard#', "<strong>" . $today_count_workorders . "</strong>", 'style="color:#8ec165"  class="dropdown-toggle" data-toggle="dropdown"') : "<strong>" . $today_count_workorders . "</strong>"; ?>
			<?php if ($today_workorders_records) : ?>
				<ul class="dropdown-menu animated fadeInRight">
					<?php foreach($today_workorders as $personal) : ?>
						<li>
							<a style="text-decoration:none">
                                <strong>
                                    <?php echo user_fullname($personal); ?>
                                </strong>: <?php echo $personal['count']; ?>
                                workorder(s) - <?php echo money($personal['estimated_sum']); ?>
							</a>
						</li>
					<?php endforeach; ?>
					<li class="divider"></li>
					<?php foreach ($today_workorders_records as $workorder) : ?>
						<li>
							<a href="<?php echo base_url($workorder->workorder_no); ?>">
								<?php echo $workorder->workorder_no; ?>
								<?php if ($workorder->firstname && $workorder->lastname) : ?>
									<small> - <?php echo money($workorder->sum_without_tax); ?> (<?php echo $workorder->firstname . " " . $workorder->lastname; ?>)</small>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<strong class="pull-right"><?php echo money($today_sum_workorders); ?></strong>
	</td>
</tr>
<tr><td colspan="3"></td></tr>
<tr class="border-right">
	<td align="left" width="33%" class="border-right p-top-5 p-bottom-10">Invoices</td>
	<td align="left" width="20%" class="p-top-5 p-bottom-10 p-sides-5"><?php echo $totalInvoicesCount; ?></td>
	<?php 
	$file_success = "text-error";
	$plus = "-";
	if ($totalInvoices->count_invoices):
		$file_success = "text-success";
		$plus = "+";
	endif;
	?>
	<td align="left" width="45%" class="p-top-5 p-bottom-10 p-sides-5 <?php echo $file_success ?>">
		<div class="btn-group clear pull-left" style="display: inline-block;overflow: visible;">
			<?php echo $plus;
			echo $totalInvoices->count_invoices ? anchor('dashboard#', "<strong>" . $totalInvoices->count_invoices . "</strong>", 'style="color:#8ec165"  class="dropdown-toggle" data-toggle="dropdown"') : "<strong>" . $totalInvoices->count_invoices . "</strong>"; ?>
			<?php if ($totalInvoicesByEstimator) : ?>
				<ul class="dropdown-menu animated fadeInRight">
					<?php foreach($totalInvoicesByEstimator as $personal) : ?>
						<li>
							<a style="text-decoration:none">
                                <strong><?php echo isset($personal->emailid) ? $personal->emailid : ''; ?></strong>: <?php echo $personal->count_invoices; ?>
                                invoice(s) - <?php echo money($personal->total_revenue); ?>
							</a>
						</li>
					<?php endforeach; ?>
					<li class="divider"></li>
					<?php foreach ($invoicesRows as $invoice) : ?>
						<li>
							<a href="<?php echo base_url($invoice->invoice_no); ?>">
								<?php echo $invoice->invoice_no; ?>
								<?php if ($invoice->firstname && $invoice->lastname) : ?>
                                    <small> - <?php echo money($invoice->sum_without_tax); ?>
                                        (<?php echo $invoice->firstname . " " . $invoice->lastname; ?>)</small>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<strong class="pull-right"><?php echo money($totalInvoices->total_revenue); ?></strong>
	</td>
</tr>
<tr class="border-right">
	<td colspan="3" style="padding-left:0; padding-right: 0;">
		<div class="product_pulse_div">
			<table class="table table-striped table-pulse m-b-none">
				<thead>
				<tr>
					<th width="33%">Invoice Status</th>
					<th width="20%">QTY:</th>
					<th width="45%">Revenue/Loss</th>
				</tr>
				</thead>
				<tbody>
					<?php foreach($totalInvoicesByStatus as $k=>$v) : ?>
						<tr>
							<td><?php echo $v->invoice_status_name;?> Invoices:</td>
							<td><?php echo $v->count_invoices; ?></td>
							<td><?php echo money($v->total_revenue); ?></td>
						</tr>
					<?php endforeach; ?>
					
				</tbody>
			</table>
		</div>
	</td>
</tr>
</table>

<style type="text/css">
	.editable-input textarea{ width: 358px!important; margin-bottom: 5px; }
	.members-expenses .editable-input textarea{ width: 313px!important; margin-bottom: 5px; }
    @media screen and (max-width: 1440px) {
        .client-dropdown{
            left: -80px;
        }
    }
    @media screen and (max-width: 375px) {
        .client-dropdown{
            left: -135px;
        }
    }
</style>
