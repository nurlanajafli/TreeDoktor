<?php $this->load->view('includes/header'); ?>
<!-- Title -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Search Confirmed Estimates</li>
	</ul>
	<!-- Estimates header -->
	
	<section class="panel panel-default" style="min-height: calc(100% - 64px);">
		<header class="panel-heading">Estimates
			<div class="btn-group  pull-right" style="margin-top: -8px; padding-right:15px; padding-left:15px;">
				<!-- Search Estimates -->
				<?php $this->load->view('clients/client_search_estimates'); ?>
			</div>
		</header>
		<div class="">
			<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
				<thead>
					<tr>
						<th width="200px">Client Name</th>
						<th width="350px">Address</th>
						
						<th width="110px">Total</th>
						
					</tr>
				</thead>
				<tbody>
					<?php if (isset($estimates) && !empty($estimates)) : ?>
						<?php foreach ($estimates as $key=>$rows) : ?>
							<tr>
								<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
								<td><?php echo $rows->lead_address . ",&nbsp;" . $rows->lead_city . ",&nbsp;" . $rows->lead_state . ",&nbsp;" . $rows->lead_zip . ",&nbsp;" . $rows->lead_country; ?></td>

                                <td width="75"><?php echo money($rows->estimate_price); ?></td>
							</tr>
						<?php endforeach; ?>
						<?php //if ($estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate_links']) : ?>
							<tr>
								<td colspan="8" style="color:#FF0000;">
									<?php //echo $estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate_links']; ?>
								</td>
							</tr>
						<?php //endif; ?>
					<?php  else : ?>
						<tr>
							<td colspan="8" style="color:#FF0000;">No record found</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-sm-6 text-right text-center-xs pull-right">
					<?php echo $links; ?>
				</div>
			</div>
		</footer>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
