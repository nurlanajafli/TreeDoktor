<?php $this->load->view('includes/header'); ?>

<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li class="active">Sales</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading">Sales
			<a class="btn btn-success btn-xs pull-right" type="button" style="margin-top: -1px;"
			   href="#sale-" role="button"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
				<i class="fa fa-plus"></i>
			</a>
			<select class="change form-control pull-right m-r-md" style="width: 90px; margin-top: -8px;">
				<?php for($i = $minYear; $i <= (date('Y')+1); $i++) : ?>
					<option value="<?php echo $i; ?>"<?php if($year == $i) : ?> selected<?php endif; ?>><?php echo $i; ?></option>
				<?php endfor; ?>
			</select>
			<div class="clear"></div>
		</header>
		
		

		<div class="m-bottom-10 p-sides-10 table-responsive">
			<table class="table tsble-striped m-n">
				<thead>
				<tr>
					<th>#</th>
					<th>Month</th>
					<th>Sales Target</th>
					<th>Sales Actual</th>
					<th>Ratio</th>
					<th>Invoices Created</th>
					<th>Invoices Paid</th>
					<th>Received Payments</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<?php if ($sales):
					$saleSum = $totalSum = $totalInvoicesSum = $totalInvoicesEstimatesSum = 0;
					foreach ($sales as $key => $sale):
						$saleSum += $sale['sale_amount'];
						
						$totalSum += $sale['total_company'];
						$totalInvoicesSum += $sale['total_created_invoices_company'];
						$totalInvoicesEstimatesSum += $sale['total_invoices_company'];
                ?>
						<tr>
							<td><?= $key+1; ?></td>
							<td><?= date("F", mktime(0, 0, 0, date('m', strtotime($sale['sale_date'])), 10)); ?></td>
							<td><?= money($sale['sale_amount']); ?></td>
							<td><?= money($sale['total_company']); ?></td>
							<td><?= $sale['complete_company']; ?>%</td>
							<td><?= money($sale['total_invoices_company']); ?></td>
							<td><?= money($sale['total_created_invoices_company']); ?></td>
							<td><?= $sale['received_payments']; ?></td>
							<td> 
								<a class="btn btn-xs btn-default" href="#sale-<?php echo $sale['sale_id']; ?>" role="button" 
									data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i></a>
									&nbsp;
								<a class="btn btn-xs btn-danger deleteSale"
								   data-delete-id="<?php echo $sale['sale_id']; ?>">
									<i class="fa fa-trash-o"></i>
								</a>
							</td>
						</tr>
                        <?php endforeach; ?>
                        <tr>
                            <td></td>
                            <td><strong>TOTAL:</strong></td>
                            <td><?php echo money($saleSum); ?></td>
                            <td><?php echo money($totalSum); ?></td>
                            <td><?php echo (int)$saleSum === 0 ? 0 : round(($totalSum / $saleSum * 100), 2); ?>%</td>
                            <td><?php echo money($totalInvoicesEstimatesSum); ?></td>
                            <td><?php echo money($totalInvoicesSum); ?></td>
                            <td><?php echo $paymentsSum; ?></td>
                            <td></td>
                        </tr>
				<?php else: ?>
					<tr>
						<td colspan="5"><?php echo "No records found"; ?></td>
					</tr>
                <?php endif; ?>
				</tbody>
			</table>
		</div>
	</section>
</section>
<?php if ($sales) : ?>
	<?php foreach ($sales as $key => $sale):?>
		<div id="sale-<?php echo $sale['sale_id']; ?>" class="modal fade" tabindex="-1" role="dialog"  aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content panel panel-default p-n">
					<header class="panel-heading">Edit Sale <?php echo $sale['sale_date']; ?></header>
					<div class="modal-body">
						<div class="form-horizontal">
							<div class="control-group">
								<label class="control-label">Month</label>

								<div class="controls">
								   <select name="sale_date form-control" class="sale_date form-control">
										<option>Select Month</option>
										<?php for ($i = 1; $i <= 12; $i++) : ?>
											<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php if($i == date('m', strtotime($sale['sale_date']))) : ?>selected="selected"<?php endif;?>> 
												<?php echo date("F", mktime(0, 0, 0, $i, 10)); ?>
											</option>
										<?php endfor; ?>
									</select>
									<input type="hidden" name="sale_year" class="sale_year" value="<?php echo date("Y", strtotime($sale['sale_date'])); ?>">
								</div>
							</div>
							<div class="control-group">
								<label class="control-label">Amount</label>

								<div class="controls">
									<textarea class="sale_amount form-control" type="text"
									  placeholder="Sale Amount" style="background-color: #fff;"><?php echo $sale['sale_amount']; ?></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" data-save-sale="<?php echo $sale['sale_id']; ?>">
							<span class="btntext">Save</span>
							<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
								 style="display: none;width: 32px;" class="preloader">
						</button>
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach;?>
<?php endif;?>

<div id="sale-" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Create Sale</header>
			<div class="modal-body">
				<div class="form-horizontal">
					<div class="control-group">
						<label class="control-label">Month</label>

						<div class="controls">
							<select name="sale_date" class="sale_date form-control">
								<option>Select Month</option>
								<?php for ($i = 1; $i <= 12; $i++) : ?>
									<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>">
										<?php echo date("F", mktime(0, 0, 0, $i, 10)); ?>
									</option>
								<?php endfor; ?>
							</select>
							<input type="hidden" name="sale_year" class="sale_year" value="<?php echo $year; ?>">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Amount</label>

						<div class="controls">
							<input class="sale_amount form-control" type="text" value="" placeholder="Sale Amount">
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success" data-save-sale="">
					<span class="btntext">Save</span>
					<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
						 class="preloader">
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>
</div>
		<script type="text/javascript">
	
	$(document).ready(function(){
		
		$(document).on("click", '[data-save-sale]', function(){

			var id = $(this).data('save-sale');
			var date = $('#sale-' + id + ' .sale_date').val();
			var year = $('#sale-' + id + ' .sale_year').val();
			var amount = $('#sale-' + id + ' .sale_amount').val();
			
			$(this).attr('disabled', 'disabled');
			$('#sale-' + id + ' .modal-footer .btntext').hide();
			$('#sale-' + id + ' .modal-footer .preloader').show();
			$('#sale-' + id + ' .sale_date').parents('.control-group').removeClass('error');
			
			if (!date) {
				$('#sale-' + id + ' .sale_date').parents('.control-group').addClass('error');
				$('#sale-' + id + ' .modal-footer .btntext').show();
				$('#sale-' + id + ' .modal-footer .preloader').hide();
				$(this).removeAttr('disabled');
				return false;
			}
			$.post(baseUrl + 'reports/ajax_save_sale', {id : id, date : date, year : year, amount:amount}, function (resp) {
				if (resp.status == 'ok')
					location.reload();
				return false;
			}, 'json');
			return false;
		});
		$('.deleteSale').click(function () {
			var id = $(this).data('delete-id');
			if (confirm('Are you sure?')) {
				$.post(baseUrl + 'reports/ajax_delete_sale', {id: id}, function (resp) {
					if (resp.status == 'ok') {
						location.reload();
						return false;
					}
					alert('Ooops! Error!');
				}, 'json');
			}
		});
		$(document).on('change', '.change', function(){
			location.href = baseUrl + 'reports/sales_targets/' + $(this).val();
			return false;
		});
	});
</script>
		<?php $this->load->view('includes/footer'); ?>
