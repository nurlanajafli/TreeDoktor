<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Refferals</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Refferals <?php echo ucfirst($type); ?>s
			<div class="pull-right" style="margin-top:-0px;">
				<form id="dates" method="post" action="<?php echo base_url('business_intelligence') . '/refferals_' . $type . 's'; ?>" class="input-append m-t-xs">
					
					<label>
						<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
							   value="<?php if($from) : echo $from; ?>
							   <?php else : echo date('Y-m-01');  endif; ?>">
					</label>
					— 
					<label>
						<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
							   value="<?php if($to) : echo $to;
							   else : echo date('Y-m-t'); endif; ?>">
					</label>
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
				</form>
			</div>
			<div class="clear"></div>
		</header>
		<script>
			$(document).ready(function () {
				$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
			});
		</script>
		<div class="m-md">
			<div class="table-responsive">
			<table class="table table-striped b-t b-light" id="tbl_search_result">
				<thead>
				<tr>
					<th>Name</th>
					<th>Total Estimate</th>
					<th>Confirmed Estimate</th>
					<?php if($type == 'user') : ?>
						<th>Commision(5%)</th>
					<?php endif; ?>
				</tr>
				</thead>
				<tbody>
				<?php if(isset($refferals) && !empty($refferals)) : ?>
					<?php foreach($refferals as $key=>$refferal) : ?>
						<tr>
							<td>
								<?php if($type == 'client') : ?>
									<a href="<?php echo base_url($refferal['lead_reffered_client']); ?>">
										<?php echo $refferal['reffered']; ?>
									</a>
								<?php elseif($type == 'user') : ?>
									<?php echo $refferal['reffered']; ?>
								<?php endif; ?>
							</td>
							<td>
								
								<div class="btn-group pull-left clear" style="display: inline-block;overflow: visible;">
									<a href="#" style="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
										<strong><?php echo $refferal['count']; ?> (<?php echo money($refferal['sum']); ?>)</strong>
									</a>
									
									<ul class="dropdown-menu animated fadeInRight pull-right">
										<?php foreach($refferal['estimates'] as $est) : ?>
											<li>
												<a target="_blank" href="<?php echo base_url($est->estimate_no); ?>">
													<?php echo $est->estimate_no; ?>
													<small> - <?php echo money(round($est->sum, 2)); ?></small>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
								
								
								
							</td>
							<td>
								<div class="btn-group pull-left clear" style="display: inline-block;overflow: visible;">
									<a href="#" style="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
										<strong><?php echo $refferal['confirmed_count']; ?> (<?php echo money($refferal['confirmed_sum']); ?>)</strong>
									</a>
									<?php if(isset($refferal['confirmed_est']) && !empty($refferal['confirmed_est'])) : ?>
									<ul class="dropdown-menu animated fadeInRight pull-right">
										<?php foreach($refferal['confirmed_est'] as $est) : ?>
											<li>
												<a target="_blank" href="<?php echo base_url($est->estimate_no); ?>">
													<?php echo $est->estimate_no; ?>
													<small> - <?php echo money(round($est->sum, 2)); ?></small>
												</a>
											</li>
										<?php endforeach; ?>
									</ul>
									<?php endif;?>
								</div>
								
								
							</td>
							<?php if($type == 'user') : ?>
								<td>
									<?php if($refferal['confirmed_sum']) : ?>
										<?php echo money($refferal['confirmed_sum'] / 100 * 5); ?>
									<?php else : ?>
										-
									<?php endif; ?>
								</td>
							<?php endif; ?>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="4">Not found</td></tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		</div>
	</section>
</section>
<?php $this->load->view('includes/footer'); ?>
