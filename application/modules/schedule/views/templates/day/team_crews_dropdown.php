<?php /*
<div class="row">
    <div role="tablist" class="nav-tabs col-lg-1 p-right-0 no-border" style="border-right: 1px solid #e4e4e4;">
        <div role="presentation" class="text-center m-bottom-5 active">
            <a href="#edit-team" aria-controls="home" role="tab" data-toggle="tab" class="btn btn-info midle-btn border-0 bg-white br-radius-50 active no-outline"><i class="fa fa-pencil"></i></a>
        </div>
        <div class="text-center m-bottom-5">
            <a target="_blank" href="/schedule/workorder_overview/{{:team_id}}" class="btn btn-warning midle-btn border-0 bg-white br-radius-50 no-outline"><i class="fa fa-print"></i></a>
        </div>
        <div class="text-center m-bottom-5">
            <a href="#optiomize-routes" aria-controls="home" role="tab" data-toggle="tab" class="btn btn-primary midle-btn border-0 bg-white br-radius-50 no-outline"><i class="fa fa-map-signs"></i></a>
        </div>
        <div class="text-center m-bottom-5">
            <a class="btn btn-danger midle-btn border-0 bg-white br-radius-50 deleteTeam no-outline" data-team_id="{{:team_id}}" data-text="{{:crew.crew_name}}{{if team_leader!=undefined && team_leader}}({{:team_leader.full_name}}){{else}}(NoTeamLead){{/if}}">
                <i class="fa fa-trash-o"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-10 tab-content">
        <div role="tabpanel" class="tab-pane active" id="edit-team">

            <div class="row">
                <div class="col-lg-6 p-n">

                    <div class="crewInfo">
                        <select class="crew_type_id no-shadow form-control no-bordered-input" style="display: inline-block;width:100px;" data-team="{{:team_id}}">
                            {{for crews}}
                            <option data-color="{{:crew_color}}" {{if #get("root").data.team_crew_id == crew_id }}selected="selected"{{/if}} value="{{:crew_id}}">
                            {{:crew_name}}
                            </option>
                            {{/for}}
                        </select>
                        <select class="teamLead no-shadow form-control no-bordered-input" style="width: 180px;display: inline-block;" data-team="{{:team_id}}">
                            <option value="0"> - </option>
                            {{for members}}
                            <option value="{{:id}}" {{if #get("root").data.team_leader && #get("root").data.team_leader.id == id }}selected="selected"{{/if}}>{{:full_name}}</option>
                            {{/for}}
                        </select>

                        <input type="text" data-team="{{:team_id}}" class="teamColor mycolorpicker no-shadow form-control color-input-rounded" style="display:inline-block;background:{{:team_color}}" value="{{:team_color}}">
                    </div>

                    {{for members}}
                    <li class="label {{if #get('root').data.team_leader && id == #get('root').data.team_leader.id}}bg-dark{{else}}bg-info{{/if}}"
                        {{if employee && employee.emp_feild_worker == 1 }}data-field_worker="1"{{/if}} data-emailid="{{:emailid}}"
                    data-employee_id="{{:id}}">{{:full_name}}
                    <a href="#" data-user_id="{{:id}}" data-team_id="{{:#get('root').data.team_id}}" class="deleteFromCrew">x</a>
                    </li>
                    {{/for}}
                    <li class="eqInfo">Equipment:</li>
                    {{for equipment}}
                    <li class="label bg-warning" data-item_code="{{:eq_code}}" data-eq_id="{{:eq_id}}" data-eq_group_id="{{:group_id}}" data-origin-color="{{if group}}{{:group.group_color}}{{else}}#fff{{/if}}">{{:eq_name}}
                        <a href="#" data-item_id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}" class="deleteItemFromCrew">x</a>
                    </li>
                    {{/for}}
                    <div class="crewTools">
                        <label class="label text-bold text-dark" style="font-size: 13px;display: block;text-align: left;margin-top: 10px;">
                            Team Tools:
                        </label>
                        {{for tools}}
                        <li class="label bg-danger">
                            {{:eq_name}}&nbsp;<a href="#" class="deleteToolFromTeam" data-stt-id="{{:pivot.stt_id}}" data-item_id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}">x</a>
                        </li>
                        {{/for}}
                    </div>

                </div>
                <div class="col-lg-6 p-n">
                    <div role="tablist">

                        <a class="btn btn-primary bg-white br-radius-0 border-bottom" href="#free-members-tab" aria-controls="free-members-tab" role="tab" data-toggle="tab">Members</a>

                        <a class="btn btn-warning bg-white br-radius-0 border-bottom" href="#free-items-tab" aria-controls="free-items-tab" role="tab" data-toggle="tab">Equipment</a>

                        <a class="btn btn-danger bg-white br-radius-0 border-bottom" href="#free-tools-tab" aria-controls="free-tools-tab" role="tab" data-toggle="tab">Tools</a>

                    </div>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="free-members-tab">
                            <div class="line line-dashed line-lg line-members"></div>
                            <h4 class="freeMembersTitle">Free Members:</h4>
                            {{for free_members}}
                            {{if employee && employee.emp_feild_worker==1}}
                            <li class="label bg-primary ui-draggable addMember b-a" style="text-shadow: 1px 1px #626262;border-color: #000;"
                                data-emailid="{{:emailid}}" data-emp_id="{{:id}}">{{:full_name}}</li>
                            {{/if}}
                            {{/for}}
                            <div class="line line-dashed line-lg line-members"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="free-items-tab">
                            <div class="line line-dashed line-lg line-items"></div>
                            {{for free_items}}
                            <li class="label bg-danger ui-draggable addItem b-a"
                                style="border-color: #000;text-shadow: 1px 1px #626262;background: {{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}};"
                                data-item_id="{{:eq_id}}" data-item_code="{{:eq_code}}"
                                data-item_group_id="{{:group_id}}"
                                data-origin-color="{{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}}">{{:eq_name}}</li>
                            {{/for}}
                        </div>
                        <div role="tabpanel" class="tab-pane" id="free-tools-tab">
                            <h4>Tools</h4>
                            {{for free_tools}}
                            <li class="label bg-danger ui-draggable addTool b-a" style="text-shadow: 1px 1px #626262;border-color: #000;" data-tool-id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}">{{:eq_name}}</li>
                            {{/for}}
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <div role="tabpanel" class="tab-pane" id="optiomize-routes">
            optimize routes
        </div>
    </div>
</div> */ ?>
<?php /*
<a href="#" class="btn btn-xs btn-danger no-shadow deleteTeam" style="position: absolute;top: 0px;right: 0px;"><i class="fa fa-trash-o"></i></a>
<div class="arrow top" style=""></div>
 */ ?>

