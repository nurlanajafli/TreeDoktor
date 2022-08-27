<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('reports'); ?>">Reports</a></li>
	<li class="active">Workorders</li>
</ul>
<section class="col-sm-7">
	<section class="panel panel-default p-n">
		<header class="panel-heading">Corporate Productivity:&nbsp; Overall</header>
		<table class="table table-striped bg-whote">
			<thead>
			<tr>
				<th>Workorder Status</th>
				<th>Qty:</th>
				<th>Revenue/Loss</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>Total:</td>
				<td><?php echo $total_workorders; ?></td>
				<td><?php echo money($revenue_total_workorders); ?></td>
			</tr>
			<?php $sum = 0; ?>
			<?php foreach($workorders as $key=>$val): ?>
				<tr>
					<td><?php echo $statuses[$key]['wo_status_name']; ?>:</td>
					<td><?php echo $workorders[$key]['qty']; ?></td>
					<td><?php echo money($workorders[$key]['revenue']); ?></td>
				</tr>
				<?php if($statuses[$key]['wo_status_id']) : ?>
				<?php $sum += $workorders[$key]['revenue'];?>
				<?php endif; ?>
			<?php endforeach; ?>
			
			<tr>
				<td></td>
				<td></td>
				<td><?php echo money($sum);?></td>
			</tr>
			</tbody>
		</table>
	</section>
</section>

<?php $bar_total = $revenue_total_workorders - $sum; ?>

<section class="col-sm-5">
	<section class="panel panel-default p-n">
		<header class="panel-heading">&nbsp;</header>
		<table class="table table-striped bg-whote">
			<thead>
			<tr>
				<th>&nbsp;</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td>&nbsp;</td>
			</tr>
			<?php $bar_total = $bar_total ? $bar_total : 1;?>
			<?php foreach($workorders as $key=>$val): ?>
			<tr>
				<td>
					<?php $bar = round($val['revenue'] * 100 / $bar_total, 2);?>
					<div class="progress progress-striped active m-n h-18">
						<div class="progress-bar progress-bar-info"
						     style="width: <?php echo $bar; ?>%"></div>
						&nbsp;-&nbsp;<?php echo $bar; ?>%
					</div>
				</td>
			</tr>
			<?php endforeach; ?>
			<tr>
				<td>&nbsp;</td>
			</tr>
			</tbody>
		</table>
	</section>
</section>
</section>
<?php $this->load->view('includes/footer'); ?>
