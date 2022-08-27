
<script type="text/x-jsrender" id="schedule-y-header-tmp">
    <?php $this->load->view('schedule/templates/timeline/schedule_y_header'); ?>
</script>

<script type="text/x-jsrender" id="timeline-team-modal-body-tmp">
    <?php $this->load->view('schedule/templates/timeline/timeline_team_modal_body'); ?>
</script>

<script type="text/x-jsrender" id="timeline-tooltip-tmp">
    <?php $this->load->view('schedule/templates/main/tooltip'); ?>
</script>

<script type="text/x-jsrender" id="team-leaders-dropdown-tmp">
    <option value="0">Select team leader</option>
    {{if team.team_id}}
        {{for team_members }}
        <option value="{{:id}}" {{if  ~integer(#get("root").data.team_leader) && ~integer(#get("root").data.team_leader)==~integer(id)}}selected="selected"{{/if}}>{{:text}}</option>
        {{/for}}
    {{/if}}
    {{for free_members }}

        {{if (~integer(#get("root").data.team_leader)!=~integer(id) && #get("root").data.team.team_id) || #get("root").data.team.team_id==undefined}}
        <option value="{{:id}}" {{if  ~integer(#get("root").data.team_leader) && ~integer(#get("root").data.team_leader)==~integer(id)}}selected="selected"{{/if}}>{{:text}}</option>
        {{/if}}

    {{/for}}
</script>

<script type="text/x-jsrender" id="free-members-list-content-tmp">
    {{for free_members}}
    <span class="label bg-primary p-5 m-5 timeline-add-member-to-team" data-id="{{:id}}">{{:text}}</span>
    {{/for}}
</script>

<script type="text/x-jsrender" id="free-equipment-list-content-tmp">
    {{props free_items_groups}}
        {{for prop}}
            <span class="label bg-danger timeline-add-item-to-team"
                style="background: {{if group!=undefined }}{{:group.group_color}}{{else}}#cccedd{{/if}}"
                data-id="{{:eq_id}}"
                >{{:eq_name}}</span>
        {{/for}}
    {{/props}}
</script>

<script type="text/x-jsrender" id="team-warning-tmp">
{{if busy_members_in_other_teams.length || busy_items_in_other_teams.length}}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="row">
            <div class="col-md-3 col-sm-3 col-xs-3 p-top-5 text-center"></div>
            <div class="col-md-9 col-sm-9 col-xs-9 m-bottom-10" style="min-height: 39px;">
                <div class="p-top-10" style="border-top: 1px solid #ffc773;width: 575px;">
                    {{if busy_members_in_other_teams.length}}
                        {{for busy_members_in_other_teams}}
                        <strong style="color:#ffc773">{{:text}}{{if #index+1 < #get("array").data.length}},&nbsp;{{/if}}</strong>
                        {{/for}}
                    {{/if}}
                    {{if busy_items_in_other_teams.length}}<strong style="color:#ffc773">,&nbsp;</strong>
                        {{for busy_items_in_other_teams}}
                        <strong style="color:#d69c46">{{:equipment.eq_name}}{{if #index+1 < #get("array").data.length}},&nbsp;{{/if}}</strong>
                        {{/for}}
                    {{/if}}
                    already busy on selected date range
                    <a href="javascript:void(0)" class="reset-team-modal" style="text-decoration:none" data-team_id="{{:team.team_id}}" data-team_date_start="{{:team.team_date_start}}" data-team_date_end="{{:team.team_date_end}}">
                        <strong class="text-primary">Cancel</strong>&nbsp;&nbsp;<i class="fa fa-undo text-primary"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
{{/if}}
</script>