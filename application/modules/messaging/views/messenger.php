<script src="https://cdn.jsdelivr.net/npm/libphonenumber-js@1.9.34/bundle/libphonenumber-max.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/css/chat/wssmschat.css?v.1.12'); ?>">
<script>
    const PHONE_CLEAN_LENGTH = <?php echo (int)config_item('phone_clean_length'); ?>;
    const PHONE_COUNTRY_CODE = "<?php echo config_item('phone_country_code'); ?>";
    const PHONE_MASK_REGEX_PATTERN = /^<?php echo config_item('phone_mask_php_regex_pattern'); ?>$/;
    const PHONE_MASK_REGEX_PATTERN_PREVIEW = "<?php echo config_item('phone_mask_php_regex_pattern_preview'); ?>";
    const SMS_MESSAGES_SHOW_LIMIT = <?php echo config_item('sms_messages_show_limit') ?? 100; ?>;
    const SMS_CHATS_SHOW_LIMIT = <?php echo config_item('sms_chats_show_limit') ?? 100; ?>;
    const SMS_UNLIMITED = !!<?php echo config_item('sms_unlimited'); ?>;
</script>
<script type="text/javascript" src="<?php echo base_url('assets/js/chat/smschat.js?v1.29'); ?>"></script>

<div class="modal modal-static fade" id="messenger" role="dialog" aria-hidden="true" data-backdrop="static"
     tabindex="false">
    <div class="modal-dialog messenger-dialog">
        <div class="modal-content smsmodal">
            <div class="modal-body" style="padding: 0;">
                <section class="bg-white smssection">

                    <div class="sms-preloader" id="chatboxes_preloader">
                        <div class="sms-preloader-cell">
                            <img src="<?php
                            echo base_url('assets/img/preloader.gif'); ?>">
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-4 col-xs-12 p-n sms-history animated fadeInLeft" id="chatboxes">
                        <div class="bg-light p-10 b-r b-b">
                            <div class="m-b-sm">
                                <div class="pull-left h3 hidden-sm hidden-xs messenger-title">
                                    <strong>Messages</strong>
                                </div>
                                <ul class="nav nav-tabs pull-left mode-nav m-l-sm">
                                    <li class="active">
                                        <a href="#chat_box" data-toggle="tab" class="change-chatbox" data-mode="all">
                                            ALL
                                        </a>
                                    </li>
                                    <!--<li class="active">
                                        <a href="#chat_box_unread" data-toggle="tab" class="change-chatbox">
                                            UNREAD
                                        </a>
                                    </li>-->
                                    <?php if (config_item('sms_support_chat_box') == 1): ?>
                                        <li class="">
                                            <a href="#chat_box_supportchat" data-toggle="tab" data-mode="supportchat"
                                               class="change-chatbox">
                                                CHAT
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li class="">
                                        <a href="#chat_box_users" data-toggle="tab" data-mode="users"
                                           class="change-chatbox">
                                            USERS
                                        </a>
                                    </li>
                                    <li class="">
                                        <a href="#chat_box_clients" data-toggle="tab" data-mode="clients"
                                           class="change-chatbox">
                                            CLIENTS
                                        </a>
                                    </li>
                                </ul>
                                <div class="pull-right">
                                    <a href="#" class="h3 m-r-xs change-notifications"
                                       title="Turn Off Sms Notifications">
                                        <i class="fa fa-volume-up"></i>
                                    </a>
                                    <a href="#chat_box_new_message" class="h3" data-toggle="tab"
                                       id="new_message_button">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div id="sms_search_form">
                                <form autocomplete="off" onsubmit="return false;">
                                    <div class="form-group m-n">
                                        <input type="text" name="search" id="smsSearch" class="form-control sms-search"
                                               placeholder="Search" autocomplete="off">
                                        <i class="fa fa-search pos-abt sms-search-icon"></i>
                                        <i class="fa fa-times sms-search-clear-icon" title="Clear search"></i>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 col-sm-8 col-xs-12 p-n sms animated fadeInRight">
                        <div class="panel-body p-n" id="chatboxes_history_containers">

                        </div>
                    </div>
                    <div class="clear"></div>
                </section>
            </div>
        </div>
    </div>
