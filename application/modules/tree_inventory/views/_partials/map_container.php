<section class="scrollable p-sides-15 p-n" style="position: absolute;top: 0; bottom: 50px; left: 0;
right: 0; overflow-x: auto;" id="mapper-image" <?php if(!isset($tree_inventory_map) || !isset($tree_inventory_map->tim_image) || !intval($tree_inventory_map->tim_image)): ?>style="display: none;"<?php endif; ?>>
	<div data-height="<?php echo element('tim_height', (array)$tree_inventory_map); ?>" data-width="<?php echo element('tim_width', (array)$tree_inventory_map); ?>" data-background="<?php echo isset($map_image)?$map_image:''; ?>" class="p-sides-15 p-n" id="inventory_map_image">
	</div>
</section>
<?php /*background: url(<?php echo isset($map_image)?$map_image:''; ?>) no-repeat; background-size: cover; */ ?>
<section class="scrollable p-sides-15 p-n mapper" id="inventory_map" data-scheme_lat="<?php if(isset($scheme) && !empty($scheme->tis_lat)) echo $scheme->tis_lat?>" data-scheme_lng="<?php if(isset($scheme) && !empty($scheme->tis_lng)) echo $scheme->tis_lng?>"  data-origin_lat="<?php echo config_item('office_lat'); ?>" data-origin_lon="<?php echo config_item('office_lon'); ?>" <?php if(isset($tree_inventory_map) && isset($tree_inventory_map->tim_image) && (int)$tree_inventory_map->tim_image): ?>style="display: none;"<?php endif; ?>></section>

<div class="clear"></div>