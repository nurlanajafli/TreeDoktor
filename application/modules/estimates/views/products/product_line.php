<?php if(!empty($item)) : ?>
<li class="dd-item item dd3-item" data-id="<?= $item['service_id']; ?>" data-parent_id="<?= $category['category_id']; ?>"
    <?php $style = $item['service_status'] ? '' : ' style="text-decoration: line-through;"'; ?> >
    <div class="dd-handle test dd3-handle">&nbsp;</div>
    <div class="dd3-content">

        <div class="service-name"<?= $style; ?>>
            <i class="fa fa-gift text-success fa-lg m-r-sm"></i>
            <?= $item['service_name']; ?> (<?= $item['cost'] ?  money($item['cost']) : money(0); ?>)&nbsp;
            <div class="actionButtons pull-right" >
                <a class="btn btn-xs btn-default showHideDesc dd-nodrag" style="padding: 1px 6px;"><i class="fa fa-angle-down"></i></a>
                <?php $CI->load->view('qb/partials/qb_logs', ['module' => 'item', 'entityId' => $item['service_id'], 'entityQbId' => $item['service_qb_id'], 'lastQbTimeLog' => $item['service_last_qb_time_log'], 'lastQbSyncResult' => $item['service_last_qb_sync_result'], 'class' => 'pull-right m-left-10']); ?>

                <form id="delete-product-<?= $item['service_id']; ?>" data-url="<?= base_url('estimates/products/delete'); ?>" data-type="ajax" data-location="<?= base_url('estimates/products'); ?>" class="pull-right m-left-10">
                    <input type="hidden" name="service_id" value="<?= $item['service_id']; ?>">
                    <input type="hidden" name="status" value="<?= $item['service_status']; ?>">

                    <button class="btn btn-xs btn-info deleteService" data-href="#delete-product-<?= $item['service_id']; ?>" data-title="<?= $item['service_name']; ?>">
                        <i class="fa <?php if ($item['service_status']) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i>
                    </button>
                </form>
                <form data-url="<?= base_url('estimates/products/edit'); ?>" data-type="ajax" data-callback="window.edit_modal" class="pull-right m-left-10">
                    <input type="hidden" name="service_id" value="<?= $item['service_id']; ?>">
                    <button class="btn btn-xs btn-default"><i class="fa fa-pencil"></i></button>
                </form>
            </div>
            <div class="clear"></div>
        </div>
        <div class="service-desc" style="display:none; "><br><?= $item['service_description']; ?></div>

    </div>
</li>
<?php endif; ?>