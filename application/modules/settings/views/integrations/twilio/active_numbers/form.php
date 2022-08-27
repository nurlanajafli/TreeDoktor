<?php
/** @var Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance $activeNumber */
/** @var bool $creatAction */
?>
<form name="modalActivenumbersForm" role="form" method="post" action="<?=base_url('/settings/integrations/twilio/active-numbers/update/'.$sid)?>">
    <div class="modal-body">
        <div class="form-group">
            <label>Friendly Name</label>
            <span><?= $activeNumber->friendlyName ?? '' ?></span>
            <input type="hidden" name="friendlyName" class="form-control" placeholder="friendlyName" value="<?= $activeNumber->friendlyName ?? '' ?>" />
            <span class="form-attribute-invalid">
                <?=(isset($errors['friendlyName']) && !empty($errors['friendlyName'])) ? $errors['friendlyName'][0] : ''?>
            </span>
        </div>

        <hr >

        <div class="form-group">
            <label>Choose Application</label>
            <?php if (!empty($applications)): ?>
                <select name="voiceApplicationSid" class="form-control ">
                    <option>Chose application</option>
                    <?php foreach ($applications as $application): ?>
                        <option data-sid="<?=$activeNumber->voiceApplicationSid?>" value="<?= $application['sid'] ?>" <?=  $activeNumber->voiceApplicationSid == $application['sid'] ? 'selected' : ''?>>
                            <?= $application['friendlyName'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <p class="hidden form-attribute-invalid error alert-danger"></p>
        </div>

        <hr >
    </div>
    <div class="modal-footer">
        <button class="btn btn-sm btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
        <button type="submit" class="btn btn-sm btn-success pull-right">Submit</button>
    </div>
</form>

