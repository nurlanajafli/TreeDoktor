<section class="col-md-12 col-lg-12 col-xs-12 col-sm-12 alert-info floating-line" style="visibility: hidden; bottom: -100%; opacity: 0;">

    <div class="col-md-5 col-lg-6 col-xs-12 col-sm-12 p-n panel-default" >
        <div class="text-center col-md-6 col-lg-6 col-xs-12 col-sm-12 p-left-0 p-right-0 p-top-5">
            <div class="p-top-5 selectService" >
                <textarea hidden class="categoriesWithServices" ><?= !empty($categoriesWithServices) ? json_encode($categoriesWithServices) : "" ?></textarea>
                <textarea hidden class="categoriesWithProducts" ><?= !empty($categoriesWithProducts) ? json_encode($categoriesWithProducts) : "" ?></textarea>
                <?php if(!empty($categoriesWithServices)) : ?>
                <div class="btn-group dropup media-items" style="width: 120px">
                    <button class="btn btn-success service-select2-toggle w-100" data-toggle="dropdown" style="background-color: #ffc333; border-color: #ffc333">Service <span class="caret"></span></button>
                    <input type="hidden" class="selectServices w-100 selectServiceItem service-select2" />
                </div>
                <?php endif; ?>
                <?php if(!empty($categoriesWithProducts)) : ?>
                <div class="btn-group dropup media-items" style="width: 120px">
                        <button class="btn btn-success product-select2-toggle w-100" data-toggle="dropdown">Product <span class="caret"></span></button>
                        <input type="hidden" class="productSelect w-100 selectServiceItem product-select2 " />
                </div>
                <?php endif; ?>
                <?php if(isset($bundles) && !empty($bundles)): ?>
                    <div class="btn-group dropup media-items" style="width: 120px">
                        <button class="btn btn-success bundle-select2-toggle w-100" data-toggle="dropdown" style="background-color: #4cc0c1; border-color: #4cc0c1;">Bundle <span class="caret"></span></button>
                        <select class="bundle-select2">
                            <?php foreach ($bundles as $bundle) : ?>
                                <option
                                        value="<?php echo $bundle->service_id; ?>"
                                        data-is_bundle="1"
                                        data-is_view="<?php echo $bundle->is_view_in_pdf; ?>"
                                        data-bundle_records='<?php echo $bundle->bundle_records; ?>'
                                        data-service_markup="<?php echo $bundle->service_markup; ?>"
                                        data-service_type_id="<?php echo $bundle->service_id; ?>"
                                        class="selectBundle"
                                        data-description="<?php echo htmlspecialchars($bundle->service_description); ?>"
                                        data-product_cost="<?php echo $bundle->cost; ?>"
                                        data-setups="<?php echo htmlentities($bundle->service_attachments, ENT_QUOTES, 'UTF-8');?>"
                                >
                                    <?php echo $bundle->service_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            <div class="service_buttons_block inline m-top-10">
                <?php if(!empty($favouriteIcons))
                    foreach ($favouriteIcons as $item): ?>
                    <label>
                        <span data-toggle="tooltip" data-placement="top" title="<?= $item->service_name; ?>"
                              class="<?php if($item->service_favourite_icon != 'first-letters'): ?> favourite-icon <?php endif; ?> <?= $item->service_favourite_icon ?> <?php  if($item->is_bundle) : ?>createBundleService favourite-icon-bundle<?php elseif($item->is_product): ?> createEstimateProduct favourite-icon-product<?php else: ?>createEstimateService favourite-icon-service<?php endif; ?>"
                              data-est-service-id="<?= $item->service_id; ?>"
                              style="cursor:pointer" >
                            <?php if($item->service_favourite_icon == 'first-letters') echo $item->favouriteShortcut ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class=" col-md-6 col-lg-6 col-xs-12 col-sm-12 p-top-10">
            <textarea name="estimate_crew_notes" rows="4" class="form-control estimate_item_note_crew" placeholder="Write some notes to the crew" style="resize: none;background-color: #f5f5f5;"><?php echo isset($estimate_data) ? $estimate_data->estimate_crew_notes : '';?></textarea>
        </div>
    </div>
    <div class="col-md-7 col-lg-6 col-xs-12 col-sm-12 p-n">
        <?php $this->load->view('estimate_form_total'); ?>
    </div>
</section>
<div class="clearfix"></div>
