<!-- Task Queue Modal Form -->

<div id="taskQueueModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Task Queue</header>
            <form action="<?= site_url('settings/integrations/twilio/workspace/' . $workspaceSid . '/task_queue_save/0') ?>" id="taskQueueForm">
                <div class="modal-body">
                    <div class="form-horizontal">

                        <div class="control-group">
                            <label class="control-label">FriendlyName</label>

                            <div class="controls">
                                <input name="sid" class="qa_name form-control" type="hidden"
                                       value="<?= isset($sid) ? $sid : '' ?>"/>
                                <input name="friendlyName" class="qa_name form-control" type="text"
                                       value="<?= isset($friendlyName) ? $friendlyName : '' ?>"
                                       placeholder="FriendlyName"/>
                            </div>
                        </div>

                        <!--<div class="control-group">
                            <label class="control-label">TaskOrder</label>

                            <div class="controls">
                                <input name="TaskOrder" class="qa_name form-control" type="text"
                                       value=""
                                       placeholder="TaskOrder">
                            </div>
                        </div>-->

                        <div class="control-group">
                            <label class="control-label">reservationActivitySid</label>

                            <div class="controls">
                                <select name="reservationActivitySid" class="qa_name form-control">
                                    <?php if ($unavailableActivities && !is_null($unavailableActivities)): ?>
                                        <?php foreach ($unavailableActivities as $activity): ?>
                                            <option value="<?= $activity->sid; ?>"
                                                    <?= (isset($reservationActivitySid) && $reservationActivitySid === $activity->sid) ? 'selected' : '' ?>>
                                                <?= $activity->friendlyName ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">assignmentActivitySid</label>

                            <div class="controls">
                                <select name="assignmentActivitySid" class="qa_name form-control">
                                    <?php if ($unavailableActivities && !is_null($unavailableActivities)): ?>
                                        <?php foreach ($unavailableActivities as $activity): ?>
                                            <option value="<?= $activity->sid ?>"
                                                    <?= (isset($assignmentActivitySid) && $assignmentActivitySid === $activity->sid) ? 'selected' : '' ?>>
                                                <?= $activity->friendlyName ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">MaxReservedWorkers</label>

                            <div class="controls">
                                <input name="maxReservedWorkers" class="qa_rate form-control" type="text"
                                       value="<?= isset($maxReservedWorkers) ? $maxReservedWorkers : '' ?>"
                                       placeholder="MaxReservedWorkers"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">targetWorkers</label>

                            <div class="controls">
                                <textarea name="targetWorkers" class="qa_description form-control"
                                          placeholder="targetWorkers"
                                          rows="5"><?= isset($targetWorkers) ? $targetWorkers : '' ?></textarea>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <div id="taskQueueFormError" class="" style="display: none">
                        <p class=""></p>
                    </div>
                    <button class="btn btn-success">
                        <span class="btntext">Save</span>
                    </button>
                    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </form>
        </div>
    </div>
</div><!-- //Task Queue Modal Form -->