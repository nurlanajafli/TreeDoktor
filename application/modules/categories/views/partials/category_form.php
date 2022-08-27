<header class="panel-heading">
    <?php if(!empty($category)): ?>
        Edit Category
    <?php else: ?>
        Create Category
    <?php endif; ?>
</header>
<form data-type="ajax" data-url="<?php echo base_url('categories/ajaxSaveCategory'); ?>" data-location="<?php echo current_url(); ?>" data-callback="checkCategoryError">
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="control-group">
                            <label class="control-label">Name</label>
                            <div class="controls">
                                <input name="categoryName" class="category_name form-control" type="text"
                                       value="<?= !empty($category['category_name']) ? $category['category_name'] : '' ?>"
                                       placeholder="Category Name" style="background-color: #fff;">
                            </div>
                        </div>
                        <div class="control-group parentCategory">
                            <label class="control-label">Parent Category</label>
                            <div class="controls">
                                <input type="text" class="parentCategorySelect w-100" value="<?= !empty($category['category_parent_id']) ? $category['category_parent_id'] : '' ?>" name="categoryParentId"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success">
                        <span class="btntext">Save</span>
                        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
                             class="preloader">
                    </button>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
    <?php if(!empty($category['category_id'])) : ?>
        <input type="hidden" value="<?= $category['category_id'] ?>" name="categoryId">
    <?php endif; ?>
    </form>