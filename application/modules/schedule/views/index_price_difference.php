<?php $this->load->view('includes/header'); ?>

	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">Reports</li>
		</ul>
		<section class="panel panel-default">
			<header class="panel-heading">Schedule|Estimate Price Difference (<?php echo isset($prices) && !empty($prices) ? count($prices) : 0;?>)
				<div class="pull-right">
					<form id="dates" method="post" action="<?php echo base_url('schedule/price_difference'); ?>" class="input-append m-t-xs">
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
                <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
			</header>
			<script>
				$(document).ready(function () {
					// $('.datepicker').datepicker({format: 'yyyy-mm-dd'});
                    $('.datepicker').datepicker({format: $('#php-variable').val()});
				});
			</script>
		</section>
	<div class="row">
		<section class="col-md-12">
			<section class="panel panel-default p-n">
				<div class="table-responsive">
					<table class="table table-striped table-pulse">
						<thead>
							<tr>
								<th class="text-center">ESTIMATE</th>
								<th class="text-center">WO PROFILE</th>
								<th class="text-center">EVENT PRICE</th>
								<th class="text-center">ESTIMATE PRICE</th>
								<th class="text-center">DIFFERENCE</th>
								
							</tr>
						</thead>
						<tbody>
						<?php if(isset($prices) && !empty($prices)) : ?>
							<?php foreach($prices as $key=>$val) :  ?>
								 
								<tr>
									<td class="text-center"><a href="<?php echo base_url($val['estimate_no']); ?>" target="_blank"><?php echo $val['estimate_no']; ?></a></td>
									<td class="text-center <?php if($val['price_diff'] > 0) : ?>text-success <?php else :?>text-danger <?php endif; ?>"><a href="<?php echo base_url($val['workorder_no']); ?>" target="_blank"><?php echo $val['workorder_no']; ?></a></td>
                                    <td class="text-center <?php if ($val['price_diff'] > 0) : ?>text-success <?php else : ?>text-danger <?php endif; ?>"><?php echo money($val['total_event_price']); ?></td>
                                    <td class="text-center <?php if ($val['price_diff'] > 0) : ?>text-success <?php else : ?>text-danger <?php endif; ?>"><?php echo money($val['total_price']); ?></td>
                                    <td class="text-center <?php if ($val['price_diff'] > 0) : ?>text-success <?php else : ?>text-danger <?php endif; ?>"><?php echo money($val['price_diff']); ?></td>
									
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr><td colspan="5">No records found</td></tr>
						<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</section>

	</div>
	</section>
	
<?php $this->load->view('includes/footer'); ?>
