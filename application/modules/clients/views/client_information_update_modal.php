<?php if($client_data): ?>
    <div id="clientUpdateModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form name="update_workorder_priority_form" action="">
                    <div class="modal-body">
                        <h5 class="p-bottom-20">Edit Customer Information</h5>
                        <input type="hidden" id="author"
                               value="<?php echo($this->session->userdata('firstname')); ?>&nbsp;<?php echo($this->session->userdata('lastname')); ?>">
                        <input type="hidden" id="client_id" value="<?php echo $client_data->client_id; ?>">
                        <table class="table table-striped b-a b-light m-t-n-xxs m-b-none">
                            <tr>
                                <td class="w-200">
                                    <label class="control-label">Client name:</label>
                                </td>
                                <td class="p-left-30">
                                    <?php  $update_client = array(
                                        'id' => 'client_name',
                                        'name' => 'client_name',
                                        'class' => 'form-control',
                                        'value' => $client_data->client_name);?>
                                    <?php echo form_input($update_client) ?></td>
                            </tr>
                            <tr>
                                <td class="w-200">
                                    <label class="control-label">Client Type:</label>
                                </td>
                                <td class="p-left-30">
                                    <select id="client_type" class="form-control">
                                        <option value="1" <?php if ($client_data->client_type == '1') {
                                            echo "selected";
                                        } ?> >Residential
                                        </option>
                                        <option value="2" <?php if ($client_data->client_type == '2') {
                                            echo "selected";
                                        } ?> >Corporate
                                        </option>
                                        <option value="3" <?php if ($client_data->client_type == '3') {
                                            echo "selected";
                                        } ?>>Municipal
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-200">
                                    <label class="control-label">Tax:</label>
                                </td>
                                <td class="p-left-30">
                                    <select id="client_tax" class="form-control">
                                        <?php $clientTax = get_client_tax_text($client_data); ?>
                                        <?php $all_taxes = get_all_taxes_with_client_tax($client_data); ?>
                                        <?php foreach ($all_taxes as $tax): ?>
                                            <option value="<?php echo $tax['id'] ?>" <?php if ($clientTax['editText'] === $tax['text']) echo "selected"; ?>
                                                data-tax-name="<?php echo $tax['name'] ?>"
                                                data-tax-value="<?php echo $tax['value'] ?>"
                                                data-tax-rate="<?php echo $tax['rate'] ?>"
                                            ><?php echo $tax['text'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="w-200">
                                    <label class="control-label">Newsletters:</label>
                                </td>
                                <td class="p-left-30">
                                    <select id="client_unsubscribe" class="form-control">
                                        <option value="1" <?php if ($client_data->client_unsubscribe) {
                                            echo "selected";
                                        } ?> >Unsubscribed
                                        </option>
                                        <option value="0" <?php if (!$client_data->client_unsubscribe || $client_data->client_unsubscribe == '') {
                                            echo "selected";
                                        } ?> >Subscribed
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <?php echo form_submit('submit', 'Save Changes', 'class="btn btn-info update__client"'); ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?php echo base_url('assets/js/modules/clients/client_update_modal.js?v=1.00'); ?>"></script>
<?php endif; ?>