<?php $this->load->view('includes/header'); ?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('estimates'); ?>">Estimates</a></li>
		<li class="active">Estimators Statistic</li>
	</ul>
    <div class="panel panel panel-default p-n">
        <header class="header bg-gradient bg-white">
            <p class="text-success pull-left" style="margin-top: 5px!important;">
                <a href="#" class="pull-left thumb avatar filled_dark_grey b-3x m-r"  style="width:50px; height:50px;    border: 3px #4cc0c1 solid;">
                    <img src="<?php echo base_url('assets/img/estimates.png'); ?>" class="img-circle">
                </a>

            </p>
            <div class="inline pull-left" >
                <form id="dates" method="post" action="<?php echo base_url('business_intelligence/new_estimates_statistic'); ?>" class="input-append m-t-md">
                    <div class="inline">
                        <div id="reportrange" class="pull-left" style="color: #555; background: #fff; cursor: pointer; padding: 7px 10px; border: 1px solid #ccc; margin-right: -11px;">
                            <i class="fa fa-calendar"></i>&nbsp;
                            <span></span> <i class="fa fa-caret-down"></i>
                        </div>
                        <input type="hidden" name="from" class="from" value="<?php if ($from) : echo getDateTimeWithDate($from, "Y-m-d");
                        else : echo date(getDateFormat()); endif; ?>">
                        <input type="hidden" name="to" class="to" value="<?php if ($to) : echo getDateTimeWithDate($to, "Y-m-d");
                        else : echo date(getDateFormat()); endif; ?>">
                        <div class="pull-left">
                            <select id="estimator" name="estimator" class="form-control date-input-client user  " style="margin-left: 20px;    max-width: 200px;">
                                <option value="0">Choose Estimator</option>
                                <?php foreach($estimators as $key=>$val) : ?>
                                    <option value="<?php echo $val['id']?>" <?php if($val['id'] == $estimator_id) : ?>selected="selected"<?php endif; ?>><?php echo isset($val['firstname']) ? $val['firstname'] : '-' ; ?> <?php echo isset($val['lastname']) ? $val['lastname'] : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="pull-right">
                            <input id="date_submit" type="submit" class="btn btn-default date-input-client " value="Calculate!">
                        </div>
                    </div>


                    <div class="clear"></div>
                </form>
            </div>
            <script>
                $(function() {

                    var start = moment('<?php if ($from) : echo $from; else : echo date("Y-m-d"); endif; ?>');
                    var end = moment('<?php if ($to) : echo $to; else : echo date("Y-m-d"); endif; ?>');

                    if(!start)
                        start = moment().startOf('month');
                    if(!end)
                        end = moment().endOf('month');

                    function cb(start, end) {
                        $('#reportrange span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
                        $('#dates').find('.from').val(start.format(MOMENT_DATE_FORMAT));
                        $('#dates').find('.to').val(end.format(MOMENT_DATE_FORMAT));
                    }

                    $('#reportrange').daterangepicker({
                        startDate: start,
                        endDate: end,
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        }
                    }, cb);

                    $('#reportrange span').html(start.format(MOMENT_DATE_FORMAT) + ' - ' + end.format(MOMENT_DATE_FORMAT));
                });
            </script>
            <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />

            <div class="clearfix"></div>

        </header>
    </div>
