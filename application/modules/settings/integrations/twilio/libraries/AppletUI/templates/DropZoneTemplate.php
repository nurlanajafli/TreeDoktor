<!-- Empty Drop Zone -->

<?php if (empty($value)): ?>
    <div class="empty-item flowline-item" title="<?= $label ?>">
        <div class="item-body">
            <?= $label ?>
        </div><!-- .item-body -->
        <input type="hidden" autocomplete="off" name="<?= $name ?>" value=""/>
    </div><!-- .flowline-item -->
<?php else: ?>

    <!-- Filled Drop Zone -->
    <div class="filled-item flowline-item" title="<?= $label ?>">
        <div class="item-body">
            <a href="#flowline/<?= $value ?>" class="item-box">
                <div class="<?= $applet ?>-icon applet-icon"
                     style="background: url(<?= $icon_url ?>) no-repeat center center;">
                    <span class="replace"><?= $label ?></span>
                </div>
                <span class="applet-item-name"><?= $label ?></span>
            </a>
            <div class="flowline-item-remove fa fa-minus-circle remove-mini">
                <span class="replace">remove</span>
            </div>
        </div><!-- .item-body -->
        <input type="hidden" autocomplete="off" name="<?= $name ?>" value="<?= $value ?>"/>
    </div><!-- .flowline-item -->
<?php endif; ?>
