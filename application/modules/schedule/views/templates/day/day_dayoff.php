<div id="freeAgents" class="p-5 b-a bg-white">
    <ul class="text-left emp-dropdown p-left-0"
        data-crew_id="0">
        <div style="width:100%" class="pull-left">
            <li class="crewInfo ">Free Members:</li>
            <div class="line line-dashed line-lg line-members m-t-none"></div>
            <span class="freeMembersTitle"></span>
            {{for free_members}}
            {{if employee && employee.emp_feild_worker==1}}
            <li class="label bg-primary ui-draggable addMember b-a ui-sortable-handle free-member" style="text-shadow: 1px 1px #626262;" data-emailid="{{:emailid}}" data-emp_id="{{:id}}" data-user_id="{{:id}}" data-field_worker="{{:employee.emp_feild_worker}}">
                {{:full_name}}{{:#get('root').data.reasons}}
            </li>
            {{/if}}
            {{/for}}
            {{for free_members}}
            {{if employee && employee.emp_feild_worker!=1}}
            <li class="label bg-warning ui-draggable addMember b-a ui-sortable-handle free-member" style="text-shadow: 1px 1px #626262;" data-emailid="{{:emailid}}" data-emp_id="{{:id}}" data-user_id="{{:id}}">
                {{:full_name}}{{:#get('root').data.reasons}}
            </li>
            {{/if}}
            {{/for}}
        </div>
        <div class="clear"></div>
    </ul>
</div>
<div id="offAgents" class="p-5 b-a bg-white emp-dropdown m-t-xs">
    <div class="crewInfo">Absent Members:</div>
    <div class="line line-dashed line-lg line-members m-t-none"></div>
    <ul class="text-left emp-dropdown animated fadeInLeft p-left-0" data-crew_id="0">
        <div style="width:100%" class="pull-left">
            <div class="line line-dashed line-lg line-members m-t-none hide"></div>
            <span class="freeMembersTitle dayOff"></span>
        </div>
        {{for absences}}
        {{if user.employee}}
        <li class="label m-t-xs label-info employee_{{:user.id}}"
            data-field_worker="{{:user.employee.emp_feild_worker}}"
            data-emailid="{{:user.emailid}}"
            data-emp_id="{{:user.id}}"
            data-employee_id="{{:user.id}}"
            style="background:#f21b1b;display: inline-block;font-size: 12px;white-space: normal;">{{:user.full_name}} <span class="reasonTitle"> ({{:reason.reason_name}})</span> <a href="#" data-user_id="{{:user.id}}" class="deleteFromDayOff">x</a></li>
        {{/if}}
        {{/for}}
    </ul>
    </ul>
</div>