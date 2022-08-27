<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/workorder_pdf.css" type="text/css" media="print">
	<style>
		@page chapter1{
			size: auto;
		}
		@page chapter2{
			size: landscape;
		}
		div.chapter2 {
			page: chapter2;
		}
		div.chapter1 {
			page: chapter1;
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
        .acceptance_1_title {
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
        }
        .acceptance_1_fineprint {
            font-size: 8pt;
            font-family: Arial, Helvetica, sans-serif;
        }
	</style>

    <?php

    $default_img = '/assets/' . $this->config->item('company_dir') . '/img/logo.png';
    $brand_id = get_brand_id($estimate_data, $client_data);
    $workorder_logo = get_brand_logo($brand_id, 'estimate_logo_file', $default_img);
    $pdf_footer = get_pdf_footer($brand_id);

    ?>

</head>
<body>

<div class="client_data" style="position: relative;">
	<div style="float: left; width: 50%;">
		<div class="workorder_number" style="float: left;">
			<div class="workorder_number_1">Workorder No.</div>
			<div class="workorder_number_2"><?php echo $workorder_data->workorder_no; ?></div>
			<br class="clearBoth"/>
		</div>
		<div class="title" style="text-align: left;">Client Information:</div>
		<?php $servicesNames = []; ?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="p_top_5">
			<tbody>
			<tr>
				<td width="150px" valign="top" class="p_top_5">Date:</td>
				<td valign="top" class="p_top_5"><?php echo date(getDateFormat()); ?></td>
			</tr>
			<tr>
				<td valign="top" class="p_top_5">Client:</td>
				<td valign="top" class="p_top_5"><?php echo $client_data->client_name; ?></td>
			</tr>
			<tr>
				<td valign="top" class="p_top_5">Client Address:</td>
				<td valign="top"
				    class="p_top_5"><?php echo $estimate_data->lead_address . ", " . $estimate_data->lead_city; ?>
					<br>
					<?php echo $estimate_data->lead_state . ", " . $client_data->client_country ?><br>
					<?php echo isset($client_data->client_zip) ? $client_data->client_zip : $estimate_data->lead_zip; ?>
				</td>
			</tr>
            <?php if(isset($estimate_data->lead_add_info) && $estimate_data->lead_add_info != '') : ?>
                <tr>
                    <td valign="top" class="p_top_5">Add. Info:</td>
                    <td valign="top" class="p_top_5"><?php echo $estimate_data->lead_add_info; ?></td>
                </tr>
            <?php endif; ?>
			<tr>
				<td valign="top" class="p_top_5">Client Phone:</td>
				<td valign="top" class="p_top_5"><?php echo isset($client_contact['cc_phone']) && $client_contact['cc_phone'] ? numberTo($client_contact['cc_phone']) : ''; ?></td>
			</tr>
			<?php if (isset($client_contact['cc_name']) && $client_data->client_name != $client_contact['cc_name']) : ?>
				<tr>
					<td>Contact Persone:</td>
					<td><?php echo $client_contact['cc_name']; ?></td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
	<div style="width: 50%;">
		<?php $tags = str_replace(' ', '+', $estimate_data->lead_address); ?>
		<img
			src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $tags; ?>+<?php echo $estimate_data->lead_city; ?>+<?php echo $estimate_data->lead_state; ?>+<?php echo $client_data->client_country; ?>+<?php echo $estimate_data->lead_zip; ?>&zoom=11&size=380x270&markers=<?php echo $tags; ?>+<?php echo $client_data->client_city; ?>+<?php echo $client_data->client_state; ?>+<?php echo $client_data->client_country; ?>>+<?php echo $estimate_data->lead_zip; ?>&key=<?php echo $this->config->item('gmaps_key'); ?>"
			alt="Google Map" height="270" width="380">
	</div>
</div>

<div class="title">Estimate Details:</div>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table" style="font-size: 12px;">
	<thead>
	<tr>
		<th align="left" class="p_top_5">
			Estimator: <?php echo nl2br($user_data->firstname . ' ' . $user_data->lastname); ?>
			<?php if($emp_data && isset($emp_data->emp_phone) && $emp_data->emp_phone) : ?>
				(<?php echo numberTo($emp_data->emp_phone); ?>)
			<?php endif; ?>
		</th>
	</tr>
	<tr>
		<th align="left" class="p_top_5" style="padding-bottom: 5px">Description</th>
	</tr>
	</thead>
</table>

    <?php $this->load->view('includes/pdf_templates/tree_inventory'); ?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table" style="font-size: 12px;">
    <tbody>
    <?php
    if(!empty($estimate_data->mdl_services_orm)):
    foreach ($estimate_data->mdl_services_orm as $service_data) : ?>
		<?php
            if($service_data->service_status == 1)
                continue;
			$service_style = '';
			$servicesNames[] = trim($service_data->service->service_name);
			if(isset($service_ids) && !empty($service_ids))
			{
				
				if(array_search($service_data->id, $service_ids) === FALSE)
					continue;
			}
		?>			
					<tr>
						<td  style="<?php echo $service_style?>">
							<div>
								<strong><?php echo isset($service_data->estimate_service_ti_title) && !empty($service_data->estimate_service_ti_title) ? $service_data->estimate_service_ti_title : $service_data->service->service_name; ?></strong>
                            </div>
                            <br>
						</td>
						<td style="width: 150px; text-align: right;">
							<?php if(config_item('show_workorder_pdf_amounts') && (!isset($event_id) || !$event_id)): ?>
							<div style="width: 200px; border-bottom: 1px solid #ca8427;"><strong style="color: #ca8427; float:left">Price:</strong><strong style="float:right">&nbsp;<?php echo money($service_data->service_price); ?></strong></div>
							<br>
							<br>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td style="<?php echo $service_style?>" colspan="2">
							<?php echo decorate_text($service_data->service_description, true); ?>
                            <?php $this->load->view('workorders/partials/workorder_service_time_for_pdf', [ 'service_data' => $service_data]); ?>
						</td>
					</tr>
					<tr>
                        <?php if(!$service_data->service->is_bundle && !$service_data->service->is_product): ?>
						<td style="<?php echo $service_style?>" colspan="2">
                            <?php $this->load->view('workorders/partials/workorder_equipments_for_pdf', [ 'service_data' => $service_data]); ?>
						</td>
                        <?php elseif ($service_data->service->is_bundle && !empty($service_data->bundle_records)): ?>
                           <?php $service_style .= ' padding-left: 20px; border-left: 1px solid #bebebe;';
                           foreach ($service_data->bundle_records as $record): ?>
                               <tr>
                                   	<td style="<?php echo $service_style?>" colspan="2">
                                       <div><strong><?php echo $record->service->service_name; ?></strong></div>
                                       <?php echo decorate_text($record->service_description, true); ?>
                                       <?php $this->load->view('workorders/partials/workorder_service_time_for_pdf', [ 'service_data' => $record]); ?>
                                   	</td>
                               </tr>
                               <tr>
                                   <?php if($record->service->is_product): ?>
                                       <td style="<?php echo $service_style?> padding-top: 5px; padding-bottom: 10px;" colspan="2">
                                           Quantity: <?php echo $record->quantity ?>
                                       </td>
                                    <?php else : ?>
                                        <td style="<?php echo $service_style?>" colspan="2">
                                            <?php $this->load->view('workorders/partials/workorder_equipments_for_pdf', [ 'service_data' => $record]); ?>
                                        </td>
                                    <?php endif; ?>
                               </tr>
                            <?php endforeach; ?>
                        <?php elseif($service_data->service->is_product): ?>
                            <td style="<?php echo $service_style?> padding-top: 5px; padding-bottom: 10px ">
                                Quantity: <?php echo $service_data->quantity ?>
                            </td>
                        <?php endif; ?>
					</tr>
	<?php endforeach; endif;?>
	</tbody>
</table>

<?php if(config_item('show_workorder_pdf_amounts') && (!isset($event_id) || !$event_id)): ?>
<div>
	<div style="float: left; width:49%"></div>
	<div style="border-radius: 10px; background: #EBEBE7; padding: 5px; width: 50%; margin-top: -10px; float: right;">
		<table  class="estimate_table" width="100%" style="">
			<tr>
				<td class="text-left" style="padding-top: 5px;padding-left: 3px;"><strong style="color: #ca8427;">Total For Estimate:</strong></td>
				<td style="text-align: right;padding-top: 5px;padding-right: 3px;">
			        <?php echo money($workorder_data->sum_without_tax); ?>
				</td>
			</tr>
			<?php if (floatval($workorder_data->discount_total)) : ?>
				<tr>
					<td style="padding-top: 3px;padding-left: 3px;"><strong style="color: #ca8427;">Discount:</strong></td>
					<td style="text-align: right;padding-top: 3px;padding-right: 3px;">
						<?php if(!$workorder_data->discount_in_percents) : ?>
						- <?php echo money($workorder_data->discount_total); ?>
						<?php else : ?>
						- <?php echo $workorder_data->discount_percents_amount; ?>% (<?php echo money($workorder_data->discount_total); ?>)
					<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td style="padding-top: 3px;padding-left: 3px;"><strong style="color: #ca8427;">Tax:</strong></td>
				<td style="text-align: right;padding-top: 3px;padding-right: 3px;">
			        (<?php echo round($workorder_data->tax_value, 3); ?>%): <?php echo money($workorder_data->total_tax); ?>
				</td>
			</tr>

			<tr>
				<td style="padding-top: 3px;padding-left: 3px;"><strong style="color: #ca8427;">Total:</strong></td>
				<td style="text-align: right;padding-top: 3px;padding-right: 3px;">
			        <?php echo money($workorder_data->total_with_tax); ?>
				</td>
			</tr>

            <?php if(floatval($workorder_data->payments_total)) : ?>
            <tr>
                <td style="padding-top: 3px;padding-left: 3px;"><strong style="color: #ca8427;">Deposit amounts:</strong></td>
                <td style="text-align: right;padding-top: 3px;padding-right: 3px;">
                    <?php echo money($workorder_data->payments_total); ?>
                </td>
            </tr>
            <?php endif; ?>

			<tr>
				<td style="padding-bottom: 5px;padding-left: 3px;"><strong style="color: #ca8427;">Total due:</strong></td>
				<td style="text-align: right;padding-bottom: 5px;padding-right: 3px;">
			        <?php echo money($workorder_data->total_due); ?>
				</td>
			</tr>
		</table>
	</div>
	<div style="clear: both;"></div>
</div>
<br>
<?php endif; ?>

<div style="padding-bottom: 10px">
<?php $this->load->view('includes/pdf_templates/tree_inventory_legend'); ?>
</div>
<!-- Notes to the crew -->
<div class="title">Notes</div>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table">
	<tr>
		<td class="p_top_5"><?php echo nl2br($estimate_data->estimate_crew_notes); ?></td>
	</tr>
</table>
<!-- /Notes to the crew -->

<!-- Workorder Requirements -->
<div class="title">Workorder Requirements</div>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table" style="font-size: 12px;">
	<thead>
	<tr>
		<th align="left" width="30%" class="p_top_5">Team</th>
		<th align="left" width="20%" class="p_top_5">Estimated Time</th>
		<th align="left" width="20%" class="p_top_5" align="center">Actual Time</th>
		<th align="left" class="p_top_5">Equipment</th>
	</tr>

	</thead>
	<tbody>
	<?php foreach($events as $key => $val) : ?>
		<?php if($val['team_id'] != $team_id && $team_id) continue; ?>
	<tr>
		<td width="30%">
			<b><?php echo date(getDateFormat(), $val['event_start']); ?></b><br>
			<?php $team = NULL; ?>
			<?php $teamMembers = 0; ?>
			<?php foreach ($members[$val['id']] as $worker) : ?>
				<?php $team .= $worker['emp_name'] . ", "; ?>
				<?php $teamMembers++; ?>
			<?php endforeach; ?>
			<?php echo rtrim($team, ', ') ? rtrim($team, ', ') : '-'; ?>
		</td>
		<td width="20%">
			<?php echo date(getPHPTimeFormatWithOutSeconds(), $val['event_start']); ?> - <?php echo date(getPHPTimeFormatWithOutSeconds(), $val['event_end']); ?>
			<?php //echo nl2br($estimate_data->estimate_item_estimated_time); ?>
		</td>
		<td width="20%" align="center">
			<?php if ($val['er_id'] !== null) : ?>
                <?php echo isset($val['er_event_start_work']) ? getTimeWithDate(date("H:i", strtotime($val['er_event_start_work'])), 'H:i', true) : '-'; ?> -
                <?php echo isset($val['er_event_finish_work']) ? getTimeWithDate(date("H:i", strtotime($val['er_event_finish_work'])), 'H:i', true) : '-'; ?>
                <?php if ($val['er_on_site_time']) : ?>
                    <br>(<?php echo round($val['er_on_site_time'] * $teamMembers / 3600, 2); ?> mhr.)
                <?php endif; ?>
            <?php endif; ?>
		</td>
		<td>
			<?php $teamItems = NULL; ?>
			<?php foreach ($items[$val['id']] as $item) : ?>
                <?php $teamItems .= $item['eq_name'] . ", "; ?>
			<?php endforeach; ?>
			<?php echo rtrim($teamItems, ', ') ? rtrim($teamItems, ', ') : '-'; ?>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>Equipment setup: <?php echo nl2br($estimate_data->estimate_item_equipment_setup); ?></td>
	</tr>
</table>
<pagebreak>
	<div class="title">Workorder Report</div>

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table" style="margin-bottom: -10px;">
		<tr>
			<td width="38%" style="padding: 10px 0;">LEFT PREVIOUS JOB</td>
			<td width="12%" style="padding: 10px 0;">____ : ____</td>
			<td width="38%" style="padding: 10px 0;">LEFT THE JOB SITE</td>
			<td width="12%" style="padding: 10px 0;">____ : ____</td>
		</tr>
		<tr>
			<td style="padding: 10px 0;">ARRIVED AT THE JOB SITE</td>
			<td style="padding: 10px 0;">____ : ____</td>
			<td style="padding: 10px 0;">ARRIVED AT THE OFFICE</td>
			<td style="padding: 10px 0;">____ : ____</td>
		</tr>
	</table>

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table" style="border-top: 0px;">
		<tr>
			<td width="40%" class="p_top_5" style="padding: 10px 0;">
				WAS THE JOB COMPLETED IN FULL?
			</td>
			<td width="20%" class="p_top_5" align="center" valign="middle" style="padding: 10px 0;">YES / NO</td>
			<td width="40%" class="p_top_5" align="center" valign="middle" style="padding: 10px 0;">
				___________________________________________________
			</td>
		</tr>
		<tr>
			<td width="40%" style="padding: 10px 0;" valign="middle" >
				WAS THE PAYMENT RECEIVED?
			</td>
			<td width="20%" style="padding: 10px 0;" align="center" valign="middle">YES / NO</td>
			<td width="40%" style="padding: 10px 0;" align="center" valign="middle">
				HOW MUCH ____________________ CHECK / CASH
			</td>
		</tr>
		<tr>
			<td width="40%" style="padding: 10px 0;" valign="middle" >
				DAMAGES / COMPLAINTS / NOTES:
			</td>
			<td width="20%" style="padding: 10px 0;" align="center" valign="middle"></td>
			<td width="40%" style="padding: 10px 0;" align="center" valign="middle">
				
			</td>
		</tr>
		<tr>
			<td width="40%" style="padding: 10px 0;" valign="middle" >
			</td>
			<td width="20%" style="padding: 10px 0;" align="center" valign="middle"></td>
			<td width="40%" style="padding: 10px 0;" align="center" valign="middle">
				<BR/><BR/><BR/><BR/><BR/><BR/><BR/>	
			</td>
		</tr>
	</table>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table">
		<tr>
			<td width="33%" style="padding: 10px 0;" valign="middle" >
				EXTRA WORK ESTIMATED:
			</td>
			<td width="33%" style="padding: 10px 0;" align="center" valign="middle">
				NAME OF ESTIMATOR
			</td>
			<td width="34%" style="padding: 10px 0;" align="center" valign="middle">
				___________________________________________________
			</td>
		</tr>
	</table>

	<table cellpadding="5" cellspacing="0" border="1" width="100%" class="info_table">
		<tr>
			<td align="center" width="25%">ADDRESS</td>
			<td align="center" width="45%">DESCRIPTION</td>
			<td align="center" width="15%">AMOUNT</td>
			<td align="center" width="15%">SIGNATURE</td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td>
				<BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/><BR/>
			</td>
		</tr>
	</table>


	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="info_table" style="border-top: 0px;">
		<tr>
			<td width="50%" class="p_top_5" valign="top">&nbsp;</td>
			<td width="34%" class="p_top_5" valign="top" align="right">TEAM LEADER SIGNATURE</td>
			<td width="33%" class="p_top_5" align="center" valign="middle">&nbsp;&nbsp;_______________________________________
		</tr>
	</table>
	<?php if(isset($files) && $files && !empty($files)) : ?>
	<pagebreak>

		<?php foreach($files as $file) : ?>
			<img style="width:100%;" src="<?php echo $file; ?>">
		<?php endforeach; ?>
	<?php endif; ?>
	<?php if(isset($estFiles) && $estFiles && !empty($estFiles)) : ?>
		<?php foreach($estFiles as $name => $file) : ?>
		<?php //$imgData = getimagesize($file); //echo $imgData[0] . '<br>' . $imgData[1];?>
		<?php /*if($imgData && count($imgData) && (intval($imgData[0]) > intval($imgData[1]))) : ?>
			<div class="chapter2" style="text-align: center"> 
				<img style="width:100%;text-align:center;" src="<?php echo base_url($file); ?>">
			</div>
		<?php else: */ ?>
			 <?php //var_dump(is_file($file)); continue;?>
            <?php foreach ($file as $key => $filePath): ?>
                <pagebreak>
				<div class="chapter1" style="position:absolute; padding-right: 15px">
                    <div><strong><?= is_string($name) && $key == 0 ? $name : '' ?></strong></div>
                    <?php if (!is_file($filePath)) : ?>
					    <img width="100%" style=""  src="<?php echo base_url(urlencode($filePath)); ?>">
                    <?php else : ?>
                        <img rotate="<?php echo getRotation($filePath); ?>" style="width:100%;" src="<?php echo base_url(urlencode($filePath)); ?>">
                    <?php endif; ?>
				</div>
			<?php endforeach; //endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php $files = isset($workorder_data) && $workorder_data->wo_pdf_files ? json_decode($workorder_data->wo_pdf_files) : []; ?>
	<?php /*if(isset($files) && $files && count($files)) : ?>
	<pagebreak>
		<div class="des_1">
		<?php foreach($files as $key => $file) : ?>
			<?php if(!is_file($file)) continue; ?>
			<img rotate="<?php echo getRotation('./' . $file); ?>" style="width:100%;" src="<?php echo $file; ?>">
			<?php if(isset($files[$key + 1])) : ?><br><br><?php endif; ?>
		<?php endforeach; ?>
		</div>
	<?php endif;*/ ?>
</body>
</html>
