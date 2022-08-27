<?php $active_brands = array_filter((isset($brands))?$brands->toArray():[], function($item){ return ($item['deleted_at']===NULL); }); ?>
<?php if(isset($brands) && (count($brands)==1 || count($active_brands)==1)): ?>
    <input type="hidden" name="estimate_brand_id" value="<?php echo (isset($estimate_data) && $estimate_data->estimate_brand_id)?$estimate_data->estimate_brand_id:$brands[0]->b_id; ?>">
<?php else: ?>
    <select class="<?php echo (isset($class))?$class:''; ?>" id="estimate-brand-select" name="estimate_brand_id" style="<?php echo (isset($brand_style))?$brand_style:''; ?>">
    <?php if(isset($brands) && $brands): ?>
        <?php $selected = ''; ?>
        <?php foreach($brands as $brand_key => $brand): ?>
            
            <?php $selected = ((isset($estimate_data) && $estimate_data->estimate_brand_id==$brand->b_id) || (!isset($estimate_data->estimate_brand_id) && (!isset($client_data->client_brand_id) || !$client_data->client_brand_id) && $brand->b_is_default) || (!isset($estimate_data->estimate_brand_id) && isset($client_data->client_brand_id) && $client_data->client_brand_id && $client_data->client_brand_id == $brand->b_id))?'selected="selected" selected':''; ?>

            <?php if(!$brand->deleted_at || ($brand->deleted_at && $selected)): ?>
            <option <?php if(isset($estimate_data) && isset($estimate_data->estimate_id)): ?>data-estimate_id="<?php echo $estimate_data->estimate_id; ?>"<?php endif; ?> <?php echo $selected; ?> data-url="<?php echo $brand->main_logo; ?>" value="<?php echo $brand->b_id; ?>" <?php if($brand->deleted_at): ?>data-deleted="true"<?php endif; ?>>
                <?php echo $brand->b_name; ?>
            </option>
            <?php endif; ?>

        <?php $selected = ''; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    </select>
    <style type="text/css">#s2id_estimate-brand-select .select2-choice{padding: 0;}</style>
<?php endif; ?>