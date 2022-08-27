<!-- Modal -->
<script>
	var mainstatus = '<?php echo $invoice_data->in_status ?>';
	var invoiceid = '<?php echo $invoice_data->id ?>';
	var clientemail = '<?php echo $client_contact['cc_email'] ? addslashes($client_contact['cc_email']) : ''; ?>';
</script>
<div id="changeInvoiceStatus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Edit Invoice Status</header>
			<?php    
				echo form_open_multipart('invoices/payment_file_upload', ['id' => 'change_invoice_status', 'method' => 'post']);
			?>
			<div class="modal-body">
				<input type="hidden" name="invoice_id" id="invoice_id" value="<?php echo $invoice_data->id ?>"/>
				<div class="p-10">
					<div>
						<span class="error" style="color:#FF0000;" id="change_status_error"></span><br>Invoice Status:<br>
						<?php if(isset($invoices_statuses) && !empty($invoices_statuses)) : ?>
							
							<select name="new_invoice_status" id="new_invoice_status" class="form-control">
								<?php foreach($invoices_statuses as $key=>$status) : ?>
									<option value="<?php echo $status->invoice_status_id; ?>" <?php if($invoice_data->in_status == $status->invoice_status_id) : ?> selected="selected" <?php endif; ?> data-default="<?php echo $status->default; ?>" data-is_hold_backs="<?php echo $status->is_hold_backs; ?>" data-is_sent="<?php echo $status->is_sent; ?>" data-is_overdue="<?php echo $status->is_overdue; ?>" data-completed="<?php echo $status->completed; ?>" data-protected="<?php echo $status->protected; ?>" data-is_overpaid="<?php echo $status->is_overpaid; ?>">
										<?php echo $status->invoice_status_name; ?>
									</option>
								<?php endforeach; ?>
							</select>
							
						<?php endif; ?>
					</div>
					<br>
					
					<div id="payment-method-container" <?php if(element('completed', (array)$invoices_statuses[$invoice_data->in_status], 0)!=1): ?>style="display:none"<?php endif; ?> class="row">
						
						<div class="col-md-6">
							<div class="control-group">
								<label class="control-label">Payment Method:</label>
                                <?php $pay_method = $this->config->item('payment_methods');?>
                                <select name="method" id="payment_method_status" class="form-control">
                                    <option value=""> -- Select -- </option>
                                    <?php foreach($pay_method as $k=>$v) : ?>
                                        <?php if($k == $this->config->item('default_cc')) : ?>
                                            <?php if(config_item('processing')) : ?>
                                                <option value="<?php echo $k; ?>"><?php echo ucwords($v);  ?></option>
                                            <?php endif; ?>
                                        <?php else :  ?>
                                            <option value="<?php echo $k; ?>"><?php echo ucwords($v);  ?></option>
                                        <?php endif; ?>
                                    <?php endforeach;?>
                                </select>
							</div>
							<span class="help-inline"></span>
                        </div>

                        <div class="col-md-6">
                            <div class="control-group">
                                <label class="control-label">Amount:</label>

                                <div class="controls">
                                    <input name="payment_amount" id="payment_amount" class="form-control" type="text"
                                           placeholder="Payment Amount" value="">
                                    <span class="help-inline" style="margin-top: -10px;"></span>
                                    <?php
                                    if ((float) config_item('cc_extra_fee') > 0) : ?>
                                        <small class="block pull-left with-fee hide">
                                            <div>+ <?php echo (float) config_item('cc_extra_fee') ?>% transaction fee</div>
                                            Total Payment: <span id="with_fee" class="font-bold"><?php echo money(0); ?></span>
                                        </small>
                                    <?php
                                    endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php
                        if (config_item('processing')) : ?>
                            <div class="clear"></div>
                            <div style="margin-top: 15px;" class="col-md-12 form-group credit_card hide">
                                <label class="control-label">Payment Card</label>
                                <div>
                                    <select id="cc_select" name="cc_id" class="form-control">
                                    </select>
                                </div>
                                <span class="help-inline"></span>
                                <a href="#card-form" id="add_cc_card" class="pull-right add_credit_card" data-toggle="modal">Add card</a>
                            </div>

                        <?php endif; ?>
						
						<input type="hidden" name="estimate_id" id="estimate_id"
						       value="<?php echo $estimate_data->estimate_id; ?>">
						<input type="hidden" name="client_unsubscribe" 
						       value="<?php echo $client_data->client_unsubscribe;?>">
						<input type="hidden" name="invoice_id" id="invoice_id" value="<?php echo $invoice_data->id; ?>">
						<input type="hidden" name="payment_type" id="payment_type" value="invoice">
						<input type="hidden" name="invoice_sum" id="invoice_sum" value="">
					</div>

					<div id="upload_file" class="pull-left file_block" <?php if(element('completed', (array)$invoices_statuses[$invoice_data->in_status], 0)!=1): ?>style="display:none"<?php endif; ?>>
						<span class="btn btn-primary btn-file" id="file">Choose File
							<input type="file" name="payment_file" id="paymentFileToUpload" class="btn-upload">
						</span>
						<span class="help-inline"></span>

						<?php if (!empty($payment_files)): ?>
							<?php foreach($payment_files as $kk => $pp): ?>
								<div>
									<a href="<?php echo base_url() . PAYMENT_FILES_PATH . $pp["payment_file"] ?>" target="_new">
										<?php echo $pp["payment_file"] ?>
									</a>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<br>
                    <div class="text-danger form_error" style="display: none; text-align: left; font-size: 20px; padding-top: 20px;"></div>
				</div>
				
			</div>

			<div class="modal-footer">
				<button name="save" id="changeStatus" class="btn btn-success pull-right m-right-1-0 m-left-10" data-invoice_id="<?php echo $invoice_data->id; ?>" data-in_status="<?php echo $invoice_data->in_status; ?>" data-payment="<?php echo isset($payment) ? $payment : ''; ?>" style="display: block;margin-right: 10px;"/>
						<span class="btntext">Save</span>
						<img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif" style="display: none;width: 32px;"
					     class="preloader">
				</button>
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
            <?php
            echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    var processing = false;
    var default_cc = '<?php echo $this->config->item('default_cc')?>';
    var cc_extra_fee = <?php echo (float) config_item('cc_extra_fee') ?>;
    $(document).on('click', '#add_cr_card_payment', function () {
        if (processing)
            return false;
        var form = $(this).parents('form:first');
        $(form).find('.add_cc_block').slideToggle();
    });
    $(document).on('change', '.payment_method1', function () {
        var form = $(this).parents('form:first');
        if ($(this).val() == default_cc) {
            $('#changeInvoiceStatus').find('[name="save"]').attr('disabled', 'disabled');
			$(form).find('.credit_card').removeClass('hide');
			$(form).find('#payment_amount').after('<span style="margin-left: 10px;" id="processingCC1"><a href="#" class="pull-right">Processing</a></span>');
		}
		else {
			$('#changeInvoiceStatus').find('[name="save"]').removeAttr('disabled');
			$(form).find('.credit_card').addClass('hide');
			$(form).find('#processingCC1').remove();
		}
	});

    $(document).on('change', '#payment_method_status', function () {
        var form = $(this).parents('form:first');
        if ($(this).val() == default_cc) {
            $(form).find('.file_block').hide();
            console.log('open cc list');
            var client = $('#new_payment').attr('client-id');
            $.post(baseUrl + 'clients/ajax_get_billing_details', {client_id:client}, function (resp) {
                if(resp.status == 'ok') {
                    var select = $(form).find('#cc_select');
                    $(form).find('#cc_select option').remove();
                    resp.cards.map(function (card) {
                        select.append('<option value="' + card.card_id + '">' + card.number + '</option>');
                    });
                }
                return false;
            }, 'json');
            $(form).find('.credit_card').removeClass('hide');
            if (cc_extra_fee > 0) {
                $(form).find('.with-fee').removeClass('hide');
            }
            //$(form).find('input[name=payment_amount]').after('<span style="margin-left: 10px;" class="processingCC"><a href="#" class="pull-right">Processing</a></span>');
        } else {
            $(form).find('.credit_card').addClass('hide');
            $(form).find('.with-fee').addClass('hide');
            $(form).find('.file_block').show();
            $(form).find('.processingCC').remove();
            $(form).find('#processingCC1').remove();
        }
    });
    if ($('#payment_amount').parents('.control-group').find('#with_fee').length != 0) {
        $('#payment_amount').on("keyup", function (event) {
            let amount = parseFloat($(this).val()) || 0;
            let with_fee = amount + (amount * (cc_extra_fee / 100));
            $(this).parents('.control-group').find('#with_fee').text(Common.money(with_fee.toFixed(2)));
        });
    }
    $('form#change_invoice_status').submit(function (event) {
        var $form = $(this);
        $form.find('.form_error').hide();
        $form.find('.btntext').hide();
        $form.find('.preloader').show();
        $form.find('.preloader').parent().attr('disabled', 'disabled');

        var $target = $($form.attr('data-target'));
        var formData = new FormData(this);
        //formData.append('estimate_id', estimate_id);
        formData.append('pre_invoice_status', mainstatus);
        $.ajax({
            type: $form.attr('method'),
            url: baseUrl + 'invoices/ajax_change_invoice_status',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            dataType: 'json',
            cache: false,
            processData: false,
            success: function (data, status) {
                $form.find('.error .help-inline').html('');
                $form.find('.error').removeClass('error').removeClass('has-error');
                if (data.status == 'error') {
                    $form.find('.btntext').show();
                    $form.find('.preloader').hide();
                    $form.find('.preloader').parent().removeAttr('disabled');
                    if(data.errors) {
                        $.each(data.errors, function (key, val) {
                            if (val) {
                                $form.find('#' + key).parent().parent().addClass('error').addClass('has-error');
                                $form.find('#' + key).next().html(val);
                                $form.find('#' + key).next().addClass('text-danger');
                            }
                        });
                    }
                    if(data.error){
                        $form.find('.form_error').html(data.error);
                        $form.find('.form_error').show();
                    }
                    return false;
                }
                if (data.status == 'ok') {
                    $('#changeInvoiceStatus').modal('hide');
                    if(data.thanks !== undefined && data.thanks != ''){
                        $('#email-template-form').find('input[name="estimate_id"]').val(data.thanks['estimate_id']);
                        $('#new_payment').modal('hide');

                        ClientsLetters.init_modal(data.thanks['email_template_id'], 'ClientsLetters.invoice_email_modal');

                    }else {
                        location.reload();
                    }
                    return false;
                }
            }
        });
        event.preventDefault();
    });
	<?php  if(config_item('processing')) :  ?>
	//$(document).on('click', '#processingCC1', function () {
	//	var form = $(this).parents('form:first');
	//	if (processing)
	//		return false;
	//	$(form).find('#payment_amount').parent().parent().removeClass('has-error');
	//	$(form).find('#payment_amount').next().next().text('');
	//	var obj = $(this);
	//	var payment_type = 'invoice';
	//	var invoice_id = invoiceid;
	//	var cc_id = $(obj).parents('form:first').find('[name=credit_card]').val();
	//	var amount = $(form).find('#payment_amount').val();
	//	var sum = parseFloat($('#totalSum').text().replace('$', ''));
	//	if (amount < sum) {
	//		$(form).find('#payment_amount').parent().parent().addClass('has-error');
	//		$(form).find('#payment_amount').next().next().text('Incorrect Amount');
	//		$(form).find('#changeStatus .btntext').show();
	//		$(form).find('#changeStatus .preloader').hide();
	//		$(form).find('#changeStatus .preloader').parent().removeAttr('disabled');
	//		return false;
	//	}
	//	if (amount > <?php //echo _CC_MAX_PAYMENT; ?>//)
    //		if (!confirm('Confirm payment processing > <?php //echo money(_CC_MAX_PAYMENT); ?>//'))
	//			return false;
	//	processing = true;
	//	$(this).replaceWith('<img width="25px" id="processingCCWait" style="margin-left: 10px;position: absolute; right: 160px; margin-top: -30px;" src="' + baseUrl + 'assets/img/wait.gif' + '">');
	//	$.each($('#changeInvoiceStatus .btn'), function (key, val) {
	//		$(val).attr('disabled', 'disabled');
	//	});
	//	$.each($('#changeInvoiceStatus input'), function (key, val) {
	//		$(val).attr('disabled', 'disabled');
	//	});
	//	$.each($('#changeInvoiceStatus select'), function (key, val) {
	//		$(val).attr('disabled', 'disabled');
	//	});
	//	$.each($('#changeInvoiceStatus a'), function (key, val) {
	//		$(val).attr('disabled', 'disabled');
	//	});
	//	$.post(baseUrl + 'payments/ajax_payment', {type: payment_type, invoice_id: invoice_id, amount: amount, cc_id:cc_id}, function (resp) {
	//		if (resp.status == 'error') {
	//			$.each($('#changeInvoiceStatus .btn'), function (key, val) {
	//				$(val).removeAttr('disabled');
	//			});
	//			$.each($('#changeInvoiceStatus input'), function (key, val) {
	//				$(val).removeAttr('disabled');
	//			});
	//			$.each($('#changeInvoiceStatus select'), function (key, val) {
	//				$(val).removeAttr('disabled');
	//			});
	//			$.each($('#changeInvoiceStatus a'), function (key, val) {
	//				$(val).removeAttr('disabled');
	//			});
	//			$('#changeInvoiceStatus').find('[name="save"]').attr('disabled', 'disabled');
	//			$('#processingCCWait').replaceWith('<span class="help-inline" style="margin-top: -12px; margin-left: 10px;" id="processingCC1"><a href="#" class="pull-right">Processing</a></span>');
	//			alert(resp.msg);
	//			processing = false;
	//		}
	//		else {
	//			$('#changeInvoiceStatus button[data-dismiss="modal"]').removeAttr('disabled');
	//			$('#changeInvoiceStatus button[data-dismiss="modal"]').attr('onclick', 'location.reload();');
	//			var arr = resp.file.split('/');
	//			$('#processingCCWait').replaceWith('<i class="fa fa-check" style="position: absolute;right: 170px;margin-top: 3px;"></i><a target="_blank" class="pull-right" href="' + resp.file + '">' + arr[arr.length - 1] + '</a>');
	//			processing = false;
	//		}
	//	}, 'json');
	//});
	<?php endif; ?>
	$('#payment_amount').on("keypress keyup blur", function (event) {
		if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
			event.preventDefault();
		}
	});
</script>