<div class="row">
	<div class="col-sm-7">
		<section class="panel" style="border: 3px solid #5fc7c8;">
            <header class="panel-heading bg-info lt no-border">
                <h4> Estimator Statistic Details</h4>
            </header>
			<div class="list-group no-radius alt">
				<?php $net_sales = 0;?>
				<?php if(isset($estimator_invoiced_statistic) && $estimator_invoiced_statistic) : ?>
				<?php $confEst = $estimator_invoiced_statistic['quantity']; unset($estimator_invoiced_statistic['quantity']);?>
				<?php $net_sales = $estimator_invoiced_statistic['sales'] - $estimator_invoiced_statistic['direct_expenses'] - $estimator_invoiced_statistic['discounts']; ?>
				<?php $total = $confEst + $estimator_not_invoiced_statistic; ?>
				<?php $i = 0; ?>
					<div class="row">
						<div class="col-sm-4">
                            <?php $mhrs = $estimator_invoiced_statistic['event_man_hours']; unset($estimator_invoiced_statistic['event_man_hours']); ?>
							<?php foreach($estimator_invoiced_statistic as $key=>$val) : //echo '<pre>'; var_dump($estimator_invoiced_statistic); die;?>

								<?php if($i == 3) : ?>
									<?php $i = 0; ?>
									
									</div>
									<div class="col-sm-4">
								<?php endif; ?>
								<span class="list-group-item" style="color: #1b252e;">
									<span class="badge bg-info" style="font-size: 15px;">
										<?php  if($val) : ?>
											<?php echo ($key != 'crew_hrs') ? money($val) : $val; ?>
										<?php else : ?>
										0
										<?php endif; ?>
									</span><?php echo ucwords(str_replace('_', ' ', $key)); ?>
								</span>
                                <?php if($key == 'direct_expenses') : ?>
                                    <span class="list-group-item" style="color: #1b252e;">
											<span class="badge bg-info" style="font-size: 15px;"><?php echo ($net_sales) ? money($net_sales) : money(0); ?></span>Net Sales
										</span>


                                <?php endif; ?>
								<?php $i++; ?>

							<?php endforeach; ?>
							<span class="list-group-item" style="color: #1b252e;">
								<span class="badge bg-info" style="font-size: 15px;"><?php echo ($mhrs > 0) ? money($net_sales / $mhrs) : money(0); ?></span>Average Hourly Rate
							</span>
							<span class="list-group-item" style="color: #1b252e;">
								<span class="badge bg-info" style="font-size: 15px;"><?php echo ($confEst) ? money($estimator_invoiced_statistic['sales'] / $confEst) : 0; ?></span>Average Job Size
							</span>
							<span class="list-group-item" style="color: #1b252e;">
								<span class="badge bg-info" style="font-size: 15px;"><?php echo ($total_invoiced_statistic['quantity']) ? round($confEst / $total_invoiced_statistic['quantity'] * 100, 0) : 0; ?>%</span>% Division Sales 
							</span>
						</div>
						<div class="col-sm-4">
							
							
							
							<span class="list-group-item" style="color: #1b252e;">
								<span class="badge bg-info" style="font-size: 15px;"><?php echo $total; ?></span>Total Jobs Estimated 
							</span>
							<span class="list-group-item" style="color: #1b252e;">
								<span class="badge bg-info" style="font-size: 15px;"><?php echo $confEst; ?></span>Total Approvals
							</span>
							<span class="list-group-item" style="color: #1b252e;">
								<span class="badge bg-info" style="font-size: 15px;"><?php echo ($total) ? round($confEst / $total * 100, 0) : 0; ?>%</span>Conversion Rate 
							</span>
						</div>
					</div>
				<?php else : ?>
					<span class="list-group-item" style="color: #1b252e;">
						<p style="color:#FF0000;"> No record found</p>
					</span>
				<?php endif; ?>
			</div>
		</section>
	</div>
	<div class="col-sm-5">
		<section class="panel">
            <header class="panel-heading bg-info lt no-border">
                <h4> Estimates Details</h4>
            </header>
			<?php $this->load->view('kpi', ['net_sales' => $net_sales]); ?>
			
		</section>
	</div>

	
	<div class="col-sm-12">
		<section class="panel panel-default p-n">
			<header class="panel-heading">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active"><a href="#invoiced" data-toggle="tab">Invoiced Clients</a></li>
                    <li><a href="#not_invoiced" data-toggle="tab">Not Invoiced Clients</a></li>
                </ul>
            </header>
            <div class="panel-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="invoiced">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tbl_Estimated">
                                <?php if (isset($invoiced_estimates) && count($invoiced_estimates)) : ?>
                                    <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>No</th>
                                        <th>Sales</th>
                                        <th>Discounts</th>
                                        <th>Expenses</th>
                                        <th>Total</th>
                                        <th>Crew Hours On Site</th>
                                        <th>Hourly Rate</th>
                                    </tr>
                                    </thead>
                                    <tbody id="invoiced_table">
                                    <?php foreach($invoiced_estimates as $key=>$val) : ?>
                                        <?php $totalConf = round($val['total_confirmed'] - $val['expenses_total'] - $val['discount_total'], 2);?>
                                        <tr>
                                            <td width="170px"><a href="<?php echo base_url() . $val['client_id'];?>" target="_blank"><?php echo $val['client_name']; ?></a></td>
                                            <td width="170px"><a href="<?php echo base_url() . $val['estimate_no'];?>" target="_blank"><?php echo $val['estimate_no']; ?></a></td>
                                            <td width="170px"><?php echo money($val['sum_services_without_discount']); ?></td>
                                            <td width="170px"><?php echo money($val['discount_total']); ?></td>
                                            <td width="170px"><?php echo money($val['expenses_total']); ?></td>
                                            <td width="170px"><?php echo money($totalConf); ?></td>
                                            <td width="170px"><a href="<?php echo base_url($val['workorder_no']); ?>" target="_blank"><?php /*echo ($val['sum_mhrs'] > 0) ? $val['sum_mhrs'] : 1;*/ echo  $val['event_man_hours']; ?></a></td>
                                            <td width="170px"><?php echo /*($val['sum_mhrs'] > 0)*/ ($val['event_man_hours'] > 0) ? money(round($totalConf / $val['event_man_hours'], 2)) : money(round($totalConf / 1, 2)); ?></td>
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
                    </div>
                    <div class="tab-pane" id="not_invoiced">
                        <div class="table-responsive">
                            <table class="table table-hover" id="tbl_Estimated">
                                <?php if (isset($not_invoiced_estimates) && count($not_invoiced_estimates)) : ?>
                                    <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>No</th>
                                        <th>Estimate</th>
                                        <th>Date</th>
                                        <th>Address</th>
                                        <th>Status</th>
                                        <th>Phone</th>
                                    </tr>
                                    </thead>
                                    <tbody id="not_invoiced_table">
                                    <?php foreach($not_invoiced_estimates as $key=>$val) : ?>
                                        <?php //$totalConf = round($val['total_confirmed'] - $val['expenses_total'] - $val['discount_total'], 2);?>
                                        <tr>
                                            <td width="170px"><a href="<?php echo base_url() . $val['client_id'];?>" target="_blank"><?php echo $val['client_name']; ?></a></td>
                                            <td width="170px"><a href="<?php echo base_url() . $val['estimate_no'];?>" target="_blank"><?php echo $val['estimate_no']; ?></a></td>
                                            <td width="170px"><?php echo money($val['sum_services_without_discount'] - $val['discount_total']); ?></td>
                                            <td width="170px"><?php echo date(getDateFormat(), $val['date_created']); ?></td>
                                            <td width="170px"><?php echo $val['client_address'] . ', ' . $val['client_state']; ?></td>
                                            <td width="170px"><?php echo $val['est_status_name']; echo ($val['reason_name']) ? '/' . $val['reason_name'] : ''; ?></td>
                                            <td width="170px"><?php echo ($val['cc_phone']) ? numberTo($val['cc_phone']) : '-';?></td>
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

                    </div>
                </div>
            </div>

		</section>
	</div>
