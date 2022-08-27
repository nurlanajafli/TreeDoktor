<?php
$this->load->view('includes/header');
use application\modules\settings\integrations\twilio\classes\BaseTwilio as BT;
?>
    <link rel="stylesheet" href="<?= base_url(); ?>assets/css/modules/soft_twilio_calls/install_sms.css" type="text/css"/>
    <section class="panel panel-default bg-white m-t-lg wrapper-md animated">
        <div class="container aside-xxl">
            <header class="panel-heading text-center">
                <strong>Sms Twilio Integration</strong>

            </header>
            <form action="" class="panel-body wrapper-lg" method="POST">

                <input type="hidden" name="<?=BT::SMS_TWILIO_NUMBER?>" class="primarySmsTwilioNumber" />

                <div class="form-group">
                    <label class="control-label">Sms account sid</label>
                    <input type="text" id="sms_account_sid" name="<?=BT::SMS_ACCOUNT_SID?>" placeholder="" required class="form-control input-lg">
                    <span class="text-danger">
                        <?= (isset($errors[BT::SMS_ACCOUNT_SID]) && !empty($errors[BT::SMS_ACCOUNT_SID])) ? $errors[BT::SMS_ACCOUNT_SID][0] : '' ?>
                    </span>
                </div>
                <div class="form-group">
                    <label class="control-label">Sms auth token</label>
                    <input type="text" id="sms_auth_token" name="<?=BT::SMS_AUTH_TOKEN?>" placeholder="" required class="form-control input-lg">
                    <span class="text-danger">
                        <?= (isset($errors[BT::SMS_AUTH_TOKEN]) && !empty($errors[BT::SMS_AUTH_TOKEN])) ? $errors[BT::SMS_AUTH_TOKEN][0] : '' ?>
                    </span>
                </div>
                <div class="form-group row">
                    <div class="col-md-10 pull-right" style="padding-top: 10px;">
                        <label class="control-label">Show messenger</label>
                    </div>
                    <div class="col-md-2">
                        <input type="checkbox" id="sms_messenger" name="messenger" placeholder="" class="form-control input-sm" />
                    </div>
                    <div class="col-md-12">
                        <span class="text-danger">
                            <?= (isset($errors['messenger']) && !empty($errors['messenger'])) ? $errors['messenger'][0] : '' ?>
                        </span>
                    </div>
                </div>
                <div class="form-group center-block text-center servicesBlock">
                    <label class="control-label">
                        <button class="btn btn-default view_existing_services">View Existing Messaging Services</button>
                        <br />
                        <button class="btn btn-success get_form_create_services hidden">Create Messaging Services</button>
                    </label>
                </div>

                <div class="form-group">
                    <div id="messaging_service_block"></div>
                    <span class="text-danger messagingError">
                    <?= (isset($errors[BT::SMS_MESSAGING_SERVICE_SID]) && !empty($errors[BT::SMS_MESSAGING_SERVICE_SID])) ? $errors[BT::SMS_MESSAGING_SERVICE_SID][0] : '' ?>
                </span>
                </div>


                <br />
                <button class="btn btn-primary btn-block">Install</button>
            </form>
        </div>
    </section>

    <div id="messaging-services-modal"></div>

    <script src="<?php echo base_url(); ?>assets/js/modules/soft_twilio_calls/js/install.js"></script>


<?php $this->load->view('includes/footer'); ?>