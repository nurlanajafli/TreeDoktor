<script type="text/x-jsrender" id="day-create-team-form-tpl">
    <?php $this->load->view('schedule/templates/day/day_create_team'); ?>
</script>

<script type="text/x-jsrender" id="day-team-header-tpl">
    <?php $this->load->view('schedule/templates/day/day_team_header'); ?>
</script>

<script type="text/x-jsrender" id="team-crews-dropdown-tpl">
    <?php $this->load->view('schedule/templates/day/team_crews_dropdown'); ?>
</script>

<script type="text/x-jsrender" id="day-dayoff-tpl">
    <?php $this->load->view('schedule/templates/day/day_dayoff'); ?>
</script>

<script type="text/x-jsrender" id="route-optimization-preview-tmp">
    <?php $this->load->view('schedule/templates/day/route_optimization_preview'); ?>
</script>

<script type="text/x-jsrender" id="day-dayoff-reasons-tpl">
    <select class="reasonAbsence" style="color: #000;max-width: 120px;">
        <option value="">Select Reason</option>
        {{for reasons}}
            <option value="{{:reason_id}}">{{:reason_name}}</option>
        {{/for}}
    </select>
</script>



<script type="text/x-jsrender" id="day-create-team-crew-leader-tpl">
    <option value="">No Leader</option>
    {{for free_members}}
    <option value="{{:id}}">{{:full_name}}</option>
    {{/for}}
</script>

<script type="text/x-jsrender" id="day-free-members-list-tpl">
    {{for free_members}}
        {{if employee && employee.emp_feild_worker==1}}
        <li class="label bg-primary ui-draggable addMember b-a ui-sortable-handle"
        data-emailid="{{:emailid}}" data-emp_id="{{:id}}" data-field_worker="{{:employee.emp_feild_worker}}">{{:full_name}}</li>
        {{/if}}
    {{/for}}
</script>

<script type="text/x-jsrender" id="day-free-equipment-list-tpl">
{{for free_items}}
    <li class="label bg-danger ui-draggable addItem b-a"
        style="border-color: #000;text-shadow: 1px 1px #626262;background: {{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}};"
        data-item_id="{{:eq_id}}" data-item_code="{{:eq_code}}"
        data-item_group_id="{{:group_id}}"
        data-origin-color="{{if group!=undefined && group}}{{:group.group_color}}{{else}}#ffffff{{/if}}">{{:eq_name}}</li>
{{/for}}
</script>

<script type="text/x-jsrender" id="day-create-team-free-members-tpl">
    <li class="crewInfo">{{:crews[0].crew_name}}:</li>
    <div class="line line-dashed line-lg line-members"></div>
    {{for free_members}}
        <li class="label bg-primary ui-draggable addMember b-a" style="text-shadow: 1px 1px #626262;"
		data-emailid="{{:emailid}}"
		data-emp_id="{{:id}}">{{:full_name}}</li>
    {{/for}}
</script>

<script type="text/x-jsrender" id="day-create-team-free-items-tpl">
    <li class="eqInfo">Equipment:</li>
    <div class="line line-dashed line-lg line-items"></div>
    {{for free_items}}
    <li class="label bg-danger ui-draggable addItem b-a"
        style="border-color: #000;text-shadow: 1px 1px #626262;background: {{if group}}{{:group.group_color}}{{else}}#ffffff{{/if}};"
        data-item_id="{{:eq_id}}" data-item_code="{{:eq_code}}"
        data-item_group_id="{{:group_id}}"
        data-origin-color="{{if group}}{{:group.group_color}}{{else}}#ffffff{{/if}}">{{:eq_name}}</li>
    {{/for}}
</script>
