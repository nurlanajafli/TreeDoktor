{{if team.team_date_start == team.team_date_end }}
<div class="btn-group copyCrewDropdown pos-abt">
    <a href="#" class="btn btn-default btn-xs dropdown-toggle copyCrew" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-copy"></i></a>
    <div class="dropdown-menu animated fadeInLeft pos-abt emp-dropdown">
        <form class="form-horizontal copyCrewForm" data-type="ajax" data-callback="copyTeamCallback" data-url="<?php echo base_url('schedule/ajax_copy_team'); ?>">
            <div class="form-group">
                <label class="col-sm-4 control-label" style="padding-right: 5px;padding-left: 0;">Select Date</label>
                <div class="col-sm-4" style="padding-left: 7px;">
                    <div>
                        <input type="text" class="form-control crew-datepicker text-center newTeamDate" readonly name="date" value="">
                        <input type="hidden" name="crew_id">
                    </div>
                </div>
                <div class="col-sm-2 p-n pull-right">
                    <button type="submit" class="btn btn-default btn- pull-right m-r">Copy</button>
                </div>
                <div class="clear"></div>
            </div>
        </form>
    </div>
</div>
{{/if}}

<div class="edit-team-dropdown dropdown p-top-5" id="edit-team-dropdown-{{:team.team_id}}">
    <a href="#" data-color="{{:team.team_color}}" data-crew_leader="{{:team.team_leader_user_id}}" data-crew_id="{{:team.team_id}}" data-crew_type_id="{{:team.crew.crew_id}}" class="dropdown-toggle day-edit-team" data-toggle="dropdown">
        {{:team.crew.crew_name}}
        {{if team.team_leader!=undefined && team.team_leader}}
            ({{:team.team_leader.full_name}})
        {{else}}
            (NoTeamLead)
        {{/if}}
    </a>

    <ul id="edit-team-dropdown-body-{{:team.team_id}}" class="dropdown-menu text-left edit-team-dropdown-body emp-dropdown animated fadeInLeft" data-crew_id="" data-team_id="">

    </ul>
    <?php /*
    <a class="btn btn-default btn-xs m-l-xs" target="_blank" href="/schedule/workorder_overview/{{:team.team_id}}">
        <i class="fa fa-print"></i>
    </a>
    */ ?>
</div>

<textarea class="team-note" placeholder="Team Note..." style="background-color:{{:team.team_color}};">{{:team.team_note}}</textarea>
<textarea class="hidden-team-note" placeholder="Hidden Team Note..." style="background-color:{{:team.team_color}};">{{:team.team_hidden_note}}</textarea>
