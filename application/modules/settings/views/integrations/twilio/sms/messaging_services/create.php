<div id="messagingServicesModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">

            <?php $this->load->view('settings/integrations/twilio/sms/messaging_services/form', [
                'numbers' => $numbers,
                'usedPhoneNumbersArray' => $usedPhoneNumbersArray,
                'accountSid' => $accountSid,
                'authToken' => $authToken,
                'creatAction' => true
            ]);?>

        </div>
    </div>
</div>