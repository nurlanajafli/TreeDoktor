<ul class="list-group alt bg-white m-n">
  <?php if(!empty($results)) : //var_dump($results); die; ?>
    <?php foreach ($results as $key => $value) : ?>
      <li class="list-group-item gsearch_result" style="cursor: pointer; max-width: 400px;" title="<?php echo $value->item_name; ?>">
        <?php //$value->item_no = $value->item_module_name == 'clients' ? 'client/' . $value->item_no : $value->item_no; ?>
        <a class="searchLink" href="<?php echo base_url($value->item_no); ?>" target="_blank">
          <div class="media">
            <span class="pull-left thumb-sm">
              <span class="block img-circle text-center bg-dark" style="padding: 6px;line-height: 24px;" >
                <?php $avatar = 'NA'; ?>
                <?php $name = explode(' ', htmlentities($value->item_name)); ?>
                <?php $avatar = count($name) > 1 ? strtoupper(substr($name[0], 0, 1) . substr($name[1], 0, 1)) : strtoupper(substr($value->item_name, 0, 2));//countOk ?>
                <?php $avatar = $avatar && strlen($avatar) == 2 ? $avatar : 'NA'; ?>
                <?php echo $avatar; ?>
              </span>
            </span>
            <div class="pull-right text-muted m-t-sm">
              <i class="fa fa-chevron-right"></i>
            </div>
            <div class="media-body">
              <small class="block" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo ucfirst(rtrim($value->item_module_name, 's')); ?>
                <?php echo $value->item_title; ?>, 
                <?php echo $value->item_address; ?>
                  <?php if (isset($value->total) && $value->total) : echo money(round($value->total, 2)); endif; ?>
              </small>
              <small class="text-muted text-ul block" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo numberTo($value->item_phone); ?> - <?php echo $value->item_cc_name; ?></small>
            </div>
          </div>
        </a>
      </li>
    <?php endforeach; ?>
  <?php else : ?>
    <li class="list-group-item text-center">No Records Found</li>
  <?php endif; ?>
</ul>
