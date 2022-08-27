<!-- Set Actiive Task Status -->
<?php if ($todo->task_urgency == "2") {
    $status = 'task_urgent';
} else {
    $status = 'task_normal';
}; ?>
<!--Set active task ownership -->
<?php if ($todo->task_created_by == $todo->user_id) {
    $owner = "";
    $postal_stripes = "";
} else {
    $owner = $todo->firstname . '&nbsp;' . $todo->lastname . '&nbsp;on&nbsp;';
    $postal_stripes = "postal_stripes";
} ?>
<li class="list-group-item <?php echo $status; ?>" data-time="<?= str2ts(getDateTimeWithDate($todo->task_date_created, 'Y-m-d H:i:s', true)); ?>" id="taskItem-<?= $todo->task_id ?>">
    <div class="pull-left task_completion <?php echo $postal_stripes; ?>">
        <a href="#" class="actionTask" data-task-id="<?= $todo->task_id ?>" todo-action="complete">
            <img src="<?= base_url() ?>assets/img/checkbox_empty.png" alt="Mark Done">
        </a>
    </div>
    <div class="pull-left m-l-xl w-90">
        <p><?php echo nl2br($todo->task_description) ?></p>
        <small class="block text-muted"><i class="fa fa-clock-o"></i>
            <?= $owner; ?><?= getDateTimeWithDate($todo->task_date_created, 'Y-m-d H:i:s', true); ?>
            <?php if ($todo->user_id != $todo->task_created_by) : ?>
                <br>Assigned to <?php echo $todo->to_firstname . ' ' . $todo->to_lastname; ?>
            <?php endif; ?>
            <div class="pull-right">
                <a data-toggle="collapse" href="#assignTask_<?= $todo->task_id ?>"
                   title="Assign task to another user" class="btn btn-default btn-xs"><i
                            class="fa fa-share-square-o"></i></a>
                <a data-toggle="collapse" href="#editTask_<?= $todo->task_id ?>" title="Edit task"
                   class="btn btn-default btn-xs"><i class="fa fa-pencil"></i></a>
                <a href="#" class="btn btn-default btn-xs actionTask" data-task-id="<?= $todo->task_id ?>" todo-action="delete">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>
        </small>

        <div id="assignTask_<?= $todo->task_id ?>" class="collapse w-100 clear" style="height:0px;">
            <div class="border-top">
                <form id="assignTask-<?=$todo->task_id?>" todo-action="assign">
                <?= form_hidden(['task_id' => $todo->task_id]); ?>
                <div class="pull-left">
                    <span class="error" style="color:#FF0000;" id="assignTask_error"></span><br>
                    <span>Assign to:</span><br>
                    <?php $options = array();
                    $user_id = $this->session->userdata('user_id');
                    if (isset($users) && !empty($users)) {
                        foreach ($users as $user) {
                            if ($user->id != $user_id) {
                                $options["$user->id"] = $user->firstname . " " . $user->lastname;
                            }
                        }
                    }
                    echo form_dropdown('user_id', $options, '', 'id = "user_id_' . $todo->task_id . '"'); ?>
                </div>
                <div class="pull-right m-t-lg">
                    <input type="submit" value="Assign" class="btn btn-sm btn-success"/>
                </div>
                <div class="clear"></div>
                </form>
            </div>
        </div>

        <div id="editTask_<?= $todo->task_id ?>" class="collapse">
            <div class="filled_grey border-top divider">
                <form id="editTask-<?=$todo->task_id?>" todo-action="edit">
                    <?= form_hidden(['task_id' => $todo->task_id]); ?>
                <span class="error" style="color:#FF0000;" id="editTask_error"></span>
                <!-- Edit Task Form-->
                <div class="p-10 border-bottom m-bottom-10">
                    <div>Edit task description:</div>
                    <div class="taskItem-edit">
                        <?php $ta_options = array(
                            'name' => 'task_description',
                            'value' => $todo->task_description,
                            'rows' => '6',
                            'class' => 'input-block-level');

                        echo form_textarea($ta_options); ?>
                    </div>
                </div>
                <!-- /Edit Task Form-->
                <!-- Edit Task Submit -->
                <button class="btn btn-success btn-xs pull-right" type="submit"
                        style="margin-top: 5px;margin-left: 20px;">
                    &nbsp;&nbsp;&nbsp;<i class="fa fa fa-check"></i>&nbsp;&nbsp;&nbsp;
                </button>
                <div class="checkbox pull-right">
                    <label class="checkbox-custom pull-right">
                        <input type="checkbox" name="task_urgency" id="Task Urgency"
                               value="2"<?php if ($todo->task_urgency == '2') echo ' checked'; ?>>
                        <i class="fa fa-fw fa-square-o <?php if ($todo->task_urgency == '2') echo ' checked'; ?>"></i>
                        Urgent
                    </label>
                </div>
                <div class="clear"></div>
                <!-- /Edit Task Submit -->
                <?php echo form_close(); ?>
            </div>
        </div>
        <!--/Edit Task End-->
    </div>
    <div class="clear"></div>
</li>