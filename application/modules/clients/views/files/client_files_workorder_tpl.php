<tr {{if #getIndex()%2 == 1}}class="active" {{/if}}data-no="{{:estimate.workorder.id}}">
    <td>Workorder</td>
    <td>{{if estimate.user}}{{:estimate.user.full_name}}{{else}}-{{/if}}</td>
    <td>{{:estimate.workorder.workorder_no}}</td>
    <td>{{:estimate.workorder.date_created_view}}</td>
    <td>{{:estimate.workorder.status.wo_status_name}}</td>
    <td>
        <a href="{{:~getBaseUrl()}}{{:estimate.workorder.workorder_no}}" class="btn btn-xs btn-default btn-mini">
            <i class="fa fa-eye"></i>
        </a>
        <a href="{{:~getBaseUrl()}}{{:estimate.workorder.workorder_no}}/pdf" class="btn btn-xs btn-default btn-mini">
            <i class="fa fa-file"></i>
        </a>
        <?php if (isAdmin()): ?>
            <a href="{{:~getBaseUrl()}}workorders/delete_workorder/{{:estimate.workorder.id}}"
               class="btn btn-default btn-xs btn-danger btnDelete"
               data-title="Workorder"
               data-text="Invoice"
            >
                <i class="fa fa-trash-o"></i>
            </a>
        <?php endif; ?>
    </td>
</tr>
