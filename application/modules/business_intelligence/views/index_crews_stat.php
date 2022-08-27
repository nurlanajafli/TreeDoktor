<?php $this->load->view('includes/header');?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Crews Statistic</li>
	</ul>
	<section class="panel panel-default p-n">
		
		<header class="panel-heading">Filter
			<div class="pull-right">
				
				<form id="dates" method="post" action="<?php echo base_url('business_intelligence/crews_statistic'); ?>" class="input-append m-t-xs">
					 
					<label>
						<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
                               value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d 00:00:00');
                               else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
					</label>
					— &nbsp;&nbsp;
					<label>
						<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
                               value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d 23:59:59');
                               else : echo date(getDateFormat()); endif; ?>">
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

	<section class="col-sm-12 panel panel-default p-n">
		<header class="panel-heading">Crews Reports:</header>
		<div class="table-responsive">
			<table class="table table-hover" id="tbl_Estimated">
				<thead>
					<tr>
						<th>Crew</th>
						<th>AVG</th>
						<th>Team Amount</th>
						<th>Team MHRS</th>
					</tr>
				</thead>
				<tbody id="estimatorFiles">
				<?php if(isset($data) && !empty($data)) : ?>
					
					<?php foreach($data as $key=>$val) : ?>
						
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
		</div>
	</section>

 
</section>
<?php $this->load->view('includes/footer'); ?>
