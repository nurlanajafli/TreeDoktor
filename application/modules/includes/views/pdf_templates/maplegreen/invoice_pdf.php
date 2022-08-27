<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo !empty($title) ? $title : ''; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <?php setlocale(LC_ALL, 'en_US.utf8', 'en_US'); ?>

		<?php

		$default_img = 'assets/'.$this->config->item('company_dir').'/print/header2_short.png';
		$brand_id = get_brand_id(isset($invoice_data)?$invoice_data:[], isset($client_data)?$client_data:[]);
		$invoice_logo = get_brand_logo($brand_id, 'invoice_logo_file', $default_img);
		$invoice_watermark = get_brand_logo($brand_id, 'watermark_logo_file', false);
		$invoice_left_side = get_brand_logo($brand_id, 'invoice_left_side_file', false);
		$invoice_terms = get_invoice_terms($brand_id);
		$pdf_footer = get_pdf_footer($brand_id);

		?>

    <style type="text/css">
        .acceptance_1_fineprint, .acceptance_1_title{
            font-size: 0.8em;
        }
    </style>
</head>
<body style="font-family: sans-serif;">

<div style="width: 100%;">

<div style="width: 45%; text-align: left; float: left;">
  <span style="font-family: serif; font-size: 3em; font-style: italic;">Invoice</span>
  <div style="color: #0d83a3;">
    for
    <div style="border-radius: 4px; background-color: #d9d9d9; padding: 4px;">
      <div style="font-size: 1.2em; margin-bottom: 10px;"><?php echo $client_data->client_name; ?></div>
      <div>
        <?php echo $client_data->client_address ?><br />
        <?php echo $client_data->client_city . ", " . $client_data->client_state . ", " . $client_data->client_zip; ?><br />
          <?php if(isset($client_data->client_main_intersection) && $client_data->client_main_intersection != '') : ?>
              <?php echo $client_data->client_main_intersection; ?><br />
          <?php endif; ?>
        <?php echo isset($client_contact['cc_phone']) ? numberTo($client_contact['cc_phone']) : '-'; ?><br />

        <?php if ($client_contact['cc_email']) : ?>
          <?php echo $client_contact['cc_email']; ?><!-- br / -->
        <?php endif; ?>

        <?php if ($client_data->client_address != $estimate_data->lead_address) : ?>
          Job site location: <?php echo $estimate_data->lead_address . " " . $estimate_data->lead_city; ?><br />

        <?php endif; ?>
          <?php if(isset($estimate_data->lead_add_info) && $estimate_data->lead_add_info != '') : ?>
              <br />Add. Info: <?php echo $estimate_data->lead_add_info; ?><br />
          <?php endif; ?>
        <?php if (isset($client_contact['cc_name']) && $client_data->client_name != $client_contact['cc_name']) : ?>
          Job site contact: <?php echo $client_contact['cc_name']; ?><br />
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div style="width: 45%; text-align: right; float: right;">
  <div>
    <img src="<?php echo base_url($invoice_logo); ?>"
         style="max-height: 120px; <?php // echo $this->config->item('company_header_pdf_logo_styles'); ?>" />
  </div>

  <!-- NB TODO update the client's config and client's brand with this!  -->
  <?php $pdf_header="58 Palmer Circle<br />Caledon, ON, L7E 0A5<br />416-951-0487<br /><a href='https://maplegreen.ca' target='_blank' style='color: #0d83a3;'>maplegreen.ca</a>"; ?>

  <div style="padding-right: 10px;">
    <?php $brand_address = brand_address($brand_id); ?>
    <?php if(isset($brand_id) && $brand_id): ?>
        <?php echo ($pdf_header)?$pdf_header:''; ?>
    <?php else: ?>
        <?php echo $this->config->item('footer_pdf_address'); ?>
    <?php endif; ?>
  </div>
</div>

<br style="clear:both;"/>
</div>

