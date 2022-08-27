<section class="col-md-12 panel panel-default p-left-0 p-right-0 p-bottom-0 m-n bundle bundleTotal" data-markup="<?php if(isset($service_data) && isset($service_data->service->service_markup)) echo $service_data->service->service_markup; ?>" data-service_id="<?php if(isset($service_data) && isset($service_data->id)) echo $service_data->id; else echo '0'; ?>">
    <header class="panel-heading p-right-15">
        <span class="serviceTitle font-bold"><?php if(isset($service_data) && isset($service_data->service->service_name)) echo $service_data->service->service_name; else echo 'UNDEFINED'; ?></span>

        <a class="btn btn-danger btn-xs deleteBundle pull-right m-top-5" data-service_id="<?php if(isset($service_data)) echo $service_data->id; ?>">
			<i class="fa fa-trash-o"></i>
		</a>
        <div style="display: flex; align-items: center;" class="pull-right p-right-15">
            <?php if(isset($products) && !empty($products)): ?>
                <div class="btn-group dropdown m-left-10">
                    <div  class="product-select2-bundle-toggle text-success" data-toggle="dropdown" style="cursor:pointer;text-decoration: underline;">+ Add Product to bundle<span class="caret"></span></div>
                    <input type="hidden" class="productSelect w-100 selectServiceItem product-select2-bundle bundle" data-bundle_id="<?php echo isset($service_data) ? $service_data->id : ''; ?>"/>
                </div>
            <?php endif;?>
            <div class="btn-group dropdown m-left-20 m-right-50">
                <div class="service-select2-bundle-toggle" style="color: #ffc333; cursor:pointer;text-decoration: underline;" data-toggle="dropdown" >+ Add Service to bundle<span class="caret"></span></div>
                <input type="hidden" class="selectServices w-100 selectServiceItem service-select2-bundle bundle" data-bundle_id="<?php echo isset($service_data) ? $service_data->id : ''; ?>"/>
            </div>
            <div class="inline m-right-10"><strong>QTY</strong></div>
            <div class="inline" style="">
                <input style="width: 50px;"
                       class="integer bundleQty form-control input-mini text-center"
                       type="text" <?php if (!isset($service_data)) : ?> data-name="quantity" name="" value="1" <?php else : ?> name="quantity[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->quantity; ?>"
                    data-service_id="<?php echo $service_data->id; ?>"
                <?php endif; ?>>
            </div>
        </div>
        <div style="  align-items: center">
            <input type="checkbox" id="isView-<?php echo isset($service_data) ? $service_data->id : '' ?>" style="margin-top: 0px !important;" name="is_view_in_pdf[<?php echo isset($service_data) ? $service_data->id : '' ?>]" <?php if(isset($service_data) && !empty($service_data->is_view_in_pdf)) : ?> checked <?php endif; ?>>
            <label class="form-check-label overflow-ellipsis" style="margin-bottom: 0px !important; margin-left: 5px" title="Display bundle components when printing or sending transactions" for="isView-<?php echo isset($service_data) ? $service_data->id : '' ?>">Display bundle components when printing or sending transactions</label>
        </div>

		<?php if(isset($service_data->id)) : ?>
            <input type="hidden" name="non_taxable[<?php echo $service_data->id; ?>]"  id="service-checkbox-<?php echo $service_data->id; ?>" value="<?php echo $service_data->non_taxable; ?>" class="nonTaxableHidden">
			<input type="hidden" name="id[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->id; ?>">
			<input type="hidden" class="form-control" name="service_type_id[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_id; ?>" data-service_id="<?php echo $service_data->id; ?>">
			<input type="hidden" name="is_bundle[<?php echo $service_data->id; ?>]" value="1" data-service_id="<?php echo $service_data->id; ?>">
            <input type="hidden" data-name="bundle_qty" value="<?php echo $service_data->quantity; ?>">
            <input type="hidden" name="service_priority[<?php echo $service_data->id; ?>]" value="<?= $service_data->service_priority ?? 0 ?>">
        <?php else : ?>
			<input type="hidden" data-name="is_bundle" data-is_bundle="1" value="1">
			<input type="hidden" class="form-control" data-name="service_type_id" data-service_type_id="1" name="">
            <input type="hidden" data-name="non_taxable" value="0" class="nonTaxableHidden">
            <input type="hidden" data-name="bundle_records">
            <input type="hidden" data-name="bundle_qty" value="1">
            <input type="hidden" data-name="service_priority" value="1">
		<?php endif; ?>
	</header>

	<div>
		<div class="col-sm-12 b-r form-group m-m m-b-none serviceTop" style="border: 0;">
            <div class="row pos-rlt">
                <div class="col-md-12 col-xs-12 form-group m-m pull-left m-b-none p-n serviceDescriptionBlock">
                    <textarea   class="form-control serviceDescription m-t-n-xxs " placeholder="Service Description" <?php if(!isset($service_data)) : ?>data-name="service_description" name=""<?php else : ?> name="service_description[<?php echo $service_data->id; ?>]" data-service_id="<?php echo $service_data->id; ?>" <?php endif; ?>><?php if(isset($service_data)) echo $service_data->service_description; ?></textarea>
                </div>
            </div>

		</div>
		<div style="clear:both;"></div>
    </div>

    <div class="bundleRecords">
        <?php
            if(!empty($service_data->bundle_records))
                foreach ($service_data->bundle_records as $record){
                    if(!empty($bundle_id))
                        $record->bundle_id = $bundle_id;
                    if($record->service->is_product)
                        $this->load->view('products/product_tpl', ['service_data' => $record]);
                    else
                        $this->load->view('service_tpl', ['service_data' => $record]);
                }
                ?>
    </div>
    <footer class="m-top-10 m-bottom-10 bundleFooter">


        <div class="form-group m-m pull-right m-b-none priceSum m-t-n-xs m-top-5" >
            <div class="form-group m-m m-b-none">
                <div class="inline font-bold text-dark">Total For Bundle</div>
                <div class="inline">
                    <input readonly style="width: 100px; border: none; background-color: white" placeholder="Price" class="form-control input-mini servicePrice bundlePrice text-center currency bg-light text-success font-bold" type="text"
                        <?php if(!isset($service_data)) : ?> data-name="service_price" name=""
                        <?php else : ?> name="service_price[<?php echo $service_data->id; ?>]" value="<?php echo $service_data->service_price; ?>"
                            data-service_id="<?php $service_data->service_id?>"
                    <?php endif; ?> >

                </div>
            </div>
        </div>
    </footer>

</section>