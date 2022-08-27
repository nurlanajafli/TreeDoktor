<header class="panel-heading">Email Preview</header>
<form id="send_pdf_to_email" method="POST">
    <div class="modal-body">

        <div class="form-horizontal">
            <div class="control-group">
                <label class="control-label">Email To</label>
                <div class="controls">
                    <input id="email-tags"  placeholder="Email to..." style="background-color: #fff;"
                           value="{{if workorder.estimate.client.primary_contact && workorder.estimate.client.primary_contact.cc_email != undefined }}{{:workorder.estimate.client.primary_contact.cc_email}}{{/if}}"
                           type="text" multiple="multiple" autocomplete="nope"/>
                    <input type="hidden" name="email_tags"  autocomplete="off" autocorrect="off"
                           autocapitalize="off" spellcheck="false">
                    <!--<input class="form-control" name="emails" id="emails" type="text" value="{{if workorder.estimate.client.primary_contact && workorder.estimate.client.primary_contact.cc_email != undefined }}{{:workorder.estimate.client.primary_contact.cc_email}}{{/if}}" style="background-color: #fff;">-->
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">CC</label>
                <div class="controls">
                    <input class="form-control" name="cc" id="cc" type="text" value="" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">BCC</label>
                <div class="controls">
                    <input class="form-control" name="bcc" id="bcc" type="text" value="" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email From</label>
                <div class="controls">
                    <input class="form-control" name="email_from" id="emails" type="text" value="{{if workorder.estimate.user.user_email!=undefined }}{{:workorder.estimate.user.user_email}}{{else}}{{:brand.brand_email}}{{/if}}" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email Subject</label>
                <div class="controls">
                    <input class="form-control" type="text" value="{{:letter.email_template_title}}" name="subject" id="subject" style="background-color: #fff;">
                </div>
            </div>
            <div class="control-group">
                <label class="control-label">Email to {{:workorder.estimate.client.client_name}}</label>
                <div class="controls">
                    <textarea class="form-control" name="text" rows="5" id="client_template_text" style="background-color: #fff; height: 130px;"> {{:letter.email_template_text}}</textarea>

                </div>
            </div>

            <a target="_blank" class="pull-right pdf-link" href="<?php echo base_url('workorders/partial_invoice_pdf/'); ?>{{:workorder.id}}">Pdf File</a>
            <input type="hidden" name="id" value="{{:workorder.id}}">
        </div>

    </div>
    <div class="modal-footer">
        <div class="pull-right ">
            <button name="send" type="submit" class="btn btn-success m-right-5">
                <span class="btntext">Send</span>
                <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                     style="display: none;width: 32px;" class="preloader">
            </button>
            <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
    </div>
</form>