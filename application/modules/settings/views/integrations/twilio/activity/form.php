<?php
/** @var Twilio\Rest\Taskrouter\V1\Workspace\ActivityInstance $activity */
/** @var bool $creatAction */
$formAction = $creatAction
    ? "/settings/integrations/twilio/workspace/" . $workspaceSid . "/activity/create"
    : "/settings/integrations/twilio/workspace/" . $workspaceSid . "/activity/update/" . $activity->sid;

?>
<form role="form" method="post" action="<?= $formAction ?>">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Activity name</label>
            <input type="text" name="friendlyName" class="form-control" placeholder="Activity name"
                   value="<?= $activity->friendlyName ?? '' ?>"/>
            <span class="form-attribute-invalid">
            <?= (isset($errors['friendlyName']) && !empty($errors['friendlyName'])) ? $errors['friendlyName'][0] : '' ?>
        </span>
        </div>
    </div>
    <?php if ($creatAction): ?>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Availability</label>
                <div>
                    <label>True</label>
                    <input type="radio" name="available" class="form-control"/>
                    <label>False</label>
                    <input type="radio" name="available" class="form-control"/>
                    <span class="form-attribute-invalid">
                    <?= (isset($errors['available']) && !empty($errors['available'])) ? $errors['available'][0] : '' ?>
                </span>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <hr>
    <div class="col-sm-12">
        <a href="/settings/integrations/twilio/workspace/<?=$workspaceSid?>/activity" class="btn btn-sm btn-default pull-left">Back</a>
        <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
    </div>
</form>
