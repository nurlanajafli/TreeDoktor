<?php
/** @var Twilio\Rest\Taskrouter\V1\Workspace\WorkerInstance $worker */
/** @var array $activities */
/** @var array $availableUsers */
/** @var bool $is_support */
$formAction = $creatAction
    ? "/settings/integrations/twilio/workspace/" . $workspaceSid . "/worker/create"
    : "/settings/integrations/twilio/workspace/" . $workspaceSid . "/worker/update/" . $worker->sid;

?>
<form role="form" method="post" action="<?= $formAction ?>">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Worker User</label>
            <select name="user_id" class="form-control">
                <?php if (!empty($availableUsers)): ?>
                    <?php foreach ($availableUsers as $availableUser): ?>
                        <option value="<?= $availableUser['id'] ?>"><?= $availableUser['full_name'] ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <?php if (isset($worker->sid)): ?>
            <div class="form-group">
                <label>Worker Sid </label>
                <label><?= $worker->sid ?></label>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-sm-6">
        <div class="form-group">
            <label>Activity</label>
            <select name="activitySid" class="form-control">
                <?php if (!empty($activities)): ?>
                    <?php foreach ($activities as $activity): ?>
                        <option value="<?= $activity['sid'] ?>"><?= $activity['friendlyName'] ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>
    <hr>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Support</label>
            <div class="col-10">
                <label class="">
                    <input type="checkbox" name="is_support"
                           <?= isset($is_support) && $is_support === true ? 'checked' : '' ?>/>
                    <span></span>
                </label>
            </div>
        </div>
    </div>
    <hr>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Attributes <i class="open_attributes_textarea_block fa fa-plus-square"></i></label>
            <div class="attributes_textarea_block hidden">
                <textarea name="attributes" class="form-control" rows="5"
                          cols="33"><?= $worker->attributes ?? '' ?></textarea>
            </div>
        </div>
    </div>
    <hr>
    <div class="col-sm-12">
        <a href="/settings/integrations/twilio/workspace/<?= $workspaceSid ?>/worker"
           class="btn btn-sm btn-default pull-left">Back</a>
        <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
    </div>
</form>

