<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo !empty($title) ? $title : ''; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <?php setlocale(LC_ALL, 'en_US.utf8', 'en_US'); ?>

    <?php

        $default_img = 'assets/'.$this->config->item('company_dir').'/print/header.png';
        $brand_id = get_brand_id($estimate_data, $client_data);
        $estimate_logo = get_brand_logo($brand_id, 'estimate_logo_file', $default_img);
        $estimate_watermark = get_brand_logo($brand_id, 'watermark_logo_file', false);
        $estimate_left_side = get_brand_logo($brand_id, 'estimate_left_side_file', 'assets/' . config_item('company_dir') . '/print/container_table_left_margin.png');
        $estimate_terms = get_estimate_terms($brand_id);
        $pdf_footer = get_pdf_footer($brand_id);

    ?>

    <style type="text/css">
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .acceptance_1_fineprint, .acceptance_1_title{
            font-size: 0.8em;
        }
    </style>
</head>
<body style="font-family: sans-serif;">

<div style="width: 100%;">

<div style="width: 45%; text-align: left; float: left;">
  <span style="font-family: serif; font-size: 3em; font-style: italic;">Estimate</span>
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

        <!-- NB deprecating client email display -->
        <?php // if ($client_contact['cc_email']) : ?>
          <?php // echo $client_contact['cc_email']; ?><!-- br / -->
        <?php // endif; ?>

        <?php if ($client_data->client_address != $estimate_data->lead_address) : ?>
          Job site location: <?php echo $estimate_data->lead_address . " " . $estimate_data->lead_city; ?><br />

        <?php endif; ?>
          <?php if(isset($estimate_data->lead_add_info) && $estimate_data->lead_add_info != '') : ?>
             Add. Info: <?php echo $estimate_data->lead_add_info; ?><br />
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
    <img src="<?php echo base_url($estimate_logo); ?>"
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