<!-- INVOICE BODY -->
<div style="border-radius: 4px; background-color: #d9d9d9; padding: 24px;">
  <span style="font-family: serif; font-size: 1.6em; font-weight: bold; ">Invoice No. <?php echo $invoice_data->invoice_no; ?> </span>
  issued on <?php echo getDateTimeWithDate($invoice_data->date_created, 'Y-m-d');?> and <b>due on <?php echo getDateTimeWithDate($invoice_data->overdue_date, 'Y-m-d'); ?></b>
  <br /><br />

  <table style="width: 100%;">
    <!-- HORIZONTAL -->
    <tr>
      <td colspan="4" style="height: 2px; font-size: 2px; background-color: grey;">&nbsp;</td>
    </tr>

      <tr>
          <td colspan="4">
              <?php $this->load->view('includes/pdf_templates/tree_inventory'); ?>
          </td>
      </tr>
      <?php if(!empty($estimate_services_data)): ?>

    <!-- HEADER -->
    <tr style="margin-bottom: 10px;">
      <td style="font-size: 0.8em; width: 40px; text-align: left; font-weight: bold;">Qty</td>
      <td style="font-size: 0.8em; width: 160px; text-align: left; font-weight: bold;">Name</td>
      <td style="font-size: 0.8em; text-align: left; font-weight: bold;">Description</td>
      <td style="font-size: 0.8em; width: 120px; text-align: right; font-weight: bold;">Amount</td>
    </tr>

    <!-- ITEMS -->
    <?php foreach ($estimate_services_data as $numService => $service_data) : ?>

      <tr style="margin-bottom: 10px;">
        <td style="font-size: 0.8em; text-align: left; vertical-align: top;"><?php echo $service_data['quantity']?></td>
        <td style="font-size: 0.8em; text-align: left; vertical-align: top;"><?= isset($service_data['estimate_service_ti_title']) && !empty($service_data['estimate_service_ti_title']) ? $service_data['estimate_service_ti_title'] : $service_data['service_name']; ?></td>
        <td style="font-size: 0.8em; text-align: left; vertical-align: top; text-align: justify; text-justify: inter-word;">
          <?php if ($service_data['is_product']) : ?>
            <!-- PRODUCT -->
            <?php echo decorate_text($service_data['service_description']); ?>(<?php echo str_replace(' ', '', nl2br(money($service_data['cost']))); ?>)
          <?php else : ?>
            <!-- SERVICES -->
            <pre style="font-size: 1em; font-family: sans-serif;"><?php echo decorate_text($service_data['service_description']); ?></pre>
          <?php endif; ?>


          <?php if($service_data['is_bundle'] && $service_data['is_view_in_pdf']): ?>
            <?php if(isset($service_data['bundle_records'])) foreach($service_data['bundle_records'] as $bundle_record) : ?>
              <table style="width: 100%; margin-top: 10px; border: 1px solid #b6b6b8"><tr><td style="font-size: 0.8em; font-family: sans-serif;">
                <?php echo $bundle_record->service_name; ?>

                <?php if($bundle_record->non_taxable): ?>
                    <span>(Non-taxable)</span>
                <?php endif; ?>

                <?php if($bundle_record->is_product) : ?>
                (<?php echo $bundle_record->quantity ?> x <?php echo money($bundle_record->cost); ?> )
                <?php else : ?>
                (<?php echo money($bundle_record->service_price); ?>)
                <?php endif ?>

                <pre style="font-size: 1em; font-family: sans-serif;"><?php echo decorate_text($bundle_record->service_description); ?></pre>
              </td></tr></table>
            <?php endforeach; ?>
          <?php endif; ?>


        </td>
        <td style="font-size: 0.8em; text-align: right; vertical-align: bottom; border-bottom: 1px solid #b6b6b8;"><?php echo nl2br(money($service_data['service_price'])); ?></td>
      </tr>


    <?php endforeach; ?>

      <?php endif; ?>
    <!-- HORIZONTAL -->
    <tr>
      <td colspan="4" style="height: 2px; font-size: 2px; background-color: grey;">&nbsp;</td>
    </tr>

    <!-- TOTALS -->

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
			      <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
			        Interest Of Month '<?php echo date_format(date_sub(date_create($interset_row->overdue_date), date_interval_create_from_date_string($term . ' days')), getDateFormat()); ?>' is  <?php echo $interset_row->rate; ?>%:
			      </td>
			      <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
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
		<?php }} ?>




    <?php if ((float)$estimate_data->discount_total && $estimate_data->discount_total > 0) : ?>
        <tr>
            <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
                SUM
            </td>
            <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
                <?php echo money($estimate_data->sum_taxable + $estimate_data->sum_non_taxable); ?>
            </td>
        </tr>
      <tr>
        <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
            DISCOUNT<?php if($estimate_data->discount_in_percents) : ?> <?php echo $estimate_data->discount_column; ?>%<?php endif; ?>
        </td>
        <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
					<?php if($estimate_data->discount_in_percents) : ?>
					- <?php echo money($estimate_data->discount_total); ?>
					<?php else : ?>
					- <?php echo money($estimate_data->discount_total, 2); ?>
					<?php endif; ?>
        </td>
      </tr>
    <?php endif; ?>

      <tr>
          <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
              SUBTOTAL
          </td>
          <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
              <?php echo money($estimate_data->sum_without_tax); ?>
          </td>
      </tr>

		<?php if ($estimate_data->estimate_hst_disabled != 1 && $estimate_data->estimate_tax_name && $estimate_data->estimate_tax_value) : ?>
			<tr>
				<td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
					<span <?php if ($estimate_data->estimate_hst_disabled == 1) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<?php echo $estimate_data->estimate_tax_name . ' ' . round($estimate_data->estimate_tax_value, 3) . '%'; ?>
					</span>
				</td>
				<td style="font-weight: bold; text-align: right; font-size: 0.8em;">
					<span <?php if ($estimate_data->estimate_hst_disabled == 1) : ?> style="text-decoration: line-through;"<?php endif; ?>>
						<?php echo money($estimate_data->total_tax); ?>
					</span>
				</td>
			</tr>
		<?php endif; ?>
      <tr>
          <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
              TOTAL
          </td>
          <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
              <?php echo money($estimate_data->total_with_tax); ?>
          </td>
      </tr>

		<?php $paidInTotal = 0; ?>
		<?php foreach ($payments_data as $row) : ?>
			<tr>
				<td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
                    PAYMENT
				</td>
				<td style="font-weight: bold; text-align: right; font-size: 0.8em;">
					-<?php echo money($row['payment_amount']); ?>
				</td>
			</tr>
		<?php endforeach; ?>

		<?php
		if (isset($invoice_data->interest_status) && $invoice_data->interest_status == 'Yes')
			$overue_sum = 0;
		?>
		<tr>
			<td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
                TOTAL PAYABLE
			</td>
			<td style="font-weight: bold; text-align: right; font-size: 0.8em;">
				<?php echo money($estimate_data->total_due); ?>
			</td>
		</tr>

		<?php if($estimate_data->total_due <= 0) : ?>
			<tr>
				<td colspan="4" style="font-weight: bold; text-align: right; font-size: 0.8em;">
					PAID IN FULL
				</td>
			</tr>
		<?php endif; ?>

  </table>
