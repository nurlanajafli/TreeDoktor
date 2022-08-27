<div data-id="<?= isset($project) && !empty($project->tis_id) ? $project->tis_id : '' ?>" class="p-10 data-id" style="border: 1px solid #D6D6D6; border-radius: 8px; cursor: pointer">
    <div>
        <span><strong><?= isset($project) && !empty($project->tis_name) ? $project->tis_name : '' ?></strong></span>
        <?php if(isset($edit)) :?>
            <a href="#new_project" class="pull-right text-success project-edit" data-id="<?= isset($project) && !empty($project->tis_id) ? $project->tis_id : '' ?>" data-toggle="modal"><i class="fa fa-pencil"></i></a>
        <?php else: ?>
            <a href="#new_project" class="pull-right text-success copy-project" data-id="<?= isset($project) && !empty($project->tis_id) ? $project->tis_id : '' ?>" data-toggle="modal"><i class="fa fa-copy "></i></a>
        <?php endif ?>
    </div>
    <div class="p-top-5">
        <?php if(isset($project) && !empty($project->tis_address)) : ?>
            <?= $project->tis_address ?>
            <?php if(!empty($project->tis_city)): ?>, <?= $project->tis_city; endif ?>
            <?php if(!empty($project->tis_state)): ?>, <?= $project->tis_state; endif ?>
            <?php if(!empty($project->tis_zip)): ?>, <?= $project->tis_zip; endif ?>
        <?php endif; ?>
    </div>
</div>