</div>

<script id="chat_box_load_more_tpl" type="text/x-jsrender">
  <li class="text-center">
    <a href="#" class="text-center load_more_chats">
      <u><i>Load More</i></u>
    </a>
  </li>
</script>

<script id="chat_box_history_load_more_tpl" type="text/x-jsrender">
  <div class="text-center m-b">
    <a href="#" class="text-center block load_more_messages p-top-10 p-bottom-10" data-number={{:number}}>
      <u><i>Load More</i></u>
    </a>
  </div>
</script>

<script id="search_no_data_tpl" type="text/x-jsrender">
  <li class="h2 search-service-messages">No matches found</li>
</script>

<script id="search_min_length_tpl" type="text/x-jsrender">
  <li class="h2 search-service-messages">Please enter more characters</li>
</script>

<script id="chatboxes_list_tpl" type="text/x-jsrender">
  <ul class="smsnav nav-tab b-r" id="{{:id}}">
    {{include tmpl = "#sms_preloader_tpl" /}}
  </ul>
</script>

<script id="sms_preloader_tpl" type="text/x-jsrender">
    <li class="sms-preloader p-n">
      <div class="sms-preloader-cell">
        <img src="/assets/img/preloader.gif">
      </div>
    </li>
</script>

<script id="chatboxes_history_containers_list_tpl" type="text/x-jsrender">
  <div class="tab-content" id="{{:id}}"></div>
</script>

<script id="message_tpl" type="text/x-jsrender">
  {{if ~isNewDate(sms_date)}}
    <div class="chat-date">{{:~getHistoryDateFormat(sms_date)}}</div>
  {{/if}}
  <div class="message-row" id="chat-sms-{{:sms_id}}">
    {{if sms_incoming == '1'}}
      <div class="message-block">
        <div class="message from{{if sms_support}} support{{/if}}{{if !sms_readed}} unreaded-message{{/if}}">{{:sms_body}}</div>
      </div>
      <div class="message-time from">{{:~getTimeFormat(sms_date)}}</div>
    {{else}}
      <div class="message-time to">
        {{:~getTimeFormat(sms_date)}}
        {{if sender_name}}<br><span class="message-sender-name">{{:sender_name}}</span>{{/if}}
        <span class="badge pos-abt{{if sms_status == 'undelivered'}} bg-danger{{/if}}
            {{if sms_status == 'delivered'}} bg-success{{/if}}
            {{if sms_status != 'delivered' && sms_status != 'undelivered'}} bg-warning{{/if}} sms-status"
            data-toggle="tooltip" data-html="true" data-placement="left" title=""
            data-original-title="{{:~ucfirst(sms_status)}}{{if sms_error && sms_error != 'NULL'}}<br>{{:sms_error}}{{/if}}">
          <i class="fa fa-info"></i>
        </span>
      </div>
      <div class="message-block">
        <div class="message to">{{:sms_body}}</div>
      </div>
    {{/if}}
  </div>
</script>

<script id="chat_box_tpl" type="text/x-jsrender">
  <li class="b-b chat_box_button_{{:~trimNumber(sms_number)}} chat_box_button_block">
      {{include tmpl = "#chat_box_tpl_partial" /}}
      {{if incoming_present == "1"}}
          <a href="#" class="set_unread_chat_box{{if !sms_readed}} hidden{{/if}}" data-number="{{:~trimNumber(sms_number)}}"
             data-toggle="tooltip" data-placement="left" data-original-title="Mark as Unread" title="Mark as Unread">
            <i class="fa fa-envelope"></i>
          </a>
      {{/if}}
  </li>
</script>

