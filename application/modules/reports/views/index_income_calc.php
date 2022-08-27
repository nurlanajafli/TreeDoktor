<?php $this->load->view('includes/header'); ?>

<?php if (isAdmin()) : ?>
	<section class="scrollable p-sides-15">
		<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
			<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
			<li class="active">General</li>
		</ul>
		<section class="panel panel-default p-n">
		<header class="panel-heading">Filter
			<div class="pull-right" style="margin-top:-14px;">
				<form id="dates" method="post" action="<?php echo base_url('reports/general'); ?>" class="input-append m-t-xs">
					<label>
						<input name="from" class="datepicker form-control date-input-client from" type="text" readonly
							   value="<?php if ($from) : echo date('Y-m-d', strtotime($from));
							   else : echo date('Y-m-d', (time() - 86400 * 7)); endif; ?>">
					</label>
					—
					<label>
						<input name="to" class="datepicker form-control date-input-client to" type="text" readonly
							   value="<?php if ($to) : echo date('Y-m-d', strtotime($to));
							   else : echo date('Y-m-d'); endif; ?>">
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
	<div class="row">
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Payed Invoices</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">Total Made Sum:</td>
						<td>
							<?php if(!isset($total_sum_invoices_paid) || !$total_sum_invoices_paid) : ?>
								<?php $total_sum_invoices_paid = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_sum_invoices_paid, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;"><?php $defaultTax['name'] ?: 'Tax'; ?> Collected</td>
						<td>
							<?php if(!isset($total_hst_invoices_paid) || !$total_hst_invoices_paid) : ?>
								<?php $total_hst_invoices_paid = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_hst_invoices_paid, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total</td>
						<td>
							<?php echo money(round($total_sum_invoices_paid + $total_hst_invoices_paid, 2)); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>

		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Expenses</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">Total Expenses Sum:</td>
						<td><?php if(!isset($total_sum_expenses) || !$total_sum_expenses) : ?>
								<?php $total_sum_expenses = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_sum_expenses, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total <?php $defaultTax['name'] ?: 'Tax'; ?> Spent</td>
						<td>
							<?php if(!isset($total_hst_expenses) || !$total_hst_expenses) : ?>
								<?php $total_hst_expenses = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_hst_expenses, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total Expenses</td>
						<td>
							<?php echo money(round($total_sum_expenses + $total_hst_expenses, 2)); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Balance</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">TOTAL (received-spent):</td>
						<td><?php echo money(round(($total_sum_invoices_paid - $total_sum_expenses), 2)); ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold;"><?php $defaultTax['name'] ?: 'Tax'; ?> (spent-collected)</td>
						<td>-<?php echo money(round(($total_hst_invoices_paid - $total_hst_expenses), 2));?></td>
					</tr>
					<tr>
						<td style="font-weight: bold;">GRAND TOTAL</td>
						<td><?php echo money(round(round(($total_sum_invoices_paid - $total_sum_expenses), 2) - round(($total_hst_invoices_paid - $total_hst_expenses), 2), 2)); ?></td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Created Invoices</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">Total Made Sum:</td>
						<td>
							<?php if(!isset($total_sum_invoices) || !$total_sum_invoices) : ?>
								<?php $total_sum_invoices = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_sum_invoices, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;"><?php $defaultTax['name'] ?: 'Tax'; ?> Collacted</td>
						<td>
							<?php if(!isset($total_hst_invoices) || !$total_hst_invoices) : ?>
								<?php $total_hst_invoices = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_hst_invoices, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total</td>
						<td>
							<?php echo money(round($total_sum_invoices + $total_hst_invoices, 2)); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Expenses</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">Total Expenses Sum:</td>
						<td><?php if(!isset($total_sum_expenses) || !$total_sum_expenses) : ?>
								<?php $total_sum_expenses = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_sum_expenses, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total <?php echo $defaultTax['name'] ?: 'Tax'; ?> Spent</td>
						<td>
							<?php if(!isset($total_hst_expenses) || !$total_hst_expenses) : ?>
								<?php $total_hst_expenses = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_hst_expenses, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total Expenses</td>
						<td>
							<?php echo money(round($total_sum_expenses + $total_hst_expenses, 2)); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>


		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Balance</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">TOTAL (received-spent):</td>
						<td><?php echo money(round(($total_sum_invoices - $total_sum_expenses), 2)); ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold;"><?php $defaultTax['name'] ?: 'Tax'; ?> (spent-collected)</td>
						<td>-<?php echo money(round(($total_hst_invoices - $total_hst_expenses), 2));?></td>
					</tr>
					<tr>
						<td style="font-weight: bold;">GRAND TOTAL</td>
						<td><?php echo money(round(round(($total_sum_invoices - $total_sum_expenses), 2) - round(($total_hst_invoices - $total_hst_expenses), 2), 2)); ?></td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Received Payments</header>
				<table class="table table-striped table-pulse">
					<tbody>
						<?php if(isset($received_payments)) : ?>
						<?php $totalPay = 0; ?>
						<?php $totalHst = 0; ?>
						<?php if($received_payments) : ?>
							<?php foreach($received_payments as $key=>$val): ?>
								<?php $totalPay += $val['sum'];?>
                                <?php if($defaultTax['value']) : ?>
								        <?php $totalHst += $val['sum'] * 100 / ($defaultTax['value'] + 100);?>
								<?php else :?>
                                        <?php $totalHst += $val['sum'];?>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
							<tr>
								<td style="font-weight: bold;">Total Made Sum:</td>
								<td style="text-align:center;">
									<?php echo money(round($totalHst, 2)); ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;"><?php $defaultTax['name'] ?: 'Tax'; ?> Collacted</td>
								<td style="text-align:center;">
									<?php echo money(round(($totalPay - $totalHst), 2)); ?>
								</td>
							</tr>
							<tr>
								<td style="font-weight: bold;">Total</td>
								<td style="text-align:center;">
									<?php echo money(round($totalPay, 2)); ?>
								</td>
							</tr>
						<?php else : ?>
							<tr>
								<td>No record find</td>
							</tr>
						<?php endif; ?>
					</tbody>

				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Expenses</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">Total Expenses Sum:</td>
						<td><?php if(!isset($total_sum_expenses) || !$total_sum_expenses) : ?>
								<?php $total_sum_expenses = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_sum_expenses, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total <?php $defaultTax['name'] ?: 'Tax'; ?> Spent</td>
						<td>
							<?php if(!isset($total_hst_expenses) || !$total_hst_expenses) : ?>
								<?php $total_hst_expenses = 0;?>
							<?php endif; ?>
							<?php echo money(round($total_hst_expenses, 2));?>
						</td>
					</tr>
					<tr>
						<td style="font-weight: bold;">Total Expenses</td>
						<td>
							<?php echo money(round($total_sum_expenses + $total_hst_expenses, 2)); ?>
						</td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>
		<section class="col-md-4">
			<section class="panel panel-default p-n">
				<header class="panel-heading">Balance</header>
				<table class="table table-striped table-pulse">
					<tbody>
					<tr>
						<td style="font-weight: bold;">TOTAL (received-spent):</td>
						<td><?php echo money(round(($totalHst - $total_sum_expenses), 2)); ?></td>
					</tr>
					<tr>
						<td style="font-weight: bold;"><?php $defaultTax['name'] ?: 'Tax'; ?> (spent-collected)</td>
						<td>-<?php echo money(round((($totalPay - $totalHst) - $total_hst_expenses), 2));?></td>
					</tr>
					<tr>
						<td style="font-weight: bold;">GRAND TOTAL</td>
						<td><?php echo money(round(round(($totalHst - $total_sum_expenses), 2) - round((($totalPay - $totalHst) - $total_hst_expenses), 2), 2)); ?></td>
					</tr>
					</tbody>
				</table>
			</section>
		</section>
	</div>

	<?php $this->load->view('index');?>

	</section>

<?php endif; ?>
<?php $this->load->view('includes/footer'); ?>
