<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo !empty($title) ? $title : ''; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/invoice_pdf.css" type="text/css" media="print">
    <style type="text/css">
        .estimate_number {
            padding-bottom: 10px;
        }

        .estimate_number_1, .estimate_number_2 {
            font-size: 14px!important;
            height: 22px;
            margin-bottom: 5px;
        }

        .estimate_table {
            margin-left: 10px;
            margin-right: 10px;
            width: 100%;
        }

        .estimate_table {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            padding: 5px 15px 5px 0;
        }
        .estimate_table pre {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            padding: 0;
            margin: 0;
        }

        .m-t-5 {
            margin-top: 5px;
        }
        .m-t-b-5 {
            margin: 5px 0;
        }
        .p-b-5 {
            padding-bottom: 5px;
        }
        .pull-left {
            float: left;
        }
        .pull-right {
            float: right;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .clear {
            clear: both;
        }
        .invoice_terms {
            padding: 0 20px 0 85px;
        }
        .invoice_terms .title {
            margin-left: -30px;
            padding-left: 30px;
        }
        .invoice_terms .des_1 {
            margin-left: 0;
            padding-left: 0;
        }
        .invoice_terms .payment_terms {
            margin-left: 0;
            padding-left: 0;
        }
        .ql-align-center{
            text-align: center;
        }
        .ql-align-right{
            text-align: right;
        }
        .ql-align-left{
            text-align: left;
        }
    </style>
</head>
<?php

$default_img = 'assets/'.$this->config->item('company_dir').'/print/header2_short.png';
$brand_id = get_brand_id(isset($invoice_data)?$invoice_data:[], isset($client_data)?$client_data:[]);
$invoice_logo = get_brand_logo($brand_id, 'invoice_logo_file', $default_img);
$invoice_watermark = get_brand_logo($brand_id, 'watermark_logo_file', false);
$invoice_left_side = get_brand_logo($brand_id, 'invoice_left_side_file', false);
$invoice_terms = get_invoice_terms($brand_id);
$pdf_footer = get_pdf_footer($brand_id);

?>
<?php if($invoice_left_side): ?>
<style type="text/css">
    body{
        font-family: Sans-Serif;
        font-size: 11pt;
        background-image: url('<?php echo base_url($invoice_left_side); ?>')!important;
        background-position: 0 -48px;
        background-repeat: no-repeat;
        margin: 30px 0 20px 7px;
    }
    .top_logo {
        max-width: 350px;
        max-height: 120px;
    }
</style>
<?php endif; ?>

<body>
<div class="holder" style="position: absolute; top: 0; right: -45px;"><img class="top_logo" src="<?php echo base_url($invoice_logo); ?>"></div>
<?php if(isset($invoice_data) && $invoice_data) : ?>
<div style="width: 100%; margin-left: -20px; margin-right: -40px; padding-top: 20px;">
    <div class="estimate_number pull-left" style="width: 30%; margin-right: 0;">
        <div class="estimate_number_1" style="width: 100px; font-size: 16px;">Invoice Date</div>
        <div class="estimate_number_2" style="width: 130px; font-size: 16px;"><?php echo getDateTimeWithDate($invoice_data->date_created, 'Y-m-d'); ?></div>
    </div>

    <div class="estimate_number pull-left" style="width: 30%; margin-left: 25px; margin-right: 0;">
        <div class="estimate_number_1 text-center" style="width: 80px; font-size: 16px;">Due Date</div>
        <div class="estimate_number_2" style="width: 130px; font-size: 16px;"><?php echo getDateTimeWithDate($invoice_data->overdue_date, 'Y-m-d'); ?></div>
    </div>

    <div class="estimate_number pull-left" style="width: 30%; margin-left: 25px; margin-right: 0;">
        <div class="estimate_number_1" style="width: 75px; font-size: 16px;">Invoice #</div>
        <div class="estimate_number_2" style="width: 130px; font-size: 16px;"><?php echo $invoice_data->invoice_no; ?></div>
    </div>
    <div class="clearBoth"></div>
</div>
<?php endif; ?>
<div class="title">Client</div>
<div class="data">
    <!-- CLIENT INFO -->
    <table cellpadding="0" cellspacing="0" border="0" class="client_table" width="100%">
        <tr>
            <td width="50%">
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="font-size: 16px;"><strong><?php echo $client_data->client_name; ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php echo $client_data->client_address . ", " . $client_data->client_city . "<br>" . $client_data->client_state . " " . $client_data->client_zip; ?>
                        </td>
                    </tr>
                    <?php /*if(isset($client_data->client_main_intersection) && $client_data->client_main_intersection != '') : ?>
                <tr>
                    <td>Add. Info:</td>
                    <td><?php echo $client_data->client_main_intersection; ?></td>
                </tr>
            <?php endif;*/ ?>

                    <?php /*if(isset($estimate_data->lead_add_info) && $estimate_data->lead_add_info != '' && (isset($client_data->client_main_intersection) && $estimate_data->lead_add_info != $client_data->client_main_intersection)) : ?>
                <tr>
                    <td>Add. Info:</td>
                    <td><?php echo $estimate_data->lead_add_info; ?></td>
                </tr>
            <?php endif;*/ ?>

                </table>
            </td>
            <td width="52%" align="right">
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td><?php echo isset($client_contact['cc_phone']) ? numberTo($client_contact['cc_phone']) : '-'; ?></td>
                    </tr>
                    <?php if (isset($client_contact['cc_email']) && !empty($client_contact['cc_email'])) : ?>
                        <tr>
                            <td><?php echo $client_contact['cc_email']; ?></td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($client_data->client_address != $estimate_data->lead_address) : ?>
                        <tr>
                            <td>
                                <strong>Job Location</strong><br><?php echo $estimate_data->lead_address . " " . (isset($estimate_data->lead_city) && !empty($estimate_data->lead_city) ? $estimate_data->lead_city : ''); ?>
                                <?php if (isset($client_contact['cc_name']) && $client_data->client_name != $client_contact['cc_name']) : ?>
                                    <br><?php echo $client_contact['cc_name']; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>
    <!-- CLIENT INFO -->
</div>

<div class="title">Invoice Details</div>
<div class="data">
	<div class="min_height_350" <?php if($invoice_watermark): ?>style="background-image: url('<?php echo base_url($invoice_watermark); ?>')!important;"<?php endif; ?>>

        <?php $maxPriceLength = 0; ?>
        <?php $sum = 0; ?>
        <?php
            $services = array_merge($estimate_tree_inventory_services_data ?? [], $estimate_services_data);
            foreach ($services as $service_data) : ?>
            <?php $intPrice = intval($service_data['service_price']); ?>
            <?php $sum += $intPrice; ?>
            <?php $maxPriceLength = strlen($intPrice) > $maxPriceLength ? strlen($intPrice) : $maxPriceLength; ?>
            <?php $maxPriceLength = strlen($sum) > $maxPriceLength ? strlen($sum) : $maxPriceLength; ?>
        <?php endforeach; ?>

        <!-- ESTIMATE INFO -->
        <?php $allPricesSum = array_sum(array_column($estimate_services_data, 'service_price')); ?>
        <?php $priceColumnWidth = strlen(intval($allPricesSum) . '.00') * 11; ?>
        <div class="estimate_table">
           <?php $this->load->view('includes/pdf_templates/tree_inventory'); ?>
            <?php if(!empty($estimate_services_data)): ?>
            <div>
                <div class="pull-right text-right" style="width:<?php echo $priceColumnWidth; ?>px;">
                    <strong>PRICE</strong>
                </div>
                <div class="pull-left">
                    <strong>DESCRIPTION</strong>
                </div>
                <div class="clear"></div>
            </div>
            <div>
                <?php foreach ($estimate_services_data as $numService => $service_data) : ?>
                    <div class="m-b-5" style="padding-top: 10px;">
                        <div class="pull-right text-right" style="width: <?php echo $priceColumnWidth; ?>px;">
                            <?php echo nl2br(money($service_data['service_price'])); ?>
                        </div>
                        <?php if($service_data['is_product']) : ?>
                        <div class="pull-left text-left p-b-5" style="text-align: justify;">
                            <strong><?php echo (config_item('service_numeration') ? $service_data['service_priority'] . '. ' : ''); ?><?php echo $service_data['service_name']; ?></strong>
                            <span style="width: <?php echo $priceColumnWidth; ?>px;">
                                                <?php if($service_data['is_product']) : ?>
                                                    (<?php echo $service_data['quantity']?> x <?php echo str_replace(' ', '', nl2br(money($service_data['cost']))); ?>)
                                                <?php endif ?>
                            </span>
                        </div>
                        <?php else : ?>
                            <strong><?php echo (config_item('service_numeration') ? $service_data['service_priority'] . '. ' : ''); ?><?= isset($service_data['estimate_service_ti_title']) && !empty($service_data['estimate_service_ti_title']) ? $service_data['estimate_service_ti_title'] : $service_data['service_name']; ?></strong>
                            <div class="pull-left p-b-5" style="text-align: justify;">
                                <pre><?php echo decorate_text($service_data['service_description']); ?></pre>
                            </div>
                        <?php endif; ?>
                        <div class="clear"></div>
                    </div>
                <?php if($service_data['is_product']) : ?>
                    <div class="m-t-b-5">
                        <div class="pull-left p-b-5" style="text-align: justify; padding-right: <?php echo $priceColumnWidth; ?>px;">
                            <pre><?php echo decorate_text($service_data['service_description']); ?></pre>
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php endif; ?>
                    <?php if($service_data['is_bundle'] && $service_data['is_view_in_pdf']): ?>
                        <div class="list-group">
                            <?php if(isset($service_data['bundle_records'])) foreach($service_data['bundle_records'] as $bundle_record) : ?>
                                <div class="list-group-item" style="padding-left: 20px; border-left: 1px solid #bebebe; padding-right: <?php echo $priceColumnWidth; ?>px;">
                                    <div class="">
                                        <div class="pull-left text-left">
                                            <strong ><?php echo (config_item('service_numeration') ? $service_data['service_priority'] . '. ' : ''); ?><?php echo $bundle_record->service_name; ?></strong>
                                            <?php if($bundle_record->non_taxable): ?>
                                                <span>(Non-taxable)</span>
                                            <?php endif; ?>
                                            <span style="width: <?php echo $priceColumnWidth; ?>px;">
                                                <?php if($bundle_record->is_product) : ?>
                                                    (<?php echo $bundle_record->quantity ?> x <?php echo str_replace(' ', '', nl2br(money($bundle_record->cost))); ?>  <?php echo str_replace(' ', '', nl2br(money($bundle_record->service_price)))?>)
                                                <?php else : ?>
                                                    (<?php echo str_replace(' ', '', nl2br(money($bundle_record->service_price))); ?>)
                                                <?php endif ?>
                                        </span>

                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                    <div class="m-t-b-5">
                                        <div class="pull-left p-b-5" style="text-align: justify;">
                                            <pre><?php echo decorate_text($bundle_record->service_description); ?></pre>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div style="border-bottom: 1px solid #bebebe;"></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <br>
		<!-- ESTIMATE INFO -->
		<table cellpadding="0" cellspacing="0" border="0" class="invoice_table" width="100%">

			<tr>
				<td valign="middle" align="right" class="p-l-500 m-t-10 p-10 small_arrow">Sum:</td>
				<td  valign="middle" align="right" class="p-10" width="130px"><?php echo money($estimate_data->sum_taxable + $estimate_data->sum_non_taxable); ?></td>
			</tr>
            <?php
            $term = \application\modules\invoices\models\Invoice::getInvoiceTerm($client_data->client_type ?? null);
            $totalConfirmed = ($estimate_data->sum_taxable + $estimate_data->sum_non_taxable) - $estimate_data->discount_total;//$overdue_amt
            /* Showing overdue only in case of invoices  */

            if (!empty($invoice_interest_data)) {
            foreach ($invoice_interest_data as $jk=>$interset_row) {
                $totalToDue = $totalConfirmed;
                $minusPayments = 0;
                if ($payments_data && count($payments_data)) {
                    foreach ($payments_data as $pay) {
                        if ($pay['payment_date'] < (strtotime($interset_row->overdue_date) - $term * 86400))
                            $minusPayments += $pay['payment_amount'];
                    }
                }
                $totalToDue -= $minusPayments;
                $interest = abs($interset_row->rate / 100);
                $monthDue = $totalToDue * $interest;
                $monthDue = $monthDue >= 0 ? $monthDue : 0;
                $totalConfirmed += $monthDue;
                $totalToDue += $minusPayments;

            ?>
                <?php if ((!isset($estimate_data->interest_status) || $estimate_data->interest_status == 'No') && $interest !== 0) : ?>
                    <tr>
                        <td valign="top" align="right" class="p-l-200 p-10 small_arrow_interest" style="font-size:13px;">Interest Of Month '<?php echo date_format(date_sub(date_create($interset_row->overdue_date), date_interval_create_from_date_string($term . ' days')), getDateFormat()); ?>' is  <?php echo $interset_row->rate; ?>%:
                        </td>
                        <td valign="middle" align="right" class="p-10">
                            <?php if (isset($estimate_data->interest_status) && $estimate_data->interest_status == 'Yes') {
                                $id = 'interest_amt';
                                $overue_sum -= $overue;
                            } else {
                                $id = 'interest_amt_none';
                            }
                            ?>
                            <span id="<?php echo $id; ?>"
                                  <?php if (isset($estimate_data->interest_status) && $estimate_data->interest_status == 'Yes') : ?>style="text-decoration: line-through;"<?php endif; ?>><?php echo money($monthDue); ?></span>


                        </td>
                    </tr>
                <?php endif; ?>
            <?php }
			}  ?>
			<?php if ((float)$estimate_data->discount_total && $estimate_data->discount_total > 0) : ?>
				<tr>
					<td valign="middle" align="right" class="small_arrow">Discount:</td>
					<td valign="middle" align="right">
					<?php if($estimate_data->discount_in_percents) : ?>
					- <?php echo $estimate_data->discount_column; ?>% (<?php echo money($estimate_data->discount_total); ?>)
					<?php else : ?>
					- <?php echo money($estimate_data->discount_total, 2); ?>
					<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ($estimate_data->estimate_hst_disabled != 1 && $estimate_data->estimate_tax_name && $estimate_data->estimate_tax_value) : ?>
				<tr>
					<td valign="middle" align="right"
					    class="small_arrow"<?php if ($estimate_data->estimate_hst_disabled == 1) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<?php echo /*$estimate_data->estimate_tax_name . ' ' . */ 'Tax: ' .  round($estimate_data->estimate_tax_value, 3) . '%'; ?>:
					</td>
					<td valign="middle"
					    align="right"<?php if ($estimate_data->estimate_hst_disabled == 1) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<?php echo money($estimate_data->total_tax); ?>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td valign="middle" align="right" class="small_arrow"><strong>Subtotal:</strong></td>
				<td valign="middle" align="right"><?php echo money($estimate_data->total_with_tax); ?></td>
			</tr>
			<?php $paidInTotal = 0; ?>
			<?php foreach ($payments_data as $row) : ?>
				<tr>
					<td valign="middle" align="right"
					    class="small_arrow"><?php echo $row['payment_type'] == 'deposit' ? ucfirst($row['payment_type']) : 'Payment'; ?>:
					</td>
					<td valign="middle" align="right">-
						<?php echo money($row['payment_amount']); ?>
                    </td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<?php
				if (isset($invoice_data->interest_status) && $invoice_data->interest_status == 'Yes')
					$overue_sum = 0;
				?>
				<td valign="top" align="right" class="small_arrow"><strong>Total Payable:</strong></td>
				<td valign="middle" align="right">
                    <?php echo money($estimate_data->total_due); ?>
                </td>
			</tr>
			<?php if($estimate_data->total_due <= 0) : ?>
			<tr>
				<td valign="middle" align="center" colspan="2"
				    class="p_left_435 "><strong>PAID IN FULL</strong></td>
			</tr>
			<?php endif; ?>

		</table>
		<!-- /ESTIMATE ITEMS-->
	</div>
</div>
</div>

<div class="thank_you" style="font-size: 20px; margin-top: 10px; font-family: Arial, Helvetica, sans-serif;">Thank you for your business!</div>

<?php //$this->load->view('includes/pdf_templates/tree_inventory_legend'); ?>

<div class="invoice_terms">
    <?php
    if(isset($brand_id) && $brand_id): ?>
        <?php echo (strip_tags($invoice_terms, ['img']))?$invoice_terms:''; ?>
    <?php
    else:
    list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/payment_terms', 'includes', 'views/');
    if($result) {
        $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'payment_terms');
    }
