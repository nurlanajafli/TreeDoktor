<?php
if ($tasks) { ?>
    <?php foreach ($tasks as $row) { ?>
        <tr>
            <td>Task</td>
            <td><?php echo $row['task_id']; ?></td>
            <td><?php echo $row['task_lead_id'] ? $row['task_lead_id'] . '-L' : '-'; ?></td>
            <td><?php echo getDateTimeWithDate($row['task_date_created'], 'Y-m-d'); ?></td>
            <td><?php echo ucfirst($row['task_status']); ?></td>
            <td><?php echo $row['category_name']; ?></td>
            <td> <span><?php echo getDateTimeWithDate($row['task_date'], 'Y-m-d') . ' ' . getTimeWithDate($row['task_start'], 'H:i:s') . ' - ' .  getTimeWithDate($row['task_end'], 'H:i:s'); ?></span><br></td>
            <td>
                <!-- Modal -->
                <div id="task-<?php echo $row['task_id']; ?>" data-task-id="<?php echo $row['task_id']; ?>" class="modal fade overflow" tabindex="-1"
                     role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content panel panel-default p-n">
                            <header class="panel-heading">Task for&nbsp;<?php echo $row['client_name']; ?></header>
                            <div class="modal-body">
                                <div class="p-10">
                                    <span>Task Created: <?php echo getDateTimeWithDate($row['task_date_created'], 'Y-m-d'); ?></span><br>
                                    <form action="#">
                                        <span class="task_status">Task Status:
                                            <select name="task_status_change" class="task_status_change form-control">
                                                <option value="new" <?php if($row['task_status'] == 'new') : ?>selected="selected"<?php endif; ?>>
                                                    New
                                                </option>
                                                <option value="canceled" <?php if($row['task_status'] == 'canceled') : ?>selected="selected"<?php endif; ?>>
                                                    Canceled
                                                </option>
                                                <option value="done" <?php if($row['task_status'] == 'done') : ?>selected="selected"<?php endif; ?>>
                                                    Done
                                                </option>
                                            </select>
                                        </span><br>
                                        <textarea placeholder="This field is required..." name="new_status_desc" class="new_status_desc form-control"><?php echo $row['task_desc']; ?></textarea><br>
                                        <button class="pull-right btn btn-success submit" style="display:none; padding: 6px 12px;">
                                            <span class="btntext">Save</span>
                                            <img src="<?php echo base_url("assets/img/ajax-loader.gif"); ?>" style="display: none;width: 32px;" class="preloader">
                                        </button>
                                        <button class="pull-right btn submit" style="display:none; padding: 6px 12px; margin-right:10px;">Close</button>

                                    </form><br><br>
                                    <?php if($row['ass_firstname']) : ?>
                                        <span>Task Date: <?php echo getDateTimeWithDate($row['task_date'], 'Y-m-d') . ' ' . getTimeWithDate($row['task_start'], "H:i:s") . ' - ' .   getTimeWithDate($row['task_end'], "H:i:s"); ?></span><br>
                                    <?php endif; ?>
                                    <span>Task Category: <?php echo ucfirst($row['category_name']); ?></span><br>
                                    <?php if($row['ass_firstname']) : ?>
                                        <span>Task Assigned To: <?php echo $row['ass_firstname'] . ' ' . $row['ass_lastname']; ?></span><br>
                                    <?php endif; ?>
                                    <span>Task Created By: <?php echo $row['firstname'] . ' ' . $row['lastname']; ?></span><br>
                                    <?php if(isset($row['task_lead_id'])): ?>
                                        <span>Task Lead No: <a href="/<?= $row['task_lead_id'] ?>-L" title="Edit lead" target="_blank"> <?= $row['task_lead_id'] ?>-L</a> </span><br>
                                    <?php endif; ?>
                                    <div class="line line-dashed line-lg"></div>
                                    <?php echo $row['task_desc']; ?>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                                <button class="btn btn-success submit" style="padding: 6px 12px; margin-left: 5px">
                                    <span class="btntext">Save</span>
                                    <img src="<?php echo base_url("assets/img/ajax-loader.gif"); ?>" style="display: none;width: 32px;" class="preloader">
                                </button>
                            </div>
                            </div>
                    </div>

                </div>

                <div class="d-flex">
                    <!--Triggers -->
                    <a href="#email-template-modal" data-system_label="client_schedule_appointment" data-task_id="<?php echo $row['task_id']; ?>" role="button" class="btn btn-xs btn-default m-r-xs"
                       data-toggle="modal" data-callback="ClientsLetters.schedule_appointment_email_modal"><i class="fa fa-envelope-o"></i></a>

                    <a href="#task-<?php echo $row['task_id']; ?>" role="button" class="btn btn-xs btn-default m-r-xs"
                       data-toggle="modal"><i class="fa fa-eye"></i></a>

                    <?php
                        echo anchor('tasks/edit/' . $row['task_id'], '<i class="fa fa-pencil"></i>', 'class="btn btn-xs btn-default btn-mini m-r-xs"');
                        //if ($this->session->userdata('user_type') == "admin") {
                        echo ' ' . anchor('tasks/delete/' . $row['task_id'], '<i class="fa fa-trash-o"></i>', 'class="btn btn-default btn-xs btn-danger"');
                        //}
                    ?>
                </div>
            </td>
        </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td style="color:#FF0000;">No record found</td>
    </tr>
<?php } ?>
