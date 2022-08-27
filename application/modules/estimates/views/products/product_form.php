<header class="panel-heading">
		<?php if(isset($product) && count($product)): ?>
		Edit Product
		<?php else: ?>
		Create Product
		<?php endif; ?>
	</header>
<form data-type="ajax" id="service-" data-location="<?php echo base_url('estimates/products'); ?>" data-url="<?php echo base_url('estimates/products/save'); ?> " data-callback="ItemSaveCallback" >
	<div class="modal-body">
		<div class="form-horizontal">
			
			<?php if(isset($product) && count($product)): ?>
			<input type="hidden" name="service_id" value="<?php echo isset($product)?element('service_id', $product, ''):''; ?>">
			<?php endif; ?>
			<div class="control-group">
				<label class="control-label">Product Name:</label>

				<div class="controls">
					<input class="service_name form-control" name="service_name" type="text" value="<?php echo isset($product)?htmlentities(element('service_name', $product, '')):''; ?>" placeholder="Product Name">
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Product Description:</label>

				<div class="controls">
					<textarea class="service_description form-control" name="service_description" placeholder="Product Description" rows="5"><?php echo isset($product)?htmlentities(element('service_description', $product, '')):''; ?></textarea>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">Cost:</label>
				<input class="service_cost form-control currency" name="cost" placeholder="Cost" value="<?php echo isset($product)?element('cost', $product, ''):''; ?>">
			</div>
            <div class="control-group parentCategory">
                <label class="control-label">Category</label>
                <div class="controls">
                    <input type="text" class="parentCategorySelect w-100" value="<?= !empty($product['service_category_id']) ? $product['service_category_id'] : 1 ?>" name="categoryId"/>
                </div>
            </div>
            <?php if (!empty($classes)): ?>
            <div class="control-group parentClass">
                <label class="control-label">Class</label>
                <div class="controls">
                    <input type="text" class="parentClassSelect w-100" value="<?= !empty($product['service_class_id']) ? $product['service_class_id'] : null ?>" name="classId"/>
                </div>
            </div>
            <?php endif; ?>
			<?php /*
			<button class="btn btn-xs btn-success pull-right pos-abt addAttach" style="bottom: 0px;right: 55px;" role="button"><i class="fa fa-plus"></i></button>
			*/ ?>
            <div class="control-group">
                <div class="controls">
                    <label class="control-label">
                        <input type="checkbox" name="service_collapsed_view" class="service-collapsed-view" <?php if(empty($product) || !empty($product) && $product['service_is_collapsed'] == 1) {echo 'checked';} ?>> Collapsed view
                    </label>
                </div>
            </div>
            <?php $this->load->view('partials/is_favourite', ['item' => !empty($product) ? (object)$product : null, 'type' => 'product']); ?>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn btn-success">
			<span class="btntext">Save</span>
			<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
			     class="preloader">
		</button>
		<button class="btn close-modal" data-dismiss="modal" aria-hidden="true">Close</button>
	</div>
</form>

<script>
    if(typeof categoriesWithChildren != 'undefined')
        $('.parentCategorySelect').select2({data: categoriesWithChildren});
    if(typeof classWithChildren != 'undefined')
        $('.parentClassSelect').select2({data: classWithChildren});
</script>
