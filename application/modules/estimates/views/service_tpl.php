<?php $rand = rand(1, 10000);
$is_tree_inventory_service = !empty($service_data) && !empty($service_data->tree_inventory);

?>
<div class="hidden">
    <?=json_encode($service_data);?>
</div>
<section class="col-md-12 panel panel-default p-left-0 p-right-0 p-bottom-0 serviceGroup m-n"
         data-markup="<?php if (isset($service_data) && isset($service_data->service->service_markup)) echo $service_data->service->service_markup; ?>"
         data-service_id="<?php if (isset($service_data) && isset($service_data->id)) echo $service_data->id; else echo '0'; ?>"
        <?php if (isset($service_data) && isset($service_data->bundle_id)):?> data-bundle_id="<?= $service_data->bundle_id; ?>" <?php endif; ?>>
    <header class="panel-heading p-bottom-10"
            style="<?php if (!empty($service_data->bundle_id)) : ?> background-color: white; border: none <?php endif ?>">
        <div class="m-t-n-xs m-b-n-xs" style="display: flex;align-items: center;">
            <div class="col-md-3 col-lg-3 col-xs-3 p-left-0 p-right-0 col-sm-3">
                <i class="fa fa-wrench text-warning fa-lg m-r-sm"></i>
                <span class="serviceTitle" id="taxable">
                <?php
                    if(isset($service_data) && isset($service_data->estimate_service_ti_title) && !empty($service_data->estimate_service_ti_title))
                        echo $service_data->estimate_service_ti_title;
                    else if (isset($service_data) && isset($service_data->service->service_name))
                        echo $service_data->service->service_name;
                    else echo 'UNDEFINED'; ?>
                </span>
                <span class="non-taxable-title"
                      <?php if (isset($service_data)) : ?>id="service-checkbox-<?php echo $service_data->id; ?>-title" <?php endif;
                if (isset($service_data) && $service_data->non_taxable == 0 || !isset($service_data)) : ?> hidden <?php endif; ?> >(Non-taxable)</span>

                <a class="toggleChevron" style="cursor:pointer;text-decoration:none;" data-toggle="collapse"
                   href="#collapse<?= isset($service_data->id) ? $service_data->id : $rand ?>" aria-expanded="false">
                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                </a>

                    <a  class="m-l-sm btn btn-xs editService <?php if (!$is_tree_inventory_service) : ?>hidden<?php endif;?>" style="margin-left: 9px"
                        data-service_id="<?php if (isset($service_data)) echo $service_data->id; ?>">
                        <i class="fa fa-pencil"></i>
                    </a>

                <a class="m-l-sm btn btn-danger deleteService hidden-md hidden-lg m-left-0 btn-block m-top-10"
                   data-service_id="<?php if (isset($service_data)) echo $service_data->id; ?>">
                    Delete
                    <i class="fa fa-trash-o"></i>
                </a>

                <?php if (isset($service_data->id)) : ?>
                    <input type="hidden" name="non_taxable[<?php echo $service_data->id; ?>]"
                           id="service-checkbox-<?php echo $service_data->id; ?>"
                           value="<?php echo $service_data->non_taxable; ?>" class="nonTaxableHidden">
                    <?php if (empty($service_data->new)) : ?>
                        <input type="hidden" name="id[<?php echo $service_data->id; ?>]"
                               value="<?php echo $service_data->id; ?>">
                    <?php endif; ?>
                    <input type="hidden" class="form-control" name="service_type_id[<?php echo $service_data->id; ?>]"
                           value="<?php echo $service_data->service_id; ?>"
                           data-service_id="<?php echo $service_data->id; ?>">
                    <input type="hidden" name="is_product[<?php echo $service_data->id; ?>]" value="0">
                    <input type="hidden" name="is_collapsed[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service->service_is_collapsed ?>">
                    <input type="hidden" name="tree_inventory_title[<?= $service_data->id; ?>]" value="<?= isset($service_data->estimate_service_ti_title) ? $service_data->estimate_service_ti_title : ''?>">
                    <input type="hidden" name="service_priority[<?php echo $service_data->id; ?>]" value="<?= $service_data->service_priority ?? 0 ?>">
                <?php else : ?>
                    <input type="hidden" data-name="is_product" data-is_product="0" value="0">
                    <input type="hidden" data-name="is_product" data-is_product="0" value="0">
                    <input type="hidden" data-name="non_taxable" value="0" class="nonTaxableHidden">
                    <input type="hidden" class="form-control" data-name="service_type_id" data-service_type_id="1"
                           name="">
                    <input type="hidden" data-name="is_collapsed" value="1">
                    <input type="hidden" data-name="tree_inventory_title" value="">
                    <input type="hidden" data-name="service_priority" value="1">
                <?php endif; ?>
                <?php if (!empty($service_data->bundle_id)) : ?>
                    <input type="hidden" name="bundles_services[<?php echo $service_data->id; ?>]"
                           value="<?php echo $service_data->bundle_id; ?>"
                           data-service_id="<?php echo $service_data->id; ?>">
                <?php else : ?>
                    <input type="hidden" data-name="bundles_services" value="0">
                <?php endif; ?>
                <?php if (!empty($service_data->service->service_name)) : ?>
                    <input type="hidden" name="bundles_services_name[<?php echo $service_data->id; ?>]"
                           value="<?php echo $service_data->service->service_name; ?>"
                           data-service_id="<?php echo $service_data->id; ?>">

                <?php endif; ?>

                <?php if ($is_tree_inventory_service) : ?>
                    <input type="hidden" name="ties_work_types[<?= $service_data->id; ?>]" value="[<?=($service_data->tree_inventory->work_types);?>]">

                    <input type="hidden" name="ties_type[<?= $service_data->id; ?>]" value="<?php if ($service_data->tree_inventory->tree) : ?><?=$service_data->tree_inventory->tree->trees_id;?><?php endif;?>">
                    <input type="hidden" name="ties_number[<?= $service_data->id; ?>]" value="<?=$service_data->tree_inventory->ties_number;?>">
                    <input type="hidden" name="ties_size[<?= $service_data->id; ?>]" value="<?=$service_data->tree_inventory->ties_size;?>">
                    <input type="hidden" name="ties_priority[<?= $service_data->id; ?>]" value="<?=$service_data->tree_inventory->ties_priority;?>">

                <?php endif;?>

            </div>

            <div class="col-md-9 col-xs-9 col-lg-9 col-sm-9">
                <div class="row row-media">
                    <div class="form-group m-m m-b-none priceSum col-md-4 p-left-0 p-right-0 col-sm-4 col-lg-4">
                        <div <?php if (empty($access_token) || empty($classes)) : ?> hidden <?php endif; ?> class="QBclass pull-right">
                            <label class="inline"><strong>QB Class</strong></label>
                            <div class="inline">
                                <input type="text" class="classSelect w-200 class-media"
                                       value="<?= isset($service_data) && isset($service_data->estimate_service_class_id) ? $service_data->estimate_service_class_id : '' ?>"
                                       data-name="class"
                                       name="class[<?php echo isset($service_data) ? $service_data->id : ''; ?>]"
                                       data-service_id="<?= isset($service_data) ? $service_data->id : ''; ?>"/>
                            </div>
                        </div>
                    </div>


                    <div class="form-group m-m m-b-none tree-inventory-cost col-md-3 p-left-0 p-right-0 text-right col-sm-3 col-lg-2"  <?php if(!$is_tree_inventory_service) : ?> hidden="hidden" <?php endif; ?>>
                        <div class="inline m-right-10"><strong>Cost</strong></div>
                        <div class="inline" style="">
                            <input style="width: 100px;" placeholder="Cost"
                                   class="treeInventoryCost form-control input-mini text-center currency"
                                   type="text"
                                <?php if ($is_tree_inventory_service) : ?>
                                    name="ties_cost[<?= $service_data->id ?>]" value="<?=  $service_data->tree_inventory->ties_cost; ?>"  data-service_id="<?= $service_data->id; ?>"
                                <?php endif; ?>>
                        </div>
                    </div>

                    <div class="form-group m-m m-b-none tree-inventory-stump col-md-5 p-right-0 text-right col-sm-5 col-lg-3 cost-media" <?php if(!$is_tree_inventory_service) : ?> hidden="hidden" <?php endif; ?>>
                        <div class="inline m-right-10"><strong>Stump</strong></div>
                        <div class="inline" style="">
                            <input style="width: 100px;" placeholder="Cost"
                                   class="treeInventoryStump form-control input-mini text-center currency"
                                   type="text"
                                <?php if ($is_tree_inventory_service) : ?>
                                    name="ties_stump[<?= $service_data->id ?>]" value="<?=  $service_data->tree_inventory->ties_stump_cost; ?>"  data-service_id="<?= $service_data->id; ?>"
                                <?php endif; ?>>
                        </div>
                    </div>

                    <div class="form-group m-m pull-right m-b-none priceSum col-md-12 p-right-0 text-right col-sm-12 col-lg-3">

                        <div class="form-group m-m m-b-none pull-right">
                            <div class="inline text-ul m-right-10 taxable " data-trigger="focus" tabindex="0"
                                 style="cursor: pointer"><strong>Price</strong></div>
                            <div class="inline" style="">
                                <input style="width: 120px;" placeholder="Price"
                                       class="form-control input-mini servicePrice text-center currency" type="text" <?php if ($is_tree_inventory_service) : ?> disabled <?php endif; ?>
                                    <?php if (!isset($service_data)) : ?> data-name="service_price" name=""
                                    <?php else : ?> name="service_price[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_price; ?>"
                                        data-service_id="<?php echo $service_data->id; ?>"
                                    <?php endif; ?>/>
                            </div>
                            <a class="m-l-sm btn btn-danger btn-xs deleteService hidden-xs hidden-sm " style="margin-left: 9px"
                               data-service_id="<?php if (isset($service_data)) echo $service_data->id; ?>">
                                <i class="fa fa-trash-o"></i>
                            </a>
                            <div class="form-check pull-right m-left-10 form-check-inline content hide">
                                <label class="form-check-label check-tax" for="nonTaxable"><strong>Non-taxable</strong></label>
                                <input type="checkbox" class="form-check-input big-checkbox check-tax nonTaxable"
                                       data-id="#service-checkbox-<?php if (isset($service_data->id)) echo $service_data->id; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </header>
    <div class="collapse" id="collapse<?= isset($service_data->id) ? $service_data->id : $rand ?>">
        <div>
            <div class="col-sm-12 b-r form-group m-m m-b-none serviceTop" style="border: 0;">
                <div class="row pos-rlt">
                    <div class="col-md-10 col-xs-12 form-group m-m pull-left m-b-none p-n serviceDescriptionBlock">
                        <textarea class="form-control serviceDescription m-t-n-xxs  " placeholder="Service Description"
                                  <?php if (!isset($service_data)) : ?>data-name="service_description"
                                  name=""<?php else : ?> name="service_description[<?php echo $service_data->id; ?>]" data-service_id="<?php echo $service_data->id; ?>"<?php endif; ?>
                        ><?php if (isset($service_data)) echo $service_data->service_description; ?></textarea>
                    </div>
                    <div class="col-md-2 col-xs-12 form-group m-m pull-right m-b-none p-n"
                         style="position: initial; margin-bottom: 34px;">
                        <div class="dropzone"></div>
                        <span href="#/" class="btn btn-success pos-abt col-md-2 col-xs-12 use-calc" style="bottom: 0" <?php if ($is_tree_inventory_service) : ?> disabled <?php endif; ?>
                              onclick="showHideCalc($(this))">Use Calculator</span>

                    </div>
                </div>
                <div class="row calculateBlock block" style="display: none">
                    <header class="panel-heading p-0"
                            style="border-color: #e8e8e8; color: #333; background-color: #f5f5f5;">
                        <span>Calculator</span></header>
                    <div class="calcFields">
                        <div class=" col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center">
                            <div class="  m-m">
                                <strong>On Site</strong>
                                <input placeholder="Estimated Time"
                                       class="form-control hours input-mini serviceTime text-center bg-success"
                                    <?php if (!isset($service_data)) : ?> data-name="service_time" name="" value="0"
                                    <?php else : ?> name="service_time[<?php echo $service_data->id; ?>]" value="<?php echo isset($service_data->service_time) ? $service_data->service_time : 0; ?>"
                                        data-service_id="<?php echo $service_data->id; ?>"
                                    <?php endif; ?>>
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center">
                            <div class="  m-m">
                                <strong>Travel</strong>
                                <input placeholder="Travel Time"
                                       class="form-control hours input-mini serviceTravelTime text-center" type="text"
                                    <?php if (!isset($service_data)) : ?> data-name="service_travel_time" name="" value="0"
                                    <?php else : ?> name="service_travel_time[<?php echo $service_data->id; ?>]" value="<?php echo isset($service_data->service_travel_time) ? $service_data->service_travel_time : 0; ?>"
                                        data-service_id="<?php echo $service_data->id; ?>"
                                    <?php endif; ?>>
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center">
                            <div class="  m-m">
                                <strong>Extra</strong>
                                <input placeholder="Disposal Time"
                                       class="form-control hours input-mini serviceDisposalTime text-center" type="text"
                                    <?php if (!isset($service_data)) : ?> data-name="service_disposal_time" name="" value="0"
                                    <?php else : ?> name="service_disposal_time[<?php echo $service_data->id; ?>]" value="<?php echo isset($service_data->service_disposal_time) ? $service_data->service_disposal_time : 0; ?>"
                                        data-service_id="<?php echo $service_data->id; ?>"
                                    <?php endif; ?>>
                            </div>
                        </div>

                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center text-muted">
                            <strong>MH</strong>
                            <input type="text" class="form-control hours manHours text-center bg-light" value="0"
                                   readonly>
                        </div>

                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center text-muted">
                            <strong>MHR</strong>
                            <input type="text" class="form-control currency manHoursRate text-center bg-light"
                                   value="75" readonly>
                        </div>

                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center text-muted">
                            <strong>Overhead</strong>
                            <input type="text" class="form-control overheadRate text-center currency "
                                <?php if (!isset($service_data)) : ?> data-name="service_overhead_rate" name="" value="<?php echo config_item('service_overhead_rate'); ?>"
                                <?php else : ?> name="service_overhead_rate[<?php echo $service_data->id; ?>]" value="<?php echo ($service_data->service_overhead_rate) ? $service_data->service_overhead_rate : config_item('service_overhead_rate'); ?>"
                                    data-service_id="<?php echo $service_data->id; ?>"
                                <?php endif; ?>>
                        </div>

                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center text-muted">
                            <strong>Cost</strong>
                            <input type="text" class="form-control companyCost text-center bg-light currency" readonly>
                        </div>

                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center text-muted">
                            <strong>Markup</strong>
                            <input type="text" class="form-control decimal serviceMarkup text-center"
                                <?php if (!isset($service_data)) : ?> data-name="service_markup_rate" name="" value="20"
                                <?php else : ?> name="service_markup_rate[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_markup_rate ? $service_data->service_markup_rate : 0; ?>"
                                    data-service_id="<?php echo $service_data->id; ?>"
                                <?php endif; ?>>
                        </div>


                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-left m-b-none text-center text-muted">
                            <strong>Expenses</strong>
                            <a href="#" class="addExpenseRow"
                               style="display: inline-block; text-decoration: none; background: #8ec165; border-radius: 50px; color: #fff; font-size: 10px; line-height: 13px; border: 1px solid #676767; width: 15px; height: 15px;">+</a>
                            <input class="form-control extraExpenses text-center bg-light currency"
                                   placeholder="Extra Total" value="0" readonly>

                            <div class="expenseRow hide">
                                <?php $this->load->view('partials/estimate_service_expense_row', ['expense' => FALSE, 'key' => FALSE]); ?>
                            </div>

                        </div>


                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-1 form-group m-m pull-left m-b-none text-center"
                             style="min-height: 60px;">
                            <div class="form-group m-m useTotal" style="display: none;">
                                <div><strong>Use</strong></div>
                                <a class="btn btn-xs btn-success m-t-xs"><i class="fa fa-magic"></i></a>
                            </div>
                        </div>

                        <div class="col-xs-4 col-sm-4 col-md-2 col-lg-1 form-group m-m pull-right p-right-0 m-b-none text-center text-muted"
                             style="margin-right: 0;margin-left: 0px;">
                            <div><strong>Total</strong></div>
                            <input type="text"
                                   class="form-control pull-right serviceTotal text-center currency bg-light"
                                   value="<?php echo isset($service_data) && isset($service_data->service_price) ? $service_data->service_price : 0; ?>"
                                   readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both;"></div>
            <div class="serviceExtraExpenses col-sm-12" style="display: none">
                <?php if (isset($service_data) && isset($service_data->expenses) && $service_data->expenses && is_array($service_data->expenses)) : ?>
                    <?php foreach ($service_data->expenses as $key => $expense) : ?>
                        <?php $this->load->view('partials/estimate_service_expense_row', ['expense' => $expense, 'key' => $key]); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="row m-n crewEquipmentBlock" style="display: none">
            <div class="col-md-2 p-n b-r">
                <section class="panel panel-default w-100 m-b-none"
                         style="border-bottom: none; border-left: none; box-shadow: none;">
                    <header class="panel-heading">
                        Select Crew
                        <h4 class="pull-right text-success m-n font-bold"><span class="teamCost"></span></h4>
                    </header>
                    <div class="panel-body">
                        <div data-toggle="buttons" style="">
                            <?php foreach ($crews as $key => $crew) : ?>
                                <label class="btn btn-sm btn-dark empType m-b-xs inline" data-name="empType"
                                       data-crew_rate="<?php echo $crew->crew_rate; ?>"
                                       data-crew_id="<?php echo $crew->crew_id; ?>"<?php if (isset($service_data->id)) : ?> data-service_id="<?php echo $service_data->id; ?>"<?php endif; ?>
                                       style="padding: 3px 4px; font-size: 11px;">
                                    <i class="fa fa-check text-active"></i> <?php echo $crew->crew_name; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <?php if (isset($estimate_data->estimate_item_team) && $estimate_data->estimate_item_team) {
                            echo "  Originally proposed crew: " . $estimate_data->estimate_item_team;
                        } ?>
                    </div>
                    <div class="panel-body selectedCrew" data-toggle="buttons" style="padding-top: 0;">
                        <?php if (isset($service_data->crew)) : ?>
                            <?php foreach ($service_data->crew as $crew) : ?>

                                <label class="btn btn-success inline m-r-xs"
                                       data-emp_type_id="<?php echo $crew->crew_id; ?>"
                                       data-rate="<?php echo $crew->crew_rate; ?>" style="padding: 3px 5px;">
                                    <?php echo $crew->crew_name; ?>
                                    <a href="#" class="moveFromCrew">x</a>
                                    <input type="hidden" name="service_crew[<?php echo $service_data->id; ?>][]"
                                           value="<?php echo $crew->crew_id; ?>"
                                           data-service_id="<?php echo $service_data->id; ?>">
                                    <input type="hidden" name="estimate_service_crew_id[<?php echo $service_data->id; ?>][]"
                                           value="<?php echo $crew->estimate_service_crew_id; ?>">
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="p-left-15 p-right-15"><span class="label bg-warning block" id="boomErrorMsg"
                                                            style="white-space: inherit;text-align:left;"></span></div>
                    <div class="p-left-15 p-right-15"><span class="label bg-warning block" id="stumpErrorMsg"
                                                            style="white-space: inherit;text-align:left;"></span></div>
                </section>
            </div>
            <div class="col-md-10 p-n" style="border-left: 1px solid #cfcfcf; margin-left: -1px;">
                <section class="panel panel-default m-b-none "
                         style="margin-bottom: 0; border-right: none; border-bottom: none; box-shadow: none; border-left: none;">
                    <header class="panel-heading pos-rlt">Select Equipment
                        <span class="btn btn-info btn-xs pull-right block hide addCopyEquipment m-r-lg">Copy Last</span>
                        <h4 class="pull-right text-success m-l-none m-b-none m-t-none m-r-lg font-bold"><span
                                    class="equipCost"></span></h4>
                        <div class="clear"></div>
                    </header>
                    <div class="panel-body p-bottom-0">

                        <?php /*<div data-toggle="buttons" class="input-group-addon">
						<div class="clear m-t-sm"></div>
						<label class="btn btn-sm btn-dark checkbox hide"></label>
					 foreach($equipment as $item) : ?>
						<?php $class = ''; $checked = ''; ?>
						<?php if(isset($service_data->equipments)) :  ?>
							<?php foreach($service_data->equipments as $eq) : ?>
								<?php if($eq->eq_id == $item->eq_id) {$class = ' active'; $checked = ' checked';}?>
							<?php endforeach; ?>
						<?php endif; ?>
						<label class="btn btn-sm btn-info checkbox m-l-xs m-b-sm pull-left eq<?php echo $class; ?>">
							<i class="fa fa-check text-active"></i> <?php echo $item->eq_name; ?>
							<input type="checkbox"
								<?php if(!isset($service_data)) : ?> name="" data-name="service_equipment" data-array="1"
								<?php else : ?> name="service_equipment[<?php echo $service_data->id; ?>][]"
								<?php endif; ?>
							    value="<?php echo $item->eq_id; ?>"<?php echo $checked; ?>>
						</label>
					<?php endforeach; */ ?>


                        <!-----------//SETUPS------------>
                        <div class="">
                            <?php if (isset($service_data) && ($service_data->equipments || ($service_data->service->service_attachments  && empty($service_data->estimate_id)))): ?>
                                <?php if ($service_data->equipments) : ?>
                                    <div class="serviceSetupList">
                                        <?php foreach ($service_data->equipments as $key => $v) : ?>
                                            <div class="row pos-rlt p-left-5">
                                                <div class="serviceSetupTpl">
                                                    <div class="control-group col-md-2 col-xs-10 p-left-0">
                                                        <?php /*<label class="control-label">Service Equipment</label>*/ ?>

                                                        <div class="controls text-left p-left-10">
                                                            <?php $selOpt = '' ?>
                                                            <select data-name="service_vehicle[<?php echo $service_data->id; ?>][]"
                                                                    data-key="<?php echo $key; ?>"
                                                                    data-service_id="<?php echo $service_data->id; ?>"
                                                                    name="service_vehicle[<?php echo $service_data->id; ?>][]"
                                                                    class="service_vehicle form-control">
                                                                <option value=""> -</option>
                                                                <?php foreach ($vehicles as $veh) : ?>

                                                                    <option value="<?php echo $veh->vehicle_id; ?>"
                                                                            data-rate="<?php echo $veh->vehicle_per_hour_price; ?>"
                                                                            data-option='<?php echo htmlentities($veh->vehicle_options, ENT_QUOTES, 'UTF-8'); ?>'
                                                                            <?php if (intval($v->equipment_item_id) == $veh->vehicle_id) : ?>selected="selected"<?php $selOpt = $veh->vehicle_options; ?><?php endif; ?>>
                                                                        <?php echo $veh->vehicle_name; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div data-toggle="buttons"
                                                                 class="text-center text-center btn-group m-t-xs">
                                                                <?php if ($selOpt != '') : ?>
                                                                    <?php foreach (json_decode($selOpt) as $k => $op) : ?>
                                                                        <?php $btnClass = $k % 2 ? 'btn-primary' : 'btn-warning'; ?>
                                                                        <label class="btn btn-xs <?php echo $btnClass;
                                                                        if ($v->equipment_item_option && !empty(json_decode($v->equipment_item_option)) && array_search($op, (array)json_decode($v->equipment_item_option)) !== FALSE) : ?> active<?php endif; ?>">
                                                                            <i class="fa fa-check text-active"></i> <?php echo $op; ?>
                                                                            <input class="vehicle_option"
                                                                                   type="checkbox"
                                                                                   value="<?php echo htmlentities($op, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($v->equipment_item_option && !empty(json_decode($v->equipment_item_option)) && array_search($op, (array)json_decode($v->equipment_item_option)) !== FALSE) : ?> checked<?php endif; ?>
                                                                                <?php if (!isset($service_data)) : ?> data-name="vehicle_option[]" name=""
                                                                                <?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="vehicle_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]" data-name="vehicle_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]"
                                                                                <?php endif; ?>>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="control-group  col-md-2 col-xs-10 p-left-0">
                                                        <?php /*<label class="control-label">Service Attachment</label>*/ ?>

                                                        <div class="controls text-left p-left-10">
                                                            <?php $traOpt = '' ?>
                                                            <select data-name="service_trailer[<?php echo $service_data->id; ?>][]"
                                                                    data-key="<?php echo $key; ?>"
                                                                    data-service_id="<?php echo $service_data->id; ?>"
                                                                    name="service_trailer[<?php echo $service_data->id; ?>][]"
                                                                    class="service_trailer form-control">

                                                                <option value=""> -</option>
                                                                <?php foreach ($trailers as $trai) : ?>
                                                                    <option value="<?php echo $trai->vehicle_id ?>"
                                                                            data-rate="<?php echo $trai->vehicle_per_hour_price; ?>"
                                                                            data-traioptions='<?php echo htmlentities($trai->vehicle_options, ENT_QUOTES, 'UTF-8'); //$trai->vehicle_options;?>'
                                                                            <?php if ($v->equipment_attach_id == $trai->vehicle_id) : ?>selected="selected"<?php $traOpt = $trai->vehicle_options; ?><?php endif; ?>>
                                                                        <?php echo $trai->vehicle_name; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div data-toggle="buttons"
                                                                 class="text-center text-center btn-group m-t-xs">
                                                                <?php if ($traOpt != '') : ?>
                                                                    <?php foreach (json_decode($traOpt) as $k => $op) : ?>
                                                                        <?php $btnClass = $k % 2 ? 'btn-primary' : 'btn-warning'; ?>
                                                                        <label class="btn btn-xs <?php echo $btnClass;
                                                                        if ($v->equipment_attach_option && !empty(json_decode($v->equipment_attach_option)) && array_search($op, (array)json_decode($v->equipment_attach_option)) !== FALSE): ?> active<?php endif; ?>">
                                                                            <i class="fa fa-check text-active"></i> <?php echo $op; ?>
                                                                            <input class="trailer_option"
                                                                                   type="checkbox"
                                                                                   value="<?php echo htmlentities($op, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($v->equipment_attach_option && !empty(json_decode($v->equipment_attach_option)) && array_search($op, (array)json_decode($v->equipment_attach_option)) !== FALSE): ?> checked<?php endif; ?>
                                                                                <?php if (!isset($service_data)) : ?> data-name="trailer_option[]" name=""
                                                                                <?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="trailer_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]" data-name="trailer_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]"
                                                                                <?php endif; ?>>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="control-group col-md-7 col-xs-12 p-left-0 ">
                                                        <?php /*<label class="control-label">Service Tools</label>*/ ?>

                                                        <div class="controls tools text-left">

                                                            <?php $attachTool = json_decode($v->equipment_attach_tool); ?>
                                                            <?php $opts = json_decode($v->equipment_tools_option); ?>
                                                            <?php foreach ($tools as $k => $tool) : ?>
                                                                <div class="row m-b-xs p-left-10">

                                                                    <div class="col-md-3 col-lg-3"
                                                                         style="padding-right: 0;">
                                                                        <div class="text-left">
                                                                            <strong><?php echo $tool->vehicle_name; ?></strong>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-lg-9 pull-left p-right-0 p-top-0 p-bottom-0   ">
                                                                        <div data-toggle="buttons"
                                                                             class="text-center text-center btn-group">
                                                                            <?php if ($tool->vehicle_options && !empty(json_decode($tool->vehicle_options))) : ?>
                                                                                <?php foreach (json_decode($tool->vehicle_options) as $j => $opt) : ?>
                                                                                    <?php $active = '';
                                                                                    $check = ''; ?>
                                                                                    <?php if (isset($attachTool) && !empty($attachTool) && array_search($tool->vehicle_id, $attachTool) !== FALSE) : ?>
                                                                                        <?php if (isset($opts->{$tool->vehicle_id}) && array_search($opt, $opts->{$tool->vehicle_id}) !== FALSE) : ?>
                                                                                            <?php $active = ' active';
                                                                                            $check = 'checked="checked"'; ?>
                                                                                        <?php endif; ?>
                                                                                    <?php endif; ?>
                                                                                    <?php $btnClass = $j % 2 ? 'btn-primary' : 'btn-warning'; ?>
                                                                                    <label class="btn btn-xs <?php echo $btnClass;
                                                                                    echo $active; ?>">
                                                                                        <i class="fa fa-check text-active"></i><?php echo $opt; ?>
                                                                                        <input class="tools_option"
                                                                                               type="checkbox"
                                                                                               data-rate="<?php echo $tool->vehicle_per_hour_price; ?>"
                                                                                               value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $check; ?>
                                                                                               data-tool_id="<?php echo $tool->vehicle_id; ?>"
                                                                                            <?php if (!isset($service_data)) : ?> data-name="tools_option[]" name=""
                                                                                            <?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="tools_option[<?php echo $service_data->id; ?>][<?php echo $tool->vehicle_id; ?>][<?php echo $key; ?>][]" data-name="tools_option[<?php echo $service_data->id; ?>][<?php echo $tool->vehicle_id; ?>][<?php echo $key; ?>][]"
                                                                                            <?php endif; ?>>
                                                                                    </label>
                                                                                <?php endforeach; ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>

                                                                    <div class="clear"></div>
                                                                </div>


                                                            <?php endforeach; ?>
                                                            <?php /* foreach ($tools as $k=>$tool) : ?>
															<?php if($k%3==0) : ?><div class="row"><br><?php endif; ?>
															<div class="col-md-4">
																<strong><?php echo $tool->vehicle_name; ?></strong>
																<?php if($tool->vehicle_options && count(json_decode($tool->vehicle_options))) : ?>
																	<?php foreach(json_decode($tool->vehicle_options) as $j=>$opt) : ?>
																		<div class="radio p-left-0">
																			<label>
																				<input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]" value="<?php echo $opt; ?>">
																				<font style="vertical-align: inherit;"><font style="vertical-align: inherit;"><?php echo $opt; ?></font></font>
																			</label>
																		</div>
																	<?php endforeach; ?>
																<?php else : ?>
																	<div class="radio p-left-0">
																		<label>
																			<input type="checkbox" class="tools_option" data-tool_id="<?php echo $tool->vehicle_id; ?>" name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]" value="Any">
																			<font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Any</font></font>
																		</label>
																	</div>
																<?php endif; ?>

															</div>
															<?php if($k%3 == 2 || !isset($tools[$k+1])) : ?></div><?php endif; ?>
													<?php endforeach; */ ?>
                                                            <?php /*
													<?php $toolOpt = ''?>
													<select name="service_tools[<?php echo $service_data->id; ?>][]" data-name="service_tools[<?php echo $service_data->id; ?>][]" data-service_id="<?php echo $service_data->id; ?>" class="service_tools form-control">
														<option value=""> - </option>
														<?php foreach ($tools as $tool) : ?>
															<option value="<?php echo $tool->vehicle_id?>" data-tooloptions='<?php echo htmlentities($tool->vehicle_options, ENT_QUOTES, 'UTF-8');?>' <?php if($v->equipment_attach_tool == $tool->vehicle_id) : $toolOpt = $tool->vehicle_options; ?>selected="selected"<?php endif; ?>>
																<?php echo $tool->vehicle_name; ?>
															</option>
														<?php endforeach; ?>
													</select>
													<div data-toggle="buttons" class="text-center text-center btn-group m-t-xs">
														<?php if($toolOpt != '') : ?>
															<?php foreach(json_decode($toolOpt) as $k=>$op) : ?>
															<?php $btnClass = $k % 2 ? 'btn-primary' : 'btn-warning';?>
															<label class="btn btn-xs <?php echo $btnClass; if($v->equipment_tools_option && count(json_decode($v->equipment_tools_option)) && array_search($op, json_decode($v->equipment_tools_option)) !== FALSE): ?> active<?php endif; ?>">
																<i class="fa fa-check text-active"></i> <?php echo $op; ?>
																<input type="checkbox" value="<?php echo $op; ?>"<?php if($v->equipment_tools_option && count(json_decode($v->equipment_tools_option)) && array_search($op, json_decode($v->equipment_tools_option)) !== FALSE): ?> checked<?php endif; ?>
																	<?php if(!isset($service_data)) : ?> data-name="tools_option[]" name=""
																	<?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="tools_option[<?php echo $service_data->id; ?>][<?php echo $key;?>][]" data-name="tools_option[<?php echo $service_data->id; ?>][<?php echo $key;?>][]"
																	<?php endif; ?>>
															</label>
															<?php endforeach; ?>
														<?php endif; ?>
													</div>
													*/ ?>
                                                        </div>
                                                    </div>
                                                    <div class="control-group pos-abt" style="top: 0;right: 10px;">
                                                        <a class="btn btn-xs btn-danger removeAttach"
                                                           data-service_id="<?php echo $service_data->id; ?>"><i
                                                                    class="fa fa-trash-o"></i></a>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ($service_data->service->service_attachments) : ?>
                                    <div class="serviceSetupList">
                                        <?php foreach (json_decode($service_data->service->service_attachments) as $key => $v) : ?>
                                            <div class="row pos-rlt p-left-5">
                                                <div class="serviceSetupTpl">
                                                    <div class="control-group col-md-2 col-xs-10 p-left-0">
                                                        <?php /*<label class="control-label">Service Equipment</label>*/ ?>

                                                        <div class="controls text-left p-left-10">
                                                            <?php $selOpt = '' ?>
                                                            <select data-name="service_vehicle[<?php echo $service_data->id; ?>][]"
                                                                    data-key="<?php echo $key; ?>"
                                                                    data-service_id="<?php echo $service_data->id; ?>"
                                                                    name="service_vehicle[<?php echo $service_data->id; ?>][]"
                                                                    class="service_vehicle form-control">
                                                                <option value=""> -</option>
                                                                <?php foreach ($vehicles as $veh) : ?>

                                                                    <option value="<?php echo $veh->vehicle_id; ?>"
                                                                            data-rate="<?php echo $veh->vehicle_per_hour_price; ?>"
                                                                            data-option='<?php echo htmlentities($veh->vehicle_options, ENT_QUOTES, 'UTF-8'); //$veh->vehicle_options;?>'
                                                                            <?php if (intval($v->vehicle_id) == $veh->vehicle_id) : ?>selected="selected"<?php $selOpt = $veh->vehicle_options; ?><?php endif; ?>>
                                                                        <?php echo $veh->vehicle_name; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div data-toggle="buttons"
                                                                 class="text-center text-center btn-group m-t-xs">
                                                                <?php if ($selOpt != '') : ?>
                                                                    <?php foreach (json_decode($selOpt) as $k => $op) : ?>
                                                                        <?php $btnClass = $k % 2 ? 'btn-primary' : 'btn-warning'; ?>
                                                                        <label class="btn btn-xs <?php echo $btnClass;
                                                                        if (trim($op) == $v->vehicle_option): ?> active<?php endif; ?>">
                                                                            <i class="fa fa-check text-active"></i> <?php echo $op; ?>
                                                                            <input class="vehicle_option"
                                                                                   type="checkbox"
                                                                                   value="<?php echo htmlentities($op, ENT_QUOTES, 'UTF-8'); ?>"<?php if (trim($op) == $v->vehicle_option): ?> checked<?php endif; ?>
                                                                                <?php if (!isset($service_data)) : ?> data-name="vehicle_option[]" name=""
                                                                                <?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="vehicle_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]" data-name="vehicle_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]"
                                                                                <?php endif; ?>>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="control-group  col-md-2 col-xs-10 p-left-0">
                                                        <?php /*<label class="control-label">Service Attachment</label>*/ ?>

                                                        <div class="controls text-left p-left-10">
                                                            <?php $traOpt = '' ?>
                                                            <select data-name="service_trailer[<?php echo $service_data->id; ?>][]"
                                                                    data-key="<?php echo $key; ?>"
                                                                    data-service_id="<?php echo $service_data->id; ?>"
                                                                    name="service_trailer[<?php echo $service_data->id; ?>][]"
                                                                    class="service_trailer form-control">
                                                                <option value=""> -</option>
                                                                <?php foreach ($trailers as $trai) : ?>
                                                                    <option value="<?php echo $trai->vehicle_id ?>"
                                                                            data-rate="<?php echo $trai->vehicle_per_hour_price; ?>"
                                                                            data-traioptions='<?php echo htmlentities($trai->vehicle_options, ENT_QUOTES, 'UTF-8'); //$trai->vehicle_options;?>'
                                                                            <?php if ($v->trailer_id == $trai->vehicle_id) : ?>selected="selected"<?php $traOpt = $trai->vehicle_options; ?><?php endif; ?>>
                                                                        <?php echo $trai->vehicle_name; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <div data-toggle="buttons"
                                                                 class="text-center text-center btn-group m-t-xs">
                                                                <?php if ($traOpt != '') : ?>
                                                                    <?php foreach (json_decode($traOpt) as $k => $op) : ?>
                                                                        <?php $btnClass = $k % 2 ? 'btn-primary' : 'btn-warning'; ?>
                                                                        <label class="btn btn-xs <?php echo $btnClass;
                                                                        if (trim($op) == $v->trailer_option): ?> active<?php endif; ?>">
                                                                            <i class="fa fa-check text-active"></i> <?php echo $op; ?>
                                                                            <input class="trailer_option"
                                                                                   type="checkbox"
                                                                                   value="<?php echo htmlentities($op, ENT_QUOTES, 'UTF-8'); ?>"<?php if (trim($op) == $v->trailer_option): ?> checked<?php endif; ?>
                                                                                <?php if (!isset($service_data)) : ?> data-name="trailer_option[]" name=""
                                                                                <?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="trailer_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>]" data-name="trailer_option[<?php echo $service_data->id; ?>][<?php echo $key; ?>][]"
                                                                                <?php endif; ?>>
                                                                        </label>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="control-group col-md-7 col-xs-12 p-left-0">
                                                        <?php /*<label class="control-label">Service Tools</label>*/ ?>

                                                        <div class="controls tools text-left">
                                                            <?php foreach ($tools as $k => $tool) : ?>
                                                                <div class="row m-b-xs p-left-10">

                                                                    <div class="col-md-3 col-lg-3"
                                                                         style="padding-right: 0;">
                                                                        <div class="text-left">
                                                                            <strong><?php echo $tool->vehicle_name; ?></strong>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-lg-9 pull-right   p-right-0 p-top-0 p-bottom-0 ">
                                                                        <div data-toggle="buttons"
                                                                             class="text-center text-center btn-group ">
                                                                            <?php if ($tool->vehicle_options && !empty(json_decode($tool->vehicle_options))) : ?>
                                                                                <?php foreach (json_decode($tool->vehicle_options) as $j => $opt) : ?>
                                                                                    <?php $btnClass = $j % 2 ? 'btn-primary' : 'btn-warning'; ?>

                                                                                    <label class="btn btn-xs <?php echo $btnClass; ?>">
                                                                                        <i class="fa fa-check text-active"></i><?php echo $opt; ?>
                                                                                        <input type="checkbox"
                                                                                               class="tools_option"
                                                                                               data-rate="<?php echo $tool->vehicle_per_hour_price; ?>"
                                                                                               data-tool_id="<?php echo $tool->vehicle_id; ?>"
                                                                                               name="tools_option[<?php echo $tool->vehicle_id; ?>][0][]"
                                                                                               value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                               data-array="1">

                                                                                    </label>
                                                                                <?php endforeach; ?>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clear"></div>
                                                                </div>

                                                            <?php endforeach; ?>

                                                            <?php /*
														<?php $toolOpt = ''?>
														<select name="service_tools[<?php echo $service_data->id; ?>][]" data-name="service_tools[<?php echo $service_data->id; ?>][]" data-service_id="<?php echo $service_data->id; ?>" class="service_tools form-control">
															<option value=""> - </option>
															<?php foreach ($tools as $tool) : ?>
																<option value="<?php echo $tool->vehicle_id?>" data-tooloptions='<?php echo htmlentities($tool->vehicle_options, ENT_QUOTES, 'UTF-8');?>' <?php if($v->tool_id == $tool->vehicle_id) : $toolOpt = $tool->vehicle_options; ?>selected="selected"<?php endif; ?>>
																	<?php echo $tool->vehicle_name; ?>
																</option>
															<?php endforeach; ?>
														</select>
														<div data-toggle="buttons" class="text-center text-center btn-group m-t-xs">
															<?php if($toolOpt != '') : ?>
																<?php foreach(json_decode($toolOpt) as $k=>$op) : ?>
																<?php $btnClass = $k % 2 ? 'btn-primary' : 'btn-warning';?>
																<label class="btn btn-xs <?php echo $btnClass; if($v->tools_option == trim($op)): ?> active<?php endif; ?>">
																	<i class="fa fa-check text-active"></i> <?php echo $op; ?>
																	<input type="checkbox" value="<?php echo $op; ?>"<?php if($v->tools_option == trim($op)): ?> checked<?php endif; ?>
																		<?php if(!isset($service_data)) : ?> data-name="tools_option[]" name=""
																		<?php else : ?>  data-service_id="<?php echo $service_data->id; ?>" name="tools_option[<?php echo $service_data->id; ?>][<?php echo $key;?>][]" data-name="tools_option[<?php echo $service_data->id; ?>][<?php echo $key;?>][]"
																		<?php endif; ?>>
																</label>
																<?php endforeach; ?>
															<?php endif; ?>
														</div>
														*/ ?>
                                                        </div>
                                                    </div>

                                                    <div class="control-group pos-abt" style="top: 0;right: 10px;">
                                                        <a class="btn btn-xs btn-danger removeAttach"
                                                           data-service_id="<?php echo $service_data->id; ?>"><i
                                                                    class="fa fa-trash-o"></i></a>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else : ?>
                                <div class="serviceSetupList">
                                    <div class="row pos-rlt ">
                                        <div class="serviceSetupTpl">
                                            <div class="control-group col-md-2 col-xs-10 p-left-0">
                                                <?php /*<label class="control-label">Service Equipment</label>*/ ?>

                                                <div class="controls text-left p-left-10">
                                                    <select data-name="service_vehicle" data-array="1"  <?php if(isset($service_data)): ?> name="service_vehicle[<?php echo $service_data->id; ?>][]" <?php endif;?>
                                                            data-key="0" class="service_vehicle form-control">
                                                        <option value=""> -</option>
                                                        <?php foreach ($vehicles as $veh) : ?>
                                                            <option value="<?php echo $veh->vehicle_id ?>"
                                                                    data-rate="<?php echo $veh->vehicle_per_hour_price; ?>"
                                                                    data-option='<?php echo htmlentities($veh->vehicle_options, ENT_QUOTES, 'UTF-8'); //echo htmlspecialchars($veh->vehicle_options);?>'>
                                                                <?php echo $veh->vehicle_name; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div data-toggle="buttons"
                                                         class="text-center text-center btn-group m-t-xs">

                                                        <label class="btn btn-xs">
                                                            <i class="fa fa-check text-active"></i>
                                                            <input class="vehicle_option" type="checkbox" value=""
                                                                   data-array="1"
                                                                   data-name="vehicle_option" name="">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="control-group col-md-2 col-xs-10 p-left-0">
                                                <?php /*<label class="control-label">Service Attachment</label>*/ ?>

                                                <div class="controls text-left p-left-10">
                                                    <select data-name="service_trailer" data-array="1" data-key="0"
                                                        <?php if(isset($service_data)): ?> name="service_trailer[<?php echo $service_data->id; ?>][]"  <?php endif; ?>class="service_trailer form-control">
                                                        <option value=""> -</option>
                                                        <?php foreach ($trailers as $trai) : ?>
                                                            <option value="<?php echo $trai->vehicle_id ?>"
                                                                    data-rate="<?php echo $trai->vehicle_per_hour_price; ?>"
                                                                    data-traioptions='<?php echo htmlentities($trai->vehicle_options, ENT_QUOTES, 'UTF-8'); //htmlspecialchars($trai->vehicle_options);?>'>
                                                                <?php echo $trai->vehicle_name; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div data-toggle="buttons"
                                                         class="text-center text-center btn-group m-t-xs">

                                                        <label class="btn btn-xs">
                                                            <i class="fa fa-check text-active"></i>
                                                            <input class="trailer_option" type="checkbox" value=""
                                                                   data-name="trailer_option" data-array="1" name="">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="control-group col-md-7 col-xs-12 p-left-0">
                                                <?php /*<label class="control-label">Service Tools</label>*/ ?>

                                                <div class="controls tools text-left">
                                                    <?php foreach ($tools as $k => $tool) : ?>
                                                        <div class="row m-b-xs p-left-10 ">

                                                            <div class="col-md-3 col-lg-3" style="padding-right: 0;">
                                                                <div class="text-left">
                                                                    <strong><?php echo $tool->vehicle_name; ?></strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 col-lg-9 p-right-0 p-top-0 p-bottom-0 ">
                                                                <div data-toggle="buttons"
                                                                     class="text-center text-center btn-group ">
                                                                    <?php if ($tool->vehicle_options && !empty(json_decode($tool->vehicle_options))) : ?>
                                                                        <?php foreach (json_decode($tool->vehicle_options) as $j => $opt) : ?>
                                                                            <?php $btnClass = $j % 2 ? 'btn-primary' : 'btn-warning'; ?>
                                                                            <!----------------------->

                                                                            <!----------------------->
                                                                            <label class="btn btn-xs <?php echo $btnClass; ?>">
                                                                                <i class="fa fa-check text-active"></i>
                                                                                <input type="checkbox"
                                                                                       class="tools_option"
                                                                                       data-rate="<?php echo $tool->vehicle_per_hour_price; ?>"
                                                                                       data-tool_id="<?php echo $tool->vehicle_id; ?>"
                                                                                       value="<?php echo htmlentities($opt, ENT_QUOTES, 'UTF-8'); ?>"
                                                                                       data-array="1"
                                                                                       <?php if(isset($service_data)): ?> name="tools_option[<?php echo $service_data->id; ?>][<?php echo $tool->vehicle_id; ?>][0][]" <?php endif; ?>
                                                                                ><?php echo $opt; ?>

                                                                            </label>
                                                                        <?php endforeach; ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>

                                                    <?php endforeach; ?>
                                                    <?php /*
													<select name="" data-name="service_tools" data-array="1"  class="service_tools form-control">
														<option value=""> - </option>
														<?php foreach ($tools as $tool) : ?>
															<option value="<?php echo $tool->vehicle_id?>" data-tooloptions='<?php echo htmlentities($tool->vehicle_options, ENT_QUOTES, 'UTF-8');?>'>
																<?php echo $tool->vehicle_name; ?>
															</option>
														<?php endforeach; ?>
													</select>
													<div data-toggle="buttons" class="text-center text-center btn-group m-t-xs">

														<label class="btn btn-xs" >
															<i class="fa fa-check text-active"></i>
															<input type="checkbox" value=""
																data-name="tools_option" data-array="1" name="">
														</label>
													</div>
													*/ ?>
                                                </div>
                                            </div>
                                            <div class="control-group pos-abt" style="top: 0;right: 10px;">
                                                <a class="btn btn-xs btn-danger removeAttach"><i
                                                            class="fa fa-trash-o"></i></a>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="text-center pos-abt" style="top: 10px;left: 130px;">
                                <a class="btn btn-xs btn-success addAttach" role="button"><i class="fa fa-plus"></i></a>
                            </div>
                        </div>
                        <!-----------//SETUPS END------------>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <?php /*
	<div class="row m-n">
		<div class="col-md-12 p-n">
			<section class="panel panel-default" style="margin-bottom: 0; border-right: none; border-left: none; border-bottom: none; box-shadow: none;">
				<header class="panel-heading">Options</header>
				<div class="panel-body">

					<div class="row p-bottom-0">
						<div class="col-md-5 form-horizontal">
							<div class="form-group m-b-none">
								<div class="col-md-7 p-n">
									<div class="col-lg-6 col-xs-12 m-b">
										<input type="text" class="form-control" placeholder="Size"
											<?php if(!isset($service_data)) : ?> data-name="service_size" name=""
											<?php else : ?> name="service_size[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_size; ?>"
											<?php endif; ?>data-toggle="tooltip" data-placement="top" title="" data-original-title="">
									</div>

									<div class="col-lg-6 col-xs-12  m-b">
										<input type="text" class="form-control" placeholder="Reason"
											<?php if(!isset($service_data)) : ?> data-name="service_reason" name=""
											<?php else : ?> name="service_reason[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_reason; ?>"
											<?php endif; ?>>
									</div>



									<div class="col-lg-6 ui-widget col-xs-12 m-b">
										<input <?php if(isset($service_data)) : ?> id="species-<?php echo $service_data->id; ?>" <?php else : ?> id="species" <?php endif; ?> type="text" class="form-control species" placeholder="Species"
											<?php if(!isset($service_data)) : ?> data-name="service_species" name=""
											<?php else : ?> name="service_species[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_species; ?>"
											<?php endif; ?>>
									</div>

									<div class="col-lg-6 text-center col-xs-12">
										<div data-toggle="buttons">
											<label class="btn btn-info permit<?php if(isset($service_data) && $service_data->service_permit) : ?> active<?php endif; ?>" style="padding: 6px 0; width: 42%;">
												<i class="fa fa-check text-active"></i> Permit
												<input type="checkbox" value="1"
													<?php if(!isset($service_data)) : ?> data-name="service_permit" name=""
													<?php else : ?> name="service_permit[<?php echo $service_data->id; ?>]" <?php if($service_data->service_permit) : ?> checked<?php endif; ?>
													<?php endif; ?>>
											</label>
											<label class="btn btn-info exemption<?php if(isset($service_data) && $service_data->service_exemption) : ?> active<?php endif; ?>" style="padding: 6px 0; width: 55%;">
												<i class="fa fa-check text-active"></i> Exemption
												<input type="checkbox" value="1"
													<?php if(!isset($service_data)) : ?> data-name="service_exemption" name=""
													<?php else : ?> name="service_exemption[<?php echo $service_data->id; ?>]" <?php if($service_data->service_exemption) : ?> checked<?php endif; ?>
												<?php endif; ?>>
											</label>
										</div>
									</div>
									<div class="clear"></div>
								</div>


								<div class="col-md-5 ">
									<span class="btn btn-primary btn-file" style="display: block;">
										Choose Files...
										<input type="file" multiple="multiple" class="btn-upload" style="width: 100%;"
											<?php if(!isset($service_data)) : ?> data-name="service_files" name="" data-array="1"
											<?php else : ?> name="service_files[<?php echo $service_data->id; ?>][]"
											<?php endif; ?>>
									</span>
									<div class="m-top-10 pull-left m-bottom-10" id="upload-file-info"></div>
									<?php if(isset($service_data)) : ?>
										<?php $files = bucketScanDir('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $service_data->id . '/'); ?>
										<?php if ($files && count($files)) : ?>
											<div class="clear" style="overflow: visible;">
												<?php foreach ($files as $img) : ?>
													<div class="b-a inline poser m-b-xs">
														<a href="#" class="pos-abt remove-file" title="Delete File" data-estimate_no="<?php echo $estimate_data->estimate_no; ?>" data-name="<?php echo $img; ?>" data-client="<?php echo $estimate_data->client_id; ?>" data-service="<?php echo $service_data->id; ?>">X</a>
														<a href="<?php echo base_url(get_image_dir($estimate_data->client_id, $estimate_data->estimate_no, $service_data->id) . $img); ?>"
														   data-lightbox="works" style="text-decoration: none;">
															<img
																src="<?php echo base_url('uploads/clients_files/' . $estimate_data->client_id . '/estimates/' . $estimate_data->estimate_no . '/' . $service_data->id . '/' . $img); ?>"
																width="32" height="32"/>
														</a>
													</div>
												<?php endforeach; ?>
											</div>
										<?php endif; ?>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="col-md-7 form-horizontal">
							<div class="form-group m-b-none">

								<div class="col-md-6 col-lg-3  col-xs-12">
									<div class="form-group m-b-none form-horizontal">
										<div class="col-sm-12 m-b">
											<input type="text" class="form-control" placeholder="Wood Chips (%)"
											<?php if(!isset($service_data)) : ?> data-name="service_wood_chips" name=""
											<?php else : ?> name="service_wood_chips[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_wood_chips ? $service_data->service_wood_chips : NULL; ?>"
											<?php endif; ?>>
										</div>
									</div>
								</div>
								<div class="col-lg-3 col-md-6  m-t col-xs-12">
									<div class="form-group m-b-none form-horizontal">
										<div class="col-sm-12">
											<input type="text" class="form-control" placeholder="Wood Trailers (%)"
											<?php if(!isset($service_data)) : ?> data-name="service_wood_trailers" name=""
											<?php else : ?> name="service_wood_trailers[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_wood_trailers ? $service_data->service_wood_trailers : NULL; ?>"
											<?php endif; ?>>
										</div>
									</div>
								</div>

								<div class="col-lg-6 col-md-12 m-t col-xs-12" style="padding-right: 0; ">
									<div data-toggle="buttons">
										<label class="btn btn-primary<?php if(!isset($service_data) || (isset($service_data) && $service_data->service_access == 12)) : ?> active<?php endif; ?>" style="width: 19%; padding: 6px 0;">
											<i class="fa fa-check text-active"></i> >12ft
											<input type="radio" value="12"<?php if(!isset($service_data) || (isset($service_data) && $service_data->service_access == 12)) : ?> checked<?php endif; ?>
												<?php if(!isset($service_data)) : ?> data-name="service_access" name=""
												<?php else : ?> name="service_access[<?php echo $service_data->id; ?>]"
												<?php endif; ?>>
										</label>
										<label class="btn btn-primary<?php if(isset($service_data) && $service_data->service_access == 6) : ?> active<?php endif; ?>" style="width: 20%; padding: 6px 0;">
											<i class="fa fa-check text-active"></i> 6ft-12ft
											<input type="radio" value="6"
												<?php if(!isset($service_data)) : ?> data-name="service_access" name=""
												<?php else : ?> name="service_access[<?php echo $service_data->id; ?>]"<?php if(isset($service_data) && $service_data->service_access == 6) : ?> checked<?php endif; ?>"
												<?php endif; ?>>
										</label>
										<label class="btn  btn-primary<?php if(isset($service_data) && $service_data->service_access == 3) : ?> active<?php endif; ?>" style="width: 20%; padding: 6px 0;">
											<i class="fa fa-check text-active"></i> 3ft-6ft
											<input type="radio" value="3"
												<?php if(!isset($service_data)) : ?> data-name="service_access" name=""
												<?php else : ?> name="service_access[<?php echo $service_data->id; ?>]"<?php if(isset($service_data) && $service_data->service_access == 3) : ?> checked<?php endif; ?>"
												<?php endif; ?>>
										</label>
										<label class="btn btn-primary<?php if(isset($service_data) && $service_data->service_access == 1) : ?> active<?php endif; ?>" style="width: 20%; padding: 6px 0;">
											<i class="fa fa-check text-active"></i> 1ft-3ft
											<input type="radio" value="1"
												<?php if(!isset($service_data)) : ?> data-name="service_access" name=""
												<?php else : ?> name="service_access[<?php echo $service_data->id; ?>]"<?php if(isset($service_data) && $service_data->service_access == 1) : ?> checked<?php endif; ?>"
												<?php endif; ?>>
										</label>
										<label class="btn btn-primary<?php if(isset($service_data) && $service_data->service_access == 0) : ?> active<?php endif; ?>" style="width: 16%; padding: 6px 0;">
											<i class="fa fa-check text-active"></i> No
											<input type="radio" value="0"
												<?php if(!isset($service_data)) : ?> data-name="service_access" name=""
												<?php else : ?> name="service_access[<?php echo $service_data->id; ?>]"<?php if(isset($service_data) && $service_data->service_access == 0) : ?> checked<?php endif; ?>"
												<?php endif; ?>>
										</label>
									</div>
								</div>
							</div>
						</div>
					</div>


				</div>
			</section>
		</div>
	</div>
	*/ ?>
</section>
