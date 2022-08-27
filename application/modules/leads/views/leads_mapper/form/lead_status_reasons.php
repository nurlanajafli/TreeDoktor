{{if reasons.length}}
<div class="col-lg-12 m-bottom-5">
    <select name="lead_reason_status" class="form-control input-sm" style="width: 100%">
        {{props reasons}}
        <option value="{{:prop.reason_id}}">{{:prop.reason_name}}</option>
        {{/props}}
    </select>
</div>
{{/if}}