<header class="panel-heading">Email Preview</header>
<form id="send_pdf_to_email" method="POST">
    <div class="modal-body">

        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label">Email To</label>
                <div class="controls">
                    <input id="email-tags"  placeholder="Email to..." style="background-color: #fff;"
                           value="{{if estimate.client.primary_contact && estimate.client.primary_contact.cc_email != undefined }}{{:estimate.client.primary_contact.cc_email}}{{/if}}"
                           type="text" multiple="multiple" autocomplete="nope"/>
                    <input type="hidden" name="email_tags"  autocomplete="off" autocorrect="off"
                           autocapitalize="off" spellcheck="false">
                    <!--<input class="form-control" name="emails" id="emails" type="text" value="{{if estimate.client.primary_contact && estimate.client.primary_contact.cc_email != undefined }}{{:estimate.client.primary_contact.cc_email}}{{/if}}" style="background-color: #fff;">-->
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">CC</label>
                <div class="controls">
                    <input class="form-control" name="cc" id="cc" type="text" value="{{if letter.email_static_cc != null && letter.email_static_cc != '' }}{{:letter.email_static_cc}}{{/if}}" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">BCC</label>
                <div class="controls">
                    <input class="form-control" name="bcc" id="bcc" type="text" value="{{if letter.email_static_bcc != null && letter.email_static_bcc != '' }}{{:letter.email_static_bcc}}{{/if}}" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email From</label>
                <div class="controls">
                    <input class="form-control" name="email_from" id="emails" type="text" value="{{if letter.email_static_sender != null && letter.email_static_sender != '' }}{{:letter.email_static_sender}}{{else estimate.user.user_email!= undefined }}{{:estimate.user.user_email}}{{else}}{{:brand.brand_email}}{{/if}}" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email Subject</label>
                <div class="controls">
                    <input class="form-control" type="text" value="{{:letter.email_template_title}}" name="subject" id="subject" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email to {{:client.client_name}}</label>
                <div class="controls">
                    <textarea class="form-control" name="text" rows="5" type="text" id="client_template_text" value="" style="background-color: #fff;">
                        {{:letter.email_template_text}}
                    </textarea>

                </div>
            </div>
            <?php if(config_item('messenger')) : ?>
                <div class="control-group">
                    <label class="checkbox">
                        <input type="checkbox" name="sent_sms" id="sent_sms" class="sent_sms" checked="checked">Send SMS to client
                    </label>
                </div>
            <?php endif; ?>
            <a target="_blank" class="pull-right pdf-link" href="<?php echo base_url(); ?>{{:estimate.estimate_no}}/pdf">Pdf File</a>
            <input type="hidden" name="id" value="{{:estimate.estimate_id}}">
        </div>

    </div>

    <div class="modal-footer">
        <div class="pull-right ">
            <button name="send" id="send-estimate-pdf-email" class="btn btn-success m-right-5"
            >
                <span class="btntext">Send</span>
                <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                     style="display: none;width: 32px;" class="preloader">
            </button>
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>
</form>
