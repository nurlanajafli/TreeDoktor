<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('schedule'); ?>">Schedule</a></li>
		<li class="active">Crews Statistic</li>
	</ul>
	<section class="panel panel-default p-n">
		
		<header class="panel-heading">Filter
			<div class="pull-right" style="margin-top:-14px;">
				
				<form id="dates" method="post" action="<?php echo base_url('schedule/crews_statistic'); ?>" class="input-append m-t-xs">
					 
					<label>
						<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
							   value="<?php if ($from) : echo date('Y-m-d', strtotime($from));
							   else : echo date('Y-m-01'); endif; ?>">
					</label>
					— &nbsp;&nbsp;
					<label>
						<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
							   value="<?php if ($to) : echo date('Y-m-d', strtotime($to));
							   else : echo date('Y-m-t'); endif; ?>">
					</label>
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
				</form>
			</div>
		
		</header>
		<script>
			$(document).ready(function () {
				$('.datepicker').datepicker({format: 'yyyy-mm-dd'});
			});
		</script>


	</section>

	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Crews Reports:</header>
		
		<table class="table table-hover" id="tbl_Estimated">
			<thead>
				<tr>
					<th>&nbsp;</th>
					
					<th class="text-center">AVG</th>
					<th class="text-center">Total Team Amount</th>
					<th class="text-center">Total Team MHRS</th>
				</tr>
			</thead>
			<tbody id="estimatorFiles">
			<?php if(isset($data_avg) && !empty($data_avg)) : ?>
				
				<?php foreach($data_avg as $key=>$val) : ?>
					
					<tr>
						<td width="170px"><?php echo $val['crew_full_name']; ?> (<?php echo $val['crew_name'];?>)</td>
                        <td width="170px"><?php echo money($val['avg']); ?> </td>
                        <td width="170px"><?php echo money($val['avg_team_amount']); ?> </td>
						<td width="170px"><?php echo $val['avg_team_mhrs']; ?> </td>
					</tr>
				<?php endforeach; ?>
				
			</tbody>
			<?php else : ?>
					<tr>
						<td>
							<p style="color:#FF0000;"> No record found</p>
						</td>
					</tr>
			<?php endif;  ?>
			</tbody>
		</table>
		
	</section>

 
</section>
<?php $this->load->view('includes/footer'); ?>
