<header class="panel-heading client-name">Send Email to {{if client.primary_contact && client.primary_contact.cc_name!=undefined}}{{:client.primary_contact.cc_name}}{{/if}}</header>
<div class="modal-body">
    <div class="form-horizontal">
        <div class="control-group">
            <label class="control-label">Email Address</label>

            <div class="controls">
                <input class="wo_id form-control" type="hidden" value="{{:schedule_event.workorder.id}}">
                <input class="estimate_id form-control" type="hidden" value="{{:schedule_event.workorder.estimate_id}}">
                <input id="email-tags" placeholder="Email to..." style="background-color: #fff;"
                       value="{{if client.primary_contact && client.primary_contact.cc_email!=undefined}}{{:client.primary_contact.cc_email}}{{/if}}"
                       type="text" multiple="multiple" autocomplete="nope"/>
                <input type="hidden" name="email_tags"  autocomplete="off" autocorrect="off"
                       autocapitalize="off" spellcheck="false">
                <!--<input class="template_email form-control" type="text"
                       value="{{if client.primary_contact && client.primary_contact.cc_email!=undefined}}{{:client.primary_contact.cc_email}}{{/if}}"
                       placeholder="Email Address" style="background-color: #fff;">-->
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">From Email</label>

            <div class="controls">
                <input class="template_from_email form-control" type="text"
                       value="{{:brand.brand_email}}"
                       placeholder="From Email" style="background-color: #fff;">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Email Subject</label>

            <div class="controls">
                <input class="subject form-control" type="text"
                       value="{{if letter.email_template_title }}{{:letter.email_template_title}}{{else}}Tree Services Schedule{{/if}}"
                       placeholder="Email Subject" style="background-color: #fff;">
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Email Text</label>
            <div class="controls">
                <textarea placeholder="Template Text"  class="form-control textMsg" id="client_template_text">{{:letter.email_template_text}}</textarea>
            </div>
        </div>
        <?php if(config_item('messenger')) : ?>
            <div class="control-group" {{if !related_sms_id }}style="display:none;"{{/if}}>
                <label class="checkbox">
                    <input type="checkbox" {{if related_sms_id }}data-sms_id="{{:related_sms_id}}"{{/if}} name="sent_sms" id="sent_sms" class="sent_sms" {{if related_sms_id }}checked="checked"{{/if}}>Send SMS to client
                </label>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="clientData" style="display:none"
     data-name="{{if client.primary_contact && client.primary_contact.cc_name!=undefined}}{{:client.primary_contact.cc_name}}{{/if}}"
     data-date="{{:schedule_event.event_date_time}}"
     data-date-ymd="{{:schedule_event.event_date}}"
     data-event-time-interval="{{:schedule_event.event_time_interval_string}}"
     data-brand-name="{{:brand.brand_name}}"
     data-brand-email="{{:brand.brand_email}}"
     data-brand-phone="{{:brand.brand_phone}}"
     data-brand-address="{{:brand.brand_address}}"
     data-brand-site="{{:brand.brand_site}}"
     data-address="{{if schedule_event.workorder.estimate.lead.lead_address!=undefined }}{{:schedule_event.workorder.estimate.lead.lead_address}}{{/if}}"
     data-client_email="{{if client.primary_contact && client.primary_contact.cc_email!=undefined}}{{:client.primary_contact.cc_email}}{{/if}}"
     data-client_phone="{{if client.primary_contact && client.primary_contact.cc_phone!=undefined}}{{:client.primary_contact.cc_phone}}{{/if}}"
     data-amount="{{:schedule_event.event_price}}"></div>

<div class="modal-footer">
    <button class="btn btn-success sendEmail">
        <span class="btntext">Send</span>
        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
             style="display: none;width: 32px;" class="preloader">
    </button>
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
</div>
