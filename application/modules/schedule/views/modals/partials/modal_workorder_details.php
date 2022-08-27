{{if event }}
<section class="media-body panel panel-default p-n">
    <header class="panel-heading">Event Notes</header>
    <table class="table table-striped b-t bg-white m-n p-n">
        <tr>
            <td class="m-n p-n">
                <textarea id="eventNotes" class="form-control no-shadow" style="background: #fff;border:0px!important;height: 100px;">{{:event_note}}</textarea>
            </td>
        </tr>
    </table>
</section>
{{/if}}


<style type="text/css">
    .map-workorder-details .panel-default{margin-right: 0px!important;}
</style>
<section class="panel panel-default p-n" style="margin-bottom: 10px;">
    <!-- Workorder Details Header -->
    <header class="panel-heading">
        Workorder Details
        <a class="pull-right" target="_blank" href="/{{:workorder.workorder_no}}">
            <u>{{:workorder.workorder_no}}, {{:workorder.estimate.client.client_name}} - {{if workorder.estimate.user }}{{:workorder.estimate.user.emailid}}{{else}}deleted{{/id}}</u>
        </a>
    </header>
    <!-- Data Display -->
    <div class="panel-body">

        <input type="hidden" name="client_id" value="{{:workorder.estimate.client.client_id}}">
        <input type="hidden" name="estimate_id" value="{{:workorder.estimate.estimate_id}}">
        <input type="hidden" name="workorder_id" value="{{:workorder.id}}">
        <input type="hidden" name="workorder_no" value="{{:workorder.workorder_no}}">


        {{if workorder.estimate.crews}}
        <div class="p-top-10" style="position: relative;line-height: 10px;">
            <div style="display: flex; justify-content: space-between; flex-direction: row;">
                <div class="p-right-10 h5" style="width: 50%;">
                    <label><i class="fa fa-users text-primary"></i>&nbsp;<b>Crew:</b> </label>
                    {{for workorder.estimate.crews}}
                    <span>
                        {{:crew_name}}{{if #index+1 < #get("array").data.length}},&nbsp;{{/if}}
                    </span>
                    {{/for}}
                </div>
                <div class="p-left-10 h5" style="width: 50%;">
                    <label><i class="fa fa-money text-primary"></i>&nbsp;<b>Deposit amount:</b> </label>
                    {{if workorder.estimate.client_payments }}
                        {{:~client_payments_sum(workorder.estimate.client_payments)}}
                    {{else}}
                        {{:~currency_format(0)}}
                    {{/if}}
                </div>
            </div>
        </div>
        <hr class="m-5">
        {{/if}}

        <?php /*
        <div class="p-top-5">
            <label class="m-n text-nowrap">Notes:</label>
            <textarea name="wo_deposit_taken_by" id="wo_deposit_taken_by" rows="2" class="form-control" placeholder="Notes">{{:workorder.wo_deposit_taken_by}}</textarea>
        </div>
        <div class="p-top-5">
            <label class="m-n text-nowrap">Scheduling preferences:</label>
            <textarea name="wo_scheduling_preference" id="wo_scheduling_preference" rows="2" class="form-control" placeholder="Example only on weekends, within 2 weeks, in winter etc.">{{:workorder.wo_scheduling_preference}}</textarea>
        </div>
        <div class="p-top-5">
            <label class="m-n text-nowrap">Extra note for crew:</label>
            <textarea name="wo_extra_not_crew" id="wo_extra_not_crew" rows="2" class="form-control" placeholder="Extra note for crew">{{:workorder.wo_extra_not_crew}}</textarea>
        </div>
        <div class="p-top-5">
            <label class="m-n text-nowrap">Notes to the crew:</label>
            <textarea name="estimate_item_note_crew" id="estimate_item_note_crew" rows="2" class="form-control" placeholder="Write some notes to the crew">{{:workorder.estimate.estimate_item_note_crew}}</textarea>
            <!-- /Estmate items -->
        </div>
        */ ?>

        <div class="p-top-10" style="position: relative;line-height: 10px;">
            <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Office notes:</b></label>
            <textarea name="wo_office_notes" id="wo_office_notes" rows="2" data-id="{{:workorder.id}}" class="form-control p-right-20 workorder-note-text" placeholder="Over the phone, in person, by email etc.">{{:workorder.wo_office_notes}}</textarea>
            <a href="#" data-href="#wo_office_notes" data-id="{{:workorder.id}}" style="display: none" class="btn btn-sm btn-icon btn-default btn-save-note btn-save-office-note"><i class="fa fa-save text-primary"></i></a>
        </div>
        <div class="p-top-10" style="position: relative;line-height: 10px;">
            <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Crew notes:</b></label>
            <textarea name="estimate_crew_notes" id="estimate_crew_notes" rows="2" data-id="{{:workorder.id}}" class="form-control p-right-20 workorder-note-text" placeholder="Write some notes to the crew">{{:workorder.estimate.estimate_crew_notes}}</textarea>
            <a href="#" data-href="#estimate_crew_notes" data-id="{{:workorder.id}}" style="display: none" class="btn btn-sm btn-icon btn-default btn-save-note btn-save-crew-note"><i class="fa fa-save text-primary"></i></a>
        </div>
        <div class="p-top-15" style="line-height: 10px;">
            <label class="m-n text-nowrap"><i class="fa fa-list-alt text-primary"></i>&nbsp;<b>Confirmed How :</b></label><label class="m-n text-nowrap">{{:workorder.wo_confirm_how}}</label>
        </div>

    </div>
</section>

<!-- Estimate Information Display -->
<section class="media-body p-n">
    <section class="panel panel-default">
        <table class="table table-striped b-light m-n estimate_statuses">
            <thead>
            <tr>
                <th>Description
                    {{if workorder.estimate && workorder.estimate.tree_inventory_pdf && ~intval(workorder.estimate.tree_inventory_pdf)  }}{{:workorder.estimate.tree_inventory_pdf}}
                    <div class="pull-right m-bottom-0">
                        <div class="checkbox m-n">
                            <label class="checkbox-custom">`
                                <input type="checkbox" name="tree_inventory_pdf" checked="checked" disabled="disabled">
                                <i class="fa fa-fw fa-square-o"></i>
                                <strong style="color: #4aa700;text-decoration: underline;">Tree Inventory PDF</strong>&nbsp;&nbsp;
                            </label>
                            <i class="h4 fa fa-file-text-o"></i>
                        </div>
                    </div>
                    <div class="clear"></div>
                    {{/if}}
                </th>
            </tr>
            </thead>
            <tbody>
            {{for workorder.estimate.estimates_service itemVar="~service_data"}}

                <tr data-estimate_sesvice_time="{{:(~floatval(~service_data.service_time) + ~floatval(~service_data.service_travel_time) + ~floatval(~service_data.service_disposal_time)) * ~service_data.services_crew_count }}" data-estimate_service_id="{{:~service_data.id}}" data-workorder-id="{{:#parent.parent.data.workorder.id}}">

                    <td>
                        {{if #get("root").data.estimates_services_status && #get("root").data.estimates_services_status.length }}
                            <div class="control-group m-t-sm m-b-sm">
                                <div class="inline pull-left">
                                    <label>Status: </label>
                                    <select name="service_status" {{if #get("root").data.workorder.estimate.estimate_status.est_status_confirmed==0}}disabled{{/if}} class="form-control" id="{{:~service_data.id}}" data-id="{{:~service_data.id}}" onchange="changeStatus($(this))" style="display: inline-block; width: 145px;">
                                        {{for #get("root").data.estimates_services_status}}
                                            <option name="{{:services_status_name}}" value="{{:services_status_id}}" {{if ~service_data.service_status == services_status_id}}selected="selected"{{/if}}>
                                                {{:services_status_name}}
                                            </option>
                                        {{/for}}
                                    </select>
                                </div>

                                <div class="inline pull-right">
                                    {{:~currency_format(~floatval(~service_data.service_price)) }}
                                    <span class="btn btn-xs btn-warning selectService{{if ~service_data.service_status == 1 || ~service_data.service_status == 2}} disabled{{/if}}" style="margin-top: -3px; width: 24px;"><i class="fa fa-circle-o"></i></span>
                                </div>
                                <div class="clear"></div>
                            </div>
                        {{/if}}
                        {{if !~service_data.estimate_service_ti_title}}
                        <h5><strong>{{:~service_data.service.service_name}}</strong></h5>
                        {{else}}
                        <h5><strong>{{:~service_data.estimate_service_ti_title}}
                                {{if ~service_data.tree_inventory && ~service_data.tree_inventory.ties_priority}}, Priority: {{:~service_data.tree_inventory.ties_priority}}  {{/if}}
                            </strong>
                        </h5>
                        {{/if}}

                        {{if ~service_data.tree_inventory && ~service_data.tree_inventory.tree_inventory_work_types.length}}
                            Work Types:
                            {{for ~service_data.tree_inventory.tree_inventory_work_types itemVar="~work_type"}}
                                {{if ~work_type.work_type }}
                                    {{:~work_type.work_type.ip_name}}
                                    {{if #getIndex() + 1 != ~service_data.tree_inventory.tree_inventory_work_types.length}}, {{/if}}
                                {{/if}}
                            {{/for}}
                            <br>
                        {{/if}}
                        {{:~text_decorate(~nl2br(~service_data.service_description, false))}}

                        {{if ~service_data.service.is_bundle == "1" && ~service_data.bundle.length}}
                        <div  style="border-left: 1px solid #bebebe;">
                            {{for ~service_data.bundle itemVar="~bundle"}}
                            <div style="padding-left: 20px;">
                                <div class="">
                                    <div class="pull-left text-left">
                                        <strong>{{:~bundle.estimate_service.service.service_name}}</strong>
                                        {{if ~intval(~bundle.estimate_service.non_taxable)}}
                                            <span>(Non-taxable)</span>
                                        {{/if}}
                                        <span> <?php /*echo str_replace(' ', '', nl2br(money($bundle_record->cost)));*/ ?>
                                            {{if ~intval(~bundle.estimate_service.service.is_product)}}
                                                ({{:~bundle.estimate_service.quantity }} x {{:~currency_format(~bundle.estimate_service.cost)}}  {{:~currency_format(~bundle.estimate_service.service_price)}})
                                            {{else}}
                                                ({{:~currency_format(~bundle.estimate_service.service_price)}})
                                            {{/if}}
                                        </span>

                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="m-t-b-5 p-bottom-10">
                                    <div class="pull-left p-b-5" style="text-align: justify;">
                                        {{:~bundle.estimate_service.service_description}}
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div>
                            {{/for}}
                        </div>
                        {{/if}}

                        {{if ~service_data.service.is_product==0 && ~service_data.service.is_bundle==0}}
                        <div style="display: flex; justify-content: space-between;" class="m-t-md">
                            <div style="min-width: 130px" class="small m-right-10">
                                {{if ~service_data.crew && ~service_data.crew.length }}
                                    <label>
                                        <strong>Team Requirements:</strong>
                                    </label>
                                    {{for ~service_data.crew}}
                                        <div>
                                            <i class="fa fa-check"></i>
                                            {{:crew_name}}<br>
                                        </div>
                                    {{/for}}
                                {{/if}}
                            </div>
                            <div class="small">
                                {{if ~service_data.equipments && ~service_data.equipments.length }}
                                <label>
                                    <strong>Equip. Requirements:</strong>
                                </label>

                                {{for ~service_data.equipments}}
                                    <div>
                                        {{if equipment && equipment.vehicle_name}}
                                            <i class="fa fa-check"></i>
                                            {{:equipment.vehicle_name}}
                                            {{if equipment_item_option && ~parseStrJson(equipment_item_option).length}}
                                            (
                                            {{for ~parseStrJson(equipment_item_option) itemVar="~option"}}
                                            {{:~option}}{{if #getIndex() < #parent.length}} or {{/if}}
                                            {{/for}}
                                            )
                                            {{else}}
                                            (Any)
                                            {{/if}}
                                        {{/if}}
                                        {{if attachment && attachment.vehicle_name}}
                                            {{if !equipment || equipment.vehicle_name==undefined || !equipment.vehicle_name}}
                                                <i class="fa fa-check"></i>
                                                {{:attachment.vehicle_name}}
                                            {{else}}
                                                , {{:attachment.vehicle_name}}
                                            {{/if}}
                                            {{if equipment_attach_option && ~parseStrJson(equipment_attach_option).length}}
                                                (
                                                {{for ~parseStrJson(equipment_attach_option) itemVar="~option"}}
                                                {{:~option}}{{if #getIndex() < #parent.length}} or {{/if}}
                                                {{/for}}
                                                )
                                            {{else}}
                                            (Any)
                                            {{/if}}
                                        {{/if}}

                                        {{if equipment_attach_tool }}
                                        {{for ~parseStrJson(equipment_tools_option) itemVar="~tools_option"}}
                                            {{props ~tools_option }},&nbsp;{{:prop}}{{/props}}
                                        {{/for}}
                                        {{/if}}

                                        <br>
                                    </div>
                                {{/for}}
                                {{/if}}
                            </div>
                            <div style="min-width: 105px" class="small m-left-10">
                                {{if ~intval(~service_data.non_taxable)}}
                                <div class="small">
                                    <i class="fa fa-check"></i>
                                    Non-taxable
                                </div>
                                {{/if}}
                                {{if ~service_data.service.is_product==0 && ~service_data.service.is_bundle==0}}
                                    {{if ~service_data.service_time!=undefined && ~service_data.service_time && ~service_data.service_time > 0 ||
                                         ~service_data.service_travel_time!=undefined && ~service_data.service_travel_time && ~service_data.service_travel_time > 0 ||
                                         ~service_data.service_disposal_time!=undefined && ~service_data.service_disposal_time && ~service_data.service_disposal_time > 0 }}
                                        <label>
                                            <strong>Times:</strong>
                                        </label>

                                        <div>
                                            {{if ~service_data.service_time!=undefined && ~service_data.service_time && ~service_data.service_time > 0}}
                                            Service Time:
                                            {{:~service_data.service_time}} hrs.
                                            {{/if}}
                                        </div>
                                        <div>
                                            {{if ~service_data.service_travel_time!=undefined && ~service_data.service_travel_time && ~service_data.service_travel_time > 0}}
                                            Travel Time:
                                            {{:~service_data.service_travel_time}} hrs.
                                            {{/if}}
                                        </div>
                                        <div>
                                            {{if ~service_data.service_disposal_time!=undefined && ~service_data.service_disposal_time && ~service_data.service_disposal_time > 0}}
                                            Disposal Time:
                                            {{:~service_data.service_disposal_time}} hrs.
                                            {{/if}}
                                        </div>
                                    {{/if}}
                                {{else}}
                                <label>
                                    <strong>Product Details:</strong>
                                </label>
                                <div>
                                    Cost:
                                    {{if service_data.cost!=undefined && service_data.cost}}
                                    {{:currency_format(service_data.cost)}}
                                    {{else}}
                                    {{:currency_format(0)}}
                                    {{/if}}

                                </div>
                                <div>
                                    Quantity:
                                    {{if service_data.quantity!=undefined && service_data.quantity}}
                                    {{:currency_format(service_data.quantity)}}
                                    {{else}}
                                    0
                                    {{/if}}
                                </div>
                                {{/if}}
                            </div>
                        </div>

                        {{/if}}
                    </td>
                </tr>
            {{/for}}
            </tbody>
        </table>

        {{if workorder.estimate.invoice && workorder.estimate.invoice.invoice_notes}}
        <div class="  panel panel-default p-n">
            <header class="panel-heading">Invoice Notes</header>
            <table class="table table-striped b-t bg-white m-n">
                <tbody>
                <tr>
                    <td style="padding-top: 12px;white-space: pre-wrap; background-color:#fff;">{{:workorder.estimate.invoice.invoice_notes}}</td>
                </tr>
                </tbody>
            </table>
        </div>
        {{/if}}
    </section>

    <div class=" panel panel-default p-n" id="modal-estimate-files"></div>
    <div id="status-logs"></div>
    <section class="panel panel-default bg-white" style="border-bottom: 0;border-radius: 0;">
        <table class="table b-t bg-white m-n">
            <tbody>
                <tr>
                    <td>
                        <div class="m-top-5"><i class="fa fa-pencil text-success"></i>&nbsp;Client Notes:</div>
                    </td>
                    <td style="width:30%">
                        <div class="m-top-5">
                            <label class="switch-mini m-b-n">
                                <input type="checkbox" id="show-client-notes">
                                <span></span>
                            </label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
    <div id="client-notes" style="display: none;"></div>

</section>

<script>
    var segment = 'schedule';

    function changeStatus(value, wo_invoice)
    {
        var profilesModules = ['estimates', 'workorders', 'invoices'];
        var invoice = wo_invoice ? wo_invoice : 0;
        var id = $(value).data('id');
        var status = $(value).val();
        var nameStatus = $('#' + id + ' option:selected').text();
        $.post(baseUrl + 'estimates/ajax_change_status', {status: status, id:id, name:nameStatus}, function (resp) {

            var isUpdate = false;

            if (resp.status == 'error')
                alert('Ooops! Error...');
            else
            {
                if($(value).parents('td:first').find('.selectService').length)
                {
                    if(status == 1 || status == 2)
                        $(value).parents('td:first').find('.selectService').addClass('disabled');
                    else
                        $(value).parents('td:first').find('.selectService').removeClass('disabled');
                }

                if(resp.finish !== undefined && resp.finish == 0)
                {
                    if($(value).parents('tr:first').data('workorder-id'))
                    {
                        if(!invoice) {
                            if(confirm('All services for this Estimate was completed. Do you want change workorder status to Finished?'))
                            {
                                var woId = $(value).parents('tr:first').data('workorder-id');
                                $.post(baseUrl + 'workorders/ajax_change_workorder_status/', {workorder_id:$(value).parents('tr:first').data('workorder-id'),workorder_status:0}, function(resp) {
                                    $('#email-template-form').find('input[name="estimate_id"]').val(resp.workorder_data.estimate_id);
                                    callback = function(){
                                        ClientsLetters.init_modal(resp.invoice_email_template, 'ClientsLetters.invoice_email_modal');
                                    }

                                    DamagesModal.init(woId, false, callback);

                                }, 'JSON');
                            } else {
                                if(profilesModules.includes(segment))
                                    location.reload();
                            }
                        } else {
                            if(profilesModules.includes(segment))
                                location.reload();
                        }
                    } else {
                        if(profilesModules.includes(segment))
                            location.reload();
                    }
                } else {
                    if(profilesModules.includes(segment))
                        location.reload();
                }
            }
            return false;
        }, 'json');
    }
</script>

<style>
    .btn-save-note{
        position: absolute;
        top: 46px;
        right: 1px;
        border-bottom: 0;
        border-right: 0;
    }
</style>

<?php /* ini_set('date.timezone', 'America/Vancouver');
date_default_timezone_set( 'America/Vancouver');
 */ ?>