<script id="chat_box_tpl_partial" type="text/x-jsrender">
  <a href="#chat_box_{{:~trimNumber(sms_number)}}" data-number="{{:~trimNumber(sms_number)}}" data-toggle="tab" class="chat_box_button">
    {{if readed_all == "0"}}
      <span class="inline pos-abt unreaded"></span>
    {{/if}}
    <span class="inline pull-left">
      <span class="inline sms-avatar{{if emp_phone}} employee{{/if}}{{if last_incoming_sms_support === '1'}} supportchat{{/if}}">
        {{if emp_phone}}
          {{:~substrName(firstname + ' ' + lastname)}}
        {{else}}
          {{if cc_data == null}}
            {{if client_name == null || client_name == ''}}
              {{:~substrName('NA')}}
            {{else}}
              {{:~substrName(client_name)}}
            {{/if}}
          {{else}}
            {{:~substrName(cc_data)}}
          {{/if}}
        {{/if}}
      </span>
    </span>
    <span class="inline pull-left sms-demo">
      <span class="block row">
        <span class="block col-md-7 sms-demo-name">
          <strong>
            {{if emp_phone}}
              W {{:firstname + ' ' + lastname}}
            {{else}}
              {{if cc_data == null}}
                {{if client_name == null || client_name == ''}}
                  {{:~numberTo(sms_number)}}
                {{else}}
                  {{:client_name}}
                {{/if}}
              {{else}}
                {{:~splitName(cc_data)}}
              {{/if}}
            {{/if}}
          </strong>
        </span>
        <span class="block col-md-5">
          <span class="block pull-right">
            {{:~getDateFormat(sms_date)}} <i class="fa fa-angle-right m-l-xs"></i>
          </span>
        </span>
      </span>
      <span class="block clear"></span>
      <span class="block message-preview">
        {{:sms_body}}
      </span>
    </span>
    <span class="block clear"></span>
  </a>
</script>

<script id="chat_box_history_container_tpl" type="text/x-jsrender">
  <div class="tab-pane b-none" id="chat_box_{{:~trimNumber(sms_number)}}">
    <div class="smschat">
      <div class="chat-header bg-light p-5 text-center pos-abt b-b">
        <a class="pos-abt sms-to-list hidden-sm hidden-md hidden-lg"><i class="fa fa-chevron-left"></i></a>
        <div class="inline pull-left pos-abt">
          <span class="inline sms-avatar{{if emp_phone}} employee{{/if}}{{if cc_name && !cc_client_id}} supportchat{{/if}}">
            {{if emp_phone}}
              {{:~substrName(firstname + ' ' + lastname)}}
            {{else}}
              {{if cc_data == null}}
                {{if client_name == null || client_name == ''}}
                  {{:~substrName('NA')}}
                {{else}}
                  {{:~substrName(client_name)}}
                {{/if}}
              {{else}}
                {{:~substrName(cc_data)}}
              {{/if}}
            {{/if}}
          </span>
        </div>
        <div class="inline" style="margin-left: 45px;">
          {{if emp_phone}}
            {{:firstname + ' ' + lastname}}
          {{else}}
            {{if cc_data != null}}
                {{for cc_data tmpl="#chat_box_head_name_tpl_partial" ~length=~arrLength(cc_data) /}}
            {{else}}
                {{include tmpl = "#chat_box_head_name_tpl_partial" /}}
            {{/if}}
          {{/if}}
          <br>
          <?php if (config_item('phone')): ?>
            <a href="#" title="Call To {{:~numberTo(sms_number)}}" class="inline createCall" data-number="{{:~numberToE164(sms_number)}}"
                data-client-id="{{:cc_client_id}}">{{:~numberTo(sms_number)}}</a>
          <?php else: ?>
            <a href="#" title="{{:~numberTo(sms_number)}}" class="inline">{{:~numberTo(sms_number)}}</a>
          <?php endif; ?>
        </div>
        <div class="clear"></div>
        <div class="sms-search-buttons">
            <a class="sms-search-btn" data-action="prev" title="" data-toggle="tooltip" data-placement="bottom" data-original-title="Previous found">
                <i class="fa fa-chevron-up"></i>
            </a>
            <a class="sms-search-btn" data-action="next" title="" data-toggle="tooltip" data-placement="bottom" data-original-title="Next found">
                <i class="fa fa-chevron-down"></i>
            </a>
        </div>
      </div>
      <div class="messages-wrapper">
        <div class="sms-preloader">
          <div class="sms-preloader-cell">
            <img src="/assets/img/preloader.gif">
          </div>
        </div>
      </div>
      <div class="chat-footer p-bottom-10 p-top-10 pos-abt bg-white b-t">
          <div class="pull-left col-md-2 hide">
            <a href="#" class="btn btn-default btn-rounded open-chat-attachment"><i class="fa fa-paperclip"></i></a>
          </div>
          <div class="pull-left col-md-11 col-sm-10 col-xs-10">
            <textarea class="form-control rounded message-field-sms" data-number="{{:~trimNumber(sms_number)}}" rows="1" style="overflow-x: hidden; word-wrap: break-word; overflow-y: auto; height: 34px;"></textarea>
          </div>
          <div class="pull-left col-md-1 col-sm-2 col-xs-2 send-chat-block">
            <a href="#" class="btn btn-info btn-rounded btn-send-sms pos-abt" data-number="{{:~trimNumber(sms_number)}}"><i class="fa fa-arrow-up"></i></a>
          </div>
          <div class="clear"></div>
      </div>
    </div>
  </div>
