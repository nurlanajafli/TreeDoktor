<script id="client_files_tpl" type="text/x-jsrender">
    <div class="client-files-list b-r tab-pane{{:activeTab}}" id="{{:status}}_files">
        {{include tmpl = "#client_files_preloader_tpl" /}}
    </ul>
</script>

<script id="client_files_preloader_tpl" type="text/x-jsrender">
    <div class="client-files-preloader">
        <img src="/assets/img/preloader.gif">
    </div>
</script>

<script id="client_files_view_tpl" type="text/x-jsrender">
    <div class="table-responsive">
        <table class="table m-n">
            <thead>
                <tr>
                    <th class="cft-type">Type</th>
                    <th class="cft-user">User</th>
                    <th class="cft-id">Id</th>
                    <th class="cft-date">Date</th>
                    <th class="cft-status">Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="client-body">
                {{for data}}
                    {{include tmpl = "#client_files_lead_tpl" /}}
                    {{if estimate}}
                        {{include tmpl = "#client_files_estimate_tpl" /}}
                        {{if estimate.workorder}}
                            {{include tmpl = "#client_files_workorder_tpl" /}}
                        {{/if}}
                        {{if estimate.invoice}}
                            {{include tmpl = "#client_files_invoice_tpl" /}}
                        {{/if}}
                        {{include tmpl = "#client_files_totals_tpl" /}}
                    {{/if}}
                {{else}}
                    <tr>
                        <td style="color:#FF0000;">No record found</td>
                    </tr>
                {{/for}}
            </tbody>
        </table>
    </div>
</script>

<script type="text/x-jsrender" id="client_files_lead_tpl">
    <?php $this->load->view('clients/files/client_files_lead_tpl'); ?>
</script>

<script type="text/x-jsrender" id="client_files_estimate_tpl">
    <?php $this->load->view('clients/files/client_files_estimate_tpl'); ?>
</script>

<script type="text/x-jsrender" id="client_files_workorder_tpl">
    <?php $this->load->view('clients/files/client_files_workorder_tpl'); ?>
</script>

<script type="text/x-jsrender" id="client_files_invoice_tpl">
    <?php $this->load->view('clients/files/client_files_invoice_tpl'); ?>
</script>

<script type="text/x-jsrender" id="client_files_totals_tpl">
    <?php $this->load->view('clients/files/client_files_totals_tpl'); ?>
</script>
