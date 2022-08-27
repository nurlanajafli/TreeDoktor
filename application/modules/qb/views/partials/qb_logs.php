 <?php if(!empty($access_token)) : ?>
<a href="#" class="qbLog btn btn-lg p-right-5
                            <?php if(!empty($class)) {echo $class; } ?>
                            <?php if(!empty($lastQbSyncResult) && $lastQbSyncResult == 1 && !empty($entityQbId)) : ?> qb-svg-success
                            <?php elseif(!empty($lastQbSyncResult) && $lastQbSyncResult == 2) : ?>  qb-svg-danger
                            <?php else : ?> qb-svg-secondary
                            <?php endif; ?>" data-id="<?= $entityId ?>" data-container="body" id="popover-<?= $entityId ?>" data-toggle="popover" data-module="<?= $module ?>" data-placement="left" role="button">

</a>
<!-- loaded popover content -->
<div id="popover-content-<?= $entityId ?>" style="display: none;">
    <div class="btn-group w-100">
        <?php if($this->session->userdata('user_type') == "admin") : ?>
        <button type="button" class="btn btn-info dropdown-toggle w-100" data-toggle="dropdown" aria-expanded="false" onclick="">
            Manual sync <span class="caret">
        </button>
        <div class="dropdown-menu p-left-5 w-100 sync"
             data-id="<?= $entityId ?>"
             data-module="<?= $module ?>" style="text-align: center">
            <?php if(empty($bundle)): ?>
                <a href="#" class="btn btn-sm btn-primary inQB
                    <?php if(!empty($lastQbTimeLog) && \Carbon\Carbon::parse($lastQbTimeLog)->timestamp + 60*60 >= \Carbon\Carbon::now()->timestamp) : ?> disabled <?php endif; ?>"
                   onclick="sync(this)"><i class="fa fa-cloud-upload text">
                    </i> Push  </a>
            <?php endif; ?>
            <a href="#" class="btn btn-sm btn-info fromQB
                    <?php if(empty($entityQbId) || !empty($lastQbTimeLog) &&  \Carbon\Carbon::parse($lastQbTimeLog)->timestamp + 60*60 >= \Carbon\Carbon::now()->timestamp): ?> disabled <?php endif; ?>"
               onclick="sync(this)">
                <i class="fa fa-cloud-download text">
                </i> Pull</a>

        </div>
        <?php endif; ?>
    </div>
    <div class="logs"></div>
</div>
<?php endif;?>
