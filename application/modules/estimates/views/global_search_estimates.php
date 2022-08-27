<?php $this->load->view('includes/header'); ?>
<!-- Title -->
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Search Estimates</li>
	</ul>
	<!-- Estimates header -->
	
	<section class="panel panel-default" style="min-height: calc(100% - 64px);">
		<header class="panel-heading">Estimates
			<div class="btn-group  pull-right" style="margin-top: -8px; padding-right:15px; padding-left:15px;">
				<!-- Search Estimates -->
				<?php $this->load->view('includes/estimateSearch'); ?>
			</div>
		</header>
		<div class="">
			<table class="table table-striped b-t b-light m-n" id="tbl_Estimated">
				<thead>
					<tr>
						<th width="200px">Client Name</th>
						<th width="350px">Address</th>
						<th width="350px">Phone</th>
						<th width="90px">Estimate</th>
						<th width="110px">Price</th>
						<th width="110px">Date</th>
						<th width="130px">Estimator</th>
						<th width="100px">Status</th>
						<th width="85px">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php if (isset($estimates) && !empty($estimates)) : ?>
						<?php foreach ($estimates as $key=>$rows) : ?>
							<tr>
								<td width="200"><?php echo anchor('client/' . $rows->client_id, $rows->client_name); ?></td>
								<td><?php echo $rows->lead_address . ",&nbsp;" . $rows->lead_city . ",&nbsp;" . $rows->lead_state . ",&nbsp;" . $rows->lead_zip; ?></td>
								<td>
									<?php if($rows->cc_phone) : ?>
										<a href="#" class="<?php if($rows->cc_phone == numberTo($rows->cc_phone)) : ?>text-danger<?php else : ?>createCall<?php endif;?>" data-client-id="<?php echo $rows->client_id; ?>" data-number="<?php echo substr($rows->cc_phone, 0, 10);?>">
											<?php echo numberTo($rows->cc_phone); ?>
										</a>
									<?php else : ?>
										-
									<?php endif ;?>
								</td>
								<td width="60"><?php echo $rows->estimate_no; ?></td>
                                <td width="75"><?php echo money($rows->sum_without_tax); ?></td>
								<td width="75"><?php echo date(getDateFormat(), $rows->date_created); ?></td>
								<td width="100"><?php echo $rows->estimator; ?></td>
								<td width="100"><?php echo $rows->est_status_name; ?></td>
								<td width="70">
									<?php echo anchor('estimates/edit/' . $rows->estimate_id, '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default"') ?>
									<?php echo anchor($rows->estimate_no, '<i class="fa fa-eye"></i>', 'class="btn btn-xs btn-default"') ?>
								</td>
							</tr>
						<?php endforeach; ?>
						<?php /*if ($estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate_links']) : ?>
							<tr>
								<td colspan="8" style="color:#FF0000;">
									<?php //echo $estimates[mb_strtolower(str_replace($symbols, '_', $current_status->est_status_name)) . '_estimate_links']; ?>
								</td>
							</tr>
						<?php endif;*/ ?>
					<?php  else : ?>
						<tr>
							<td colspan="10" style="color:#FF0000;">No record found</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
        <?php if($links) : ?>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-sm-6 text-right text-center-xs pull-right">
					<?php echo $links; ?>
				</div>
			</div>
		</footer>
        <?php endif; ?>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
