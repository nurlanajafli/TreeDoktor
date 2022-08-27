{{if estimate != undefined }}
<div class="panel m-b-xs m-l-xs no-shadow" style="border: 0">

    <div role="tab" id="heading-collapse-workorder-{{:id}}">
        <label class="btn btn-dark collapsed no-shadow map-workorder-details-label"
               data-wo-id="{{:id}}"
               role="button"
               data-toggle="collapse"
               data-parent="#scheduleWorkorders"
               id="map-workorder-{{:id}}"
               href="#collapse-workorder-{{:id}}" aria-expanded="false" aria-controls="#collapse-workorder-{{:id}}">

                    <div class="workorder-actual-price" style="top: {{if estimate.sum_actual_without_tax != estimate.sum_without_tax}}4{{else}}9{{/if}}px;">
                        <label class="badge bg-white">{{:~currency_format(estimate.sum_actual_without_tax, false)}}</label>
                    </div>
                    {{if estimate.sum_actual_without_tax != estimate.sum_without_tax}}
                    <div class="workorder-original-price">
                        <label>{{:~currency_format(estimate.sum_without_tax, false)}}</label>
                    </div>
                    {{/if}}
                    <div class="text-left" style="overflow: hidden;text-overflow: ellipsis; padding: 2px 0px;">
                    {{if workorder_no != undefined}}{{:workorder_no}} – {{/if}}
                    {{if estimate.estimates_services_crew[0] != undefined}}{{:estimate.estimates_services_crew[0].crew_name}} – {{/if}}
                    {{if ~floatval(estimate.total_time) }}{{:estimate.total_time}} hrs. – {{/if}}{{if estimate.client && estimate.lead }}{{:estimate.client.client_name}}, {{:estimate.lead.lead_address}}, {{:estimate.lead.lead_city}}{{/if}}

                    {{if days_from_creation }}
                        <span class="badge badge-xs bg-danger count" style="position:absolute;top:4px;right: 4px;">{{:days_from_creation}}</span>
                    {{/if}}
                    </div>

        </label>
    </div>

    <div id="collapse-workorder-{{:id}}" style="width: 98%" data-services="{}" data-wo-id="{{:id}}" data-estimate_no="{{:estimate.estimate_no}}" data-estimate_id="{{:estimate.estimate_id}}" data-client_id="{{:estimate.client.client_id}}" class="panel-collapse collapse map-workorder-details" role="tabpanel" aria-labelledby="heading-collapse-workorder-{{:id}}">
        <div style="height:200px;"></div>
    </div>

</div>
{{/if}}