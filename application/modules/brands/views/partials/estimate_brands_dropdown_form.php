<?php if(isset($estimate_data) && isset($estimate_data->estimate_id)): ?>
<form id="update-estimate-brand" data-type="ajax" data-global="false" data-url="<?php echo base_url('/estimates/update_brand'); ?>" data-callback="BrandUi.update_brand_callback">
    <input type="hidden" name="estimate_id">
    <input type="hidden" name="estimate_brand_id">
</form>
<?php endif; ?>