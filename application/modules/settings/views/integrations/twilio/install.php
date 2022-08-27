<?php
$this->load->view('includes/header');
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
?>

<section class="panel panel-default bg-white m-t-lg wrapper-md animated">
    <div class="container aside-xxl">
        <header class="panel-heading text-center">
            <strong>Voice Twilio Integration</strong>

            <?php if(isset($errors['activeNumbers']) && $errors['activeNumbers'] == false):?>
                <span class="text-danger">You haven't buy phone number yet, <a href="https://console.twilio.com/?frameUrl=/console/phone-numbers/incoming" >Create ?</a> </span>
            <?php endif;?>
        </header>
        <form action="" class="panel-body wrapper-lg" method="POST">
            <div class="form-group">
                <label class="control-label">Voice account sid</label>
                <input type="text" name="<?=BT::VOICE_ACCOUNT_SID?>" placeholder="" required class="form-control input-lg">
                <span class="text-danger">
                    <?= (isset($errors[BT::VOICE_ACCOUNT_SID]) && !empty($errors[BT::VOICE_ACCOUNT_SID])) ? $errors[BT::VOICE_ACCOUNT_SID][0] : '' ?>
                </span>
            </div>
            <div class="form-group">
                <label class="control-label">Voice auth token</label>
                <input type="text" name="<?=BT::VOICE_AUTH_TOKEN?>" placeholder="" required class="form-control input-lg">
                <span class="text-danger">
                    <?= (isset($errors[BT::VOICE_AUTH_TOKEN]) && !empty($errors[BT::VOICE_AUTH_TOKEN])) ? $errors[BT::VOICE_AUTH_TOKEN][0] : '' ?>
                </span>
            </div>

            <br />
            <button class="btn btn-primary btn-block">Install</button>
        </form>
    </div>
</section>

<?php $this->load->view('includes/footer'); ?>