<div class="estimateTotalCalculations">
    <div class=" col-md-5 col-lg-6 col-xs-12 col-sm-12">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
				<h5 >Discount:
					<div class="inline-block pull-right text-ul">
						<span style="cursor: pointer;" data-toggle="popover" data-html="true" data-placement="top" class="discountPopover"
						   data-content="<div class='row discountPopoverContent'>
						   <div class='col-sm-8' style='padding: 0 15px 0 10px; visibility:hidden;'>
								<input type='text' value='' id='amountDiscount' placeholder='Amount' class='form-control'>
						   </div>
						   <div class='col-sm-2' style='padding: 0 0 0 15px; visibility:hidden;'>
								<label class='checkbox p-n'>
									<input type='checkbox' id='percentsDiscount' value='1'>%
								</label>
							</div>
							<div class='col-sm-1' style='padding: 10px 0 0 5px; visibility:hidden;'>
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
                <h5 class="overflow-ellipsis" style="padding-top: 2px">Tax
                    <span class="taxLabel overflow-ellipsis" title="<?php echo $taxText; ?>"><?php echo $taxText; ?></span> :

                    <div class="inline-block pull-right popover-markup text-ul" >
                        <span class="tax trigger" style="cursor: pointer"></span>
                        <div class="head hide">Tax selection <button type="button" class="close pull-right" data-dismiss="popover">×</button></div>
                        <div class="content hide">
                            <input class="select2 form-data select2Tax" id="tax" style="min-width: 156px;" type="text" value="<?php echo $taxText; ?>" data-href='#allTaxes'>
                            <div class="form-check">
                                <label class="form-check-label" for="taxIncluded">
                                    HST included in the price
                                </label>
                                <input class="form-check-input" id="inclTax" type="checkbox">
                            </div>
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



<!--            <div class="col-lg-6 col-md-6">-->
				<h5 style="padding-top: 2px">Company Cost: <div class="inline-block pull-right text-ul"><span class="companyCost"></span></div></h5>
				<h5 class="hide">Extra Expenses: <div class="inline-block pull-right"><span class="extraExpenses"></span></div></h5>
				<h5 class="hide">Crew Cost: <div class="inline-block pull-right"><span class="crewsCost"></span></div></h5>
				<h5 class="hide">Equipment Cost: <div class="inline-block pull-right"><span class="equipmentsCost"></span></div></h5>
				<h5 class="hide">Overhead Cost: <div class="inline-block pull-right"><span class="overheadsCost"></span></div></h5>
				<h5 class="profitRow overflow-ellipsis" style="padding-top: 1px" title="Profit">Profit: <div class="inline-block pull-right text-ul profitClass">(<span class="profitPercents"></span>%)&nbsp;<span class="profit"></span></div></h5>
<!--			</div>-->
            </div>
        </div>
    </div>
    <div class="col-md-7 col-lg-6 col-xs-12 col-sm-12 p-top-5" >
		<div class="row">
            <div class="col-lg-12 col-md-12" >
                <div class="col-md-6 p-left-0 p-right-0" ><h5 title="Total For Services" class="overflow-ellipsis">Total For Services: </h5></div>
                    <div class="col-md-6 pull-right totalForServicesParent w-100" style="max-width:100px">
                        <div style="overflow: hidden;text-overflow: ellipsis;"><span class="totalForServices"></span></div>
                    </div>
                    <div class="clearfix"></div>

                <div class="col-md-6  p-left-0 p-right-0"><h5 title="Total with Tax" class="overflow-ellipsis">Total with Tax:</h5></div>
                <div class="col-md-6  pull-right totalForServicesParent w-100" style="max-width:100px">
                    <div style="overflow: hidden;text-overflow: ellipsis; "><span class="total"></span></div>
                </div>

                <div class="clearfix"></div>
                <div class="col-md-6 p-n m-bottom-5" style="max-width:120px">
                    <input <?php if(isset($estimate_data) && $estimate_data->estimate_id):?> type="hidden" <?php else:?> type="button" <?php endif;?> class="btn btn-info pdf-preview w-100 disabled" value="Preview PDF"/>
                </div>

                <div class="col-md-6 p-n pull-right m-bottom-5" style="max-width:100px">
                    <button type="submit" disabled class="btn btn-success disabled  saveForm  w-100">Save</button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
	</div>
    <input type="hidden" name="estimate_planned_company_cost" class="companyCostInput">
    <input type="hidden" name="estimate_planned_crews_cost" class="crewsCostInput">
    <input type="hidden" name="estimate_planned_equipments_cost" class="equipmentsCostInput">
    <input type="hidden" name="estimate_planned_extra_expenses" class="extraExpensesInput">
    <input type="hidden" name="estimate_planned_overheads_cost" class="overheadsCostInput">
    <input type="hidden" name="estimate_planned_profit" class="profitInput">
    <input type="hidden" name="estimate_planned_profit_percents" class="profitPercentsInput">
    <input type="hidden" name="estimate_planned_tax" class="taxInput">
    <input type="hidden" name="estimate_planned_total" class="totalInput">
    <input type="hidden" name="estimate_planned_total_for_services" class="totalForServicesInput">
    <input type="hidden" name="tax_rate" class="taxRate">
    <input type="hidden" name="tax_value" class="taxValue">
    <input type="hidden" name="tax_name" class="taxName">
    <input type="hidden" class="taxOldValue" value="<?php echo isset($taxValue) ? $taxValue : ($defaultTax['value'] ?? 0) ?>">
    <input type="hidden" class="taxOldName" value="<?php echo isset($taxName) ? $taxName : ($defaultTax['name'] ?? 'default') ?>">
    <input type="hidden" class="taxRecommendation" value="<?php echo isset($taxRecommendation) ? $taxRecommendation : null ?>">
    <input type="hidden" name="tax_label" class="taxLabelForSelect2" value="<?php echo isset($taxText) ? $taxText : getDefaultTax()['text'] ?>">
    <input type="hidden" name="estimate_hst_disabled" value="<?= isset($estimate_data) && $estimate_data->estimate_hst_disabled ? $estimate_data->estimate_hst_disabled : 0 ?>">
</div>

<script>
    $(document).ready(function(){
        $('body').on('hidden.bs.popover', function (e) {
            $(e.target).data("bs.popover").inState.click = false;
        });    
    });
</script>
<!--<script src="--><?php //echo base_url(); ?><!--assets/vendors/notebook/js/select2/select2.min.js"></script>-->
