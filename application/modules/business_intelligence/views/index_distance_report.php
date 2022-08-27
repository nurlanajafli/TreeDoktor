<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('equipments'); ?>">Equipments</a></li>
		<li class="active">Distance Report</a></li>
	</ul>
<?php $href = base_url() .  'business_intelligence/' . $this->uri->segment(2) . '/'; ?>
<section class="col-sm-12 panel panel-default p-n">
	<header class="panel-heading">Distance Report
		<div class="pull-right"  >
			<form id="dates" method="post" action="<?php echo $href; ?>" class="input-append m-t-xs">
				<label>
					<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
						   value="<?php if ($from) : echo $from;
						   else : echo date('Y-m-01'); endif; ?>">
				</label>
				— &nbsp;&nbsp;
				<label>
					<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
						   value="<?php if ($to) : echo $to;
						   else : echo date('Y-m-t'); endif; ?>">
				</label>
				<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
			</form>
		</div>
		<div class="clear"></div>
	</header>

	<!-- Client Files Data -->
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
				<tr>
					<th width="350px">Item Name</th>
					<th width="100px" style="text-align: center;">Distance</th>
					<th width="180px" style="text-align: center;">Start Counter Value</th>
					<th width="180px" style="text-align: center;">End Counter Value</th>
					
				</tr>
				</thead>
				<tbody>

				<!-- Items -->
				<?php if (isset($distance) && !empty($distance)) : ?>
				<?php $summ = 0; ?>
					<?php foreach ($distance as $key=>$val) : ?>
						<?php $summ += round($val['count'], 2)?>
						<tr>
							<td>
								<a href="<?php echo base_url('equipments/profile') . '/' . $val['item_id']; ?>">
									<?php echo $val['item_name'] ? $val['item_name'] : 'N/A'; ?>
								</a>
							</td>
							<td style="text-align: center;">
								<?php echo $val['count'] ? 
								number_format($val['count'], 2, '.', ',')
								//round(, 2)
								: 'N/A'; ?>
							</td>
							<td style="text-align: center;">
								<?php echo $val['start_counter'] ?
								number_format($val['start_counter'], 2, '.', ',') : 'N/A'; ?>
							</td>
							<td style="text-align: center;">
								<?php echo number_format(($val['start_counter'] + $val['count']), 2, '.', ','); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					<tr>
						<td style="text-align: center;"><strong>Total:</strong></td>
						<td style="text-align: center;"><strong><?php echo number_format($summ, 2, ',', ''); ?></strong></td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
				<?php else : ?>
				<?php endif; ?>

				<tbody>
			</table>
		</div>
	</section>
</section>
<script>
	$(document).ready(function (){
		$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
	});
</script>
<?php $this->load->view('includes/footer'); ?>
