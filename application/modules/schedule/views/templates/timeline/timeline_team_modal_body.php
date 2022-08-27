{{if team!=undefined && team.team_id != undefined }}
<input type="hidden" id="timeline-team-id" name="team_id" value="{{:team.team_id}}">
{{/if}}

<div class="row">
    <div class="col-md-7">
        <div class="row m-bottom-5">
            <div class="col-md-3 p-top-5">
                <h5 class="modal-title" id="myModalLabel"><i class="fa fa-clock-o text-primary"></i>&nbsp;Dates:</h5>
            </div>
            <div class="col-md-9">
                <input type="text" class="team-range form-control" readonly>
                <span class="team-range-arrow"></span>

                <div class="form-group p-left-10 m-n">
                    <input type="hidden" id="timeline-team-date-start" name="team_date_start" value="{{if team_date_start!=undefined}}{{:team_date_start}}{{/if}}">
                    <span class="form-error text-danger"></span>
                </div>

                <div class="form-group p-left-10 m-n">
                    <input type="hidden" id="timeline-team-date-end" name="team_date_end" value="{{if team_date_end!=undefined}}{{:team_date_end}}{{/if}}">
                    <span class="form-error text-danger"></span>
                </div>

            </div>
        </div>
        <div class="row m-bottom-5">
            <div class="col-md-3 p-top-5">
                <h5 class="modal-title" id="myModalLabel"><i class="fa fa-user text-primary"></i>&nbsp; Leader:</h5>
            </div>
            <div class="col-md-9">
                <select class="form-control timeline-team-leader" name="team_leader" id="team-leaders-dropdown">
                </select>
            </div>
        </div>
        <div class="row m-bottom-5">
            <div class="col-md-3 col-sm-12 col-xs-12 p-top-5">
                <h5 class="modal-title" id="myModalLabel"><i class="fa fa-cog text-primary"></i>&nbsp;Type:</h5>
            </div>

            <div class="col-md-7 col-sm-9 col-xs-9">

                <select class="form-control timeline-team-type" name="team_type">
                    {{for crews }}
                    <option value="{{:crew_id}}" {{if #get("root").data.team!=undefined && ~integer(#get("root").data.team.team_crew_id)==~integer(crew_id) }} selected="selected" {{/if}} data-color="{{:crew_color}}">
                        {{:crew_name}}
                    </option>
                    {{/for}}
                </select>
            </div>

            <div class="col-md-2 col-sm-3 col-xs-3">
                <input type="text"
                       class="crew_color form-control mycolorpicker timeline-team-color"
                       readonly placeholder="Crew Color"
                       name="team_color"
                       value="{{if team!=undefined && team.team_color!=undefined}}{{:team.team_color}}{{else}}#5785fa{{/if}}">
            </div>

        </div>

    </div>

    <div class="col-md-5">
        <div class="row m-bottom-5">
            <div class="col-md-12">
                <div class="add-members-button p-10" id="free-members-btn" data-href="#free-members-list"><i class="fa fa-users"></i>&nbsp;Free members</div>
            </div>
        </div>

        <div class="row m-bottom-5">
            <div class="col-md-12">
                <div class="add-equipment-button p-10" id="free-equipment-btn" data-href="#free-equipment-list"><i class="fa fa-truck"></i>&nbsp;Free Equipment</div>
            </div>
        </div>

        <div class="free-members-list animated fadeInRight" id="free-members-list">
            <button type="button" class="close btn-rounded btn-sm btn-icon">&times;</button>
            <div id="free-members-list-content"></div>
        </div>
        <div class="free-equipment-list animated fadeInRight" id="free-equipment-list">
            <button type="button" class="close btn-rounded btn-sm btn-icon">&times;</button>
            <div id="free-equipment-list-content">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="line line-dashed line-lg pull-in"></div>
    </div>
</div>

<div id="team-warning"></div>

<div class="row">
    <div class="col-md-7">
        <div class="row m-bottom-5">
            <div class="col-md-3 col-sm-3 col-xs-3 p-top-5 text-center">
                <i class="fa fa-users fa-2x text-info" style="color: #caddff!important;"></i>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-9 form-group m-b-n">
                <input type="hidden" class="select2 timeline-team-members" id="timeline-team-members" multiple="multiple" name="team_members">
                <span class="form-error text-danger"></span>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="row m-bottom-5">
            <div class="col-md-12 col-sm-12 col-xs-12 form-group m-b-n">
                <input type="hidden" class="select2 timeline-team-items" id="timeline-team-items" multiple="multiple" name="team_items">
                <span class="form-error text-danger"></span>
            </div>
        </div>
    </div>
</div>

