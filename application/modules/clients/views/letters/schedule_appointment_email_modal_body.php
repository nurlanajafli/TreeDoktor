<header class="panel-heading">Send appointment reminder by Email</header>

<form action="" class="send-appointment-email-form form-horizontal" data-callback="ClientsLetters.appointment_email_callback" data-type="ajax" data-url="/clients/ajax_send_email">
    <input type="hidden" name="client_id" id="appointment-email-client-id" value="{{if task.client }}{{:task.client.client_id}}{{/if}}">
    <div class="modal-body">
        <div class="row">
            <div class="control-group col-xs-12 col-md-6">
                <label class="control-label" for="appointment-to-email">Recipient email</label>
                <div class="controls">
                    <input name="email" id="email-tags"  placeholder="Email to..." style="background-color: #fff;"
                           value="{{if letter.system_label=='estimator_schedule_appointment'}}{{if task.user.user_email!=undefined }}{{:task.user.user_email}}{{/if}}{{else}}{{if task.client && task.client.primary_contact }}{{:task.client.primary_contact.cc_email}}{{/if}}{{/if}}"
                           type="text" multiple="multiple" autocomplete="nope"/>
                    <!--<input type="text" class="form-control" name="email" id="appointment-to-email" placeholder="Recipient email" value="{{if letter.system_label=='estimator_schedule_appointment'}}{{if task.user.user_email!=undefined }}{{:task.user.user_email}}{{/if}}{{else}}{{if task.client && task.client.primary_contact }}{{:task.client.primary_contact.cc_email}}{{/if}}{{/if}}">-->
                </div>
            </div>
            <div class="controls col-xs-12 col-md-6">
                <label class="control-label" for="appointment-from-email">Sender email</label>
                <input type="text" class="form-control" name="from_email" id="appointment-from-email" placeholder="Sender email" value="{{:brand.brand_email}}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="appointment-email-subject">Email subject</label>
            <div class="controls">
                <input type="text" class="form-control" name="subject" id="appointment-email-subject" placeholder="Email subject" value="{{:letter.email_template_title}}">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="client_template_text">Email body</label>
            <div class="controls">
                <textarea id="client_template_text" name="text" placeholder="Email body">{{:letter.email_template_text}}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button class="btn btn-success" type="submit">Send</button>
    </div>
</form>