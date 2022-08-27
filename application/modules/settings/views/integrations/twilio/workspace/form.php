<?php
/** @var Twilio\Rest\Taskrouter\V1\WorkspaceInstance $workspace */
?>
<form role="form" method="post" action="<?=base_url('/settings/integrations/twilio/workspace/update/' . $workspace->sid)?>">
    <div class="col-sm-6">
        <div class="form-group">
            <label>Workspace name</label>
            <input type="text" name="friendlyName" class="form-control" placeholder="Workspace name"
                   value="<?= $workspace->friendlyName ?? '' ?>"/>
            <span class="form-attribute-invalid">
                <?= (isset($errors['friendlyName']) && !empty($errors['friendlyName'])) ? $errors['friendlyName'][0] : '' ?>
            </span>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label>Event calback url <span style="color: darkgrey;"> http://example.com/callback/settings/task-callbacks</span></label>
            <input type="text" name="eventCallbackUrl" class="form-control" placeholder="Url"
                   value="<?= $workspace->eventCallbackUrl ?? base_url('/callback/settings/task-callbacks') ?>"/>
            <span class="form-attribute-invalid">
                <?= (isset($errors['eventCallbackUrl']) && !empty($errors['eventCallbackUrl'])) ? $errors['eventCallbackUrl'][0] : '' ?>
            </span>
        </div>
    </div>
    <div class="col-sm-12">
        <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
    </div>
</form>