<!-- ESTIMATE BODY -->
<div style="border-radius: 4px; background-color: #d9d9d9; padding: 24px;">
  <span style="font-family: serif; font-size: 1.6em; font-weight: bold; ">Estimate No. <?php echo $estimate_data->estimate_no; ?> </span>
  issued on <?php echo getDateTimeWithDate($estimate_data->estimate_review_date ? $estimate_data->estimate_review_date : date('Y-m-d', $estimate_data->date_created), 'Y-m-d');?> <small style="color: grey;">(Rev <?php echo $estimate_data->estimate_review_number; ?>)</small>
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


    <!-- NB extras -->
    <tr>
      <td colspan="4" style="color: #0d83a3; font-size: 0.8em; font-weight: bold; padding-top: 4px;">Notes:</td>
    </tr>

    <?php $project = ['service_disposal_brush' => false, 'service_disposal_wood' => false, 'service_cleanup' => false, 'service_permit' => false, 'service_exemption' => false, 'service_client_home' => false]; ?>
    <?php $crewsData = $crews = []; ?>
    <?php if($estimate_data->mdl_services_orm): ?>
        <?php foreach ($estimate_data->mdl_services_orm as $key => $service_data) : ?>
            <?php  if($service_data->service->is_bundle && isset($service_data->bundle_records) && is_array($service_data->bundle_records)){
                $i = 0;
                foreach ($service_data->bundle_records as $record) {
                    foreach ($record->crew as $jkey => $crew)
                        $crewsData[$key][] = $crew->crew_full_name;

                    if(isset($crewsData[$key]))
                    foreach ($record->equipments as $jkey => $equipment) : ?>
                        <?php //$equipments[] = $equipment->eq_name; ?>
                        <!-- setup -->
                        <?php if ($equipment->vehicle_name) : ?>
                            <?php $equipments[$key][$i]['name'] = $equipment->vehicle_name; ?>
                            <?php $equipments[$key][$i]['equipment_item_id'] = $equipment->equipment_item_id; ?>
                        <?php endif; ?>


                        <?php if ($equipment->trailer_name) : ?>
                            <?php if (!isset($equipment->vehicle_name) || !$equipment->vehicle_name) : ?>
                                <?php $equipments[$key][$i]['name'] = $equipment->trailer_name; ?>
                            <?php else : ?>
                                <?php $equipments[$key][$i]['name'] .= ', ' . $equipment->trailer_name; ?>
                            <?php endif; ?>

                            <?php $equipments[$key][$i]['name'] = rtrim(trim($equipments[$key][$i]['name']), ','); ?>
                        <?php endif; ?>


                        <?php if ($equipment->equipment_attach_tool) : ?>

                            <?php $attTool = is_string($equipment->equipment_attach_tool) ? json_decode($equipment->equipment_attach_tool) : $equipment->equipment_attach_tool; ?>
                            <?php foreach ($attTool as $k => $v) : ?>
                                <?php if (!isset($equipments[$key][$jkey]['name'])) : ?>
                                    <?php $equipments[$key][$jkey]['name'] = ''; ?>
                                <?php endif ?>
                                <?php if (!$k) : $equipments[$key][$jkey]['name'] .= ', '; ?><?php endif ?>
                                <?php $opts = is_string($equipment->equipment_tools_option) ? json_decode($equipment->equipment_tools_option) : $equipment->equipment_tools_option; ?>
                                <?php $equipments[$key][$jkey]['name'] = trim($equipments[$key][$jkey]['name'], ','); //$equipments[$key][$jkey]['opts'] .= '</small>'; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- setup end -->

                    <?php $i++; endforeach; ?>
            <?php
                }
            } else {
                foreach ($service_data->crew as $jkey => $crew) : ?>
                <?php $crewsData[$key][] = $crew->crew_full_name;// . ' (' . $crew->crew_name . ')';?>

            <?php  endforeach; ?>
            <?php //$i = 0; ?>
            <?php foreach ($service_data->equipments as $jkey => $equipment) : ?>
                <?php //$equipments[] = $equipment->eq_name; ?>
                <!-- setup -->
                <?php if ($equipment->vehicle_name) : ?>
                    <?php $equipments[$key][$jkey]['name'] = $equipment->vehicle_name; ?>
                    <?php $equipments[$key][$jkey]['equipment_item_id'] = $equipment->equipment_item_id; ?>
                <?php endif; ?>


                <?php if ($equipment->trailer_name) : ?>
                    <?php if (!isset($equipment->vehicle_name) || !$equipment->vehicle_name) : ?>
                        <?php $equipments[$key][$jkey]['name'] = $equipment->trailer_name; ?>
                    <?php else : ?>
                        <?php $equipments[$key][$jkey]['name'] .= ', ' . $equipment->trailer_name; ?>
                    <?php endif; ?>

                    <?php $equipments[$key][$jkey]['name'] = rtrim(trim($equipments[$key][$jkey]['name']), ','); ?>
                <?php endif; ?>


                <?php if ($equipment->equipment_attach_tool) : ?>

                    <?php $attTool = json_decode($equipment->equipment_attach_tool); ?>
                    <?php foreach ($attTool as $k => $v) : ?>
                        <?php if (!isset($equipments[$key][$jkey]['name'])) : ?>
                            <?php $equipments[$key][$jkey]['name'] = ''; ?>
                        <?php endif ?>
                        <?php if (!$k) : $equipments[$key][$jkey]['name'] .= ', '; ?><?php endif ?>
                        <?php $opts = json_decode($equipment->equipment_tools_option); ?>
                        <?php $equipments[$key][$jkey]['name'] = trim($equipments[$key][$jkey]['name'], ','); //$equipments[$key][$jkey]['opts'] .= '</small>'; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- setup end -->
            <?php endforeach; }?>
            <?php
            if ($service_data->service_disposal_brush && !$project['service_disposal_brush'])
                $project['service_disposal_brush'] = true;
            if ($service_data->service_disposal_wood && !$project['service_disposal_wood'])
                $project['service_disposal_wood'] = true;
            if ($service_data->service_cleanup && !$project['service_cleanup'])
                $project['service_cleanup'] = true;
            if ($service_data->service_permit && !$project['service_permit'])
                $project['service_permit'] = true;
            if ($service_data->service_exemption && !$project['service_exemption'])
                $project['service_exemption'] = true;
            if ($service_data->service_client_home && !$project['service_client_home'])
                $project['service_client_home'] = true;
            ?>
        <?php endforeach; ?>
    <?php endif;?>
    <?php foreach (array_unique($crewsData, SORT_REGULAR) as $key => $serviceCrew) : ?>
        <?php foreach ($serviceCrew as $serviceCrewUnit) : ?>
            <?php $crews[] = $serviceCrewUnit; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php $rows = 5;
    $current = array();
    $bunch = ""; ?>
    <?php if (isset($crews) && count($crews)) : ?>
      <?php foreach ($crews as $crew) : ?>
        <?php $bunch = $bunch.''.$crew.', '; ?>
        <?php // echo $crew;
                $current[] = $crew;
                $rows--; ?>
        <?php if (!$rows) break; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ( strlen($bunch) != 0 ) : ?>
      <tr style="margin-bottom: 10px;">
        <td style="font-size: 0.8em; width: 40px; text-align: left;">&nbsp;</td>
        <td style="font-size: 0.8em; width: 160px; text-align: left; vertical-align: top;">Team Requirements</td>
        <td style="font-size: 0.8em; text-align: left;"><?php echo rtrim($bunch, ', '); ?></td>
        <td style="font-size: 0.8em; width: 120px; text-align: right;">&nbsp;</td>
      </tr>
    <?php endif; ?>

    <?php $rows = 6;
    $current = $setups = $opts = array();
    $bunch = ""; ?>
    <?php if (isset($equipments) && count($equipments)) : ?>
      <?php foreach ($equipments as $jequp) : ?>
          <?php foreach ($jequp as $equipment) : ?>
              <?php if (isset($equipment['name']) && $equipment['name'] != '' && array_search($equipment['name'], $setups) === FALSE) : ?>
                <?php // echo rtrim($equipment['name'], ', '); //$current[] = $equipment['equipment_item_id']; ?>
                <?php $bunch = $bunch.''.$equipment['name'].', '; ?>
                <?php $setups[] = $equipment['name']; ?>
              <?php endif; ?>
              <?php if (isset($equipment['opts']) && $equipment['opts'] != '' && array_search($equipment['opts'], $opts) === FALSE) : ?>
                <?php // echo rtrim($equipment['opts'], ', '); //$current[] = $equipment['equipment_item_id']; ?>
                <?php $bunch = $bunch.''.$equipment['opts'].', '; ?>
                <?php $opts[] = $equipment['opts']; ?>
              <?php endif; ?>
              <?php $rows--; ?>
          <?php endforeach; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if ( strlen($bunch) != 0 ) : ?>
      <tr style="margin-bottom: 10px;">
        <td style="font-size: 0.8em; width: 40px; text-align: left;">&nbsp;</td>
        <td style="font-size: 0.8em; width: 160px; text-align: left; vertical-align: top;">Equipment Requirements</td>
        <td style="font-size: 0.8em; text-align: left;"><?php echo rtrim($bunch, ', '); ?></td>
        <td style="font-size: 0.8em; width: 120px; text-align: right;">&nbsp;</td>
      </tr>
    <?php endif; ?>

    <?php $rows = 5;
    $current = array();
    $bunch = ""; ?>
    <?php if (isset($project) && count($project)) : ?>
      <?php $requirements = ['service_disposal_brush' => 'Brush Disposal', 'service_disposal_wood' => 'Wood Disposal', 'service_cleanup' => 'General Cleanup', 'service_permit' => 'Permit Required', 'service_exemption' => 'Exemption Required', 'service_client_home' => 'Client Must Be Home']; ?>
      <?php foreach ($project as $key => $val) : ?>
          <?php if ($val) : ?>
            <?php // $bunch = ( strlen($bunch) == 0 )? $requirements[$key] : $bunch.", ".$requirements[$key]; ?>
            <?php $bunch = $bunch.''.$requirements[$key].', '; ?>
            <?php $rows--; ?>
            <?php unset($requirements[$key]); ?>
          <?php endif; ?>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if($estimate_data->full_cleanup == 'yes') : ?>
      <?php // $bunch = ( strlen($bunch) == 0 )? "Clean Up" : $bunch.", Clean Up"; ?>
      <?php $bunch = $bunch.'Clean Up, '; ?>
    <?php endif; ?>

    <?php if($estimate_data->brush_disposal == 'yes') : ?>
      <?php // $bunch = ( strlen($bunch) == 0 )? "Dispose Brush" : $bunch.", Dispose Brush"; ?>
      <?php $bunch = $bunch.'Dispose Brush, '; ?>
    <?php endif; ?>

    <?php if($estimate_data->leave_wood == 'yes') : ?>
      <?php // $bunch = ( strlen($bunch) == 0 )? "Dispose Wood" : $bunch.", Dispose Wood"; ?>
      <?php $bunch = $bunch.'Dispose Wood, '; ?>
    <?php endif; ?>

    <?php if ( strlen($bunch) != 0 ) : ?>
      <tr style="margin-bottom: 10px;">
        <td style="font-size: 0.8em; width: 40px; text-align: left;">&nbsp;</td>
        <td style="font-size: 0.8em; width: 160px; text-align: left; vertical-align: top;">Project Requirements</td>
        <td style="font-size: 0.8em; text-align: left;"><?php echo rtrim($bunch, ', '); ?></td>
        <td style="font-size: 0.8em; width: 120px; text-align: right;">&nbsp;</td>
      </tr>
    <?php endif; ?>
    <?php endif; ?>

    <!-- HORIZONTAL -->
    <tr>
      <td colspan="4" style="height: 2px; font-size: 2px; background-color: grey;">&nbsp;</td>
    </tr>

    <!-- TOTALS -->

    <?php if ($estimate_data->discount_total && $estimate_data->estimate_hst_disabled != 2 && $estimate_data->discount_total > 0) : ?>
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
            DISCOUNT<?php if ($estimate_data->discount_in_percents) : ?> <?php echo $estimate_data->discount_percents_amount; ?>%<?php endif; ?>
        </td>
        <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
          - <?php echo money($estimate_data->discount_total); ?>
        </td>
      </tr>
    <?php endif; ?>

    <tr>
      <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
          SUBTOTAL
      </td>
      <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
        <?php echo money($estimate_data->total_with_tax - $estimate_data->total_tax); ?>
      </td>
    </tr>

    <?php if ($estimate_data->estimate_hst_disabled != 1 && $estimate_data->estimate_tax_name && $estimate_data->estimate_tax_value && config_item('display_tax_in_estimate'))  : ?>
      <tr>
        <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
          <?php echo $estimate_data->estimate_tax_name . ' ' . round($estimate_data->estimate_tax_value, 3) . '%'; ?>
        </td>
        <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
          <?php echo money($estimate_data->total_tax); ?>
        </td>
      </tr>
      <tr>
        <td colspan="3" style="font-weight: bold; text-align: right; font-size: 0.8em;">
            ESTIMATED TOTAL
        </td>
        <td style="font-weight: bold; text-align: right; font-size: 0.8em;">
          <?php echo money($estimate_data->total_with_tax); ?>
        </td>
      </tr>
    <?php endif; ?>


  </table>
