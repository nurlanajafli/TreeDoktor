<?php if (!is_cl_permission_none()): ?>
    <tr {{if #getIndex()%2 == 1}}class="active" {{/if}}data-no="{{:lead_id}}">
        <td>Lead</td>
        <td>{{if lead_author}}{{:lead_author.full_name}}{{else}}-{{/if}}</td>
        <td>{{:lead_no}}</td>
        <td>{{:lead_date_created_view}}</td>
        <td>{{:status.lead_status_name}}{{if reason_status}} ({{:reason_status.reason_name}}{{/if}}</td>
        <td>
            <!--Triggers -->
            <a href="#lead-details-modal" data-id="{{:lead_id}}" role="button" class="btn btn-xs btn-default" data-toggle="modal">
                <i class="fa fa-eye"></i>
            </a>
            {{if status && status.lead_status_estimated == '0'}}
                <a href="{{:~getBaseUrl()}}estimates/new_estimate/{{:lead_id}}" class="btn btn-xs btn-default btn-mini">
                    <i class="fa fa-leaf"></i>
                </a>
            {{/if}}
            <?php if (isAdmin()): ?>
                <a href="{{:~getBaseUrl()}}leads/delete/{{:lead_id}}"
                   class="btn btn-default btn-xs btn-danger btnDelete"
                   data-title="Lead"
                   data-text="Estimate, Workorder, Invoice"
                >
                    <i class="fa fa-trash-o"></i>
                </a>
            <?php endif; ?>
        </td>
    </tr>
    {{if !estimate}}
        <tr {{if #getIndex()%2 == 1}}class="active" {{/if}}data-no="{{:lead_id}}">
            <td colspan="6">
                <strong>{{:lead_address}}, {{:lead_city}}, {{:lead_state}} {{:lead_zip}}, {{:lead_country}}, {{:lead_add_info}}</strong>
            </td>
        </tr>
    {{/if}}
<?php endif; ?>