</script>

<script id="chat_box_head_name_tpl_partial" type="text/x-jsrender">
    {{if ((cc_name != null && cc_name != '') || (client_name != null && client_name != ''))}}
        {{if cc_client_id}}
          <a title="Open Profile" href="<?php echo base_url(); ?>{{:cc_client_id}}" target="_blank">
        {{/if}}
        <strong>
            {{if cc_name == null || cc_name == ''}}
              {{if client_name != null && client_name != ''}}
                {{:client_name}}
              {{/if}}
            {{else}}
              {{:cc_name}}
            {{/if}}
        </strong>
        {{if cc_client_id}}
          </a>
        {{/if}}
        {{if #getIndex()+1 != ~length}}
            <span class="chat_box_name_separator">, </span>
        {{/if}}
    {{/if}}
</script>

<script id="reciipient_search_options" type="text/x-jsrender">
  <option id="{{:id}}">{{:text}}
      {{if text != id}}
          {{:~numberTo(id)}}
      {{/if}}
  </option>
</script>

<script id="chat_box_new_message_container_tpl" type="text/x-jsrender">
  <div class="tab-pane b-none" id="chat_box_new_message">
    <div class="smschat">
      <div class="chat-header bg-light p-5 text-center pos-abt b-b">
        <div class="">
            <form autocomplete="off" onsubmit="return false;">
              <div class="controls pos-rlt">
                <i class="fa fa-search pos-abt sms-search-icon"></i>
                <input name="userlist[]" class="select2-sendto" style="width: 100%;height: 40px;">
              </div>
            </form>
        </div>

        <div class="clear"></div>
      </div>
      <div class="messages-wrapper">
        <?php
    /*<div class="sms-preloader">
             <div class="sms-preloader-cell">
               <img src="/assets/img/preloader.gif">
             </div>
           </div>*/ ?>
      </div>
      <div class="chat-footer p-bottom-10 p-top-10 pos-abt bg-white b-t">
          <div class="pull-left col-md-2 hide">
            <a href="#" class="btn btn-default btn-rounded open-chat-attachment"><i class="fa fa-paperclip"></i></a>
          </div>
          <div class="pull-left col-md-11">
            <textarea class="form-control rounded message-field-sms" data-number="" rows="1" style="overflow-x: hidden; word-wrap: break-word; overflow-y: auto; height: 34px;"></textarea>
          </div>
          <div class="pull-left col-md-1 send-chat-block">
            <a href="#" class="btn btn-info btn-rounded btn-send-sms pos-abt"><i class="fa fa-arrow-up"></i></a>
          </div>
          <div class="clear"></div>
      </div>
    </div>
  </div>
</script>
