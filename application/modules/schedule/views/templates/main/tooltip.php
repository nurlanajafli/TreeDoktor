<div class="inline-block" style="width: 300px;">
    <div class="clear m-bottom-10" style="margin-right: -10px; border-bottom: 1px solid #65bd77;">
        <div class="pull-left h5"><strong class="inline-block p-5">{{:wo_status_name}}</strong></div>
        <div class="h5 pull-right">
            <strong class="inline-block p-5 p-left-10 p-right-15 btn-primary" ><span class="glyphicon glyphicon-paperclip"></span> {{:workorder_no}}</strong>
        </div>
    </div>
    {{if team!=undefined}}
    <div class="clear">
        <div class="tooltip-line-label pull-left text-primary">
            <span class="fa fa-users"></span>
        </div>
        <div class="tooltip-line-text pull-right">
            <strong>
                {{:team.crew.crew_name}}
                ({{if team.team_leader.id != undefined}}
                    {{:team.team_leader.full_name}}
                {{else}}
                    No leader
                {{/if}})
            </strong>

            <i class="fa fa-circle text-white pull-right text-sm" style="margin: 5px; color: {{:team_color}}"></i>
        </div>
    </div><hr class="m-top-5">
    {{/if}}
    <div class="clear">
        <div class="tooltip-line-label pull-left text-primary">
            <span class="glyphicon glyphicon-time"></span>
        </div>
        <div class="tooltip-line-text pull-right">
            <strong>
                {{:~dateFormat(start_date, ~getTimeFormat())}} - {{:~dateFormat(end_date, ~getTimeFormat())}}
            </strong>
        </div>
    </div>
    <div class="clear">
        <div class="tooltip-line-label pull-left text-primary">
            <span class="glyphicon glyphicon-user"></span>
        </div>
        <div class="tooltip-line-text pull-right">
            {{:client_name}}
        </div>
    </div>

    <div class="clear">
        <div class="tooltip-line-label pull-left text-primary">
            <span class="glyphicon glyphicon-wrench"></span>
        </div>
        <div class="tooltip-line-text pull-right">
            {{for event_services}}
                {{if service!=undefined}}
                <strong class="inline-block" style="width:200px">{{:service.service_name}}</strong><strong class="inline-block" style="width:68px">{{:~currency_format(service_price, true)}}</strong><br>
                {{/if}}
            {{/for}}
        </div>
    </div>

    <div class="clear m-top-10 m-bottom-5" style="color: #1a73e8; background: #e8f0fe;">
        <div class="p-5 text-center">
            <div class="h5"><strong>{{:total_for_services}} - {{:total_service_time}}mhr. ({{:total_hours}} hr.)</strong></div>
        </div>
    </div>
</div>