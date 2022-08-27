<?php
/** @var Twilio\Rest\Taskrouter\V1\Workspace\TaskQueueInstance $taskQueue */
/** @var bool $creatAction */
/** @var \Illuminate\Support\Collection $unavailableActivities */
/** @var string $workspaceSid */
$formAction = $creatAction
    ? "/settings/integrations/twilio/workspace/".$workspaceSid."/task-queue/create"
    : "/settings/integrations/twilio/workspace/".$workspaceSid."/task-queue/update/".$taskQueue->sid;

?>
<form role="form" method="post" action="<?=$formAction?>">
    <div class="col-sm-6">
        <div class="form-group">
            <label class="">FriendlyName</label>
            <div class="controls">
                <input name="friendlyName" class="qa_name form-control" type="text"
                       value="<?= $taskQueue->friendlyName ?? '' ?>"
                       placeholder="FriendlyName"/>
            </div>
            <span class="form-attribute-invalid">
                <?= (isset($errors['friendlyName']) && !empty($errors['friendlyName'])) ? $errors['friendlyName'][0] : '' ?>
            </span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="">reservationActivitySid</label>
            <div class="controls">
                <select name="reservationActivitySid" class="qa_name form-control">
                    <?php if ($unavailableActivities && !is_null($unavailableActivities)): ?>
                        <?php foreach ($unavailableActivities as $activity): ?>
                            <option value="<?= $activity->sid; ?>"
                                    <?= (isset($taskQueue) && $taskQueue->reservationActivitySid == $activity->sid) ? 'selected' : '' ?>>
                                <?= $activity->friendlyName ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="">assignmentActivitySid</label>
            <div class="controls">
                <select name="assignmentActivitySid" class="qa_name form-control">
                    <?php if ($unavailableActivities && !is_null($unavailableActivities)): ?>
                        <?php foreach ($unavailableActivities as $activity): ?>
                            <option value="<?= $activity->sid ?>"
                                    <?= (isset($taskQueue) && $taskQueue->assignmentActivitySid === $activity->sid) ? 'selected' : '' ?>>
                                <?= $activity->friendlyName ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="">MaxReservedWorkers</label>
            <div class="controls">
                <input name="maxReservedWorkers" class="qa_rate form-control" type="text"
                       value="<?= $taskQueue->maxReservedWorkers ?? '1' ?>"
                       placeholder="MaxReservedWorkers"/>
            </div>
            <span class="form-attribute-invalid">
                <?= (isset($errors['maxReservedWorkers']) && !empty($errors['maxReservedWorkers'])) ? $errors['maxReservedWorkers'][0] : '' ?>
            </span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="">targetWorkers</label>
            <div class="controls">
            <textarea name="targetWorkers" class="qa_description form-control"
                      placeholder="Queue Expression"
                      rows="5"><?= $taskQueue->targetWorkers ?? '1==1' ?></textarea>
            </div>
            <span class="form-attribute-invalid">
            <?= (isset($errors['targetWorkers']) && !empty($errors['targetWorkers'])) ? $errors['targetWorkers'][0] : '' ?>
        </span>
        </div>
    </div>

    <hr>
    <div class="col-sm-12">
        <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/task-queue" class="btn btn-sm btn-default pull-left">Back</a>
        <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
    </div>
</form>

