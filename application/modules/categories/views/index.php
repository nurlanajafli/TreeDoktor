<?php $this->load->view('includes/header'); ?>
<script src="<?php echo base_url('assets/vendors/notebook/js/sortable/jquery.sortable.js'); ?>"></script>
<script src="<?= base_url('assets/vendors/notebook/js/nestable/jquery.nestable.js'); ?>"></script>
<link rel="stylesheet" href="<?= base_url('assets/vendors/notebook/js/nestable/nestable.css'); ?>" type="text/css" />
<script src="<?= base_url('assets/js/modules/category/category.js'); ?>"></script>
<style>.sortable-placeholder {
        min-height: 54px;
    }</style>
<section class="scrollable p-sides-15">
    <ul class="breadcrumb no-border no-radius b-b b-light pull-in">
        <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Categories</li>
    </ul>
    <section class="panel panel-default">
        <header class="panel-heading">Categories
            <a href="#product-modal" class="btn btn-xs btn-success pull-right bundleModal" role="button" data-toggle="modal"
               data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i></a>
        </header>
        <div class="table-responsive">
            <?php function categories($categories){ ?>
                <?php foreach ($categories as $category): ?>
                <li class="dd-item category dd3-item" data-id="<?= $category['category_id'] ?>" data-parent_id="<?= $category['category_parent_id'] ?>">
                    <div class="dd-handle dd3-handle">&nbsp;</div>
                    <div class="dd3-content font-bold">
                        <i class="fa fa-folder text-info fa-lg m-r-sm"></i>
                        <?= $category['category_name'] ?>
                    </div>

                    <ol class="dd-list">
                        <?php if(isset($category['categories'])) : ?>
                            <?php if(!empty($category['categories'])){categories($category['categories']);} ?>
                        <?php endif; ?>
                    </ol>
                </li>
            <?php endforeach; ?>
            <?php }?>
            <div class="dd" id="nestable" style="max-width: none">
                <?php if(!empty($categories)): ?>
                    <ol class="dd-list outer">
                        <?php categories($categories) ?>
                    </ol>
                <?php endif; ?>
            </div>
        </div>
    </section>
</section>