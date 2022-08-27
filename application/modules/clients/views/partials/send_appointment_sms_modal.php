<form id="get-appointment-sms-form" data-global="false" data-type="ajax" data-url="<?php echo base_url('tasks/appointment_sms_form'); ?>" data-callback="Leads.set_appointment_sms_modal">
    <input type="hidden" name="id" value="">
</form>

<div id="appointment-sms-modal" class="modal fade overflow" tabindex="-1"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Send appointment reminder by Sms</header>
            <?php echo form_open('', ['class' => 'send-appointment-sms-form form-horizontal', 'data-callback' => 'Leads.appointment_sms_callback',  'data-type' => 'ajax', 'data-url' => base_url('client_calls/send_sms_to_client') ]); ?>

            <div class="modal-body">
                <div class="row">
                    <div class="control-group col-xs-12 col-md-12">
                        <label class="control-label" for="appointment-to-email">Recipient phone</label>
                        <div class="controls">
                            <input type="text" class="form-control" name="PhoneNumber" id="appointment-sms-phone" placeholder="Recipient phone">
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="appointment-sms-text">Text</label>
                    <div class="controls">
                        <textarea type="text" class="form-control" name="sms"
                                  id="appointment-sms-text" placeholder="SMS Text"
                                  style="height: 200px; resize: vertical !important; max-width: 558px;"></textarea>
                    </div>
                </div>

            </div>
            <div class="modal-footer">                      
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                <?php echo form_submit('submit', 'Send', "class='btn btn-success'"); ?>
            </div>
            <?php echo form_close() ?>
        </div>
    </div>
</div>

<style type="text/css">
    #lead-details-modal .modal-dialog{ width:50%  }
    /*
    @media (max-width: 768px){
        #lead-details-modal .modal-dialog{ width:95% }  
    }*/
    @media (max-width:992px){
        #lead-details-modal .modal-dialog{ width:95% }
    }

    @media (min-width: 1023px) {
        #lead-details-modal .modal-dialog{ width:82% }
    }

    @media (min-width: 1366px) {
        #lead-details-modal .modal-dialog{ width:62% }
    }

</style>

