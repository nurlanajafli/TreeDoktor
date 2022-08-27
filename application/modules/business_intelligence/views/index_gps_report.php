<?php $this->load->view('includes/header'); ?>
 
	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">General</li>
		</ul>
		<section class="panel panel-default p-n">
		<header class="panel-heading">Filter
			<div class="pull-right" >
				<form id="dates" method="post" action="<?php echo base_url('business_intelligence/get_gps_report'); ?>" class="input-append m-t-xs">
					<label>
						<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
							   value="<?php if ($from) : echo date('Y-m-d', strtotime($from));
							   else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>">
					</label>
					— 
					<label>
						<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
							   value="<?php if ($to) : echo date('Y-m-d', strtotime($to));
							   else : echo date('Y-m-d'); endif; ?>">
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
		</section>
	<div class="row">
		<section class="col-md-12">
			<section class="panel panel-default p-n">
				<header class="panel-heading">GPS Data</header>
				<div class="table-responsive">
					<table class="table table-striped table-pulse"> 
						<thead>
						<tr>
							<th>#</th>
							<th>Name</th>
							<th class="text-center">Time (hrs)</th> 
							<th class="text-center">Distanse (km)</th>
							<th class="text-center">AVG Time (hrs)</th>
							<th class="text-center">AVG Distanse(km)</th>
							<th class="text-center">Days</th>
							<th class="text-center">Schedule Days</th>
						</tr>
						</thead>
						<tbody>
						<?php if(isset($data) && !empty($data)) : ?>
						<?php $count = 1; ?>
						<?php $totalTime = 0;?>
						<?php $totalDis = 0;?>
						<?php foreach ($data as $k=>$v) :  ?>
							<tr>
								<td><?php echo $count; ?></td> 
								<td><?php echo $v['name']; ?></td> 
								 
									<td class="text-center"><?php echo sprintf('%02d:%02d', (int) round($v['time'] / 3600, 2), fmod(round($v['time'] / 3600, 2), 1) * 60); ?></td>
									<td class="text-center"><?php echo $v['distance']; ?> </td>
									<td class="text-center"><?php echo sprintf('%02d:%02d', (int) round($v['time'] / $v['days'] / 3600, 2), fmod(round($v['time'] /  $v['days'] / 3600, 2), 1) * 60);  ?></td>
									<td class="text-center"><?php echo round(($v['distance'] / $v['days']), 2);   ?> kms.  </td>
									<td class="text-center"><?php echo $v['days']; ?></td>
									<td class="text-center"><?php echo $v['schedule_days']; ?></td>
								<?php $totalTime += $v['time'];?>
								<?php $totalDis += $v['distance'];?>
							</tr>
							<?php $count++; ?>
						<?php endforeach; ?>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
							<td class="text-center"><strong>Total: <?php echo sprintf('%02d:%02d', (int) round($totalTime / 3600, 2), fmod(round($totalTime / 3600, 2), 1) * 60); ?></strong></td>
							<td class="text-center"><strong>Total:<?php echo $totalDis;?></strong></td>
							<td></td>
						</tr>
						<?php else : ?>
							<tr ><td colspan="8">No records found</td></tr>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</section>
	</div> 
	</section>
	 
<?php $this->load->view('includes/footer'); ?>
