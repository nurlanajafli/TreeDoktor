<section class="col-md-12 col-xs-12 col-sm-12 alert-info floating-mobile-line closed p-sides-30" style="visibility: hidden; bottom: -100%; opacity: 0;">
	<div class="text-center"><a href="#" class="showAllLine"><i class="fa fa-arrow-circle-up"></i></a></div>
	<div class="col-md-4 col-lg-4 col-xs-12 p-n h-100 panel-default">
		<div class="estimateTotalCalculations">

            <div class="col-xs-12 col-sm-6 text-center">
                <div class="selectService p-left-0 p-right-0" >
                    <textarea hidden class="categoriesWithServices" ><?= !empty($categoriesWithServices) ? json_encode($categoriesWithServices) : "" ?></textarea>
                    <textarea hidden class="categoriesWithProducts" ><?= !empty($categoriesWithProducts) ? json_encode($categoriesWithProducts) : "" ?></textarea>
                    <?php if(!empty($categoriesWithServices)) : ?>
                    <div class="btn-group dropup">
                        <button class="btn btn-success service-select2-toggle" data-toggle="dropdown" style="background-color: #ffc333; border-color: #ffc333">Service <span class="caret"></span></button>
                        <input type="hidden" class="selectServices w-100 selectServiceItem service-select2" />
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($categoriesWithProducts)) : ?>
                        <div class="btn-group dropup">
                            <button class="btn btn-success product-select2-toggle" data-toggle="dropdown">Product <span class="caret"></span></button>
                            <input type="hidden" class="productSelect w-100 selectServiceItem product-select2" />
                        </div>
                    <?php endif; ?>
                    <?php if(isset($bundles) && !empty($bundles)): ?>
                        <div class="btn-group dropup">
                            <button class="btn btn-success bundle-select2-toggle" data-toggle="dropdown" style="background-color: #4cc0c1; border-color: #4cc0c1;">Bundle <span class="caret"></span></button>
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
                <div class="service_buttons_block_mobile inline p-left-0">
                    <?php if(!empty($favouriteIcons))
                        foreach ($favouriteIcons as $item): ?>
                            <label>
                            <span data-toggle="tooltip" data-placement="top" title="<?= $item->service_name; ?>"
                                  class="<?php if($item->service_favourite_icon != 'first-letters'): ?> favourite-icon <?php endif; ?> <?= $item->service_favourite_icon ?> <?php  if($item->is_bundle) : ?>createBundleService favourite-icon-bundle<?php elseif($item->is_product): ?> createEstimateProduct favourite-icon-product<?php else: ?>createEstimateService favourite-icon-service<?php endif; ?>"
                                  data-est-service-id="<?= $item->service_id; ?>"
                                  style="cursor:pointer">
                                <?php if($item->service_favourite_icon == 'first-letters') echo $item->favouriteShortcut ?>
                            </span>
                            </label>
                        <?php endforeach; ?>
                </div>
            </div>
            <div class="col-xs-12 col-sm-6 p-right-0 p-left-0">
                <div class="w-100">
                    <div class="inline "><h5>Total For Services: </h5></div>
                    <button type="submit" disabled class="btn btn-success disabled pull-right saveForm" style="width: 92px; margin-left: 5px">Save</button>
                    <div class="totalForServicesParent pull-right" style="width: 92px;">
                        <div class="inline-block"><span class="totalForServices"></span></div>
                    </div>

                    <div class="clearfix"></div>
                </div>

            </div>

			<textarea name="estimate_crew_notes" rows="3" class="form-control estimate_item_note_crew" placeholder="Write some notes to the crew" style="resize: none;background-color: #f5f5f5;"><?php echo isset($estimate_data) ? $estimate_data->estimate_crew_notes : '';?></textarea>
