<section class="panel panel-default p-n">
    <!-- Workorder Details Header -->
    <header class="panel-heading">Workorder Statuses History</header>

    <!-- Data Display -->
    <table class="table table-striped b-t bg-white m-n">
        <thead>
        <tr>
            <th>Status Name</th>
            <th style="width:30%">Changed</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>
                Estimate Confirmed
            </td>
            <td>
                {{:workorder.date_created_view}}
            </td>
        </tr>
        {{if status_logs.length}}
            {{for status_logs}}
                {{if workorder_status}}
                <tr>
                    <td>
                        {{:workorder_status.wo_status_name}}
                    </td>
                    <td>
                        {{:status_date_view}}
                    </td>
                </tr>
                {{/if}}
            {{/for}}

        {{/if}}
        </tbody>
    </table>
</section>