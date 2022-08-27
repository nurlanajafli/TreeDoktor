<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>">
	
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>">
	
</head>
<body>

<div class="holder p_top_20">
	<img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/print/header2.png" width="100%" height="20%" class="p-top-20" style="margin-left: 60px;">
	
</div>
<span style="font-weight: bold"> <?php echo $title; ?> </span><br><br>
<section class="panel panel-default" style="margin: 0px -30px;">
		
		
		<div class="m-bottom-10 p-sides-5" style="padding: 0px;">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" class="p_top_5 table">
				<thead>
				<tr>
					<th>#</th>
					<th>Date</th>
					<th>No.</th>
					<th>Client Name</th>
					<th>Address</th>
					<th>Method</th>
					<th width="110px">Amount</th>
					<th width="110px"><?php echo $defaultTax['name'] ?: 'Tax'; ?></th>
					<th width="110px">Total</th>

				</tr>
				</thead>
				<tbody>
				<?php
				if ($client_payments) :
					$total = 0;
					$amount = 0;
					$hst = 0;
					foreach ($client_payments as $key => $payment): 
						?>
							<tr>
								<td >
									<?php echo $key + 1; ?>
								</td>
								<td >
									<?php echo date('Y-m-d', $payment['payment_date']); ?>
								</td>
								<td >
									<?php echo $payment['estimate_no']; ?>
								</td>
								<td >
									<?php echo isset($payment['client_name']) ? $payment['client_name'] : '-'; ?>
									
								</td>
								<td >
									<?php echo isset($payment['client_address']) ? $payment['client_address'] : '-'; ?>
									
								</td>
                                <?php $methods = config_item('payment_methods');?>
                                <td><?php echo $methods[$payment['payment_method_int']] ?? 'N/A'; ?></td>
								<?php if($payment['estimate_hst_disabled'] == 1) : ?>
									<td >
										<?php echo money($payment['payment_amount']); ?>
										<?php $amount += $payment['payment_amount']; ?>
									</td>
									<td >
										-
									</td>
									<td>
										<?php echo money($payment['payment_amount']); ?>
									</td>
								<?php else : ?>
                                    <?php if(isset($defaultTax['value'])) : ?>
                                        <td >
                                            <?php echo money(($payment['payment_amount']*100/($defaultTax['value'] + 100))); ?>
                                            <?php $amount += $payment['payment_amount']*100/($defaultTax['value'] + 100); ?>
                                        </td>
                                        <td >
                                            <?php echo money(($payment['payment_amount']*$defaultTax['value']/$defaultTax['value'] + 100)); ?>
                                            <?php $hst += $payment['payment_amount']*$defaultTax['value']/$defaultTax['value'] + 100; ?>
                                        </td>
                                    <?php else : ?>
                                        <td >
                                            <?php echo money($payment['payment_amount']); ?>
                                            <?php $amount += $payment['payment_amount']; ?>
                                        </td>
                                        <td >
                                            <?php echo money($payment['payment_amount']); ?>
                                            <?php $hst += $payment['payment_amount']; ?>
                                        </td>
                                    <?php endif; ?>
									<td>
										<?php echo money($payment['payment_amount']); ?>
									</td>
								<?php endif; ?>
							</tr>
							<?php $total += $payment['payment_amount']; ?>
						<?php endforeach; ?>
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td><strong><?php echo money($amount); ?></strong></td>
							<td><strong><?php echo money($hst); ?></strong></td>
							<td><strong><?php echo money($total); ?></strong></td>
						</tr>
				<?php else :
					?>
					<tr>
						<td colspan="8"><?php echo "No records found"; ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		
	</section>

</body>
</html>
