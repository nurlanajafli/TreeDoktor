<link rel="stylesheet" href="<?= base_url('assets/vendors/notebook/js/nestable/nestable.css'); ?>" type="text/css" />
<script src="<?= base_url('assets/vendors/notebook/js/nestable/jquery.nestable.js'); ?>"></script>
<script src="<?= base_url('assets/js/modules/estimates/category.js'); ?>"></script>

    <?php function categoriesWithItems($categories, $CI, $categoryParentName = null){ ?>
        <?php foreach ($categories as $category): ?>
            <li class="dd-item category dd3-item" data-id="<?= $category['category_id'] ?>" data-parent_id="<?= $category['category_parent_id'] ?>">
                <div class="dd-handle dd3-handle">&nbsp;</div>
                <div class="dd3-content" >
                    <i class="fa fa-folder text-info fa-lg m-r-sm"></i>
                    <strong <?php  if($category['category_active'] == 0): ?> style="text-decoration: line-through;" <?php endif; ?>><?= $category['category_name'] ?></strong>

                    <div class="actionButtons pull-right" >

                        <?php $CI->load->view('qb/partials/qb_logs', ['module' => 'class', 'entityId' => $category['category_id'], 'entityQbId' => $category['category_qb_id'], 'lastQbTimeLog' => $category['category_last_qb_time_log'], 'lastQbSyncResult' => $category['category_last_qb_sync_result'], 'class' => 'pull-right m-left-10']); ?>

                        <?php if($category['category_id'] != 1): ?>
                            <button class="btn btn-xs btn-info pull-right m-left-10" href="#category-delete-<?= $category['category_id']?>" data-title="<?= $category['category_name']; ?>" data-toggle="modal">
                                <i class="fa fa-eye-slash"></i>
                            </button>
                        <?php endif; ?>

                        <a href="#category-modal-<?= $category['category_id']?>" class="btn btn-xs btn-default pull-right" role="button" data-toggle="modal"
                           data-backdrop="static" data-keyboard="false"> <i class="fa fa-pencil"></i></a>

                    </div>
                </div>
            <ol class="dd-list">
                <?php if(isset($category['products'])) : ?>
                    <?php foreach ($category['products'] as $item): ?>
                        <?php $CI->load->view('estimates/products/product_line', ['item' => $item, 'category' => $category, 'CI' => $CI]); ?>
                    <?php endforeach; ?>
                    <?php if(!empty($category['categories_with_products'])){categoriesWithItems($category['categories_with_products'], $CI, $category['category_name']);} ?>
                <?php else: ?>
                    <?php foreach ($category['services'] as $item): ?>
                        <?php $CI->load->view('estimates/partials/service_line', ['item' => $item, 'category' => $category, 'CI' => $CI]); ?>
                    <?php endforeach; ?>
                        <?php if(!empty($category['categories_with_services'])){categoriesWithItems($category['categories_with_services'], $CI, $category['category_name']);} ?>
                <?php endif; ?>
            </ol>
            </li>
            <div id="category-modal-<?= $category['category_id']?>" class="modal fade categoryModal" tabindex="-1" data-id="<?= $category['category_id']?>" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content panel panel-default p-n">
                        <?php $CI->load->view('categories/partials/category_form', ['category' => $category]); ?>
                    </div>
                </div>
            </div>
            <?php $CI->load->view('partials/category_delete_modal', ['category' => $category, 'parentName' => $categoryParentName]) ?>
    <?php endforeach; ?>
    <?php }?>
<div class="dd" id="nestable" style="max-width: none">
    <?php if(!empty($categories)): ?>
        <ol class="dd-list outer">
            <?php categoriesWithItems($categories, $this) ?>
        </ol>
    <?php endif; ?>
<!--    <input type="hidden" value='--><?//= json_encode($categoriesWithChildren) ?><!--' class="categoriesWithChildren">-->
<!--    <input type="hidden" value='--><?//= !empty($classes) ? json_encode($classes) : "" ?><!--' class="classWithChildren">-->
</div>

<script>
    let categoriesWithChildren = <?= !empty($categoriesWithChildren) ? json_encode($categoriesWithChildren) : '[]' ?> ;
    let classWithChildren = <?= !empty($classes) ? json_encode($classes) : '[]' ?> ;
</script>