<?php if(!empty($item)) : ?>
    <li class="dd-item item dd3-item" data-id="<?= $item['service_id']; ?>" data-parent_id="<?= $category['category_id']; ?>"
        <?php $style = $item['service_status'] ? '' : ' style="text-decoration: line-through;"'; ?> >
        <div class="dd-handle test dd3-handle">&nbsp;</div>
        <div class="dd3-content">

            <div class="service-name"<?= $style; ?>>
                <i class="fa fa-wrench text-warning fa-lg m-r-sm"></i>
                <?= $item['service_name']; ?> (<?php echo $item['service_markup'] ? $item['service_markup'] . '%' : '—'; ?>)&nbsp;
                <div class="actionButtons pull-right" >
                    <a class="btn btn-xs btn-default showHideDesc dd-nodrag" style="padding: 1px 6px;"><i class="fa fa-angle-down"></i></a>
                    <?php $CI->load->view('qb/partials/qb_logs', ['module' => 'item', 'entityId' => $item['service_id'], 'entityQbId' => $item['service_qb_id'], 'lastQbTimeLog' => $item['service_last_qb_time_log'], 'lastQbSyncResult' => $item['service_last_qb_sync_result'], 'class' => 'pull-right m-left-10']); ?>
                    <a class="btn btn-xs btn-default" style="margin-left: 5px" href="#service-<?= $item['service_id']; ?>"  role="button" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                        <i class="fa fa-pencil"></i>
                    </a>
                    <a class="btn btn-xs btn-info deleteService dd-nodrag" style="margin-left: 5px" data-service_id="<?php echo $item['service_id']; ?>">
                        <i class="fa <?php if ($item['service_status']) : ?>fa-eye-slash<?php else : ?>fa-eye<?php endif; ?>"></i>
                    </a>

                </div>
                <div class="clear"></div>
            </div>
            <div class="service-desc" style="display:none; "><br><?= $item['service_description']; ?></div>

        </div>
    </li>
<?php endif; ?>