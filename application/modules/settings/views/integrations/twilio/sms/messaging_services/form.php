<?php

use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
/** @var array $usedPhoneNumbersArray */
$formAction = $creatAction
    ? "/settings/integrations/twilio/messaging-services/create"
    : "/settings/integrations/twilio/messaging-services/update/" . $currentMessagingService->sid;

$primaryTwilioNumber = $primaryTwilioNumber["stt_key_value"] ?? null;


?>
<form name="modalMessagingServicesForm" role="form" method="post"
      action="<?= base_url($formAction) ?>">
    <div class="modal-body">
        <input type="hidden" name="<?= BT::SMS_ACCOUNT_SID ?>" value="<?= $accountSid ?>"/>
        <input type="hidden" name="<?= BT::SMS_AUTH_TOKEN ?>" value="<?= $authToken ?>"/>

        <span class="form-attribute-invalid text-danger sms_twilio_account_sid"></span>
        <br />
        <span class="form-attribute-invalid text-danger sms_twilio_auth_token_sid"></span>

        <div class="form-group">
            <label>Messaging service name</label>
            <input type="text" name="friendlyName" class="form-control" placeholder="Messaging service name"
                   value="<?= $currentMessagingService->friendlyName ?? ''?>"/>
            <span class="form-attribute-invalid text-danger friendlyName"></span>
        </div>
        <div class="form-group">
            <label>Webhook Request URL</label>
            <input type="text" name="requestUrl" class="form-control"
                   placeholder="<?= base_url('/callback/messaging/sms?driver=twilio_sms&method=incoming') ?>"
                   value="<?= $currentMessagingService->inboundRequestUrl ?? base_url('/callback/messaging/sms?driver=twilio_sms&method=incoming') ?>"/>
            <span class="form-attribute-invalid text-danger requestUrl"></span>
        </div>
        <div class="form-group">
            <label>Delivery Status Callback URL</label>
            <input type="text" name="callbackUrl" class="form-control"
                   placeholder="<?= base_url('/callback/messaging/sms?driver=twilio_sms&method=incoming') ?>"
                   value="<?= $currentMessagingService->statusCallback ?? base_url('/callback/messaging/sms?driver=twilio_sms&method=incoming') ?>"/>
            <span class="form-attribute-invalid text-danger callbackUrl"></span>
        </div>

        <?php if (isset($numbers) && !empty($numbers) && !is_null($numbers)): ?>
            <div class="form-group row sms_numbers_block">
                <div class="sms_numbers_item_block">
                    <?php if (isset($currentMSPhones) && !is_null($currentMSPhones)):?>
                        <?php foreach ($currentMSPhones as $key => $currentMSPhone):?>
                            <div class="col-md-12">
                                <label>Active number</label>
                                <i class="fa fa-plus-circle pull-right sms_numbers_block_duplicate"></i>
                            </div>
                            <div class="col-md-12">
                                <div class="col-md-2">
                                    <input name="sms_twilio_primary_number[<?=$key?>]" type="radio" class="form-control primary_number" <?=$currentMSPhone->phoneNumber == $primaryTwilioNumber ? 'checked' : ''?>/>
                                </div>
                                <div class="col-md-10">
                                    <select name="<?= BT::SMS_TWILIO_NUMBER ?>[<?=$key?>]"
                                            class="sms_service_twilio_number form-control input-lg">
                                        <?php foreach ($numbers as $key => $number): ?>

                                            <?php /** @var \Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance $number */ ?>
                                            <option value="<?= $number->sid ?>" <?=$number->sid === $currentMSPhone->sid ? 'selected' : '' ?>>
                                                <?= $number->friendlyName ?>
                                            </option>

                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <br/>
                        <?php endforeach;?>
                    <?php else:?>
                        <div class="col-md-12">
                            <label>Active number</label>
                            <i class="fa fa-plus-circle pull-right sms_numbers_block_duplicate"></i>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-2">
                                <input name="sms_twilio_primary_number[]" type="radio" class="form-control primary_number"/>
                            </div>
                            <div class="col-md-10">
                                <select name="<?= BT::SMS_TWILIO_NUMBER ?>[]"
                                        class="sms_service_twilio_number form-control input-lg">
                                    <?php foreach ($numbers as $key => $number): ?>

                                        <?php /** @var \Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance $number */ ?>
                                        <option value="<?= $number->sid ?>">
                                            <?= $number->friendlyName ?>
                                        </option>

                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <br/>
                    <?php endif;?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="modal-footer">
        <button class="btn btn-sm btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
        <button type="submit" class="btn btn-sm btn-default pull-right">Submit</button>
    </div>
</form>