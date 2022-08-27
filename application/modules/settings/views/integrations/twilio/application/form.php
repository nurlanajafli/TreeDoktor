<?php
/** @var Twilio\Rest\Api\V2010\Account\ApplicationInstance $application */
/** @var bool $creatAction */
?>
<div class="modal-body">
    <div class="form-group hidden">
        <input type="hidden" name="apiVersion" class="form-control" placeholder="apiVersion" value="<?= $application->apiVersion ?? '2010-04-01' ?>" />
        <input type="hidden" name="voiceMethod" class="form-control" placeholder="voiceMethod" value="<?= $application->voiceMethod ?? 'POST' ?>" />
    </div>

    <div class="form-group">
        <label>Application Twiml name</label>
        <input type="text" name="friendlyName" class="form-control" placeholder="Application name" value="<?= $application->friendlyName ?? '' ?>" />
        <span class="form-attribute-invalid">
        <?=(isset($errors['friendlyName']) && !empty($errors['friendlyName'])) ? $errors['friendlyName'][0] : ''?>
        </span>
    </div>

    <div class="form-group">
        <label>Voice url</label>
        <input type="text" name="voiceUrl" class="form-control" placeholder="Voice url" value="<?= $application->voiceUrl ?? '' ?>" />
        <span class="form-attribute-invalid">
        <?=(isset($errors['voiceUrl']) && !empty($errors['voiceUrl'])) ? $errors['voiceUrl'][0] : ''?>
        </span>
    </div>

    <div class="form-group">
        <label>Voice Status Callback Url</label>
        <input type="text" name="statusCallback" class="form-control" placeholder="Status callback url" value="<?= $application->statusCallback ?? base_url('/callback/settings/recording') ?>" />
        <span class="form-attribute-invalid">
        <?=(isset($errors['statusCallback']) && !empty($errors['statusCallback'])) ? $errors['statusCallback'][0] : ''?>
        </span>
        <p class="hidden form-attribute-invalid error alert-danger"></p>
    </div>

    <hr >
</div>
<div class="modal-footer">
    <button class="btn btn-sm btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
    <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
</div>