</div>
	<script>
		/*$('#date_submit').css('width', $('#dates').find('label:first').width());
		$('#count_month').on('change', function(){
			var count = parseInt($(this).val());
			var date = new Date();
			var yyyy = date.getFullYear().toString();
			var currYear = date.getFullYear();
			var mm = (date.getMonth() + 1).toString();
			var mmFrom = (date.getMonth() + 1 - count).toString();
			var dd = date.getDate().toString();
			var mmChars = mm.split('');
			
			if (mmChars[1] && mm > 12) {
				while (mmChars[1] && mm > 12) {
					mm -= 12;
					mm = mm.toString();
					delete mmChars;
					var mmChars = mm.split('');
					yyyy = (parseInt(yyyy) + 1).toString();
				}
			}
			if(mmFrom.length == 1)
				mmFrom = '0' + mmFrom;
			if(count)
			{
				
				
				var ModMonth = date.getMonth() + 1 - count;
				if (ModMonth < 0)
				{ 
					ModMonth = 12 + ModMonth;
					currYear = yyyy - 1;
				}
				ModMonth = ModMonth.toString();
				if(ModMonth.length == 1)
					ModMonth = '0' + ModMonth;
				var ddChars = dd.split('');
				var to = yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);
				var from = currYear + '-' + (ModMonth) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]);

				
				//console.log(from, to, count); return false;
				
				$('#dates [name="to"]').val(to);
				$('#dates [name="from"]').val(from);
				$('#count_month').val(count);
				$('#dates').submit();
			}
			else
			{
				var ddChars = dd.split('');
				var frMm = date.getMonth();
				frMm = frMm.toString();
				if(frMm.length == 1)
					frMm = '0' + frMm;
				console.log();
				$('#dates [name="to"]').val(yyyy + '-' + (mmChars[1] ? mm : "0" + mmChars[0]) + '-' + (ddChars[1] ? dd : "0" + ddChars[0]));
				$('#dates [name="from"]').val(yyyy + '-' + frMm + '-' + (ddChars[1] ? dd : "0" + ddChars[0]));
				$('#count_month').val(0);
				$('#dates').submit();
			}
			return false;
		});*/
	</script>
</section>
<?php $this->load->view('includes/footer'); ?>
