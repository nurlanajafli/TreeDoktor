<?php
/** @var $task array */

?>

<div id="modal_<?= $task->task_id ?>" data-task_id="<?= $task->task_id ?>" class="modal fade overflow" tabindex="-1"
     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Task
                <?php if($task->client): ?>for&nbsp;<?php echo $task->client->client_name; endif; ?>
            </header>
            <div class="modal-body">
                <div class="p-10">
                    <span>Task Date: <?php echo getDateTimeWithDate($task->task_date_created, 'Y-m-d') ?></span><br>
                    <form action="#">
                        <span class="task_status">Task Status:
                            <select name="task_status_change"
                                    class="task_status_change form-control">
                                <option value="new"
                                        <?php if ($task->task_status == 'new') : ?>selected="selected"<?php endif; ?>>
                                    New
                                </option>
                                <option value="canceled"
                                        <?php if ($task->task_status == 'canceled') : ?>selected="selected"<?php endif; ?>>
                                    Canceled
                                </option>
                                <option value="done"
                                        <?php if ($task->task_status == 'done') : ?>selected="selected"<?php endif; ?>>
                                    Done
                                </option>

                            </select>
                        </span><br>
                        <textarea placeholder="This field is required..." name="new_status_desc"
                                  class="new_status_desc form-control"><?php echo $task->task_desc; ?></textarea><br>
                    </form>
                    <br><br>
                    <?php if ($task->user && $task->user->firstname) : ?>
                        <span>Task Date: <?php echo getDateTimeWithDate($task->task_date,
                                    'Y-m-d') . ' ' . getTimeWithDate($task->task_start,
                                    "H:i:s") . ' - ' . getTimeWithDate($task->task_end, "H:i:s"); ?></span><br>
                    <?php endif; ?>
                    <span>Task Category: <?php
                        echo ($task->category)?ucfirst($task->category->category_name):''; ?></span><br>
                    <?php if ($task->user && $task->user->firstname) : ?>
                        <span>Task Assigned To: <?php echo $task->user->firstname . ' ' . $task->user->lastname; ?></span>
                        <br>
                    <?php endif; ?>
                    <span>Task Created By: <?php echo $task->owner->firstname . ' ' . $task->owner->lastname; ?></span><br>
                    <?php if (isset($task->task_lead_id)): ?>
                        <span>Task Lead No: <a href="/<?= $task->task_lead_id ?>-L" title="Edit lead"
                                               target="_blank"> <?= $task->task_lead_id ?>-L</a> </span><br>
                    <?php endif; ?>
                    <div class="line line-dashed line-lg"></div>
                    <?= $task->task_desc ?>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <button class="btn btn-success submit" style="padding: 6px 12px; margin-left: 5px">
                    <span class="btntext">Save</span>
                    <img src="<?php echo base_url("assets/img/ajax-loader.gif"); ?>" style="display: none;width: 32px;"
                         class="preloader">
                </button>
            </div>
        </div>
    </div>

</div>