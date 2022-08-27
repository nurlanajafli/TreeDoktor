<?php if ($todo->task_created_by == $todo->user_id) {
    $owner = "";
    $postal_stripes = "";
} else {
    $owner = $todo->firstname . '&nbsp;' . $todo->lastname . '&nbsp;on&nbsp;';
    $postal_stripes = "postal_stripes";
} ?>
<li class="list-group-item task_success" data-time="<?= str2ts(getDateTimeWithDate($todo->task_date_created, 'Y-m-d H:i:s', true)); ?>" id="taskCompleteItem-<?= $todo->task_id ?>">
    <div class="task_completion">
        <a href="#" class="actionTask" data-task-id="<?= $todo->task_id ?>" todo-action="revert">
            <img src="<?= base_url() ?>assets/img/checkbox_marked.png" alt="Not done yet">
        </a>
    </div>
    <div class="pull-left m-l-xl w-90">
        <p class="completed_task"><?php echo nl2br($todo->task_description) ?></p>
        <small class="block text-muted"><i class="fa fa-clock-o"></i>
            <?= $owner; ?><?= getDateTimeWithDate($todo->task_date_created, 'Y-m-d H:i:s', true); ?>
            <?php if ($todo->user_id != $todo->task_created_by) : ?>
                <br>Assigned to <?php echo $todo->to_firstname . ' ' . $todo->to_lastname; ?>
            <?php endif; ?>
            <div class="pull-right">
                <a data-toggle="collapse" href="#" title="Assign task to another user"
                   class="btn btn-default btn-xs disabled"><i class="fa fa-share-square-o"></i></a>
                <a data-toggle="collapse" href="#" title="Edit task"
                   class="btn btn-default btn-xs disabled"><i class="fa fa-pencil"></i></a>
                <a href="#" class="btn btn-default btn-xs actionTask" data-task-id="<?= $todo->task_id ?>" todo-action="delete">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>
        </small>
    </div>
    <div class="clear"></div>
</li>