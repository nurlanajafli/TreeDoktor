{{if team_id!=undefined}}
<div class="btn-group woStatus p-n" data-status="{{:wo_status}}" data-woId="{{:wo_id}}">
    <button data-toggle="dropdown" class="btn btn-default dropdown-toggle no-shadow statusesList" style="height: 25px;padding: 0px;width: 100%;">
        <span class="dropdown-label wo_status_name">{{:wo_status_name}}</span>
        <span class="caret pull-right" style="margin-top: 10px!important;margin-right: 5px;"></span>
    </button>
    <ul class="dropdown-menu dropdown-select" style="max-height: 200px;overflow-y: scroll;margin-top: -1px;box-shadow: none;">
        {{for ~getWorkorderStatuses() }}
            <li>
                <a href="#" class="changeWoStatus" data-value="{{:wo_status_id}}">
                    {{:wo_status_name}}
                </a>
            </li>
        {{/for}}
    </ul>
</div>


<div class="eventSticker" data-event-services='{{:event_services}}'
     data-event_wo_id="{{:wo_id}}"
     data-event_wo_color="{{:team_color}}"
     data-event_team_id="{{:team_id}}"
     data-event_estimator="{{:emailid}}"
     data-event_price="{{:event_price}}">
    {{if client!=undefined}}
    <div class="client_name"><a href="/{{:client.client_id}}" target="_blank" class="text-black">{{:client.client_name}}</a></div>
    <div class="eventAddress" data-tags="{{:address_tags}}" data-state="{{:lead_state}}" data-city="{{:lead_city}}" data-country="{{:lead_country}}">
        {{if !lead_address }}
            {{if client.length}}{{:client.client_name}},{{/if}}
        {{else}}
            {{:lead_address}},
        {{/if}} {{:lead_city}}
    </div><br>
    {{/if}}
    <div class="total_for_services">{{:total_for_services}}</div>
    <div class="eventHours">{{:total_service_time}}mhr. ({{:total_hours}}hr.)</div>
    <div class="eventLinks">
        <a href="{{:workorder_no}}/{{:id}}" target="_blank" class="text-black eventWo">{{:workorder_no}}</a>
    </div>
    {{if event_note}}
        <br><i>{{:event_note}}</i>
    {{/if}}
    <br>
    <div class="m-l-xs"><small class="eventCrew">{{:event_crew}}</small></div>
    <br>
    <div class="m-l-xs"><small class="eventEq">{{:event_equipment}}</small></div>
</div>

<?php if(config_item('messenger')) : ?>
    <div id="sms-<?php echo $sticker_sms->sms_id; ?>{{:id}}" class="modal fade eventModalSms" role="dialog" tabindex="-1"  aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 900px;">
            {{if primary_contact!=undefined}}
            <div class="modal-content panel panel-default p-n">
                <header class="panel-heading client-name" style="font-size: 14px; color: #717171;">Sms to {{if primary_contact.cc_name!=undefined}}{{:primary_contact.cc_name}}{{/if}}</header>
                <div class="modal-body">
                    <div class="form-horizontal">
                        <div class="control-group">
                            <label class="control-label client-name2 " style="font-size: 14px; color: #717171;">Sms to {{if primary_contact.cc_name!=undefined}}{{:primary_contact.cc_name}}{{/if}}</label>
                            <div class="controls">
                                <input class="client_number form-control" type="text"
                                       value="{{if primary_contact.cc_phone!=undefined}}{{:primary_contact.cc_phone}}{{/if}}"
                                       placeholder="Sms to..." style="background-color: #fff;"/>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" style="font-size: 14px; color: #717171;">Sms Text</label>
                            <div class="controls">
                                <?php $msgData = trim(str_replace([
                                        '[NAME]', '[EMAIL]', '[ADDRESS]', '[AMOUNT]', '[DATE]', '[TIME]', '{time}', '[COMPANY_NAME]', '[COMPANY_EMAIL]', '[COMPANY_PHONE]', '[COMPANY_ADDRESS]', '[COMPANY_BILLING_NAME]', '[COMPANY_WEBSITE]'
                                    ],
                                        [
                                            '{{if primary_contact.cc_name!=undefined}}{{:primary_contact.cc_name}}{{/if}}',
                                            '{{if primary_contact.cc_email!=undefined}}{{:primary_contact.cc_email}}{{/if}}',
                                            '{{:lead_address}}',
                                            '{{:event_price}}',
                                            '{{:event_start}}',
                                            '{{:event_start}}',
                                            '{{:event_start}}',
                                            '{{:brand_name}}',
                                            '{{:brand_email}}',
                                            '{{:brand_phone}}',
                                            '{{:brand_address}}',
                                            '{{:brand_name}}',
                                            '{{:brand_site}}'
                                        ],
                                        $sticker_sms->sms_text)
                                ); ?>
                                <textarea class="form-control sms_text"
                                          style="height: 200px; resize: vertical !important;"><?php echo $msgData; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success addSMS" data-reload="false" data-sms="" data-client="" data-number="">
                        <span class="btntext">Send</span>
                        <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;" class="preloader">
                    </button>
                    <button  type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
            {{/if}}
        </div>
    </div>
<?php endif; ?>
{{else}}
    New Event
{{/if}}