endif;
?>
</div>
<div class="address" style="position: absolute; bottom: 15px; right: 0; left: 0;">
    <?php if(isset($brand_id) && $brand_id): ?>
        <?php echo ($pdf_footer)?$pdf_footer:''; ?>
    <?php else: ?>
        <?php echo $this->config->item('footer_pdf_address'); ?>
    <?php endif; ?>
</div>
<?php $this->load->view('includes/pdf_templates/tree_inventory_legend'); ?>

<?php /*if($this->config->item('company_pdf_payment_terms') && $this->config->item('company_pdf_payment_terms') !== '' && $this->config->item('company_pdf_payment_terms') !== ' ') : ?>
	<?php $this->load->view($this->config->item('company_pdf_payment_terms')); ?>
<?php endif;*/ ?>



	<?php if(isset($files) && $files && !empty($files)) : ?>
	<pagebreak>

	<?php foreach($files as $file) : ?>
		<div class="des_1">
			<img style="width:100%;" src="<?php echo $file; ?>">
		</div>
	<?php endforeach; ?>
	<?php endif; ?>
	<?php if(isset($estFiles) && $estFiles && !empty($estFiles)) : ?>
	<pagebreak>
		<?php foreach($estFiles as $file) : ?>
		<div class="des_1">
            <?php if (!is_file($file)) : ?>
			    <img style="width:100%;" src="<?php echo base_url(urlencode($file)); ?>">
            <?php else : ?>
                <img rotate="<?php echo getRotation($file); ?>" style="width:100%;" src="<?php echo base_url(urlencode($file)); ?>">
            <?php endif; ?>
		</div>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php /*$filesEst = isset($estimate_data) && $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files) : []; ?>
	<?php if(isset($filesEst) && $filesEst && count($filesEst)) : ?>
	<pagebreak>
		<div class="des_1">
		<?php foreach($filesEst as $key => $file) : ?>
			<?php if(!is_file($file)) continue; ?>
			<img rotate="<?php echo getRotation('./' . $file); ?>" style="width:100%;" src="<?php echo $file; ?>">
			<?php if(isset($filesEst[$key + 1])) : ?><br><br><?php endif; ?>
		<?php endforeach; ?>
		</div>
	<?php endif;*/ ?>
</body>
</html>
