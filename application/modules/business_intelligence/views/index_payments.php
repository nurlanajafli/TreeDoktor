<?php $this->load->view('includes/header'); ?>
<?php setlocale(LC_ALL, 'en_US.utf8', 'en_US'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Client Payments</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Filter
			<div class="pull-right"  >
				<form id="dates" method="post" action="<?php echo base_url('business_intelligence/client_payments/'); ?>" class="input-append m-t-xs">
					<label>
						<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
                               value="<?php if (isset($from)) : echo getDateTimeWithDate($from, 'Y-m-d 00:00:00');
                               else : echo date(getDateFormat(), (time() - 86400 * 7)); endif; ?>">
					</label>
					— 
					<label>
						<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
                               value="<?php if (isset($to)) : echo getDateTimeWithDate($to, 'Y-m-d 23:59:59');
                               else : echo date(getDateFormat()); endif; ?>">
					</label>
					<label>
						<input name="amount"  type="text" class="input-sm form-control amount"
							   placeholder="<?php if (!empty($amount)) : echo str_replace(',', '.', $amount);
							   else : ?>Amount<?php endif; ?>"
							   value="<?php if (isset($amount)) echo str_replace(',', '.', $amount); ?>">
					</label>
					<input id="date_submit" type="submit" class="btn btn-info date-input-client" style="width:114px; margin-top:-3px;" value="GO!">
                    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
				</form>
				<script>
					$(document).ready(function () {
                        $('.datepicker').datepicker({format: $('#php-variable').val()});
					});
				</script>
			</div>
			<div class="clear"></div>
		</header>
		<div class="table-responsive">
			<table class="table tsble-striped m-n">
				<tbody>
					<?php $total = 0; ?>
					<?php if(isset($filter) && !empty($filter) && $filter) : ?>
						<?php foreach($filter as $key=>$val) : ?>
							<tr>
								<td><strong><?php echo $val['payment_account'];  ?></strong></td>
                                <td><?php echo money($val['sum']); ?>
									<a href="<?php echo base_url('/reports/payments_pdf/') . $val['payment_account'] . '/';?><?php if (isset($from)) : echo date('Y-m-d', strtotime($from));else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>/<?php if (isset($to)) : echo date('Y-m-d', strtotime($to)); else : echo date('Y-m-d'); endif; ?>"
									id="payment_pdf_<?php echo $val['payment_account'];  ?>" type="submit"
									class="btn btn-info date-input-client pull-right" value="pdf">pdf</a>
								</td>

								<?php $total += $val['sum'];?>
							</tr>
						<?php endforeach;?>
							<tr style="border-top: 2px solid #cfcfcf!important;">
								<td><strong>Total:</strong></td>
                                <td><?php echo money($total); ?></td>
							</tr>
					<?php else : ?>
					<tr>
						Not Found
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
	<section class="panel panel-default">
		<header class="panel-heading">Client Payments
		</header>
		
		<div class="m-bottom-10 p-sides-10 table-responsive">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>ID</th>
					<th>Type</th>
					<th>Estimate</th>
					<th width="100px">Date</th>
					<th>Amount</th>
					<th>Payment Author</th>
					<th>Method</th>
					<th>File</th>
					<th width="100px">Account</th>
					<th width="70px" class="text-center">Transaction Verified</th>
                    <th width="20px"></th>
				</tr>
				</thead>
				<tbody>
				<?php

				if ($client_payments) :
					foreach ($client_payments as $key => $payment):
						?>

							<tr class="payments<?php if($payment['payment_alarm']) : ?> bg-danger<?php endif; ?>" data-id="<?php echo $payment['payment_id']; ?>" <?php if($payment['payment_checked'] && !$payment['payment_alarm']) : ?>style="background-color:#1bf26d;"<?php endif; ?><?php if($payment['payment_alarm']) : ?> title="Payment Was Declined"<?php endif; ?>>
								<td>

										<?php echo $payment['payment_id']; ?>

								</td>
								<td><?php echo ucfirst($payment['payment_type']); ?></td>

								<td>
									<a href="<?php echo base_url($payment['estimate_no']); ?>">
									<?php echo $payment['estimate_no']; ?>
									</a>
								</td>
								<td><?php echo date(getDateFormat(), $payment['payment_date']); ?></td>
								<td><?php echo money($payment['payment_amount']); ?></td>
								<td><?php echo isset($payment['firstname']) ? $payment['firstname'] : '-'; ?>
									<?php echo isset($payment['lastname']) ? $payment['lastname'] : ''; ?>

								</td>
                                <?php $methods = config_item('payment_methods');?>
								<td><?php echo $methods[$payment['payment_method_int']] ?? 'N/A'; ?></td>
								<td>
									<?php if ($payment['payment_file']) : ?>
										<a class="btn btn-success btn-xs pull-left" type="button" target="_blank"
										   href="<?php echo base_url('uploads/payment_files/' . $payment['client_id'] . '/' . $payment['estimate_no'] . '/' . $payment['payment_file']); ?>">
											<i class="fa <?php if(strpos($payment['payment_file'], '.pdf')) : ?>fa fa-file-text<?php else : ?>fa-picture-o<?php endif; ?>"></i>
										</a>
									<?php else : ?>
										—
									<?php endif; ?>
								</td>
								<td align="center" class="text-justify">
									<?php if(isset($payment_account) && !empty($payment_account)) : ?>
										<?php foreach($payment_account as $jkey=>$acc) : ?>
											<label>
												<input type="radio" name="payment_account-<?php echo $payment['payment_id']; ?>" class="payment_account" data-id="<?php echo $acc['payment_account_id']; ?>" value="<?php echo $payment['payment_account_id'];?>" <?php if($payment['payment_account'] == $acc['payment_account_id']) : ?>checked="checked"<?php endif; ?>><?php echo $acc['payment_account_name']; ?>
											</label>
											&nbsp;&nbsp;
										<?php endforeach; ?>
									<?php endif; ?>
								</td>
								<td align="center">
									<input<?php if(!$payment['payment_account']) :?> disabled="disabled"<?php endif; ?> type="checkbox" name="checked" class="change_checked" data-id="<?php echo $payment['payment_id']; ?>" value="<?php echo $payment['payment_checked'];?>" <?php if($payment['payment_checked']) : ?>checked="checked"<?php endif; ?>>
								</td>
                                <td>
                                    <?php $this->load->view('qb/partials/qb_logs', ['lastQbTimeLog' => $payment['payment_last_qb_time_log'], 'lastQbSyncResult' => $payment['payment_last_qb_sync_result'], 'module' => 'payment', 'entityId' => $payment['payment_id'], 'entityQbId' => $payment['payment_qb_id'], 'class' => 'pull-right m-right-10']); ?>
                                </td>
							</tr>
						<?php endforeach; ?>
				<?php else :
					?>
					<tr>
						<td colspan="5"><?php echo "No records found"; ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-sm-5 text-right text-center-xs pull-right">
					<?php echo $links; ?>
				</div>
			</div>
		</footer>
	</section>
</section>
		<script type="text/javascript">
	$(document).ready(function(){
	    initQbLogPopover();
		$('#dates').submit(function(){
			amount = $('.amount').val();
			amount = amount.replace(',', '.').replace(Common.get_currency(), '').replace('am', '');
            let from = $('.from').val();
            let search = '/';
            from = from.replace(new RegExp(search, 'g'), '-');
            let to = $('.to').val();
            to = to.replace(new RegExp(search, 'g'), '-');
            search = /\s+/g;
            from = from.replace(search, '');
            to = to.replace(search, '');
            url = baseUrl + 'business_intelligence/client_payments/' + from + '/' + to;
			// url = baseUrl + 'business_intelligence/client_payments/' + '"' + dateFrom.getFullYear() + '-' + (dateFrom.getMonth()+1) + '-' + dateFrom.getDate()  + '/' + dateTo.getFullYear() + '-' +  (dateTo.getMonth()+1) + '-' +  dateTo.getDate();
			if(amount != '')
				url += '/' + $.trim('am' + amount);
			location.href = url;
			return false;
		});
		$(document).on("click", '.change_checked', function(){
			//console.log($(this).data('id')); return false;
			var obj = $(this);
			var checked = 0;
			var id = $(obj).data('id');
			var style = '#fff';
			//console.log($(obj).prop('checked')); return false;
			//$(obj).prop('checked', false);
			if($(this).is(':checked'))
			{
				//$(obj).prop('checked', true);
				checked = 1;
				style = '#1bf26d';
			}
			$.post(baseUrl + 'business_intelligence/ajax_change_payments', {id : id, checked : checked}, function (resp) {
				if (resp.status == 'ok')
				{
					$(obj).prop("checked", checked);
					$(obj).parent().parent().css('background-color', style); 
				}
				return false;
			}, 'json');
			return false;
		});
		$('.payment_account').on("change", function(){
			var obj = $(this);
			var id = $(obj).data('id');
			var data_id = $(obj).parents('tr:first').data('id');
			
			$.post(baseUrl + 'business_intelligence/ajax_change_account', {id : id, data_id : data_id}, function (resp) {
				$(obj).parents('tr:first').find('.change_checked').removeAttr('disabled');
				return false;
			}, 'json');
			return false;
		});
		
	});
</script>
		<?php $this->load->view('includes/footer'); ?>
