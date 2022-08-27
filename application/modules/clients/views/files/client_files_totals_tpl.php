<tr {{if #getIndex()%2 == 1}}class="active" {{/if}}data-no="{{:lead_id}}">
    <td colspan="2">
        <strong>{{:lead_address}}, {{:lead_city}}, {{:lead_state}} {{:lead_zip}}, {{:lead_country}}, {{:lead_add_info}}</strong>
    </td>
    <td colspan="2">
        <?php if($this->router->fetch_class() === 'clients') : ?>
            <strong class="lead_comment_note" data-id="{{:lead_id}}">{{if lead_comment_note}}{{:lead_comment_note}}{{else}}Click to edit{{/if}}</strong>
        <?php endif; ?>
    </td>
    <td colspan="2">
        <strong>
            Subtotal: {{:~getSubTotal(
                        estimate.total_time,
                        estimate.sum_without_tax,
                        estimate.total_tax,
                        estimate.estimate_tax_name,
                        estimate.estimate_tax_value
                    )}}
            <br>
            Total{{if estimate.estimate_tax_name}} with {{:estimate.estimate_tax_name}}{{/if}}: {{:~getTotalWithTax(estimate.total_with_tax)}}
            {{:~showEstimateCrews(estimate.estimate_crews)}}
            <br>
            Total Due: {{:~getTotalDue(estimate.total_due)}}
        </strong>
    </td>
</tr>