<div class="row">
    <ul role="tablist" class="nav-tabs col-lg-1 p-right-0 no-border edit-team-dropdown-menu" style="border-right: 1px solid #e4e4e4;">
        <li role="presentation" class="text-center m-bottom-5 active">
            <a href="#edit-team" aria-controls="edit-team" role="tab" data-toggle="tab" class="btn btn-info midle-btn border-0 bg-white br-radius-50 no-outline"><i class="fa fa-pencil"></i></a>
        </li>
        <li role="presentation" class="text-center m-bottom-5">
            <a target="_blank" href="/schedule/workorder_overview/{{:team_id}}" class="btn btn-warning midle-btn border-0 bg-white br-radius-50 no-outline"><i class="fa fa-print"></i></a>
        </li>
        <li role="presentation" class="text-center m-bottom-5">
            <a href="#optiomize-routes" data-team_id="{{:team_id}}" aria-controls="optiomize-routes" role="tab" data-toggle="tab" class="btn btn-primary midle-btn border-0 bg-white br-radius-50 no-outline optimize-route"><i class="fa fa-map-signs"></i></a>
        </li>
        <li role="presentation" class="text-center m-bottom-5">
            <a class="btn btn-danger midle-btn border-0 bg-white br-radius-50 deleteTeam no-outline" data-team_id="{{:team_id}}" data-text="{{:crew.crew_name}}{{if team_leader!=undefined && team_leader}}({{:team_leader.full_name}}){{else}}(NoTeamLead){{/if}}">
                <i class="fa fa-trash-o"></i>
            </a>
        </li>
    </ul>
    <div class="col-lg-offset-1 col-lg-10 tab-content p-left-25" style="width: 85.3%">
        <div role="tabpanel" class="tab-pane active" id="edit-team">
            <?php /*
            <div class="row">
                <div class="col-lg-6" style="width: 46%">
                    <div class="crewInfo">
                        <div class="row p-top-10">
                            <div class="col-md-3 p-top-5 p-right-0">
                                <h5 class="modal-title" id="myModalLabel"><i class="fa fa-user text-primary"></i>&nbsp; Leader:</h5>
                            </div>
                            <div class="col-lg-8 p-left-0">
                                <select class="teamLead no-shadow form-control no-bordered-input" style="display: inline-block;" data-team="{{:team_id}}">
                                    <option value="0"> - </option>
                                    {{for members}}
                                    <option value="{{:id}}" {{if #get("root").data.team_leader && #get("root").data.team_leader.id == id }}selected="selected"{{/if}}>{{:full_name}}</option>
                                    {{/for}}
                                </select>
                            </div>
                        </div>

                        <div class="row p-top-15">
                            <div class="col-lg-3 p-right-0">
                                <h5 class="modal-title" id="myModalLabel"><i class="fa fa-cog text-primary"></i>&nbsp;Type:</h5>
                            </div>
                            <div class="col-md-6 p-right-0 p-left-0">
                                <select class="crew_type_id no-shadow form-control no-bordered-input" style="display: inline-block;" data-team="{{:team_id}}">
                                    {{for crews}}
                                    <option data-color="{{:crew_color}}" {{if #get("root").data.team_crew_id == crew_id }}selected="selected"{{/if}} value="{{:crew_id}}">
                                    {{:crew_name}}
                                    </option>
                                    {{/for}}
                                </select>
                            </div>

                            <div class="col-lg-2 p-left-0 text-right">
                                <input type="text" data-team="{{:team_id}}" class="teamColor mycolorpicker no-shadow form-control color-input-rounded" style="display:inline-block;background:{{:team_color}}" value="{{:team_color}}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 p-top-15" style="width: 44%;border-left: 1px solid #eeeeee!important;padding-left: 25px;">
                    <div class="row p-top-5" style="min-height: 40px">
                        <span class=""><i class="fa fa-users text-primary"></i>&nbsp;<b>Members:</b></span>
                        {{if members.length}}
                            {{for members}}
                            <li class="label {{if #get('root').data.team_leader && id == #get('root').data.team_leader.id}}bg-dark{{else}}bg-info{{/if}}"
                                {{if employee && employee.emp_feild_worker == 1 }}data-field_worker="1"{{/if}} data-emailid="{{:emailid}}"
                            data-employee_id="{{:id}}">{{:full_name}}
                            <a href="#" data-user_id="{{:id}}" data-team_id="{{:#get('root').data.team_id}}" class="deleteFromCrew">x</a>
                            </li>
                            {{/for}}
                        {{else}}
                            <span class="text-muted">  ---  No items  ---  </span>
                        {{/if}}
                    </div>
                    <div class="row p-top-5" style="min-height: 40px">
                        <span class="eqInfo m-top-5"><i class="fa fa-truck text-warning"></i>&nbsp;<b>Equipment:</b></span>
                        {{if equipment.length}}
                            {{for equipment}}
                            <li class="label bg-warning" data-item_code="{{:eq_code}}" data-eq_id="{{:eq_id}}" data-eq_group_id="{{:group_id}}" data-origin-color="{{if group}}{{:group.group_color}}{{else}}#fff{{/if}}">{{:eq_name}}
                                <a href="#" data-item_id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}" class="deleteItemFromCrew">x</a>
                            </li>
                            {{/for}}
                        {{else}}
                            <span class="text-muted">  ---  No items  ---  </span>
                        {{/if}}
                    </div>
                    <div class="row crewTools p-top-5">
                        <span>
                            <i class="fa fa-wrench text-danger"></i>&nbsp;<b>Tools:</b>
                        </span>
                        {{if tools.length}}
                            {{for tools}}
                            <li class="label bg-danger">
                                {{:eq_name}}&nbsp;<a href="#" class="deleteToolFromTeam" data-stt-id="{{:pivot.stt_id}}" data-item_id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}">x</a>
                            </li>
                            {{/for}}
                        {{else}}
                            <span class="text-muted">  ---  No items  ---  </span>
                        {{/if}}
                    </div>

                </div>
                <div class="col-lg-12">
                    <ul class="nav nav-tabs p-bottom-5 no-border" role="tablist" style="margin: -15px 0 0px; width: 46%">
                        <li role="presentation" class="{{if tab=='members'}}active{{/if}} p-n m-n">
                            <a style="padding: 4px 10px;" class="btn primary-hover btn-sm no-border-btn br-radius-15" href="#free-members-tab" aria-controls="free-members-tab" role="tab" data-toggle="tab">
                                <i class="fa fa-users text-primary"></i>&nbsp;<b>Members</b>
                            </a>
                        </li>
                        <li role="presentation" class="{{if tab=='equipment'}}active{{/if}} p-n m-n">
                            <a style="padding: 4px 10px;" class="btn warning-hover text-dark btn-sm no-border-btn br-radius-15" href="#free-items-tab" aria-controls="free-items-tab" role="tab" data-toggle="tab">
                                <i class="fa fa-truck text-warning"></i>&nbsp;<b>Equipment</b>
                            </a>
                        </li>
                        <li role="presentation" class="{{if tab=='tools'}}active{{/if}} p-n m-n">
                            <a style="padding: 4px 10px;" class="btn danger-hover text-dark btn-sm no-border-btn br-radius-15" href="#free-tools-tab" aria-controls="free-tools-tab" role="tab" data-toggle="tab">
                                <i class="fa fa-wrench text-danger"></i>&nbsp;<b>Tools</b>
                            </a>
                        </li>
                        </ul>
                    </ul>
                    <div class="tab-content p-top-5" style="height: 220px; overflow-x: hidden;overflow-y: auto">
                        <div role="tabpanel" class="tab-pane {{if tab=='members'}}active{{/if}}" id="free-members-tab">
                            <span class="freeMembersTitle"></span>
                            {{for free_members}}
                            {{if employee && employee.emp_feild_worker==1}}
                            <li class="label bg-primary ui-draggable addMember b-a" style="text-shadow: 1px 1px #626262;border-color: #000;"
                                data-emailid="{{:emailid}}" data-emp_id="{{:id}}">{{:full_name}}</li>
                            {{/if}}
                            {{/for}}
                        </div>
                        <div role="tabpanel" class="tab-pane {{if tab=='equipment'}}active{{/if}}" id="free-items-tab">
                            {{for free_items}}
                            <li class="label bg-danger ui-draggable addItem b-a"
                                style="border-color: #000;text-shadow: 1px 1px #626262;background: {{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}};"
                                data-item_id="{{:eq_id}}" data-item_code="{{:eq_code}}"
                                data-item_group_id="{{:group_id}}"
                                data-origin-color="{{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}}">{{:eq_name}}</li>
                            {{/for}}
                        </div>
                        <div role="tabpanel" class="tab-pane {{if tab=='tools'}}active{{/if}}" id="free-tools-tab">
                            {{for free_tools}}
                            <li class="label bg-danger ui-draggable addTool b-a" style="text-shadow: 1px 1px #626262;border-color: #000;" data-tool-id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}">{{:eq_name}}</li>
                            {{/for}}
                        </div>
                    </div>
                </div>
            </div>*/ ?>
            <div class="row">
                <div class="col-lg-6" style="width: 46%">
                    <div class="crewInfo">
                        <div class="row p-top-10">
                            <div class="col-lg-3 p-right-0 text-center">
                                <i class="fa fa-calendar inline-block fa-3x text-primary" style="color: #aaddffd6"></i>
                            </div>
                            <div class="col-md-8 p-right-0 p-left-0">
                                {{if team.team_date_start != team.team_date_end}}
                                <span class="info-hover active btn-rounded btn no-shadow" style="padding: 9px 16px;font-size: 15px;">
                                    {{:~dateFormat(team.team_date_start, "ddd")}}, {{:~dateFormat(team.team_date_start, "MMMM DD")}}
                                </span>&nbsp;
                                <span class="danger-hover active btn-rounded btn no-shadow" style="padding: 9px 16px;font-size: 15px;">
                                    {{:~dateFormat(team.team_date_end, "ddd")}}, {{:~dateFormat(team.team_date_end, "MMMM DD")}}
                                </span>
                                {{else}}
                                    <span class="info-hover active btn-rounded btn no-shadow btn-block" style="font-size: 17px;padding: 7px;">
                                        {{:~dateFormat(team.team_date_start, "ddd")}}, {{:~dateFormat(team.team_date_start, "MMMM DD")}}
                                    </span>
                                {{/if}}
                            </div>
                        </div>

                        <div class="row p-top-20">
                            <div class="col-md-3 p-top-5 p-right-0">
                                <h5 class="modal-title"><i class="fa fa-user text-primary"></i>&nbsp; Leader:</h5>
                            </div>
                            <div class="col-lg-8 p-left-0">
                                <select class="teamLead no-shadow form-control no-bordered-input" style="display: inline-block;" data-team="{{:team_id}}">
                                    <option value="0"> - </option>
                                    {{for members}}
                                    <option value="{{:id}}" {{if #get("root").data.team_leader && #get("root").data.team_leader.id == id }}selected="selected"{{/if}}>{{:full_name}}</option>
                                    {{/for}}
                                </select>
                            </div>
                        </div>

                        <div class="row p-top-20">
                            <div class="col-lg-3 p-right-0">
                                <h5 class="modal-title"><i class="fa fa-cog text-primary"></i>&nbsp;Type:</h5>
                            </div>
                            <div class="col-md-6 p-right-0 p-left-0">
                                <select class="crew_type_id no-shadow form-control no-bordered-input" style="display: inline-block;" data-team="{{:team_id}}">
                                    {{for crews}}
                                    <option data-color="{{:crew_color}}" {{if #get("root").data.team_crew_id == crew_id }}selected="selected"{{/if}} value="{{:crew_id}}">
                                    {{:crew_name}}
                                    </option>
                                    {{/for}}
                                </select>
                            </div>

                            <div class="col-lg-2 p-left-0 text-right">
                                <input type="text" data-team="{{:team_id}}" class="teamColor mycolorpicker no-shadow form-control color-input-rounded" style="display:inline-block;background:{{:team_color}}" value="{{:team_color}}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 p-top-15 p-left-10" style="width: 49%;border-left: 1px solid #eeeeee!important;margin-right: -25px;">

                    <ul class="nav nav-tabs no-border" role="tablist">
                        <li role="presentation" class="{{if tab=='members'}}active{{/if}} p-n m-n">
                            <a style="padding: 4px 10px;" class="btn primary-hover btn-sm no-border-btn br-radius-15" href="#free-members-tab" aria-controls="free-members-tab" role="tab" data-toggle="tab">
                                <i class="fa fa-users text-primary"></i>&nbsp;<b>Members</b>
                            </a>
                        </li>
                        <li role="presentation" class="{{if tab=='equipment'}}active{{/if}} p-n m-n">
                            <a style="padding: 4px 10px;" class="btn warning-hover text-dark btn-sm no-border-btn br-radius-15" href="#free-items-tab" aria-controls="free-items-tab" role="tab" data-toggle="tab">
                                <i class="fa fa-truck text-warning"></i>&nbsp;<b>Equipment</b>
                            </a>
                        </li>
                        <li role="presentation" class="{{if tab=='tools'}}active{{/if}} p-n m-n">
                            <a style="padding: 4px 10px;" class="btn danger-hover text-dark btn-sm no-border-btn br-radius-15" href="#free-tools-tab" aria-controls="free-tools-tab" role="tab" data-toggle="tab">
                                <i class="fa fa-wrench text-danger"></i>&nbsp;<b>Tools</b>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-top-5" style="height: 150px; overflow-x: hidden;overflow-y: auto">
                        <div role="tabpanel" class="tab-pane {{if tab=='members'}}active{{/if}}" id="free-members-tab">
                            <span class="freeMembersTitle"></span>
                            {{for free_members}}
                            {{if employee && employee.emp_feild_worker==1}}
                            <li class="label bg-primary ui-draggable addMember" style="text-shadow: 1px 1px #626262;border-color: #000;"
                                data-emailid="{{:emailid}}" data-emp_id="{{:id}}">{{:full_name}}</li>
                            {{/if}}
                            {{/for}}
                        </div>
                        <div role="tabpanel" class="tab-pane {{if tab=='equipment'}}active{{/if}}" id="free-items-tab">
                            {{for free_items}}
                            <li class="label bg-danger ui-draggable addItem"
                                style="border-color: #000;text-shadow: 1px 1px #626262;background: {{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}};"
                                data-item_id="{{:eq_id}}" data-item_code="{{:eq_code}}"
                                data-item_group_id="{{:group_id}}"
                                data-origin-color="{{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}}">{{:eq_name}}</li>
                            {{/for}}
                        </div>
                        <div role="tabpanel" class="tab-pane {{if tab=='tools'}}active{{/if}}" id="free-tools-tab">
                            {{for free_tools}}
                            <li class="label bg-danger ui-draggable addTool" style="text-shadow: 1px 1px #626262;border-color: #000;" data-tool-id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}">{{:eq_name}}</li>
                            {{/for}}
                        </div>
                    </div>


                </div>
                <div class="col-lg-12">
                    {{if members.length}}
                    <div class="row p-top-5 p-bottom-5">
                        <div class="pull-left p-right-0 p-left-15" style="width: 83px;">
                            <h5 class="modal-title"><i class="fa fa-users text-primary"></i>&nbsp;Members:</h5>
                        </div>
                        <div class="col-md-10">
                            {{for members}}
                            <li class="label {{if #get('root').data.team_leader && id == #get('root').data.team_leader.id}}bg-dark{{else}}bg-info{{/if}}"
                                {{if employee && employee.emp_feild_worker == 1 }}data-field_worker="1"{{/if}} data-emailid="{{:emailid}}"
                            data-employee_id="{{:id}}">{{:full_name}}
                            <a href="#" data-user_id="{{:id}}" data-team_id="{{:#get('root').data.team_id}}" class="deleteFromCrew"><i class="fa fa-close text-white"></i></a>
                            </li>
                            {{/for}}
                        </div>
                    </div>
                    {{/if}}
                    {{if equipment.length}}
                    <div class="row p-top-5 p-bottom-5">
                        <div class="pull-left p-right-0 p-left-15" style="width: 83px;">
                            <span class="eqInfo inline-block"><i class="fa fa-truck text-warning"></i>&nbsp;Equipment:</span>
                        </div>
                        <div class="col-lg-10">
                        {{for equipment}}
                        <li class="label bg-warning" data-item_code="{{:eq_code}}" data-eq_id="{{:eq_id}}" data-eq_group_id="{{:group_id}}" data-origin-color="{{if group}}{{:group.group_color}}{{else}}#fff{{/if}}">{{:eq_name}}
                            <a href="#" data-item_id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}" class="deleteItemFromCrew">
                                <i class="fa fa-close text-white"></i>
                            </a>
                        </li>
                        {{/for}}
                        </div>
                    </div>
                    {{/if}}
                    {{if tools.length}}
                    <div class="row crewTools p-top-5">
                        <div class="pull-left p-right-0 p-left-15" style="width: 83px;">
                            <span class="inline-block">
                                <i class="fa fa-wrench text-danger"></i>&nbsp;Tools:
                            </span>
                        </div>
                        <div class="col-lg-10">
                            {{for tools}}
                            <li class="label bg-danger">
                                {{:eq_name}}&nbsp;<a href="#" class="deleteToolFromTeam" data-stt-id="{{:pivot.stt_id}}" data-item_id="{{:eq_id}}" data-team_id="{{:#get('root').data.team_id}}">
                                    <i class="fa fa-close text-white"></i>
                                </a>
                            </li>
                            {{/for}}
                        </div>
                    </div>
                    {{/if}}

                </div>
            </div>

        </div>

        <div role="tabpanel" class="tab-pane" id="optiomize-routes">
            <section class="scrollable wrapper p-left-30 p-top-5" style="margin-right: -17px;max-height: 350px" id="route-optimization-preview">
                <div style="padding: 100px 10px 100px 10px" class="text-center">
                    <img src="/assets/img/loading.gif"/>
                </div>
            </section>
        </div>
    </div>
</div>