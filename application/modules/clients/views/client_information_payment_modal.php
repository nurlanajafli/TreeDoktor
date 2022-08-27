<!--Modal-->
<div id="new_payment" client-id="<?php echo $client_data->client_id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Add new payment for <?php echo $client_data->client_name; ?></header>
			<form id="add_client_payment" method="POST" class="form-horizontal" enctype="multipart/form-data">
				<!-- Client Files Header-->
				<div class="modal-body">
					<div class="p-10">
						<div class="form-group">
							<label class="col-sm-3 control-label">Payment Type</label>

							<div class="col-sm-8">
								<select name="type" id="payment_type" class="form-control">
									<option value="deposit">Deposit</option>
									<option value="invoice">Invoice</option>
								</select>
								<span class="help-inline"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 control-label">Payment Method</label>
							<?php $pay_method = $this->config->item('payment_methods');?>

							<div class="col-sm-8">
								<select name="method" id="payment_method" class="form-control">
									<option value=""> -- Select -- </option>
                                    <?php if(is_array($pay_method)): ?>
                                        <?php foreach($pay_method as $k=>$v) : ?>
                                            <?php if($k == $this->config->item('default_cc')) : ?>
                                                <?php if(config_item('processing')) : ?>
                                                        <option value="<?php echo $k; ?>"><?php echo ucwords($v);  ?></option>
                                                <?php endif; ?>
                                            <?php else :  ?>
                                                <option value="<?php echo $k; ?>"><?php echo ucwords($v);  ?></option>
                                            <?php endif; ?>
                                        <?php endforeach;?>
                                    <?php endif; ?>
								</select>
                                <?php if(config_item('processing')) : ?>
								<span class="help-inline"></span>
                                <?php endif; ?>
							</div>
						</div>

                        <?php if(config_item('processing')) : ?>

						<div style="display: none" class="form-group add_cc_block" >

						</div>
						<div class="form-group credit_card hide">
							<label class="col-sm-3 control-label">Payment Card</label>

							<div class="col-sm-8">
								<select id="cc_select" name="cc_id" class="form-control">
								</select>
                                <span class="help-inline"></span>
                                <a href="#card-form" id="add_cc_card" class="pull-right add_credit_card" data-toggle="modal">Add card</a>
							</div>

						</div>

                        <?php endif; ?>
						
						<div class="form-group">
							<label class="col-sm-3 control-label" for="estimate_id">Estimate</label>
							<div class="col-sm-8">
								<select name="estimate_id" id="estimate_id" class="form-control">
                                    <?php if ($client_estimates && $client_estimates->num_rows()) : ?>
										<?php foreach ($client_estimates->result_array() as $row) : ?>
                                            <?php if(!$row['est_status_declined'] && $row['total_due'] > 0) : ?>
                                                <option data-invoice="<?php echo $row['invoice_no'] ? 1 : 0; ?>" data-amount="<?php echo $row['total_due']; ?>"
                                                    <?php echo isset($estimate_id) && $estimate_id == $row['estimate_id'] ? 'selected' : ''; ?>
                                                    value="<?php echo $row['estimate_id']; ?>">
                                                    <?php echo $row['invoice_no'] ?: $row['estimate_no']; ?>, Total due:
                                                    <?php echo money($row['total_due']); ?>
                                                </option>
										    <?php endif; ?>
										<?php endforeach; ?>
									<?php elseif(isset($client_estimates) && isset($client_estimates->estimate_id) && $client_estimates && !empty($client_estimates)) : ?>
										<option
												value="<?php echo $client_estimates->estimate_id; ?>"><?php echo $client_estimates->estimate_no; ?></option>
									<?php endif; ?>
								</select>
                                <span class="help-inline"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Amount</label>

                            <div class="col-sm-8">
                                <input name="amount" id="payment_amount" class="form-control currency" type="text"
                                       placeholder="Payment Amount" value="">
                                <span class="help-inline"></span>
                                <?php if ((float) config_item('cc_extra_fee') > 0): ?>
                                    <small class="text-left block with-fee hide">
                                        <div>+ <?php echo (float) config_item('cc_extra_fee') ?>% transaction fee</div>
                                        Total Payment: <span id="with_fee" class="font-bold"><?php echo money(0); ?></span>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-group file_block">
                            <label class="col-sm-3 control-label">Payment File</label>

                            <div class="col-sm-3">
                                <span class="btn btn-primary btn-file" id="file">Choose File
                                    <input type="file" name="payment_file" class="btn-upload" id="paymentFileToUpload">
                                </span>
                                <span class="help-inline"></span>
							</div>
                            <div class="col-sm-5">
                                <input id="payment_date" class="form-control" type="datetime-local" name="payment_date">
                            </div>
						</div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="notes">Notes</label>
                            <div class="col-sm-8">
                                <textarea id="notes" class="form-control" name="payment_notes" placeholder="Payment notes"></textarea>
                            </div>
                        </div>
					</div>

                    <div class="form-group">
                        <label class="col-sm-4 control-label">&nbsp;</label>
                        <div class="col-sm-7">
                            <div class="text-danger form_error" style="display: none; text-align: left; font-size: 14px;"></div>
                        </div>
                    </div>

				</div>
				<div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
					<button class="btn btn-info" type="submit" style="30px"><span class="btntext">Add payment</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
						     style="display: none;width: 84px;" class="preloader">
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php if (isAdmin() || $this->session->userdata('PME') == 1) : ?>
	<div id="edit_payment" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content panel panel-default p-n">
				<header class="panel-heading">Edit payment for <?php echo $client_data->client_name; ?></header>
				<form id="edit_client_payment" method="POST" class="form-horizontal" enctype="multipart/form-data">
                    <div class="modal-body p-10">
                        <!-- Client Files Header-->
                        <div class="p-10">
                            <div class="form-group same-estimates"  style="display:none">
                                <label class="col-sm-4 control-label">Estimate</label>

                                <div class="col-sm-7">
                                    <select name="estimate_id" id="edit_payment_estimate" class="form-control">
                                    </select>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group non-cc-payments"  style="display:none">
                                <label class="col-sm-4 control-label">Payment Type</label>

                                <div class="col-sm-7">
                                    <select name="payment_type" id="edit_payment_type" class="form-control">
                                        <option value="deposit">Deposit</option>
                                        <option value="invoice">Invoice</option>
                                    </select>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group non-cc-payments"  style="display:none">
                                <label class="col-sm-4 control-label">Payment Method</label>

                                <div class="col-sm-7">
                                    <select name="payment_method" id="edit_payment_method" class="form-control">
                                        <?php if(is_array($pay_method)): ?>
                                        <?php foreach($pay_method as $k=>$v) : ?>
                                            <?php if($k == config_item('default_cc')) continue; ?>
                                            <option value="<?php echo $k; ?>"><?php echo ucwords($v);  ?></option>
                                        <?php endforeach;?>
                                        <?php endif; ?>
                                    </select>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group non-cc-payments"  style="display:none">
                                <label class="col-sm-4 control-label">Amount</label>

                                <div class="col-sm-7">
                                    <input name="payment_amount" id="edit_payment_amount" class="form-control currency" type="text"
                                           placeholder="Payment Amount" value="">
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group non-cc-payments"  style="display:none">
                                <label class="col-sm-4 control-label">Date</label>
                                <div class="col-sm-7">
                                    <input name="payment_date" id="edit_payment_date" class="form-control" type="date"
                                           placeholder="Payment Date" value="" style="padding: 6px 12px;">
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group non-cc-payments"  style="display:none">
                                <label class="col-sm-4 control-label">Payment File</label>

                                <div class="col-sm-7">
                                <span class="btn btn-primary btn-file" id="file">Choose File
                                    <input type="file" name="payment_file" id="paymentFileToUpload" class="btn-upload">
                                </span>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <div class="form-group non-cc-payments">
                                <label class="col-sm-4 control-label" for="notes">Notes</label>
                                <div class="col-sm-7">
                                    <textarea id="payment_notes" class="form-control" name="payment_notes" placeholder="Payment notes"></textarea>
                                </div>
                            </div>
                            <div class="text-danger form_error" style="display: none; text-align: left; font-size: 20px; padding-top: 20px;"></div>
                        </div>
					</div>
					<div class="modal-footer">
						<input type="hidden" name="payment_id" id="edit_payment_id">
						<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
						<button class="btn btn-info" type="submit">
							<span class="btntext">Save payment</span>
							<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
							     style="display: none;width: 91px;" class="preloader">
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
    <div id="payment_refund" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content panel panel-default p-n">
                <header class="panel-heading">Refund payment</header>
                <form id="refund_client_payment" method="POST" class="form-horizontal" enctype="multipart/form-data">
                    <div class="modal-body p-10">
                        <div class="p-10">
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Amount</label>
                                <div class="col-sm-7">
                                    <input name="refund_payment_amount" id="refund_payment_amount" class="form-control"
                                           type="number" step="0.01" max="0"
                                           placeholder="Payment Amount" value="" required>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <?php if(config_item('cc_extra_fee') > 0 && !config_item('cc_extra_fee_service_id')) : ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label"></label>
                                <div class="col-sm-7">
                                    <div class="checkbox">
                                        <label>
                                            <input name="refund_payment_fee" id="refund_payment_fee" type="checkbox">
                                            Include fee <span id="refund_fee_amount"></span>
                                        </label>
                                    </div>
                                    <div>(Note: Fee can be returned when full amount refund processed)</div>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">Password</label>
                                <div class="col-sm-7">
                                    <input name="refund_password" id="refund_password" class="form-control"
                                           type="password"
                                           placeholder="Your password" value="" required>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <input name="refund_payment_id" id="refund_payment_id" class="form-control" type="hidden"
                                   value="">
                            <div class="text-danger form_error"
                                 style="display: none; text-align: left; font-size: 20px; padding-top: 20px;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                        <button class="btn btn-info" type="submit">
                            <span class="btntext">Refund</span>
                            <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                                 style="display: none;width: 91px;" class="preloader">
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
<div id="payment_details" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content panel panel-default p-n">
            <header class="panel-heading">Transaction details</header>
            <div class="modal-body">
                <div class="p-10 trans-details">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    var processing = false;
    var default_cc = '<?php echo $this->config->item('default_cc')?>';
    var cc_extra_fee = <?php echo (float) config_item('cc_extra_fee') ?: 'null'; ?>;
    const isPME = <?php echo $this->session->userdata('PME') == 1 ? 'true' : 'false'; ?>;

    const [day, month, year] = new Date().toLocaleDateString().split('.');
    const date = [year, month,day].join('-')
    const [hour, minute] = new Date().toLocaleTimeString().split(':');
    const time = [hour,minute].join(':');
    const dateTime = [date, time].join('T')

    const datePicker = document.getElementById('payment_date');
    const updatePaymentDatePicker = document.getElementById('edit_payment_date');

    if(datePicker) {
        datePicker.value = dateTime;
        datePicker.max = dateTime;
    }
    if(updatePaymentDatePicker) {
        updatePaymentDatePicker.max = date;
    }

</script>

<script src="<?php echo base_url('assets/js/modules/clients/client_information_payment_modal.js?v=1.01'); ?>"></script>
