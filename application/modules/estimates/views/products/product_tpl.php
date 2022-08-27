<?php $rand = rand(1, 10000); ?>
<section class="col-md-12 panel panel-default p-left-0 p-right-0 p-bottom-0 serviceGroup m-n"
         data-markup="<?php if (isset($service_data) && isset($service_data->service->service_markup)) echo $service_data->service->service_markup; ?>"
         data-service_id="<?php if (isset($service_data) && isset($service_data->id)) echo $service_data->id; else echo '0'; ?>"
         data-old_value="<?php if (isset($service_data) && isset($service_data->quantity)) echo $service_data->quantity; else echo '1'; ?>"
         data-service-type="product"
        <?php if (isset($service_data) && isset($service_data->bundle_id)):?> data-bundle_id="<?= $service_data->bundle_id; ?>" <?php endif; ?>>
    <header class="panel-heading" <?php if(!empty($service_data->bundle_id)) : ?> style="background-color: white" <?php endif ?>>
        <div class="m-t-n-xs m-b-n-xs" style="display: flex;align-items: center;">
            <div class="col-md-3 col-lg-2 col-xs-4 p-left-0 p-right-0 pull-right col-sm-3">
                <i class="fa fa-gift text-success fa-lg m-r-sm"></i>
                <span class="serviceTitle" data-name="service_name"><?php if (isset($service_data) && isset($service_data->service->service_name)) echo $service_data->service->service_name; else echo 'UNDEFINED'; ?></span>
                <span class="non-taxable-title"
                      <?php if (isset($service_data)) : ?>id="service-checkbox-<?php echo $service_data->id; ?>-title" <?php endif;
                if (isset($service_data) && $service_data->non_taxable == 0 || !isset($service_data)) : ?> hidden <?php endif; ?> >(Non-taxable)</span>
                <a class="toggleChevron" style="cursor:pointer" data-toggle="collapse"
                   href="#collapse<?= isset($service_data->id) ? $service_data->id : $rand ?>" aria-expanded="false">
                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
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
                    <?php if(empty($service_data->new)) : ?>
                        <input type="hidden" name="id[<?php echo $service_data->id; ?>]"
                               value="<?php echo $service_data->id; ?>">
                    <?php endif; ?>
                    <input type="hidden" class="form-control" name="service_type_id[<?php echo $service_data->id; ?>]"
                           value="<?php echo $service_data->service_id; ?>"  data-service_id="<?php echo $service_data->id; ?>">
                    <input type="hidden" name="is_product[<?php echo $service_data->id; ?>]" value="1"  data-service_id="<?php echo $service_data->id; ?>">
                    <input type="hidden" name="is_collapsed[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service->service_is_collapsed ?>">
                    <input type="hidden" name="service_priority[<?php echo $service_data->id; ?>]" value="<?= $service_data->service_priority ?? 0 ?>">
                <?php else : ?>
                    <input type="hidden" data-name="is_product" data-is_product="1" value="1">
                    <input type="hidden" data-name="service_priority" value="1">
                    <input type="hidden" class="form-control" data-name="service_type_id" data-service_type_id="1"
                           name="">
                    <input type="hidden" data-name="non_taxable" value="0" class="nonTaxableHidden">
                    <input type="hidden" data-name="is_collapsed" value="1">
                <?php endif; ?>
                <?php if(!empty($service_data->bundle_id)) : ?>
                    <input type="hidden" name="bundles_services[<?php echo $service_data->id; ?>]"
                           value="<?php echo $service_data->bundle_id; ?>"
                           data-service_id="<?php echo $service_data->id; ?>">
                <?php else : ?>
                    <input type="hidden" data-name="bundles_services" value="0">
                <?php endif; ?>
                <?php if(!empty($service_data->service->service_name)) : ?>
                    <input type="hidden" name="bundles_services_name[<?php echo $service_data->id; ?>]"
                           value="<?php echo $service_data->service->service_name; ?>"
                           data-service_id="<?php echo $service_data->id; ?>">

                <?php endif; ?>
                <div class="clear"></div>

            </div>
            <div class="col-md-10 col-xs-8 col-lg-10 col-sm-10">
                <div class="row">

                    <div class="form-group m-m m-b-none priceSum col-md-4 p-left-0 p-right-0 col-sm-4 col-lg-4" >
                        <div <?php if(empty($access_token) || empty($classes)) : ?> hidden <?php endif; ?> class="QBclass pull-right">
                            <label class="inline"><strong>QB Class</strong></label>
                            <div class="inline">
                                <input type="text" class="classSelect w-200 class-media" value="<?= isset($service_data) && isset($service_data->estimate_service_class_id) ? $service_data->estimate_service_class_id : '' ?>" data-name="class" name="class[<?php echo isset($service_data) ? $service_data->id : ''; ?>]"
                                       data-service_id="<?= isset($service_data) ? $service_data->id : ''; ?>"/>
                            </div>
                        </div>
                    </div>

                    <div class="form-group m-m m-b-none priceSum  col-md-3 p-left-0 p-right-0 text-right col-sm-3 col-lg-2">
                        <div class="inline m-right-10"><strong>QTY</strong></div>
                        <div class="inline" style="">
                            <input style="width: 50px;"
                                   class="integer productQuantity form-control input-mini text-center "
                                   type="text" <?php if (!isset($service_data)) : ?> data-name="quantity" name="" value="1" <?php else : ?> name="quantity[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->quantity; ?>"
                                   data-service_id="<?php echo $service_data->id; ?>"
                            <?php endif; ?>>
                        </div>
                    </div>

                    <div class="form-group m-m m-b-none priceSum col-md-5 p-right-0 text-right col-sm-5 col-lg-3 cost-media">
                        <div class="inline m-right-10"><strong>Cost</strong></div>
                        <div class="inline" style="">
                            <input style="width: 100px;" placeholder="Cost"
                                   class="productCost form-control input-mini text-center currency"
                                   type="text"
                                <?php if (!isset($service_data)) : ?> data-name="product_cost" name="" value=""
                                <?php else : ?> name="product_cost[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->cost; ?>"  data-service_id="<?php echo $service_data->id; ?>"
                                <?php endif; ?>>
                        </div>
                    </div>


                    <div class="form-group m-m m-b-none priceSum col-md-12 p-right-0 text-right col-sm-12 col-lg-3">
                        <div class="inline text-ul taxable m-right-10" data-trigger="focus" tabindex="0" style="cursor: pointer"><strong>Price</strong></div>
                        <div class="inline" style="">
                            <input readonly style="width: 120px; background-color: white" placeholder="Price"
                                   class="form-control input-mini servicePrice text-center currency"
                                   type="text"
                                <?php if (!isset($service_data)) : ?> data-name="service_price" name=""
                                <?php else : ?> name="service_price[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_price; ?>"  data-service_id="<?php echo $service_data->id; ?>"
                                <?php endif; ?>>
                        </div>
                        <a class="m-l-sm btn btn-danger btn-xs deleteService hidden-xs hidden-sm"
                           data-service_id="<?php if (isset($service_data)) echo $service_data->id; ?>" style="margin-left: 9px">
                            <i class="fa fa-trash-o"></i>
                        </a>
                        <div class="form-check pull-right m-left-10 form-check-inline content hide">
                            <label class="form-check-label check-tax"
                                   for="nonTaxable"><strong>Non-taxable</strong></label>
                            <input type="checkbox" class="form-check-input big-checkbox check-tax nonTaxable"
                                   data-id="#service-checkbox-<?php if (isset($service_data->id)) echo $service_data->id; ?>"
                                   data-service_id="<?php if (isset($service_data->id)) echo $service_data->id; ?>">
                        </div>
                    </div>


                </div>
            </div>

            <div class="clear"></div>
        </div>
    </header>
    <div class="collapse" id="collapse<?= isset($service_data->id) ? $service_data->id : $rand ?>">
        <div>
            <div class="col-sm-12 b-r form-group m-m m-b-none serviceTop" style="border: 0;">
                <div class="row pos-rlt">
                    <div class="col-md-10 col-xs-12 form-group m-m pull-left m-b-none p-n serviceDescriptionBlock">
                    <textarea class="form-control serviceDescription m-t-n-xxs " placeholder="Service Description"
                              <?php if (!isset($service_data)) : ?>data-name="service_description"
                              name=""<?php else : ?> name="service_description[<?php echo $service_data->id; ?>]" data-service_id="<?php echo $service_data->id; ?>"<?php endif; ?>><?php if (isset($service_data)) echo $service_data->service_description; ?></textarea>
                    </div>
                    <div class="col-md-2 col-xs-12 form-group m-m pull-right m-b-none p-n">
                        <div class="product-dropzone dropzone"></div>
                    </div>
                </div>
            </div>
            <div style="clear:both;"></div>

        </div>
    </div>
</section>