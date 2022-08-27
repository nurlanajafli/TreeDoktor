
<div class="list-group no-radius alt" id="wo_status">
    <a class="checkbox p-left-30 list-group-item {{if statuses.length == active_status.length}}active{{/if}}" href="#" data-wo_status_id="-1">
        <label class="m-n" style="width: 130px;display: inline-block;">
            <input type="checkbox" class="check-all-wo-statuses" {{if statuses.length == active_status.length}}checked="checked"{{/if}}/>
            All
        </label><span class="badge bg-info">{{:total}}</span>
    </a>

    {{if statuses.length}}
    {{for statuses}}
    <a class="checkbox wo-status-item p-left-30 list-group-item {{if selected}}active{{/if}}" href="#">
        <label class="m-n" style="width: 150px;display: inline-block;">
            <input type="checkbox" class="check-wo-status" {{if selected}}checked="checked"{{/if}} data-wo_status_id="{{:wo_status_id}}"/>
            {{:wo_status_name}}
        </label><span class="badge bg-info">{{:workorders_count}}</span>
    </a>
    {{/for}}
    {{/if}}
</div>
