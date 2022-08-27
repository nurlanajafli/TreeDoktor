<?php //$lastDate = date('Y-m-d'); ?>
<div class="tab-pane active animated fadeInUp" id="{{if client_note_type!=undefined}}{{:client_note_type}}{{else}}all{{/if}}" style="overflow-x: hidden">
    <div class="sms_notes">
        <div class="messages-wrapper">
            {{if notes!=undefined && ~object_length(notes)}}
                {{props notes}}
                <div class="text-info text-left p-top-10 p-left-15 p-right-15">
                    <p class="p-10 m-n h5" style="background: #f5faff;border-radius: 43px;"><i class="fa fa-phone-square"></i>&nbsp;{{>key}}</p>
                </div>
                <?php /* if($lastDate != date('Y-m-d', strtotime($call_note['sms_date']))) :*/ ?>
                    {{props prop}}
                    <div class="chat-date p-top-10">{{>key}}</div>

                    {{for prop}}
                    <div class="message-row" id="sms-{{:sms_id}}">
                        {{if sms_incoming }}
                            <div class="message-block">
                                <div class="message from {{if sms_support}}support{{/if}}">{{:sms_body}}:{{:sms_number}}</div>
                            </div>
                            <div class="message-time from"><?php //echo date('H:i', strtotime($call_note['sms_date'])); ?></div>
                        {{else}}
                            <div class="message-time to">
                                <?php //echo date('H:i', strtotime($call_note['sms_date'])); ?>
                            </div>
                            <div class="message-block">
                                <div class="message to">{{:sms_body}}:{{:sms_number}}</div>
                            </div>
                        {{/if}}
                    </div>
                    {{/for}}
                    {{/props}}
                {{/props}}
                {{if notes!=undefined && notes.length > limit }}
                <div class="text-center">
                    <a href="#" class="getMore" data-num="{{:limit}}" data-type="{{if client_note_type!=undefined}}{{:client_note_type}}{{/if}}" data-id="{{:client.client_id}}">Show More</a>
                </div>
                {{/if}}
            {{else}}
                <div class="client_note filled_white rounded shadow overflow">
                    <div class="corner"></div>
                    <div class="p-20 h5 text-center">
                        No record found
                    </div>
                </div>
            {{/if}}
        </div>
    </div>
</div>