<tr {{if #getIndex()%2 == 1}}class="active" {{/if}}data-no="{{:estimate.invoice.id}}">
    <td>Invoice</td>
    <td>{{if estimate.user}}{{:estimate.user.full_name}}{{else}}-{{/if}}</td>
    <td>{{:estimate.invoice.invoice_no}}</td>
    <td>{{:estimate.invoice.date_created_view}}</td>
    <td>{{:estimate.invoice.status.invoice_status_name}}</td>
    <td>
        <a href="{{:~getBaseUrl()}}{{:estimate.invoice.invoice_no}}" class="btn btn-xs btn-default btn-mini">
            <i class="fa fa-eye"></i>
        </a>
        <a href="{{:~getBaseUrl()}}{{:estimate.invoice.invoice_no}}/pdf" class="btn btn-xs btn-default btn-mini">
            <i class="fa fa-file"></i>
        </a>

        {{:estimate.invoice.qb_html}}

        <?php if (isAdmin()): ?>
            <a href="{{:~getBaseUrl()}}invoices/delete/{{:estimate.invoice.id}}"
               class="btn btn-default btn-xs btn-danger btnDelete"
               data-title="Invoice"
               data-text=""
            >
                <i class="fa fa-trash-o"></i>
            </a>
        <?php endif; ?>

        {{if estimate.invoice.invoice_like != null}}
            <a href="#feedback-{{:estimate.invoice.id}}"
               class="btn btn-xs btn-default btn-mini feedback-link-{{:estimate.invoice.id}}"
               data-toggle="modal"
            >
                {{if estimate.invoice.invoice_like == '1'}}
                    <img src="{{:~getBaseUrl()}}assets/img/up-sm.png" height="15">
                {{else estimate.invoice.invoice_like == '0'}}
                    <img src="{{:~getBaseUrl()}}assets/img/down-sm.png" height="15">
                {{/if}}
            </a>
        {{/if}}

        <div id="feedback-{{:estimate.invoice.id}}" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content panel panel-default p-n">
                    <!-- Client Files Header-->
                    <header class="panel-heading">Feedback</header>
                    <div class="p-10">
                        <div class="control-group">
                            {{if estimate.invoice.invoice_feedback}}
                                {{:estimate.invoice.invoice_feedback}}
                            {{else}}
                                No feedback given
                            {{/if}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <?php if(isAdmin()): ?>
                            <button class="btn like-btn changeInvoiceLikeBtn" data-id="{{:estimate.invoice.id}}" data-like="{{:estimate.invoice.invoice_like}}">
                                {{if estimate.invoice.invoice_like == '1'}}
                                    <img src="{{:~getBaseUrl()}}assets/img/up-sm.png" height="15" style="margin-top: -3px; margin-right: 3px;">
                                {{else estimate.invoice.invoice_like == '0'}}
                                    <img src="{{:~getBaseUrl()}}assets/img/down-sm.png" height="15" style="margin-right: 3px;">
                                {{/if}}
                                Change
                            </button>
                        <?php endif; ?>
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </td>
</tr>
