<!-- Modal -->
<div class="modal fade" id="category-delete-<?= isset($category) ? $category['category_id'] : ''?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete category <strong><?= isset($category) ? $category['category_name'] : ''?><strong></strong></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this category? Any products, services, or categories in it will be <?php if(!empty($parentName)) : ?> moved to "<?=  $parentName; ?> " <?php  else : ?>uncategorized. <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" data-id="<?= isset($category) ? $category['category_id'] : ''?>" class="btn btn-danger deleteCategory">Delete</button>
            </div>
        </div>
    </div>
</div>