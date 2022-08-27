<section class="panel panel-default scrollable tree-project" style="padding-bottom: 50px;">
    <div class="row">
        <div class="col-md-12 text-center p-top-20 p-bottom-20">
            <a href="#new_project" class="btn btn-success create-project" style="border-radius: 20px" data-toggle="modal" title="Add new project"><i class="fa fa-plus"></i> Create New Tree Inventory</a>
        </div>
    </div>

    <ul class="list-group" id="tree-list-project">
        <?php if(!empty($schemes)): ?>
            <?php foreach ($schemes as $scheme): ?>
                <li class="list-group-item" style="border-top: none;" data-id="<?= $scheme->tis_id ?>">
                    <?= $this->load->view('_partials/tree_project', ['project' => $scheme]); ?>
                </li>
            <?php endforeach ?>
        <?php else: ?>
            <li class="list-group-item" style="border:none">
                <p class="text-muted h4 text-center">Tree inventory is empty</p>
            </li>
        <?php endif; ?>
    </ul>
</section>
