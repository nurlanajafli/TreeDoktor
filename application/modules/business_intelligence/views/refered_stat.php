<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.tooltip.min.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/charts/flot/jquery.flot.pie.min.js'); ?>"></script>
			  <style type="text/css">
                @media print {
                  #flotcontainer{
                      position: absolute!important;
                      right: 0;
                      height: 50px;
                      width: 50px;
                  }
                  #flotcontainer .flot-base {width: 350px!important; height: 290px!important; left: 70px!important;}
                  #flotcontainer .flot-overlay {width: 350px!important; height: 290px!important; left: 70px!important;}
                  #flotcontainer .legend>table {left: 70px!important; width: 300px;}
                }
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
		<li class="active">Client Statistic</li>
	</ul>


		<section class="panel panel-default">
			<header class="panel-heading">
				<div class="pull-right" style="margin-top:-0px;">
					<form id="dates" method="post" action="<?php echo base_url('business_intelligence/statistic'); ?>" class="input-append m-t-xs">
						<label>
							<input name="from" class="datepicker form-control date-input-client from text-center" type="text" readonly
                                   value="<?php if ($from) : echo getDateTimeWithDate($from, 'Y-m-d');
                                   else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
						</label>
						— 
						<label>
							<input name="to" class="datepicker form-control date-input-client to text-center" type="text" readonly
                                   value="<?php if ($to) : echo getDateTimeWithDate($to, 'Y-m-d');
                                   else : echo date(getDateFormat()); endif; ?>">
						</label>
						<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
					</form>
				</div>
				<div class="clear"></div>
			</header>
		<div class="p-top-5">
			<div style="position: initial;" class="text-center col-md-8  col-sm-4 col-xs-12">

				<?php if(!isset($data) || !$data) : ?>
				<div style="display:table-cell;vertical-align: middle;color: #000;position: absolute;font-size: 50px;left: 0;right: 0;top: 30%;bottom: 0;">NO DATA</div>
				<?php else : ?>
						
						<div class="table-responsive">
							<table class="table m-b-none b-a">
								<thead>
									<tr>
										<th class="bg-light b-r b-b text-center" width="">Referral Type</th>
										<th class="bg-light b-r b-b text-center" width=""># Leads</th>
										<th class="bg-light b-r b-b text-center" width="">% Total Leads</th>
										<th class="bg-light b-r b-b text-center" width=""># Confirmed</th>
										<th class="bg-light b-r b-b text-center" width="">Conversion Rate</th>
										<th class="bg-light b-r b-b text-center" width="">Confirmed Sales</th>
										<th class="bg-light b-r b-b text-center" width="">% Confirmed Sales</th>
									</tr>
								</thead>
								<tbody>

									<?php foreach($data as $key=>$val) : ?>
											<tr>
												<td class="b-r b-b">
													<?php if(isset($statuses[$val->lead_reffered_by]) && $val->lead_reffered_by != 'existing_client') : ?>
														<?php echo $statuses[$val->lead_reffered_by]['name']; ?>
													<?php elseif($val->lead_reffered_by != 'existing_client') : ?>
														<?php echo str_replace('_', ' ', $val->lead_reffered_by); ?>
													<?php else : ?>
														<?php echo str_replace('_', ' ', $statuses[$val->lead_reffered_by]['name']) . ': ' . $client_type[$val->client_type]; ?>
													<?php endif; ?>
												</td>
												<td class="b-r b-b">
													<?php if(isset($val->leads) && !empty($val->leads)) : ?>
														<a href="#" style="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
															<?php echo $val->count;?>
														</a>
														<ul class="dropdown-menu animated fadeInRight pull-right" style="max-height: 200px; overflow: auto">
															<?php foreach($val->leads as $k=>$v) : ?>
																<li>
																	<a target="_blank" href="<?php echo base_url('client/' . $v->client_id); ?>">
																		<?php echo $v->client_name; ?>
																	</a>
																</li>
															<?php endforeach; ?>
														</ul>
													<?php else : ?>
														<?php echo $val->count;?>
													<?php endif; ?>
												</td>
												<td class="b-r b-b">
													<?php echo (intval($all->count)) ? round($val->count / $all->count * 100, 2) : 0;?>%
												</td>
												<td class="b-r b-b">
													<?php if(isset($val->workorders) && !empty($val->workorders)) : ?>
														<a href="#" style="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
															<?php echo $val->sum_conf;?>
														</a>

														<ul class="dropdown-menu animated fadeInRight pull-right" style="max-height: 200px; overflow: auto">
															<?php foreach($val->workorders as $k=>$v) : ?>

																<li>
																	<a target="_blank" href="<?php echo base_url($v->estimate_no); ?>">
																		<?php echo $v->estimate_no; ?>
																	</a>
																</li>
																
															<?php endforeach; ?>
														</ul>
													<?php else : ?>
														<?php echo $val->sum_conf;?>
													<?php endif; ?>
												</td>
												<td class="b-r b-b">
													<?php echo (intval($all->count)) ? round($val->sum_conf / $val->count * 100, 2) : 0;?>%
												</td>
												<td class="b-r b-b">
													<?php echo (intval($val->total)) ? money(round($val->total, 2)) : money(0);?>
												</td>
												<td class="b-r b-b">
													<?php echo (intval($val->total) && intval($all->total)) ? round($val->total / $all->total * 100, 2) :  0;?>%
												</td>
											</tr>
									<?php endforeach; ?>
									<tr>
										<td class="b-r b-b bg-light">&nbsp;</td>
										<td class="b-r b-b"><strong><?php echo $all->count; ?></strong></td>
										<td class="b-r b-b"><strong></strong></td>
										<td class="b-r b-b"><strong><?php echo $all->sum_conf; ?></strong></td>
										<td class="b-r b-b"><strong><?php echo (intval($all->count)) ? round($all->sum_conf / $all->count * 100, 2) : 0; ?>%</strong></td>
										<td class="b-r b-b"><strong><?php echo money(round($all->total, 2)); ?></strong></td>
										<td class="b-r b-b"><strong></strong></td>
									</tr>
								</tbody>
							</table>
						</div>
					
				<?php endif; ?>
			</div>
			<div class="col-md-4  col-sm-4 col-xs-12">
				<div class="plots text-center">
					<div id="flotcontainer" class="plotContainer"></div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		</section>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
</section>
<script>
	var leadsData = <?php echo json_encode($data); ?>;
	var statuses = <?php echo json_encode($statuses); ?>;
	var client_type = <?php echo json_encode($client_type); ?>;
	var data = [];
	var all = <?php echo (!empty($all) && isset($all->count)) ? $all->count : 0 ; ?>;
	//delete leadsData.no_info_provided;

	$(document).ready(function () {
		// $('.datepicker').datepicker({format: 'yyyy-mm-dd'});
        $('.datepicker').datepicker({format: $('#php-variable').val()});
        console.log(leadsData);
		$.each(leadsData, function (key, val) {
			var perc = (val.count / all * 100).toFixed(2) + '%';
            if(val.lead_reffered_by != null){
                console.log(val.lead_reffered_by);
                console.log(statuses[val.lead_reffered_by]);
            }

			data.push({
			  label: val.lead_reffered_by === 'existing_client' ? perc + ' ' + ((val.lead_reffered_by != null)?statuses[val.lead_reffered_by].name:'no name') + ': ' + client_type[val.client_type] + ' (' + val.count + ')' : perc + ' ' + ((val.lead_reffered_by != null)?statuses[val.lead_reffered_by].name:'no name') + ' (' + val.count + ')',
			  data: val.count
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
