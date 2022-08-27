<?php
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
?>
<?php if (isset($messagingServices) && !empty($messagingServices)):?>
    <select name="<?=BT::SMS_MESSAGING_SERVICE_SID?>" required class="form-control input-lg">
    <?php foreach ($messagingServices as $messagingService): ?>
        <?php /** @var Twilio\Rest\Messaging\V1\ServiceInstance $messagingService  */?>
        <option value="<?=$messagingService->sid?>"><?=$messagingService->friendlyName?></option>
    <?php endforeach; ?>
    </select>
<?php endif;?>


