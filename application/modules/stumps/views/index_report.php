<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('estimates'); ?>">Stumps</a></li>
		<li class="active">Stumps Report</li>
	</ul>
	<section class="panel panel-default p-n">

		<header class="panel-heading">Filter
			<div class="pull-right" style=" ">

				<form id="dates" method="post" action="<?php echo base_url('stumps/report'); ?>" class="input-append m-t-xs">

					<label>
						<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
						       value="<?php echo $from; ?>">
					</label>
					— &nbsp;&nbsp;
					<label>
						<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
						       value="<?php echo $to; ?>">
					</label>
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
				</form>
			</div>
			<div class="clear"></div>
            <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
		</header>
		<script>
			$(document).ready(function () {
                $('.datepicker').datepicker({format: $('#php-variable').val()});
			});
		</script>
	</section>

	<!-- Display New Estimates -->
	<div class="row">
		<section class="col-md-6">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Grinded Stumps Report:</header>
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Name</th>
								<th class="text-center">CMS</th>
								<th class="text-center">STPS</th>
								<th class="text-center">Client</th>
							</tr>
						</thead>
						<tbody id="estimatorFiles">
						<?php $cleaned_total_cms = 0; ?>
						<?php $cleaned_total_stps = 0; ?>
						<?php if (!empty($grinded)) : ?>
							<?php foreach ($grinded as $worker) : ?>
								<tr>
									<td><?php echo $worker['firstname'] . ' ' . $worker['lastname']; ?></td>
									<td class="text-center"><?php echo $worker['cm']; ?></td>
									<td class="text-center"><?php echo $worker['stps']; ?></td>
									<td class="text-center"><?php echo $worker['cl_name'] . ' ' . $worker['cl_lastname']; ?></td>
									<?php $cleaned_total_cms += $worker['cm']; ?>
									<?php $cleaned_total_stps += $worker['stps']; ?>
								</tr>
							<?php endforeach; ?>
							<tr>
								<td><strong>TOTAL:</strong></td>
								<td class="text-center"><strong><?php echo $cleaned_total_cms; ?></strong></td>
								<td class="text-center"><strong><?php echo $cleaned_total_stps; ?></strong></td>
								<td class="text-center"></td>
							</tr>
						<?php else : ?>
							<tr>
								<td colspan="2">
									<p style="color:#FF0000;"> No record found</p>
								</td>
							</tr>
						<?php endif;  ?>
						</tbody>
					</table>
				</div>
			</section>
		</section>

		<section class="col-md-6">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Cleaned Stumps Report:</header>
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
						<tr>
							<th>Name</th>
							<th class="text-center">CMS</th>
							<th class="text-center">STPS</th>
							<th class="text-center">Client</th>
						</tr>
						</thead>
						<tbody id="estimatorFiles">
						<?php $cleaned_total_cms = 0; ?>
						<?php $cleaned_total_stps = 0; ?>
						<?php if (!empty($cleaned)) : ?>
							<?php foreach ($cleaned as $worker) : ?>
								<tr>
									<td><?php echo $worker['firstname'] . ' ' . $worker['lastname']; ?></td>
									<td class="text-center"><?php echo $worker['cm']; ?></td>
									<td class="text-center"><?php echo $worker['stps']; ?></td>
									<td class="text-center"><?php echo $worker['cl_name'] . ' ' . $worker['cl_lastname']; ?></td>
									<?php $cleaned_total_cms += $worker['cm']; ?>
									<?php $cleaned_total_stps += $worker['stps']; ?>
								</tr>
							<?php endforeach; ?>
							<tr>
								<td><strong>TOTAL:</strong></td>
								<td class="text-center"><strong><?php echo $cleaned_total_cms; ?></strong></td>
								<td class="text-center"><strong><?php echo $cleaned_total_stps; ?></strong></td>
								<td class="text-center"></td>
							</tr>
						<?php else : ?>
							<tr>
								<td colspan="4">
									<p style="color:#FF0000;"> No record found</p>
								</td>
							</tr>
						<?php endif;  ?>
						</tbody>
					</table>
				</div>
			</section>
		</section>
	</div>
</section>
<?php $this->load->view('includes/footer'); ?>