</div>

<hr>
<div>
    <span style="font-family: serif; font-size: 2em;">THANK YOU </span>
    <span style="color: #0d83a3; font-size: 1.3em;">for considering MapleGreen Tree Services!</span></div>
<hr>

<!-- TERMS AND CONDITIONS -->
<div style="width: 100%;">

<div style="width: 23%; text-align: left; float: left;">
  <span style="color: #0d83a3; ">
    QUESTIONS? <br />
    CONTACT US! <br />
  </span>
  info@maplegreen.ca <br />
  416‐951‐0487
</div>

<div style="width: 75%; float: right; font-size: 0.8em; text-align: justify; text-justify: inter-word;">
  <span style="color: #0d83a3; ">Payment Terms</span>

	<p>
		MapleGreen is fully insured, WSIB compliant and has ISA and certified arborists on staff.<br />
		MapleGreen uses expert practices and specialized equipment to perform tree services in a safe and timely manner.<br />
		MapleGreen crews aim to safeguard all customer and company property while leaving a minimal, if any, impact on the surrounding environment.<br />
		As it is part of the trade of working on various sizes and conditions of trees in intricate spaces, our primary focus is on the safety of our climbers, groundsmen and the general public.
	</p>

	<p>
		Any additional tree work, disposal services and drive time requested, confirmed and completed will result in additional costs.<br />
		Any additional stump grinding, root chasing, regrading, and regrinding to lower depths requested, confirmed and completed will result in additional costs.
	</p>

	<p>
		It is recommended that customers move any vehicles, tables, chairs, clothes lines, flower pots, objects or valuables away from the work areas prior to work commencing.<br />
		MapleGreen is not responsible for any minor indents, markings or movement of lawns, gardens, other trees, irrigation, utility lines, eavestroughs or fences.<br />
		MapleGreen is not responsible for any minor sawdust, wood chips, fuel or oil remnants on site from chainsaws, dump trucks and wood chippers.
	</p>

	<p>
		MapleGreen may need to reschedule work due to unsafe weather conditions, emergency work arising, or equipment breakdowns.<br />
		Customers will be charged a cancellation fee of $250 if they cancel work within 24 hours of scheduled work commencement.
	</p>

	<p style="font-weight: bold;">
		MapleGreen requires payment immediately upon completion of work.<br />
		MapleGreen accepts payment via E-Transfers, *Credit Cards, Checks & Cash.<br />
		Interest of 2% per month will be charged on invoices not paid within 30 days.
	</p>

	<p style="font-weight: bold;">
		No payments are to be made on site to crew members.<br />
		All payments must be made out to MapleGreen Tree Services.<br />
		The head office is located at 58 Palmer Circle, Caledon, Ontario, Canada, L7E 0A5.<br />
		Office hours are Monday-Friday between 9am-5pm.
	</p>

	<p style="font-weight: bold;">
		E-transfers must be sent to <a href="mailto:info@maplegreen.ca">info@maplegreen.ca</a>, where funds are auto-deposited.<br />
    *Credit Card payments via this [CCLINK] or info called into the head office @ 416-951-0487.<br />
		*Credit Card payments are subject to a 2.6% fee.<br />
		Checks must be mailed to the head office.<br />
		Cash must be delivered to the head office.
	</p>

	<p style="font-weight: bold;">
			E-Transfers and *Credit Card payments are the preferred contactless payment methods during this time.
	</p>

	<?php if(isset($brand_id) && $brand_id): ?>
			<?php echo ($invoice_terms)?$invoice_terms:''; ?>
	<?php endif; ?>
</div>

<br style="clear:both;"/>
</div>

<?php $this->load->view('includes/pdf_templates/tree_inventory_legend'); ?>

<?php if($pdf_footer): ?>
		<div style="position: absolute; bottom: 10px; right: 0; left: 0; text-align: center; font-size: 0.8em;"><?php echo $pdf_footer; ?></div>
<?php endif; ?>

<?php if(isset($files) && $files && !empty($files)) : ?>
<pagebreak>

<?php foreach($files as $file) : ?>
	<div>
		<img style="width:100%;" src="<?php echo $file; ?>">
	</div>
<?php endforeach; ?>
<?php endif; ?>
<?php if(isset($estFiles) && $estFiles && !empty($estFiles)) : ?>
<pagebreak>
	<?php foreach($estFiles as $file) : ?>
	<div>
					<?php if (!is_file($file)) : ?>
				<img style="width:100%;" src="<?php echo base_url($file); ?>">
					<?php else : ?>
							<img rotate="<?php echo getRotation($file); ?>" style="width:100%;" src="<?php echo base_url($file); ?>">
					<?php endif; ?>
	</div>
	<?php endforeach; ?>
<?php endif; ?>


</body>
</html>
