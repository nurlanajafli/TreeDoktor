<div class="map-teams-list">
    <section class="panel panel-default m-b-n b-0">
        <header class="panel-heading bg-danger lt no-border">
            <div class="clearfix">
                {{if ~object_length(current_team)!=0}}
                <a href="#" class="pull-left thumb avatar b-3x m-r" style="width: 45px;display: inline-block;">
                    <img src="{{if current_team.team_leader!=undefined}}{{:current_team.team_leader.picture}}{{else}}/assets/pictures/avatar_default.jpg{{/if}}" class="img-circle">
                </a>
                <div>
                    <div class="h4 m-t-xs m-b-xs text-white">
                        {{if current_team.team_leader!=undefined && current_team.team_leader!=false}}
                            {{:current_team.team_leader.full_name}}
                        {{else}}
                            No Leader
                        {{/if}}
                        <i class="fa fa-circle text-white pull-right text-md" style="margin: 2px 4px;color: {{:current_team.team_color}}"></i>
                    </div>
                    <b style="color: #169148;">Current Crew</b>
                </div>
                {{else}}
                <div class="h5 m-t-xs m-b-xs text-white">Team Leader:</div>
                <b style="color: #169148c2;"><i class="fa fa-info-circle text-white"></i>&nbsp;Check the checkbox to show crew jobs</b>
                {{/if}}
            </div>
        </header>
        <div class="list-group no-radius alt">
            {{if teams.length}}
                {{for teams}}
                <a class="list-group-item" href="#">
                    <button data-id="{{:team_id}}" class="btn btn-default btn-rounded btn-xs no-border pull-right enable-map-directions" data-toggle="button" aria-pressed="true">
                        <i class="fa fa-road text"></i>
                        <i class="fa fa-road text-active text-success"></i>
                    </button>
                    <i class="fa fa-circle text-white pull-right text-sm" style="margin: 5px; color: {{:team_color}}"></i>
                    <div class="checkbox m-n team-lead-filter-item">
                        <label class="checkbox-custom">
                            <input type="checkbox" name="checkboxA" class="show-team-markers" value="{{:team_id}}" {{if ~object_length(#get("root").data.current_team)!=0 && team_id==#get("root").data.current_team.team_id}}checked="checked"{{/if}}>
                            <i class="fa fa-fw fa-square-o"></i>
                            {{if team_leader.full_name!=undefined}}
                                {{:team_leader.full_name}}
                            {{else}}
                                No Leader
                            {{/if}}
                        </label>
                    </div>
                </a>
                {{/for}}
            {{else}}
                <div class="empty-map-team-leaders-list text-center h4 p-5">No scheduled</div>
            {{/if}}
        </div>
    </section>



</div>


<button class="btn btn-xs team-lead-filter-close-btn" data-toggle="class:hide show_filter, active" data-target="#map-teams-marker-filter-dropdown,#show-team-lead-filter">
    <i class="fa fa-chevron-circle-right"></i>
</button>

<div class="team-lead-filter-nav">

    <button class="btn btn-block team-lead-filter-checkall" style="background: #ffffff75; border:none;outline: unset;" data-toggle="button">
        <span class="text">
            <i class="fa fa-check text-success"></i> Check All
        </span>
        <span class="text-active">
            <i class="fa fa-close text-danger"></i> Uncheck All
        </span>
    </button>

</div>