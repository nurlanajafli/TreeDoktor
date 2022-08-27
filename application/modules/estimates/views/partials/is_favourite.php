    <?php
        $class = 'favourite-icon-service';
        if(!empty($type)){
            if($type == 'product')
                $class = 'favourite-icon-product';
            if($type == 'bundle')
                $class = 'favourite-icon-bundle';
        }
    ?>
    <div class="control-group">
        <div class="controls">
            <label class="control-label">
                <input type="checkbox" class="favourite-checkbox" name="is_favourite" data-item_id="<?php if(!empty($item)) echo $item->service_id ?>" <?php if(!empty($item) && $item->service_is_favourite == 1) {echo 'checked';} ?>> Is Favourite
            </label>
        </div>
    </div>
    <div class="controls favourite-icons-<?php if(!empty($item)) echo $item->service_id ?>" <?php if(empty($item) || $item->service_is_favourite != 1 ): ?> hidden <?php endif; ?>>
        <label class="control-label">Choose the icon:</label><br>
        <label class="control-label"><span class="first-letters <?= $class ?>"><?php if(!empty($item)) echo strtoupper($item->favouriteShortcut); ?></span> <input
                    type="radio" name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                    value="first-letters" <?php if (!empty($item) && ($item->service_favourite_icon == null || $item->service_favourite_icon == 'first-letters') || empty($item)) { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon tree-removal <?= $class ?>"></span> <input type="radio"
                                                                                              name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                              value="tree-removal" <?php if ( !empty($item) && $item->service_favourite_icon == 'tree-removal') { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon arborist-report <?= $class ?>"></span> <input type="radio"
                                                                                                 name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                                 value="arborist-report" <?php if (!empty($item) && $item->service_favourite_icon == 'arborist-report') { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon root-fertilizing <?= $class ?>"></span> <input type="radio"
                                                                                                  name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                                  value="root-fertilizing" <?php if (!empty($item) && $item->service_favourite_icon == 'root-fertilizing') { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon planting-service <?= $class ?>"></span> <input type="radio"
                                                                                                  name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                                  value="planting-service" <?php if (!empty($item) && $item->service_favourite_icon == 'planting-service') { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon pruning-service <?= $class ?>"></span> <input type="radio"
                                                                                                 name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                                 value="pruning-service" <?php if (!empty($item) && $item->service_favourite_icon == 'pruning-service') { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon stump-grinding <?= $class ?>"></span> <input type="radio"
                                                                                                name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                                value="stump-grinding" <?php if (!empty($item) && $item->service_favourite_icon == 'stump-grinding') { ?> checked="checked" <?php } ?>></label>
        <label class="control-label"><span class="favourite-icon christmas-tree <?= $class ?>"></span> <input type="radio"
                                                                                                name="favourite_icon_<?php if(!empty($item)) echo $item->service_id; ?>"
                                                                                                value="christmas-tree" <?php if (!empty($item) && $item->service_favourite_icon == 'christmas-tree') { ?> checked="checked" <?php } ?>></label>

    </div>
