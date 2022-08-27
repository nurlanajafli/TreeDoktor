<?php if (!is_cl_permission_none()): ?>
    <tr {{if #getIndex()%2 == 1}}class="active" {{/if}}data-no="{{:estimate.estimate_id}}" data-type="estimate">
        <td>Estimate</td>
        <td>{{if estimate.user}}{{:estimate.user.full_name}}{{else}}-{{/if}}</td>
        <td>{{:estimate.estimate_no}}</td>
        <td>{{:estimate.date_created_view}}</td>
        <td>{{:estimate.estimate_status.est_status_name}}</td>
        <td>
            <a href="{{:~getBaseUrl()}}{{:estimate.estimate_no}}" class="btn btn-xs btn-default btn-mini">
                <i class="fa fa-eye"></i>
            </a>
            <a href="{{:~getBaseUrl()}}estimates/edit/{{:estimate.estimate_id}}" class="btn btn-xs btn-default btn-mini">
                <i class="fa fa-pencil"></i>
            </a>
            <a href="{{:~getBaseUrl()}}{{:estimate.estimate_no}}/pdf" class="btn btn-xs btn-default btn-mini">
                <i class="fa fa-file"></i>
            </a>

            <?php if (isAdmin()): ?>
                <a href="{{:~getBaseUrl()}}estimates/delete/{{:estimate.estimate_id}}"
                   class="btn btn-default btn-xs btn-danger btnDelete"
                   data-title="Estimate"
                   data-text="Workorder, Invoice"
                >
                    <i class="fa fa-trash-o"></i>
                </a>
            <?php endif; ?>
        </td>
    </tr>
<?php endif; ?>
