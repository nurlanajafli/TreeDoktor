<select class="btn btn-default no-shadow week-member-filter no-bordered-input pull-left m-right-15" id="week-member-filter">
    <optgroup label="Teams Leaders">
        <option value="">No Leader</option>
    {{for members}}
        {{if is_team_leader}}
            <option {{if id == #get('root').data.user_id}}selected="selected"{{/if}} value="{{:id}}">
            {{:full_name}}
            </option>
        {{/if}}
    {{/for}}
    </optgroup>
    <optgroup label="Teams Members">
        {{for members}}
        {{if !is_team_leader}}
        <option {{if id == #get('root').data.user_id}}selected="selected"{{/if}} value="{{:id}}">
        {{:full_name}}
        </option>
        {{/if}}
        {{/for}}
    </optgroup>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
<select class="btn btn-default no-shadow no-bordered-input pull-right m-left-15" id="week-crew_type-filter">
    <option value="">All Crews</option>
    {{if crews.length}}
        {{for crews}}
            <option {{if crew_id == #get('root').data.team_crew_id}}selected="selected"{{/if}} value="{{:crew_id}}">{{:crew_name}}</option>
        {{/for}}
    {{/if}}
</select>