<!--			<div class="col-lg-6">-->
				<h5>Discount:
					<div class="inline-block pull-right text-ul">
						<span style="cursor: pointer;" data-toggle="popover" data-html="true" data-placement="top" class="discountPopover"
						   data-content="<div class='row discountPopoverContent'>
						   <div class='col-sm-9 col-xs-9' style='padding: 0 15px 0 10px; visibility:hidden;'>
								<input type='text' value='' id='amountDiscount' placeholder='Amount' class='form-control'>
						   </div>
						   <!--<div class='col-sm-2' style='padding: 0 0 0 15px; visibility:hidden;'>
								<label class='checkbox p-n'>
									<input type='checkbox' id='percentsDiscount' value='1'>%
								</label>
							</div>-->
							<div class='col-sm-3 col-xs-3' style='padding: 7px 0 0 0; visibility:hidden;'>
								<span class='btn btn-xs btn-success' id='saveDiscount'><i class='fa fa-check'></i></span>
							</div>
							<div class='col-xs-12 m-t-xs' style='padding: 0 15px 0 10px; visibility:hidden;'>
								<textarea style='width:250px' class='form-control' id='commentDiscount' placeholder='Comment' rows='7'></textarea>
                            </div>
							</div>" title="" data-original-title="<button type=&quot;button&quot; class=&quot;close pull-right&quot; data-dismiss=&quot;popover&quot;>×</button>Edit Discount">
							<span class="discountString"></span>
						</span>
					</div>
				</h5>
                <h5>Tax
                    <span class="taxLabel"><?php echo $taxText; ?></span> :

                    <div class="inline-block pull-right popover-markup text-ul" >
                        <span class="tax trigger" style="cursor: pointer"></span>
                        <div class="head hide">Tax selection <button type="button" class="close pull-right" data-dismiss="popover">×</button></div>
                        <div class="content hide">
                            <input class="select2 w-150 form-data select2Tax" id="tax" style="min-width: 100px;" type="text" value="<?php echo $taxText; ?>" data-href='#allTaxes'>
                        </div>
                    </div>
                    <span class="popover-markup recommendation pull-right p-right-5" <?php if(!empty($taxRecommendation) && !empty($taxRate) && $taxRate == $taxRecommendation || empty($taxRecommendation)) : ?> hidden <?php endif; ?> style="padding-top: 1px">
                        <i class="fa fa-warning trigger recommendationTax" style="color: red; cursor: pointer"></i>
                        <div class="head hide">Tax advice <button type="button" class="close pull-right" data-dismiss="popover">×</button></div>
                        <div class="content hide">
                            <div style="min-width: 200px;">
                                Recommended tax (<span class="taxRecommendationTitle"><?php  echo !empty($taxRecommendation) ? $taxRecommendation : null ?></span>%)
                                <input type="button" class="btn btn-success btn-xs useTax" value="use">
                            </div>
                        </div>
                    </span>
                </h5>
				<h5>Total with Tax: <div class="inline-block pull-right text-ul"><span class="total"></span></div></h5>
<!--			</div>-->
<!--            <div class="col-lg-6">-->
				<h5>Company Cost: <div class="inline-block pull-right text-ul"><span class="companyCost"></span></div></h5>
				<h5 class="hide">Extra Expenses: <div class="inline-block pull-right"><span class="extraExpenses"></span></div></h5>
				<h5 class="hide">Crew Cost: <div class="inline-block pull-right"><span class="crewsCost"></span></div></h5>
				<h5 class="hide">Equipment Cost: <div class="inline-block pull-right"><span class="equipmentsCost"></span></div></h5>
				<h5 class="hide">Overhead Cost: <div class="inline-block pull-right"><span class="overheadsCost"></span></div></h5>
				<h5 class="profitRow">Profit: <div class="inline-block pull-right text-ul profitClass">(<span class="profitPercents"></span>%)&nbsp;<span class="profit"></span></div></h5>
<!--			</div>-->
		</div>
	</div>
    <input type="hidden" name="tax_rate" class="taxRate">
    <input type="hidden" name="tax_value" class="taxValue">
    <input type="hidden" name="tax_name" class="taxName">
    <input type="hidden" class="taxOldValue" value="<?php echo isset($taxValue) ? $taxValue : getDefaultTax()['value'] ?>">
    <input type="hidden" class="taxOldName" value="<?php echo isset($taxName) ? $taxName : getDefaultTax()['name'] ?>">
    <input type="hidden" class="taxRecommendation" value="<?php echo isset($taxRecommendation) ? $taxRecommendation : null ?>">
    <input type="hidden" name="tax_label" class="taxLabelForSelect2" value="<?php echo isset($taxText) ? $taxText : getDefaultTax()['text'] ?>">
</section>
<div class="clearfix"></div>
