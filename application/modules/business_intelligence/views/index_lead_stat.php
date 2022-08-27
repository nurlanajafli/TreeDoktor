<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.pie.min.js'); ?>"></script>
<style type="text/css">
#flotcontainer{
	width: 100%;
	height: 450px;
	display: inline-block;
}
.pieLabel{font-weight: bold;}
.plot-label{color: inherit!important;}
.legendLabel{text-align: left;padding-left: 5px;}
</style>
	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">Reports</li>
		</ul>
		<section class="panel panel-default">
			<header class="panel-heading">Leads | Statistic By Statuses 
				<div class="pull-right" style=" ">
					<form id="dates" method="post" action="<?php echo base_url('business_intelligence/lead_statistics'); ?>" class="input-append m-t-xs">
						<label>
<!--                            value="--><?php //if ($from) : echo date('Y-m-d', $from);
//                            else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?><!--">-->
							<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
                                   value="<?php if ($from) : echo getDateTimeWithTimestamp($from);
                                   else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
						</label>
						— 
						<label>
<!--                            value="--><?php //if ($to) : echo date('Y-m-d', $to);
//                            else : echo date('Y-m-d'); endif; ?><!--">-->
							<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
                                   value="<?php if ($to) : echo getDateTimeWithTimestamp($to);
                                   else : echo date(getDateFormat()); endif; ?>">
						</label>
						<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					</form>
				</div>
				<div class="clear"></div>
			</header>
		</section>
		<section class="panel panel-default p-20">
			<div class="row">
				<div class="col-md-6">
					<div class="display:table">
						<table class="table  m-b-none b-a">
							<thead>
								<tr> 
									<th class="text-center bg-light b-r b-b "  colspan="2">STATUS</th> 
									<th class="text-center bg-light b-r b-b ">TOTAL</th>   
								</tr>
							</thead>
							<tbody>
							<?php if(isset($data) && !empty($data)) : ?>
								<tr>
									<td class="text-center   b-r b-b" colspan="2"><strong>TOTAL</strong></td>
									<td class="text-center  b-r b-b "><strong><?php echo $total; ?></strong></td>
								</tr>
								<?php foreach($data as $key=>$val) :   ?>
									<?php if(isset($val['reasons']) && !empty($val['reasons'])) :  ?>
										<tr class="nogo">
											<td class="text-center  b-b"><strong><?php echo $val['status_value']; ?>:</strong></td>
											<td class="text-center b-r b-b">&nbsp;</td>
											<td class="nogoTotal text-center b-r b-b"><strong><?php echo $val['total'];?></strong></td>
										</tr>
										<?php foreach($val['reasons'] as $k=>$v) :  ?>
											<tr> 
												<td class="  b-b">&nbsp;</td>
												<td class="b-r b-b"><strong><?php echo ($v['status_value']) ? $v['status_value'] : "No Information" ; ?></strong></td>
												<td class="text-center b-r b-b"><strong><?php echo $v['total']; ?></strong></td>
											</tr> 
										<?php endforeach; ?>
									<?php else : ?>
										<tr>
											<td class="text-center b-b"><strong><?php echo $val['status_value']; ?></strong></td> 
											<td class="b-r b-b">&nbsp;</td>
											<td class="text-center b-r b-b"><strong><?php echo $val['total']; ?></strong></td>
										</tr>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php else : ?>
								<tr><td colspan="5" class="b-r b-b">No records found</td></tr>
							<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-6">
					<div class="plots text-center">
						<div id="flotcontainer" class="plotContainer"></div>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</section>
	</section>
<input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
	<script>
	var leadsData = <?php echo isset($data) ? json_encode($data) : []; ?>; 
	var data = [];
	var all = <?php echo $total; ?>; 
	$(document).ready(function () { 
		// $('.datepicker').datepicker({format: 'yyyy-mm-dd'});
        $('.datepicker').datepicker({format: $('#php-variable').val()});
		$.each(leadsData, function (key, val) {
			var perc = (val.total / all * 100).toFixed(2) + '%';
			var label = perc + ' ' + key + ' (' + val.total + ')';
			if(key === 'No Go')
			{
				$.each(val.reasons, function (k, v) {
					perc = (v.total / all * 100).toFixed(2) + '%';
					data.push({
					  label: perc + ' ' + k + ' (' + v.total + ')',
					  data: val.total
					});
				});
				return true;
			}
			data.push({
			  label:  label,
			  data: val.total
			});
		});

		var options = {
				series: {
					pie: {
						show: true,
						innerRadius: 0.3,
						label:{                        
							radius: 0.6,
						}
					}
				},
				legend: {
					show: true
				},
				grid: {
					hoverable: false,
					clickable: false
				}
			 };
		var sum = 0;
		$.plot($("#flotcontainer"), data, options);
	});
</script>
<?php $this->load->view('includes/footer'); ?>
