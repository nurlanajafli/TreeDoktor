<?php foreach ($records as $record) : $randRecord = rand(10, 10000);

//
//    $this->load->view('products/product_tpl', ['service_data' => (object)$record]);
    ?>
    <div class="p-left-30 p-top-10" style="border-bottom: 0.5px solid #d9d9d9; padding-bottom: 15px;"
         data-bundleId="<?= !empty($record['bundle_id']) ? $record['bundle_id'] : $bundleId ?>">

        <i class="fa fa-wrench text-warning fa-lg m-r-sm"></i>
        <i class="fa fa-gift text-success fa-lg m-r-sm"></i>

        <span class="serviceTitle text-dark"><?= !empty($record['service_name']) ? $record['service_name'] : '' ?>
        </span>


        <span class="non-taxable-title"
              id="service-checkbox-<?= isset($record['record_id']) ? $record['record_id'] : $randRecord ?>-title" <?php if (isset($record) && $record['non_taxable'] == 0 || !isset($record)) : ?> hidden <?php endif; ?> >(Non-taxable)</span>

        <a class="toggleChevron" style="cursor:pointer" data-toggle="collapse" href="#collapse<?= $randRecord ?>" aria-expanded="false">
            <i class="fa fa-chevron-down" aria-hidden="true"></i>
        </a>

        <?php if (isset($record['record_id'])) : ?>
            <input type="hidden" name="non_taxable[<?php echo $record['record_id']; ?>]"
                   id="service-checkbox-<?php echo $record['record_id']; ?>"
                   value="<?php echo $record['non_taxable']; ?>" class="nonTaxableHidden">
            <input type="hidden" name="id[<?php echo $record['record_id']; ?>]"
                   value="<?php echo $record['record_id']; ?>">
            <input type="hidden" class="form-control" name="service_type_id[<?php echo $record['record_id']; ?>]"
                   value="<?php echo $record['record_id']; ?>">
            <input type="hidden" name="is_bundle[<?php echo $record['record_id']; ?>]" value="1">
        <?php else : ?>
            <input type="hidden" data-name="is_bundle" data-is_bundle="1" value="1">
            <input type="hidden" class="form-control" data-name="service_type_id" data-service_type_id="1" name="">
            <input type="hidden" data-name="non_taxable" value="0" class="nonTaxableHidden"
                   id="service-checkbox-<?= $randRecord ?>"/>
        <?php endif; ?>
        <input type="hidden" data-name="record_id"
               value="<?= !empty($record['record_id']) ? $record['record_id'] : '' ?>" name="record_id"/>

        <a class="m-l-sm btn btn-danger btn-xs pull-right m-right-10" onclick="deleteBundleRecord(this)"
           data-bundleId="<?= !empty($record['bundle_id']) ? $record['bundle_id'] : $bundleId ?>">
            <i class="fa fa-trash-o"></i>
        </a>

        <div class="form-group m-m pull-right m-b-none recordSum m-t-n-xs">
            <div class="form-group m-m m-b-none">
                <div class="inline font-bold text-ul text-dark p-right-5 taxable" style="cursor: pointer">Price</div>
                <div class="inline" style="">
                    <input style="width: 100px;" placeholder="Price"
                           data-bundleId="<?= !empty($record['bundle_id']) ? $record['bundle_id'] : $bundleId ?>"
                           onchange="getBundlePrice(this)"
                           class="costBundleRecord form-control input-mini text-center currency" type="text"
                           value="<?= !empty($record['estimate_bundle_record_cost']) ? $record['estimate_bundle_record_cost'] *  $record['estimate_bundle_record_qty'] : 0 ?>"/>
                </div>

                <div class="form-check pull-right m-left-10 form-check-inline content hide">
                    <label class="form-check-label check-tax" for="nonTaxable"><strong>Non-taxable</strong></label>
                    <input type="checkbox" class="form-check-input big-checkbox check-tax nonTaxable"
                           data-id="#service-checkbox-<?= isset($record['record_id']) ? $record['record_id'] : $randRecord ?>">
                </div>
            </div>
        </div>

        <div class="form-group m-m pull-right m-b-none recordSum m-t-n-xs p-right-20">
            <div class="form-group m-m m-b-none">
                <div class="inline font-bold text-dark p-right-5">Cost</div>
                <div class="inline" style="">
                    <input style="width: 100px;" placeholder="Cost"
                           data-bundleId="<?= !empty($record['bundle_id']) ? $record['bundle_id'] : $bundleId ?>"
                           onchange="getBundlePrice(this)"
                           class="costBundleRecord form-control input-mini text-center currency" type="text"
                           value="<?= !empty($record['estimate_bundle_record_cost']) ? $record['estimate_bundle_record_cost'] : 0 ?>"/>
                </div>
            </div>
        </div>

        <div class="form-group m-m pull-right m-b-none recordSum m-t-n-xs p-right-20">
            <div class="form-group m-m m-b-none">
                <div class="inline font-bold text-dark p-right-5">QTY</div>
                <div class="inline" style="">
                    <input style="width: 50px;"
                           data-bundleId="<?= !empty($record['bundle_id']) ? $record['bundle_id'] : $bundleId ?>"
                           onchange="getBundlePrice(this)"
                           class="integer qtyBundleRecord form-control input-mini text-center" type="text"
                           value="<?= !empty($record['estimate_bundle_record_qty']) ? $record['estimate_bundle_record_qty'] : 1 ?>"/>
                </div>
            </div>
        </div>

        <div class="collapse" id="collapse<?= $randRecord ?>">
            <div>
                <div class="col-sm-12 b-r form-group m-m m-b-none serviceTop" style="border: 0;">
                    <div class="row pos-rlt">
                        <div class="col-md-12 col-xs-12 form-group m-m pull-left m-b-none p-n serviceDescriptionBlock">
                            <textarea class="form-control serviceDescription m-t-n-xxs"
                                      placeholder="Description"><?= !empty($record['estimate_bundle_record_description']) ? $record['estimate_bundle_record_description'] : '' ?></textarea>
                        </div>
                    </div>

                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
    </div>
<?php endforeach; ?>