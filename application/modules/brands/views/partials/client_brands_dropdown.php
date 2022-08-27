<?php $active_brands = array_filter((isset($brands))?$brands->toArray():[], function($item){ return ($item['deleted_at']===NULL); }); ?>
<?php if(isset($brands) && (count($brands)==1 || count($active_brands)==1)): ?>
    <input type="hidden" name="client_brand_id" value="<?php echo (isset($client_data) && $client_data->client_brand_id)?$client_data->client_brand_id:$brands[0]->b_id; ?>">
<?php else: ?>
    <select autocomplete="off" class="<?php echo (isset($class))?$class:''; ?> client-brand-select" id="brand-select" name="client_brand_id" style="<?php echo (isset($brand_style))?$brand_style:''; ?>">
    <?php if(isset($brands) && $brands): ?>
        <?php $selected = ''; ?>
        <?php foreach($brands as $brand_key => $brand):
            
            $selected = ((isset($client_data) && $client_data->client_brand_id==$brand->b_id) || (int)$this->input->post('client_brand_id')==$brand->b_id || (!isset($client_data) && !$this->input->post('client_brand_id') && $brand->b_is_default))?'selected="selected"':'';
        ?>
            <?php if(!$brand->deleted_at || ($brand->deleted_at && $selected)): ?>
            <option <?php if(isset($client_data) && isset($client_data->client_id)): ?>data-client_id="<?php echo $client_data->client_id; ?>" <?php endif; ?> <?php echo $selected; ?> data-url="<?php echo $brand->main_logo; ?>" value="<?php echo $brand->b_id; ?>" <?php if($brand->deleted_at): ?>data-deleted="true"<?php endif; ?>>
                <?php echo $brand->b_name; ?>
            </option>
            <?php endif; ?>

        <?php $selected = ''; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    </select>

    <?php if(isset($client_data) && isset($client_data->client_id)): ?>
    <form id="update-client-brand" data-type="ajax" data-url="<?php echo base_url('/clients/update_brand'); ?>" data-callback="BrandUi.update_brand_callback">
        <input type="hidden" name="brand_client_id">
        <input type="hidden" name="client_brand_id">
    </form>
    <?php endif; ?>

<?php endif; ?>