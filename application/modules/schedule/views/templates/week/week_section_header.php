<div data-date="{{:key}}" class='week-team-members-container'>
    <p style="margin-bottom: 2px">{{:~dateFormat(key, "ddd, MMMM DD")}}</p>
    <a data-crew_id="{{:team_id}}" class="hidden"></a>
    <div class="week-team-members-list">
    {{for members}}
    <div class="label label-info team-member-item employee_{{:id}}" data-emailid="{{:emailid}}" data-emp_id="{{:id}}" style="background:{{:#get('root').data.team_color}};">
        {{if id == #get('root').data.team_leader.id}}<span class="teamLeader">* </span>{{/if}}{{:full_name}}
    </div>
    {{/for}}
    </div>
</div>