</div>

<hr>
<div>
    <span style="font-family: serif; font-size: 2em;">THANK YOU </span>
    <span style="color: #0d83a3; font-size: 1.3em;">for considering MapleGreen Tree Services</span></div>
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

<div style="width: 75%; text-align: left; float: right; font-size: 0.8em; text-align: justify; text-justify: inter-word;">
  <span style="color: #0d83a3; ">Terms and conditions</span>

  <?php if(isset($brand_id) && $brand_id): ?>
      <?php if($estimate_terms): ?>
              <?php echo $estimate_terms; ?>
      <?php endif; ?>
  <?php endif; ?>

  <p style="font-weight: bold;">
    MapleGreen is fully insured, WSIB compliant and has ISA and certified arborists on staff.<br />
    MapleGreen uses expert practices and specialized equipment to perform tree services in a safe and timely manner.<br />
    All MapleGreen crews aim to safeguard all customer and company property while leaving a minimal, if any, impact on the surrounding environment.<br />
    As it is part of the trade of working on various sizes and conditions of trees in intricate spaces, our primary focus is on the safety of our climbers, groundsmen and the general public.
  </p>

  <p style="font-weight: bold;">
    Any additional tree work, disposal services and drive time requested, confirmed and completed will result in additional costs.<br />
    Any additional stump grinding, root chasing, regrading, and regrinding to lower depths requested, confirmed and completed will result in additional costs.
  </p>

  <p style="font-weight: bold;">
    It is recommended that customers move any vehicles, tables, chairs, clothes lines, flower pots, objects or valuables away from the work areas prior to work commencing.<br />
    MapleGreen is not responsible for any minor indents, markings or movement of lawns, gardens, other trees, irrigation, utility lines, eavestroughs or fences.<br />
    MapleGreen is not responsible for any minor sawdust, wood chips, fuel or oil remnants on site from chainsaws, dump trucks and wood chippers.
  </p>

  <p style="font-weight: bold;">
    MapleGreen may need to reschedule work due to unsafe weather conditions, emergency work arising, or equipment breakdowns.<br />
    Customers will be charged a cancellation fee of $250 if they cancel work within 24 hours of scheduled work commencement.
  </p>

  <p>
    MapleGreen requires payment immediately upon completion of work.<br />
    MapleGreen accepts payment via E-Transfers, *Credit Cards, Checks & Cash.<br />
    Interest of 2% per month will be charged on invoices not paid within 30 days.
  </p>

  <p>
    No payments are to be made on site to crew members.<br />
    All payments must be made out to MapleGreen Tree Services.<br />
    The head office is located at 58 Palmer Circle, Caledon, Ontario, Canada, L7E 0A5.<br />
    Office hours are Monday-Friday between 9am-5pm.
  </p>

  <p>
    E-transfers must be sent to info@maplegreen.ca, where funds are auto-deposited.<br />
    *Credit Card payments via this [CCLINK] or info called into the head office @ 416-951-0487.<br />
    *Credit Card payments are subject to a 2.6% fee.<br />
    Checks must be mailed to the head office.<br />
    Cash must be delivered to the head office.
  </p>

  <p>
    E-Transfers and *Credit Card payments are the preferred contactless payment methods during this time.
  </p>

  <p style="font-weight: bold; font-style: italic;">
    To book our services, please click this [SIGNATURELINK] and sign to confirm the scope of work, price, terms and conditions. OR print/sign/date/scan/take a picture of the <u>signed estimate</u> (#<?php echo $estimate_data->estimate_no; ?>) and return via E-mail / text.<br />
    MapleGreen will then schedule the work.
  </p>


</div>

<br style="clear:both;"/>
</div>

<!-- SIGN HERE  -->

<?php $signed = FALSE; ?>
<?php if(is_bucket_file('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/signature.png')) : ?>
    <?php $signed = TRUE; ?>
<?php endif; ?>

<div style="width: 100%;">
  <div style="width: 48%; text-align: left; float: left;">
    <div style="width: 30%; text-align: left; float: left;">
      Name: <br /><br />
      Date:
    </div>

    <div style="width: 65%; text-align: left; float: right;">
      ___________________________<br /><br />
      <?php if(!isset($signed) || (isset($signed) && !$signed)) : ?> ___________________________ <?php else : ?>  &nbsp;<?php echo getDateTimeWithDate($estimate_data->workorder_date_created, 'Y-m-d'); ?> <?php endif; ?>
    </div>
  </div>

  <div style="width: 48%; text-align: left; float: right;">
    <div style="width: 30%; text-align: left; float: left;">
      Signature:
    </div>

    <div style="width: 65%; text-align: left; float: right;">
      <?php if($signed) : ?> <img width="130px" src="<?php echo base_url('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/signature.png') ?>"> <?php else : ?>   ___________________________  <?php endif; ?>
    </div>
  </div>

  <br style="clear:both;"/>
</div>

<?php $this->load->view('includes/pdf_templates/tree_inventory_legend'); ?>

<?php if($pdf_footer): ?>
    <div style="position: absolute; bottom: 10px; right: 0; left: 0; text-align: center; font-size: 0.8em;"><?php echo $pdf_footer; ?></div>
<?php endif; ?>

<?php $allFiles = isset($estimate_data) && $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files, true) : []; ?>
<?php foreach ($allFiles as $key => $aFile) : ?>
    <?php if(pathinfo($aFile, PATHINFO_EXTENSION) != 'pdf') : ?>
        <?php $files[] = $aFile; ?>
    <?php endif; ?>
<?php endforeach; ?>
<?php if (isset($files) && $files && !empty($files)) : ?>

<pagebreak>

<div style="padding-left: 30px; padding-right: 30px;">
    <?php foreach ($files as $key => $file) : ?>
        <?php if(pathinfo($file, PATHINFO_EXTENSION) == 'pdf') continue; ?>
        <?php if (!is_file($file)) : ?>
            <img rotate="<?php /*echo getRotation('./' . $file);*/ ?>" style="width:100%; margin-top: 20px;" src="<?php echo base_url($file); ?>">
        <?php else : ?>
            <img rotate="<?php echo getRotation($file); ?>" style="width:100%; margin-top: 20px;" src="<?php echo base_url($file); ?>">
        <?php endif; ?>
        <?php if (isset($files[$key + 1])) : ?><br><br><?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
