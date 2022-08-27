<div class="schedule-y-headers">
    <div class="schedule-y-headers-label"><label class="h5">{{:label}}</label></div>
    <div class="schedule-y-headers-teams" id="schedule-y-header-team-{{:team_leader_id}}">
    {{for teams itemVar="~team"}}
    <div style="position: relative">
        <button style="border:0;color:{{:~team.team_color}};background: none" data-toggle="popover" data-html="true" data-placement="right" data-content="
            <div class='scrollable p-10' style='max-height:150px;width:400px;padding-top:0'>
                <div class='row'>
                    <div class='col-md-5 m-right-0'>
                        <div class='text-primary'><i class='fa fa-users'></i>&nbsp;Crew</div>
                        {{for ~team.schedule_teams_members_user itemVar='~member'}}
                            <div>{{:~member.full_name}}</div>
                        {{/for}}
                    </div>
                    <div class='col-md-7'>
                        <div class='text-danger'><i class='fa fa-truck'></i>&nbsp;Equipment</div>
                        {{for ~team.schedule_teams_equipments itemVar='~equipment'}}
                        <div>{{:~html_entitles(~equipment.eq_name)}}</div>
                        {{/for}}
                    </div>
                </div>
            </div><a class='btn btn-xs btn-primary btn-rounded timeline-edit-team-btn' data-toggle='modal' data-team_id='{{:~team.team_id}}' data-team_date_start='{{:~team.team_date_start}}' data-team_date_end='{{:~team.team_date_end}}' href='#timeline-team-modal' data-backdrop='static' data-keyboard='false'><i class='fa fa-pencil'></i></a>"
                title=""
                data-original-title="<div class=&quot;pull-left&quot; style=&quot;color:{{:~team.team_color}}&quot;><i class=&quot;fa fa-circle&quot;></i></div><span class=&quot;m-right-10 pull-in&quot;><i class=&quot;fa fa-calendar&quot;></i>&nbsp;{{:~team.team_date_start_view}}{{if ~team.team_date_start!=~team.team_date_end}} - {{:~team.team_date_end_view}}{{/if}}</span>">
            <i class="fa fa-calendar"></i>&nbsp;{{:~team.team_date_start_view}}{{if ~team.team_date_start!=~team.team_date_end}} - {{:~team.team_date_end_view}}{{/if}}
            <span class="badge badge-info team-users-counter">
                <i class="fa fa-users"></i> {{:~team.schedule_teams_members_user.length}}
            </span>
        </button>

        <a class="delete-timeline-team" data-id="{{:~team.team_id}}" data-team_leader="{{if ~team.team_leader!=null}}{{:~team.team_leader.full_name}}{{/if}}">
            <i class="fa fa-trash-o text-danger"></i>
        </a>
    </div>
    {{/for}}
</div>
</div>