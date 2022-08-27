<div id="{{:task_id}}" class="marker taskMarker infowindow-container" data-task="1" style="overflow: hidden">

    <input type="hidden" name="marker_key" value="{{:marker_key}}">

    <div class="row" style="min-width: 550px">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-7" style="line-height: 1.7;">
            <strong>
                <a href="/{{:client.client_id}}" target='_blank'>
                    {{if client && client.primary_contact }}
                        {{:client.primary_contact.cc_name}}
                    {{/if}}
                </a>
            </strong>

            <div>
                <i class="glyphicon glyphicon-briefcase text-success"></i>
                <strong>Task №: </strong>
                <a href="/tasks/edit/{{:task_id}}" target="_blank">
                    {{:task_id}}
                </a>
            </div>

            <div>
                <i class="glyphicon glyphicon-user text-success"></i>
                {{if user && user.id }}
                    <strong>{{:user.full_name}}</strong>
                    {{else}}
                    <strong>Not Assigned</strong>
                {{/if}}
            </div>

            {{if task_schedule_date }}
            <div>
                <i class="glyphicon glyphicon-calendar text-success"></i>
                {{:task_date}} {{:task_schedule_start}} - {{:task_schedule_end}}
            </div>
            {{/if}}

            {{if client && client.primary_contact && client.primary_contact.cc_phone_view}}
            <div>
                <i class="glyphicon glyphicon-earphone text-success"></i>
                {{:client.primary_contact.cc_phone_view}}
            </div>
            {{/if}}

            <div>
                <i class="glyphicon glyphicon-map-marker text-success"></i>
                {{:full_address}}
            </div>
            <div>
                <blockquote class="p-n" style="padding-top: 0; padding-left: 10px!important;font-size: 13.5px;border-left-color: #daefca!important;">
                    {{:task_desc}}
                </blockquote>
            </div>
        </div>
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-5">

            <div class="form-inline p-top-5">
                <form action="#"
                      data-global="false"
                      data-type="ajax"
                      data-url="/tasks/ajax_change_schedule_date"
                      data-callback="LeadsMapper.change_task_schedule_callback"
                      id="task-schedule-form"
                >
                    <input type="hidden" name="task_id" value="{{:task_id}}">
                    <input class="form-control input-sm" id="task-schedule-date" type="date" value="{{:task_date}}" name="task_date" style="width: 100%"  />

                    <div class="p-top-5" style="display: flex; justify-content: space-between">
                        <input class="form-control input-sm" id="task-schedule-start" type="time" value="{{:task_start}}" name="task_start" />
                        <input class="form-control input-sm" id="task-schedule-end" type="time" value="{{:task_end}}" name="task_end" />
                    </div>

                    <div id=task-schedule-buttons></div>
                </form>
            </div>

            <div class="form-inline p-top-5">
                {{:estimators}}
                <form action="#"
                      data-global="false"
                      data-type="ajax"
                      data-url="/tasks/ajax_change_assigned_user"
                      data-callback="LeadsMapper.change_task_assigned_user_callback"
                >
                    <input type="hidden" name="task_id" value="{{:task_id}}">
                    <select class="form-control input-sm"  name="task_assigned_user" id="task-assigned-user" style="width: 100%">
                        {{for ~getEstimators(user?user.id:0) itemVar="~estimator"}}
                        <option value="{{:~estimator.id}}" {{if ~estimator.selected!=undefined && ~estimator.selected}}selected="selected"{{/if}}>
                        {{:~estimator.full_name}}
                        </option>
                        {{/for}}
                    </select>
                </form>
            </div>

            <div class="form-inline p-top-5">
                <form action="#"
                      data-global="false"
                      data-type="ajax"
                      data-url="/tasks/ajax_change_category"
                      data-callback="LeadsMapper.change_task_category_callback"
                >
                    <input type="hidden" name="task_id" value="{{:task_id}}">
                    <select class="form-control input-sm" name="task_category" id="task-category" style="width: 100%">
                        {{for ~getCategory(categories ,task_category) itemVar="~category" }}
                        <option value={{:~category.category_id}} {{if ~category.selected }} selected="selected"{{/if}} >
                            {{:~category.category_name}}
                        </option>
                        {{/for}}
                    </select>
                </form>
            </div>

            <div class="form-inline p-top-5">
                <form action="#"
                      data-global="false"
                      data-type="ajax"
                      data-url="/tasks/ajax_change_status"
                      data-callback="LeadsMapper.change_task_status_callback"
                      id="task-status-form"
                >
                    <input type="hidden" name="id" value="{{:task_id}}">
                    <select class="form-control input-sm"  id="task-status"  name="status" style="width: 100%">
                        {{props statuses}}
                        <option value={{:key}} {{if task_status==key }}selected="selected"{{/if}} >
                            {{:prop}}
                        </option>
                        {{/props}}
                    </select>

                    <div id="task-status-buttons"></div>
                </form>
            </div>

        </div>
    </div>
</div>
