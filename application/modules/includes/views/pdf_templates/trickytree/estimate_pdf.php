<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo !empty($title) ? $title : ''; ?></title>
    <meta name="description" content="">
    <meta name="author" content="Main Prospect">

    <!-- CSS -->
    <link rel="stylesheet"
          href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/css/estimate_pdf.css"
          type="text/css" media="print">


    <?php 

        $default_img = 'assets/'.$this->config->item('company_dir').'/print/header.png';
        $brand_id = get_brand_id($estimate_data, $client_data);
        $estimate_logo = get_brand_logo($brand_id, 'estimate_logo_file', $default_img); 
        $estimate_watermark = get_brand_logo($brand_id, 'watermark_logo_file', false);
        $estimate_left_side = get_brand_logo($brand_id, 'estimate_left_side_file', 'assets/' . config_item('company_dir') . '/print/container_table_left_margin.png');
        $estimate_terms = get_estimate_terms($brand_id);
        $pdf_footer = get_pdf_footer($brand_id);
        $service_ids = [];
    ?>

    <style type="text/css">
        .float-left{
            float:left;
        }
        .float-right{
            float:right;
        }
        @media print {
            body{
                font-family: Sans-Serif;
                font-size: 11pt;
                background-image: url('<?php echo base_url($estimate_left_side); ?>')!important;
                background-position: 0 -48px;
                background-repeat: no-repeat;
                margin: 15px 0 20px 7px;
            }
            .float-left{
                float:left;
            }
            .float-right{
                float:right;
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
        }
        @page {
            body {
                font-family: Sans-Serif;
                font-size: 11pt;
                background-image: url('<?php echo base_url($estimate_left_side); ?>')!important;
                background-position: 0 -48px;
                background-repeat: no-repeat;
                margin: 15px 0 20px 7px;
            }
            .terms_block {
                padding: 5px 0 0 55px;
            }
            .terms_block .title {
                margin-left: 0;
            }
            .terms_block .title_1 {
                padding-left: 0;
            }
            .terms_block .des_1 {
                padding-left: 20px;
            }
            .float-left{
                float:left;
            }
            .float-right{
                float:right;
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
        }
        </style>

</head>
<body>
<div class="holder p_top_20"><img
            src="<?php echo base_url($estimate_logo); ?>"
            style="<?php echo $this->config->item('company_header_pdf_logo_styles'); ?>"
            class="p-top-20"></div>
<div class="estimate_number">
    <div class="estimate_number_1">Quote #</div>
    <div class="estimate_number_2"><?php echo $estimate_data->estimate_no; ?></div>
    <div class="clearBoth"></div>
</div>

<div class="title">Client Information</div>
<div class="data">
    <!-- CLIENT INFO -->
    <table cellpadding="0" cellspacing="0" border="0" class="client_table">
        <tr>
            <td>Client:</td>
            <td><?php echo $client_data->client_name; ?></td>
        </tr>
        <tr>
            <td>Client Address:</td>
            <td><?php echo $client_data->client_address . ", " . $client_data->client_city . " " . $client_data->client_state . " " . $client_data->client_zip; ?>

            </td>
        </tr>
        <?php if(isset($client_data->client_main_intersection) && $client_data->client_main_intersection != '') : ?>
            <tr>
                <td>Add. Info:</td>
                <td><?php echo $client_data->client_main_intersection; ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td>Client Phone:</td>
            <td><?php echo isset($client_contact['cc_phone']) ? numberTo($client_contact['cc_phone']) : '-'; ?></td>
        </tr>
        <?php if (isset($client_contact['cc_email']) && !empty($client_contact['cc_email'])) : ?>
            <tr>
                <td>Client Email:</td>
                <td><?php echo $client_contact['cc_email']; ?></td>
            </tr>
        <?php endif; ?>
        <?php if ($client_data->client_address != $estimate_data->lead_address) : ?>
            <tr>
                <td>Job Site Location:</td>
                <td><?php echo $estimate_data->lead_address . " " . (isset($estimate_data->lead_city) && !empty($estimate_data->lead_city) ? $estimate_data->lead_city : ''); ?>
                </td>
            </tr>
        <?php endif; ?>
        <?php if(isset($estimate_data->lead_add_info) && $estimate_data->lead_add_info != '' && (isset($client_data->client_main_intersection) && $estimate_data->lead_add_info != $client_data->client_main_intersection)) : ?>
            <tr>
                <td>Add. Info:</td>
                <td><?php echo $estimate_data->lead_add_info; ?></td>
            </tr>
        <?php endif; ?>
        <?php if (isset($client_contact['cc_name']) && $client_data->client_name != $client_contact['cc_name']) : ?>
            <tr>
                <td>Job Site Contact:</td>
                <td><?php echo $client_contact['cc_name']; ?></td>
            </tr>
        <?php endif; ?>
    </table>
    <!-- CLIENT INFO -->
</div>

<?php setlocale(LC_ALL, 'en_US.utf8', 'en_US'); ?>

<div class="title">Proposed Work</div>
<div class="data">
    <div class="min_height_350" <?php if($estimate_watermark): ?>style="background-image: url('<?php echo base_url($estimate_watermark); ?>');!important;"<?php endif; ?>>
        <!-- ESTIMATE INFO -->
        <?php $allPricesSum = array_sum(array_column(array_merge($estimate_tree_inventory_services_data ?? [], $estimate_services_data), 'service_price')); ?>
        <?php $priceColumnWidth = strlen(intval($allPricesSum) . '.00') * 12; ?>
        <div class="estimate_table">
            <?php $this->load->view('includes/pdf_templates/tree_inventory'); ?>

            <!-- the display of the name above the pictures needs to be redone, this needs to be done in the controller, I could not think of any other way than to add another foreach, since this array is filled here. PIZDEC! -->
            <?php foreach ($estimate_tree_inventory_services_data as $tree_inventory_data): ?>
                <?php $service_ids[$tree_inventory_data['id']]['name'] = isset($tree_inventory_data['estimate_service_ti_title']) && !empty($tree_inventory_data['estimate_service_ti_title']) ? $tree_inventory_data['estimate_service_ti_title'] : '#'.$tree_inventory_data['ties_number'];?>
            <?php endforeach; ?>

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
                <?php if($service_data['is_bundle']) : ?>
                    <?php foreach($service_data['bundle_records'] as $jk=>$jv) : ?>
                        <?php $service_ids[$jv->id]['name'] =  $service_data['service_name'] . '(' . $jv->service_name . ')';?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php $service_ids[$service_data['id']]['name'] = isset($service_data['estimate_service_ti_title']) && !empty($service_data['estimate_service_ti_title']) ? $service_data['estimate_service_ti_title'] : $service_data['service_name'];?>
                <?php endif; ?>
                    <div class="m-b-5" style="padding-top: 10px;">
                        <div class="pull-right text-right" style="width: <?php echo $priceColumnWidth; ?>px;">
                            <?php echo nl2br(money( empty($service_data['service_price']) ? 0 : $service_data['service_price'])); ?>
                        </div>
                        <div class="pull-left text-left p-b-5" style="text-align: justify;">
                            <?php if ($service_data['is_product']) : ?>
                                <strong><?php echo $service_data['service_name']; ?></strong>
                                <?php if(!empty($service_data['non_taxable'])): ?>
                                    <span>(Non-taxable)</span>
                                <?php endif; ?>
                                <span style="width: <?php echo $priceColumnWidth; ?>px;">
                                                    <?php /*if($service_data['is_product']) : ?>
                                                        (<?php echo $service_data['quantity']?> x <?php echo str_replace(' ', '', nl2br(money(empty($service_data['cost']) ? 0 : $service_data['cost']))); ?>)
                                                    <?php endif*/ ?>
                                </span>
                            <?php else : ?>
                            <div class="pull-left p-b-5" style="text-align: justify;">
                                <strong><?= isset($service_data['estimate_service_ti_title']) && !empty($service_data['estimate_service_ti_title']) ? $service_data['estimate_service_ti_title'] : $service_data['service_name']; ?></strong>
                                <?php if(!empty($service_data['non_taxable'])): ?>
                                    <span>(Non-taxable)</span>
                                <?php endif; ?>
                                <pre><?php echo decorate_text($service_data['service_description']); ?></pre>
                            </div>
                            <?php endif; ?>
                        </div>
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
                                            <strong ><?php echo $bundle_record->service_name; ?></strong>
                                            <?php if($bundle_record->non_taxable): ?>
                                                <span>(Non-taxable)</span>
                                            <?php endif; ?>
                                            <span style="width: <?php echo $priceColumnWidth; ?>px;">
                                                <?php /*if($bundle_record->is_product) : ?>
                                                (<?php echo $bundle_record->quantity ?> x <?php echo money($bundle_record->cost); ?>  <?php echo money($bundle_record->service_price); ?>)
                                                <?php else : ?>
                                                (<?php echo money($bundle_record->service_price); ?>)
                                                <?php endif*/ ?>
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
            <?php if ($estimate_data->discount_total && $estimate_data->estimate_hst_disabled != 2 && $estimate_data->discount_total > 0) : ?>
                <div>
                    <div class="pull-right text-right" style="width: <?php echo $priceColumnWidth; ?>px;">
                        - <?php echo money($estimate_data->discount_total); ?>
                    </div>
                    <div class="pull-right text-right">
                        <strong>Discount<?php if ($estimate_data->discount_in_percents) : ?> (<?php echo $estimate_data->discount_percents_amount; ?>%)<?php endif; ?>:</strong>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php endif; ?>

            <div class="m-t-5">
                <div class="pull-right text-right" style="width: <?php echo $priceColumnWidth; ?>px;">
                    <?php echo money($estimate_data->total_with_tax - $estimate_data->total_tax); ?>
                </div>
                <div class="pull-left text-right">
                    <strong>Subtotal:</strong>
                </div>
                <div class="clear"></div>
            </div>

            <?php if ($estimate_data->estimate_hst_disabled != 1 && $estimate_data->estimate_tax_name && $estimate_data->estimate_tax_value && config_item('display_tax_in_estimate'))  : ?>

                <div class="m-t-5">
                    <div class="pull-right text-right" style="width: <?php echo $priceColumnWidth; ?>px;">
                        <?php echo money($estimate_data->total_tax); ?>
                    </div>
                    <div class="pull-right text-right">
                        <strong>
                            <?php echo $estimate_data->estimate_tax_name . ' ' . round($estimate_data->estimate_tax_value, 3) . '%'; ?>:
                        </strong>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="m-t-5">
                    <div class="pull-right text-right" style="width: <?php echo $priceColumnWidth; ?>px;">
                        <?php echo money($estimate_data->total_with_tax); ?>
                    </div>
                    <div class="pull-right text-right">
                        <strong>
                            Total:
                        </strong>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php endif; ?>

        </div>
        <!-- /ESTIMATE ITEMS-->
    </div>
</div>
<div class="column_holder">
    <?php /*<div class="estimate_requirements_block">
        <table width="100%">
            <tr>
                <td width="100%">
                    <table width="100%">
                        <tr>
                            <td width="31%" valign="top">
                                <div class="column" style="margin-left: -25px;">
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

                                                            <?php $attTool = json_decode($equipment->equipment_attach_tool); ?>
                                                            <?php foreach ($attTool as $k => $v) : ?>
                                                                <?php if (!isset($equipments[$key][$i]['name'])) : ?>
                                                                    <?php $equipments[$key][$i]['name'] = ''; ?>
                                                                <?php endif ?>
                                                                <?php if (!$k) : $equipments[$key][$i]['name'] .= ', '; ?><?php endif ?>
                                                                <?php $opts = json_decode($equipment->equipment_tools_option); ?>
                                                                <?php $equipments[$key][$i]['name'] = trim($equipments[$key][$i]['name'], ','); //$equipments[$key][$jkey]['opts'] .= '</small>'; ?>
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
                                    <table cellpadding="0" cellspacing="0" border="0">

                                        <tr>
                                            <td colspan="2" valign="center" class="column_text"><strong>Team
                                                    Requirements</strong></td>
                                        </tr>
                                        <?php $rows = 5;
                                        $current = array(); ?>
                                        <?php if (isset($crews) && count($crews)) : ?>
                                            <?php foreach ($crews as $crew) : ?>
                                                <tr>
                                                    <td valign="center">
                                                        <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                             height="13">&nbsp;
                                                    </td>
                                                    <td valign="center" class="column_text"><?php echo $crew;
                                                        $current[] = $crew;
                                                        $rows--; ?></td>
                                                </tr>
                                                <?php if (!$rows) break; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </td>
                            <td width="48%" valign="top">
                                <div class="column" style="margin-left: -25px;">
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%">

                                        <tr>
                                            <td colspan="2" valign="center" class="column_text"><strong>Equipment
                                                    Requirements</strong>
                                            </td>
                                        </tr>

                                        <?php $rows = 6;
                                        $current = $setups = $opts = array(); ?>
                                        <?php if (isset($equipments) && count($equipments)) : ?>
                                            <?php foreach ($equipments as $jequp) : ?>
                                                <?php foreach ($jequp as $equipment) : ?>
                                                    <?php if (isset($equipment['name']) && $equipment['name'] != '' && array_search($equipment['name'], $setups) === FALSE) : ?>
                                                        <tr>
                                                            <td valign="center" width="40px">
                                                                <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                                     height="13">
                                                            </td>
                                                            <td valign="center"
                                                                class="column_text"><?php echo rtrim($equipment['name'], ', '); //$current[] = $equipment['equipment_item_id']; ?></td>
                                                            <?php $setups[] = $equipment['name']; ?>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if (isset($equipment['opts']) && $equipment['opts'] != '' && array_search($equipment['opts'], $opts) === FALSE) : ?>
                                                        <tr>
                                                            <td valign="center" width="40px">
                                                                <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                                     height="13">
                                                            </td>
                                                            <td valign="center" align="left"
                                                                class="column_text"><?php echo rtrim($equipment['opts'], ', '); //$current[] = $equipment['equipment_item_id']; ?></td>
                                                            <?php $opts[] = $equipment['opts']; ?>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php $rows--; ?>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </td>
                            <td valign="top" width="24%">
                                <div class="column">
                                    <table cellpadding="0" cellspacing="0" border="0">

                                        <tr>
                                            <td colspan="2" valign="center" class="column_text"><strong>Project
                                                    Requirements</strong>
                                            </td>
                                        </tr>
                                        <?php $rows = 5;
                                        $current = array(); ?>
                                        <?php if (isset($project) && count($project)) : ?>
                                            <?php $requirements = ['service_disposal_brush' => 'Brush Disposal', 'service_disposal_wood' => 'Wood Disposal', 'service_cleanup' => 'General Cleanup', 'service_permit' => 'Permit Required', 'service_exemption' => 'Exemption Required', 'service_client_home' => 'Client Must Be Home']; ?>
                                            <?php foreach ($project as $key => $val) : ?>
                                                <?php if ($val) : ?>
                                                    <tr>
                                                        <td valign="center">
                                                            <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                                 height="13">
                                                        </td>
                                                        <td valign="center"
                                                            class="column_text"><?php echo $requirements[$key]; ?></td>
                                                        <?php $rows--; ?>
                                                        <?php unset($requirements[$key]); ?>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if($estimate_data->full_cleanup == 'yes') : ?>
                                            <tr>
                                                <td valign="center">
                                                    <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                         height="13">
                                                </td>
                                                <td valign="center"
                                                    class="column_text">Clean Up</td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if($estimate_data->brush_disposal == 'yes') : ?>
                                            <tr>
                                                <td valign="center">
                                                    <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                         height="13">
                                                </td>
                                                <td valign="center"
                                                    class="column_text">Dispose Brush</td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if($estimate_data->leave_wood == 'yes') : ?>
                                            <tr>
                                                <td valign="center">
                                                    <img src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/check_yes.png"
                                                         height="13">
                                                </td>
                                                <td valign="center"
                                                    class="column_text">Dispose Wood</td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div> */ ?>
    <table width="100%" style="margin-top: 0px;">
        <tr>
            <td width="100%" align="center">
                <div class="thank_you">
                    We thank you for the opportunity to submit the prices and specifications noted above.<br/>
                    Please<span class="green">&nbsp;contact us at <?php echo brand_phone($brand_id); ?></span>&nbsp;if
                    you would like to proceed with the
                    quotation.
                </div>
            </td>
        </tr>
    </table>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" autosize="1">
        <tr>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" class="acceptance_1_holder" style="width: 220px; margin-left: -20px;">
                    <tr>
                        <td class="arrow" valign="top"><img
                                src="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir'); ?>/print/arrow.png"
                                width="30" height="30"></td>
                        <td class="acceptance_1_text"><span class="acceptance_1_title"><strong>Acceptance of
                            proposal.</strong></span><br>
                            <span class="acceptance_1_fineprint" valign="top">The above prices, specifications and conditions are satisfactory and hereby accepted. <?php echo (brand_name($brand_id))?brand_name($brand_id):$this->config->item('company_name_short'); ?> is authorised to do the work as specified.
                                        <?php if(config_item('tax_rate') && config_item('tax_rate') != 1 && !config_item('display_tax_in_estimate')) : ?><br/><b style="font-size: 10px;">* <?php echo config_item('tax_name') ? config_item('tax_name') : 'TAX'  ?> is extra.</b><?php endif; ?></span>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table cellpadding="0" cellspacing="0" border="0" class="acceptance" autosize="1" style="margin-top: 20px; margin-right: -20px;">
                    <tr>
                        <td align="left">
                            <table cellpadding="0" cellspacing="0" border="0" class="acceptance_2">
                                <tr>
                                    <td><strong>Estimator:</strong>
                                        <?php echo $user_data->firstname??'-'; ?>&nbsp;<?php echo $user_data->lastname??'-'; ?>
                                    </td>
                                    <td width="125px"><strong><?php echo $estimate_data->estimate_no; ?></strong>,
                                        Rev <?php echo $estimate_data->estimate_review_number; ?></td>
                                    <td width="130px">&nbsp;<strong>Date:</strong>&nbsp;
                                        <?php echo getDateTimeWithDate(!empty($estimate_data->estimate_review_date) && $estimate_data->estimate_review_date != '0000-00-00' ? $estimate_data->estimate_review_date : date('Y-m-d', $estimate_data->date_created), 'Y-m-d');?>
                                    </td>
                                </tr>
                            </table>
                            <table cellpadding="0" cellspacing="0" border="0" class="acceptance_2">
                                <tr>
                                    <td><br><br><br></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td align="left" style="position: relative;">
                                        <strong>Authorised Signature:</strong>
                                        <?php $signed = FALSE; ?>
                                        <?php if(is_bucket_file('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/signature.png')) : ?>
                                            <?php $signed = TRUE; ?>
                                        <?php else : ?>   ___________________________
                                        <?php endif; ?>
                                    </td>

                                    <td width="130px">&nbsp;<strong>Date:</strong> <?php if(!$signed || !isset($estimate_data->workorder_date_created)) : ?> ____________ <?php else : ?>  &nbsp;<?php echo getDateTimeWithDate($estimate_data->workorder_date_created, 'Y-m-d'); ?> <?php endif; ?></td>
                                </tr>
                            </table>
                            <br/><br/>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<?php if($signed) : ?>
    <div style="position: absolute; right: 220px; margin-top: -60px;">
        <img width="130px" src="<?php echo base_url('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/signature.png') ?>">
    </div>
<?php endif; ?>

<?php $this->load->view('includes/pdf_templates/tree_inventory_legend'); ?>

<div class="address" style="position: absolute; bottom: 10px; right: 0; left: 0; text-align: center;">
    <?php $brand_address = brand_address($brand_id); ?>
    <?php if(isset($brand_id) && $brand_id): ?>
        <?php echo ($pdf_footer)?$pdf_footer:''; ?>
    <?php else: ?>
        <?php echo $this->config->item('footer_pdf_address'); ?>
    <?php endif; ?>
</div>


<?php if(isset($brand_id) && $brand_id): ?>
    <?php if(strip_tags($estimate_terms, ['img'])): ?>
        <pagebreak>
        <div class="terms_block">
            <?php echo $estimate_terms; ?>
        </div>
        <?php if($pdf_footer): ?>
            <div class="address" style="position: absolute; bottom: 10px; right: 0; left: 0; text-align: center;"><?php echo $pdf_footer; ?></div>
        <?php endif; ?>
    <?php endif; ?>
<?php
    else:
        list($result, $view) = Modules::find('pdf_templates/' . config_item('company_dir') . '/terms_conditions', 'includes', 'views/');
        if($result) {
            $this->load->view('includes/pdf_templates/' . config_item('company_dir') . '/' . 'terms_conditions');
        }
    endif;
?>

<?php /* if($estimate_data->mdl_services_orm): ?>
    <?php foreach ($estimate_data->mdl_services_orm as $key => $service_data) : ?>
        <?php $files = bucketScanDir('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $service_data->id . '/'); ?>
        <?php if ($files && !empty($files)) : //echo '<pre>'; var_dump($estimate_data); die;?>
            <pagebreak>
                <div class="holder"><strong><?php echo $service_data->service->service_name;?></strong></div>
                <div style="padding-left: 75px; padding-right: 30px;">

                <?php foreach ($files as $img) : ?>
                    <?php $filepath = 'uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $service_data->id . '/' . $img; ?>
                    <?php $size = bucket_get_file_info($filepath); ?>
                        <?php if($size['mimetype'] == 'application/pdf') : continue; ?>
                        <?php else : ?>
                            <?php if (!is_file($filepath)) : ?>
                                <img  style="width:100%; margin-top: 20px;" src="<?php echo base_url($filepath); ?>">
                            <?php else : ?>
                                <img rotate="<?php echo getRotation($filepath); ?>" style="width:100%; margin-top: 20px;" src="<?php echo base_url($filepath); ?>">
                            <?php endif; ?>
                            <?php if (isset($files[$key + 1])) : ?><br><br><?php endif; ?>
                        <?php endif;?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; */ ?>
            <?php $allFiles = isset($estimate_data) && $estimate_data->estimate_pdf_files ? json_decode($estimate_data->estimate_pdf_files, true) : [];  ?>
            <?php foreach ($allFiles as $key => $aFile) : ?>
                <?php if(is_array($aFile)): ?>
                    <?php if(isset($service_ids) && array_key_exists($key, $service_ids) ) : ?>
                        <?php foreach ($aFile as $aFileResult): ?>
                            <?php $files[$key][] = $aFileResult; ?>
                        <?php endforeach; else: ?>
                        <?php foreach ($aFile as $aFileResult): ?>
                            <?php $files['else'][] = $aFileResult; ?>
                        <?php endforeach; endif; ?>
                <?php elseif(!is_array($aFile) && pathinfo($aFile, PATHINFO_EXTENSION) != 'pdf') : ?>
                    <?php $name = explode('/', $aFile); ?>
                    <?php if(isset($service_ids) && isset($name[5]) && intval($name[5]) && array_key_exists($name[5], $service_ids) ) : ?>
                        <?php $files[$name[5]][] = $aFile; ?>
                    <?php elseif(isset($service_ids) && array_key_exists($key, $service_ids) || $key == 'scheme') : ?>
                        <?php $files[$key][] = $aFile; ?>
                    <?php else :?>
                        <?php $files['else'][] = $aFile; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (isset($files) && $files && !empty($files)) :  ?>


            <?php foreach ($files as $key => $pathes) : ?>



            <?php foreach($pathes as $jk=>$file) : ?>
            <pagebreak>
                <div style="padding-left: 75px; padding-right: 30px; position:absolute">
                    <?php if(isset($service_ids[$key]) && $jk == 0) : ?>
                        <div class=""><strong><?php echo $service_ids[$key]['name'] ;?></strong></div>
                    <?php elseif(strripos($file,'_scheme') !== false) : ?>
                        <div class=""><strong>Project Scheme</strong></div>
                    <?php elseif(strripos($file,'_tree_inventory') !== false) : ?>
                        <div class=""><strong>Tree Inventory Map</strong></div>
                    <?php elseif($key == 'scheme') : ?>
                        <div class=""><strong>Project Scheme</strong></div>
                    <?php endif; ?>
                    <?php if(pathinfo($file, PATHINFO_EXTENSION) == 'pdf') continue; ?>
                    <?php if (!is_file($file)) : ?>
                        <img rotate="<?php /*echo getRotation('./' . $file);*/ ?>" style=" " src="<?php echo base_url($file); ?>">
                    <?php else : ?>
                        <img rotate="<?php echo getRotation($file); ?>" style=" " src="<?php echo base_url($file); ?>">
                    <?php endif; ?>
                    <?php if (isset($pathes[$jk + 1])) : ?><br><br><?php endif; ?>
                </div>
                <?php endforeach; ?>

                <?php endforeach; ?>

                <?php endif; ?>


</body>
</html>
