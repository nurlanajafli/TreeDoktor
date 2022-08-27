<div id="{{:lead_id}}" class="marker taskMarker infowindow-container" style="overflow-x: hidden;">

    <input type="hidden" name="marker_key" value="{{:marker_key}}">

    <div class="row" style="min-width: 550px">
        <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8" style="line-height: 1.7;">
            <strong data-user="{{:lead_estimator}}">
                <a href="/client/{{:client.client_id}}" target="_blank" class="pull-left">{{:client.client_name}}</a>
                <form action="" data-global="false" data-type="ajax" data-url="/leads/change_lead_priority" data-callback="LeadsMapper.lead_priority">
                    <input type="hidden" name="lead_id" value="{{:lead_id}}">
                    <input type="hidden" name="lead_priority" value="{{if lead_priority}}{{:lead_priority}}{{else}}Regular{{/if}}">
                    <div class="dropdown pull-right">
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle {{:~getPriorityClass(lead_priority)}}">
                        <span class="{{:~getPriorityClass(lead_priority)}}">{{if lead_priority}}{{:lead_priority}}{{else}}Regular{{/if}}</span>
                        <b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        {{for ~getPriorities(lead_priority)}}
                        <li class="change-lead-priority" data-value="{{:name}}"><a href="#"><i class="fa fa-circle {{:class}}"></i>&nbsp;{{:name}}</a></li>
                        {{/for}}
                    </ul>
                    </div>
                </form>
                <div class="clear"></div>
            </strong>
            <?php /*
            <div class="form-inline p-top-5">
                <form action="" data-global="false" data-type="ajax" data-url="/leads/change_lead_priority" data-callback="LeadsMapper.lead_priority">
                    <input type="hidden" name="lead_id" value="{{:lead_id}}">
                    <select name="lead_priority" class="form-control input-sm" style="width: 100%">
                        <option value="Regular" {{if lead_priority == 'Regular' || !lead_priority}}selected="selected"{{/if}}>
                            <i class="fa fa-circle text-success"></i>&nbsp;Regular
                        </option>
                        <option value="Priority" {{if lead_priority == 'Priority'}}selected="selected"{{/if}}>
                            <i class="fa fa-circle text-warning"></i>&nbsp;Priority
                        </option>
                        <option value="Emergency" {{if lead_priority == 'Emergency'}}selected="selected"{{/if}}>
                            <i class="fa fa-circle text-danger"></i>&nbsp;Emergency
                        </option>
                    </select>
                </form>
            </div>
 */ ?>
            <div><i class="glyphicon glyphicon-briefcase text-success"></i>&nbsp;
                {{if lead_no }}
                <strong>Lead №:</strong>&nbsp;<a href="/{{:lead_no}}" target="_blank">{{:lead_no}}</a>
                {{/if}}
            </div>
            <div><i class="glyphicon glyphicon-user text-success"></i>&nbsp;
                {{if user && user.id }}
                <strong>{{:user.full_name}}</strong>
                {{else}}
                <strong>Not Assigned</strong>&nbsp;
                {{/if}}
            </div>
            <div><i class="glyphicon glyphicon-calendar text-success"></i>&nbsp;{{:lead_date_created_view}}</div>
            {{if client && client.primary_contact && client.primary_contact.cc_phone_view}}
                <div><i class="glyphicon glyphicon-earphone text-success"></i>&nbsp;{{:client.primary_contact.cc_phone_view}}</div>
            {{/if}}
            <div><i class="glyphicon glyphicon-map-marker text-success"></i>&nbsp;{{:full_address}}</div>
            {{if ~filterServices(lead_services).length}}
            <div>
                <i class="glyphicon glyphicon-wrench text-success"></i>&nbsp;Services:
                {{for ~filterServices(lead_services) }}
                    {{:service_name}}{{if #index+1 < #get("array").data.length}},&nbsp;{{/if}}
                {{/for}}
            </div>
            {{/if}}
            {{if ~filterProducts(lead_services).length}}
            <div>
                <i class="glyphicon glyphicon-shopping-cart text-success"></i>&nbsp;Products:
                {{for ~filterProducts(lead_services) }}
                {{:service_name}}{{if #index+1 < #get("array").data.length}},&nbsp;{{/if}}
                {{/for}}
            </div>
            {{/if}}
            {{if ~filterBundles(lead_services).length}}
            <div>
                <i class="glyphicon glyphicon-gift text-success"></i>&nbsp;Bundles:
                {{for ~filterBundles(lead_services) }}
                {{:service_name}}{{if #index+1 < #get("array").data.length}},&nbsp;{{/if}}
                {{/for}}
            </div>
            {{/if}}

            <div class="m-top-5" style="line-height: 1.2;">
                <blockquote class="p-n" style="padding-top: 0; padding-left: 10px!important;font-size: 13.5px;border-left-color: #daefca!important;">{{:lead_body}}</blockquote>
            </div>

        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
            <?php /*
            {{if sms.sms_id !=undefined }}
            <a class="btn btn-info addLeadSms btn-block btn-sm" data-phone ="{{if client && client.primary_contact }}{{:client.primary_contact.cc_phone}}{{/if}}" data-email="{{if client && client.primary_contact }}{{:client.primary_contact.cc_email}}{{/if}}" data-name="{{if client && client.primary_contact }}{{:client.primary_contact.cc_name}}{{/if}}" data-company="{{if client && client.brand }}{{:client.brand.brand_name}}{{/if}}" data-company-phone="{{:client.brand.brand_phone}}" data-href="#send-sms-modal"> SMS to {{:client.primary_contact.cc_name}}</a>
            {{/if}}

            <a class="btn btn-default btn-sm btn-block btn-sm {{if ~intval(lead_call) }}active{{/if}}" style="padding: 5px 7px 3px;" href="#lead_call" data-toggle="button" onclick="$($(this).attr('href')).trigger('click');" aria-pressed="false">
                    <span class="text">
                        <span class="fa-stack fa-1x pull-left m-r-xs">
                            <i class="fa fa-circle fa-stack-2x text-danger"></i>
                            <i class="fa fa-phone fa-stack-1x text-white"></i>
                        </span><span class="inline-block" style="line-height: 24px;">Call The Client</span> <i class="fa fa-times text-danger"></i>
                    </span>
                <span class="text-active">
                        <span class="fa-stack fa-1x pull-left m-r-xs">
                            <i class="fa fa-circle fa-stack-2x text-success"></i>
                            <i class="fa fa-phone fa-stack-1x text-white"></i>
                        </span><span class="inline-block" style="line-height: 24px;">Call The Client</span> <i class="fa fa-check text-success"></i>
                    </span>
            </a>
            <input type="checkbox" class="callLead hide" id="lead_call" data-lead_id="{{:lead_id}}" name="lead_call" {{if ~intval(lead_call) }}checked="checked"{{/if}}>
            */ ?>

            <div class="btn-group btn-group-justified p-n">
                {{if sms.sms_id!=undefined }}
                    <a class="btn btn-info addLeadSms btn-xs" data-phone="{{if client && client.primary_contact }}{{:client.primary_contact.cc_phone_clean}}{{/if}}" data-email="{{if client && client.primary_contact }}{{:client.primary_contact.cc_email}}{{/if}}" data-name="{{if client && client.primary_contact }}{{:client.primary_contact.cc_name}}{{/if}}" data-company="{{if client && client.brand }}{{:client.brand.brand_name}}{{/if}}" data-company-phone="{{:client.brand.brand_phone}}" data-href="#sms-{{:sms.sms_id}}">SMS</a>
                {{/if}}

                <a class="btn btn-default btn-xs {{if ~intval(lead_call) }}active{{/if}}" style="padding: 3px 7px 1px;border-top-right-radius: 2px;border-bottom-right-radius: 2px;" href="#lead_call" data-toggle="button" onclick="$($(this).attr('href')).trigger('click');" aria-pressed="false">
                    <span class="text">
                        <span class="fa-stack fa-1x pull-left m-r-xs">
                            <i class="fa fa-circle fa-stack-2x text-danger"></i>
                            <i class="fa fa-phone fa-stack-1x text-white"></i>
                        </span>
                        <span class="inline-block" style="line-height: 24px;">Call</span> <i class="fa fa-times text-danger"></i>
                    </span>
                        <span class="text-active">
                        <span class="fa-stack fa-1x pull-left m-r-xs">
                            <i class="fa fa-circle fa-stack-2x text-success"></i>
                            <i class="fa fa-phone fa-stack-1x text-white"></i>
                        </span>
                        <span class="inline-block" style="line-height: 24px;">Call</span> <i class="fa fa-check text-success"></i>
                    </span>
                </a>
                <input type="checkbox" class="callLead hide" id="lead_call" data-lead_id="{{:lead_id}}" name="lead_call" {{if ~intval(lead_call) }}checked="checked"{{/if}}>
            </div>
            <div class="form-inline p-top-5">
                <form action="" data-global="false" data-type="ajax" data-url="/leads/assign_lead" data-callback="LeadsMapper.assigned_user">
                    <input type="hidden" name="assigned_what" value="{{:lead_id}}">
                    <select name="assigned_to" class="form-control assigned_to input-sm" style="width: 100%">
                        <option value="none">Not assigned</option>
                        {{for ~getEstimators(user?user.id:0) itemVar="~estimator" }}
                        <option value="{{:~estimator.id}}" {{if ~estimator.selected!=undefined && ~estimator.selected}}selected="selected"{{/if}} >{{:~estimator.full_name}}</option>
                        {{/for}}
                    </select>
                </form>
            </div>
            <div class="form-inline p-top-5">
                <a href="/estimates/new_estimate/{{:lead_id}}" class="btn btn-success btn-block btn-sm">&nbsp;
                    <i class="icon-leaf icon-white" title="Create an estimate"></i>&nbsp;Create Estimate&nbsp;
                </a>
            </div>

            <div class="form-inline p-top-5">
                <form action="#" action="" data-global="false" data-type="ajax" data-url="/leads/update_lead_status" data-callback="LeadsMapper.change_lead_status_callback" id="lead-status-form">
                    <input type="hidden" name="lead_id" value="{{:lead_id}}">
                    <div class="row">
                        <div class="col-lg-12">
                            <select name="lead_status" id="lead-status" class="form-control input-sm" style="width: 100%;">
                                {{for statuses itemVar="~status" }}
                                    {{if ~status.lead_status_estimated==0 && ~status.lead_status_draft==0}}
                                    <option value="{{:~status.lead_status_id}}" {{if ~status.selected }}selected="selected"{{/if}}>{{:~status.lead_status_name}}</option>
                                    {{/if}}
                                {{/for}}
                            </select>
                        </div>
                    </div>
                    <div class="row m-top-5" id="lead-status-reasons"></div>
                    <div class="row" id="lead-status-buttons"></div>

                </form>
            </div>

        </div>

    </div>

    <div style="clear: both"></div>
</div>
