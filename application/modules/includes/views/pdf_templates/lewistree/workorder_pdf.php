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
                    <td>Add. Info:</td>
                    <td><?php echo $estimate_data->lead_add_info; ?></td>
                </tr>
            <?php elseif(isset($client_data->client_main_intersection) && $client_data->client_main_intersection != '') : ?>
                <tr>
                    <td>Add. Info:</td>
                    <td><?php echo $client_data->client_main_intersection; ?></td>
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
								<strong><?php echo $service_data->service->service_name; ?></strong>
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
		<td class="p_top_5"><?php echo nl2br($estimate_data->estimate_crew_notes); ?>
		</td>
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
	<pagebreak>
	<div>
		<table>
			<tr>
				<td>
					<img src="<?php echo base_url($workorder_logo); ?>" width="80px">
				</td>
				<td class="safety-form">
					TAILGATE SAFETY MEETING FORM | PRE JOB SAFETY ASSESSMENT
				</td>
			</tr>
		</table>
	</div>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 5px 0; ">
		<tr>
			<td width="50%" valign="top">
				<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tbody>
					<tr>
                        <td valign="top"><h4>Pre-Job Hazard Survey</h4><br><br>

                            A job briefing is required at least once a day or before starting a
                            new job in order to identify and minimize hazards on the job. This
                            form may be used to help document the hazards found. Please
                            place a check mark in the box next to each of the following hazards
                            that are most relevant to this particular job and discuss them. Keep
                            a copy of the completed form in an office file.
                        </td>
                        <td valign="top">
                            <h4>Inspección del peligro antes del Trabajo de
                                Emergencia en Tormentas</h4><br>

                            Se recomienda una sesión Tailgate y una sesión informativa de
                            trabajo cada dia antes de comenzar trabajo de emergencia en
                            tormentas para identificar y minimizar los peligros en el trabajo.
                            Por favor ponga una marca en la cajita cerca de cada uno de
                            los siguientes peligros que son relevantes a este trabajo y tome
                            el tiempo para discutirlos. Mantenga una copia de la forma
                            completada en un archivo de oficina.</td>
					</tr>
                    <tr>
                        <td width="50%" valign="top"><br>
                            <table width="95%" border="1" cellpadding="0" cellspacing="0">
                                <tr><td style="height:40px;"> &nbsp;&nbsp;Date:&nbsp;</td><td style="height:40px;"> &nbsp; Crew Leader:&nbsp;<br></td></tr>
                                <tr><td colspan="2"> &nbsp;&nbsp;Job Location:&nbsp;<br><br><br></td></tr>
                                <tr><td colspan="2"> &nbsp;&nbsp;Type of job:<br><br><br></td></tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Day of the week/time of day</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Accidents can be dependent on how jobs are scheduled.<br>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;They are more likely just before lunch, just before and just<br>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;after holidays and vacation days
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Extreme weather conditions</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Frost bite, heat exhaustion, effect on driving
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Inexperienced personnel</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Their ability to detect hazardous conditions
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Improper use of PPE</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Head, eye, hearing, foot, hand, leg injuries
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Distance to electrical conductors</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Direct and/or indirect contact
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Terrain</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Slips, trips, and falls
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Noise levels</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Necessity of hand signals
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>New equipment</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Proper use and maintenance
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Obstacles</strong><br>
                                        <small>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Overhead and/or ground tevel
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Traffic control</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;Being struck, protection of the work area, cones & signs
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Moving/lifting heavy objects</strong><br>
                                        <small>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Proper techniques and/or equipment
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;<input type="checkbox"> <strong>Chemicals</strong><br>
                                        <small>
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Contact with or exposure to
                                        </small>
                                    </td>
                                </tr>
                                <tr ><td colspan="2" style="height:45px;">&nbsp;<input type="checkbox"> <strong>Others</strong><br>
                                    </td>
                                </tr><tr><td colspan="2" style="height:45px;"> &nbsp;&nbsp;Crew members signatures:<br>

                                    </td>
                                </tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;1.&nbsp;<br></td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;2.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;3.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;4.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;5.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;6.&nbsp;</td></tr>
                                <tr>
                                    <td style="height:45px;">&nbsp;&nbsp;Arrival:&nbsp;</td>
                                    <td style="height:45px;">&nbsp;&nbsp;Chipper Number:&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="height:45px;">&nbsp;&nbsp;Departure:&nbsp;</td>
                                    <td style="height:45px;">&nbsp;&nbsp;Start Hours:&nbsp;</td>
                                </tr>
                                <tr >
                                    <td colspan="2" style="height:45px;">&nbsp;&nbsp;Phone number in case of emergency: &nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            </table>
                        </td>


                        <td width="50%" valign="top"><br>
                            <table width="100%" border="1" cellpadding="0" cellspacing="0">
                                <tr><td style="height:40px;"> &nbsp;&nbsp;Fecha:&nbsp;</td><td style="height:40px;"> &nbsp; Lider de equipo:</td></tr>
                                <tr><td colspan="2"> &nbsp;&nbsp;Locación de trabajo:&nbsp;<br><br><br></td></tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Condiciones extremas de clima</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Efectos en la salud, manejo, caminar, visión & equipo
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Trabajo de noche</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Ser capaz de ver y de ser visto
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Control de tráfico</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Ser golpeado, protección de área de trabajo, conos &<br>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;señales
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Fatiga/sueño inadecuado, comida</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Tomar descanso adecuado, comida, liquidos; tomar<br>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;descansos conforme sea necesario
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Árboles/ramas caídas y escombros</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Peligros escondidos — conductores, etc.
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Conductores colgantes/desplazados</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Contacto con árboles, cualquier alambre aéreo u objetos<br>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;de metales
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Conductores caídos</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Considere TODOS los conductores como energizados
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Uso impropio de PPE</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Lesiones a la cabeza, ojos, oídos, pies, manos, piernas, y<br>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;de caídas
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Subir árboles dañados</strong><br>
                                        <small>
                                            &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;Ramas no tan fuertes se romperán; observar ramas peligrosas
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Madera bajo presión/tensión</strong><br>
                                        <small>
                                            &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Libere la tensión cortando propiamente
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2">&nbsp;&nbsp;<input type="checkbox"> <strong>Presión para trabajar más rápidamente</strong><br>
                                        <small>
                                            &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;NO tome atajos bajo ninguna circunstancia
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2" style="height:40px;">&nbsp;&nbsp;<input type="checkbox"> <strong>Hacer conjeturas en una situación poco familiar</strong><br>
                                        <small>
                                            &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;Si tiene dudas, haga preguntas
                                        </small>
                                    </td>
                                </tr>
                                <tr><td colspan="2" style="height:47px;">&nbsp;&nbsp;<input type="checkbox"> <strong>Otros</strong><br>
                                    </td>
                                </tr><tr><td colspan="2" style="height:45px;"> &nbsp;&nbsp; Firmas de miembros del equipo:<br>

                                    </td>
                                </tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;1.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;2.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;3.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;4.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;5.&nbsp;</td></tr>
                                <tr><td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;6.&nbsp;</td></tr>
                                <tr>
                                    <td style="height:45px;">&nbsp;&nbsp;&nbsp;Llegada:&nbsp;</td>
                                    <td style="height:45px;">&nbsp;&nbsp;&nbsp;Número de astilladora:&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="height:45px;">&nbsp;&nbsp;&nbsp;Salida:&nbsp;</td>
                                    <td style="height:45px;">&nbsp;&nbsp;&nbsp;Horas de inicio:&nbsp;</td>
                                </tr>
                                <tr >
                                    <td colspan="2" style="height:45px;">&nbsp;&nbsp;&nbsp;Número de teléfono en caso de emergencia:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
				</table>
			</td>

		</tr>
	</table>
</body>
</html>
