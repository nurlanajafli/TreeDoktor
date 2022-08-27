<!-- Change Estimate Status Modal -->
<div id="changeEstimateStatus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="false">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			<header class="panel-heading">Edit Estimate Status</header>
            <form id="change_estimate_status" method="POST" class="" enctype="multipart/form-data">
                <div class="modal-body">
                        <div class="p-10">
                            <span class="error" style="color:#FF0000;" id="change_status_error"></span><br>
                            <?php if ($estimate_data->status == 6) {
                                echo '<div class="alert alert-danger alert-block">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <h4>Warning!</h4>
                                    Please be advised that changing confirmed estimated status will delete all Work-orders and Invoices related to this project!
                                    </div>';
                            } ?>
                            <div class="form-group">
                                <label class="control-label">Estimate Status:</label>
                                <div>
                                    <select name="new_estimate_status" id="sel_estimate_status" class="form-control">
                                        <?php foreach($statuses as $key=>$status) : ?>
                                            <option value="<?php echo $status->est_status_id; ?>" <?php if($status->est_status_id == $estimate_data->status_id) : ?>selected="selected"<?php endif; ?> data-confirmed="<?php echo $status->est_status_confirmed; ?>"><?php echo $status->est_status_name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="help-inline"></span>
                                </div>
                            </div>
                            <?php foreach($statuses as $key=>$status) : ?>
                                <?php if(isset($status->mdl_est_reason) && !empty($status->mdl_est_reason)) : ?>
                                    <div class="form-group m-t-xs statusReasons" data-status_id="<?php echo $status->est_status_id; ?>" <?php if($status->est_status_id != $estimate_data->status) : ?>style="display:none;"<?php endif; ?>>
                                        <label class="control-label">Select Reason</label>
                                        <div>
                                            <select class="form-control" name="reason" <?php if($status->est_status_id != $estimate_data->status) : ?>disabled="disabled"<?php endif; ?>>
                                                <?php foreach($status->mdl_est_reason as $reason) : ?>
                                                <option value="<?php echo $reason->reason_id; ?>"><?php echo $reason->reason_name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <!--Confirem status div, show if the confirmed status is being selected -->
                            <div id="confirm_div" class="m-t-xs">
                                <span>
                                    <div class="form-group">
                                        <label class="control-label">Confirmed how:</label>
                                        <div>
                                            <?php $options = array(
                                                'name' => 'wo_confirm_how',
                                                'id' => 'wo_confirm_how',
                                                'class' => 'form-control',
                                                'Placeholder' => 'Over the phone, in person, by email etc.',
                                                'value' => set_value('wo_confirm_how'));

                                            echo form_input($options);
                                            ?>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Select payment:</label>
                                        <div>
                                            <?php $disabled = ''; ?>
                                            <?php if (isset($payments_data) && $payments_data) : ?>
                                                <?php foreach ($payments_data as $key => $payment) : ?>
                                                    <input type="radio" class="payment" name="payment_id"
                                                           style="margin-top:-2px;"<?php if (!$key) : ?> checked<?php endif; ?>  value="<?php echo $payment['payment_id']; ?>">
                                                    <span
                                                        style="margin-left: 10px;">Payment - <?php echo money($payment['payment_amount']); ?></span>
                                                    <br>
                                                <?php endforeach; ?>
                                            <?php //$disabled = 'disabled'; ?>
                                            <input type="radio" name="payment_id" class="newpayment" style="margin-top:-2px;" value="0">
                                            <span style="margin-left: 10px;">New Payment</span><br>
                                            <?php endif; ?>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Deposit taken by:</label>
                                        <div>
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
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <?php if(config_item('processing')) : ?>
                                        <div class="form-group credit_card hide">
                                            <label class="control-label">Payment Card:</label>
                                            <div>
                                                <select id="cc_select" name="cc_id" class="form-control">
                                                </select>
                                                <span class="help-inline"></span>
                                                <a href="#card-form" id="add_cc_card" class="pull-right add_credit_card" data-toggle="modal">Add card</a>
                                            </div>
                                        </div>
                                    <?php
                                    endif; ?>
                                    <div class="form-group">
                                            <label class="control-label">Amount:</label>
                                            <div class="input-group">
                                                <span class="input-group-addon"><?php
                                                    echo get_currency(); ?></span>
                                                <input type="text" class="form-control" name="wo_deposit"
                                                       value=""
                                                       id="appendedPrependedInput_wo_deposit" type="text" <?php
                                                echo $disabled; ?>>
                                                <!--<span class="input-group-addon">.00</span>-->
                                            </div>
                                            <span class="help-inline"></span>
                                            <?php
                                            if ((float) config_item('cc_extra_fee') > 0) : ?>
                                                <small class="text-left block with-fee hide">
                                                    <div>+ <?php echo (float) config_item('cc_extra_fee') ?>% transaction fee</div>
                                                    Total Payment: <span id="with_fee" class="font-bold"><?php echo money(0); ?></span>
                                                </small>
                                            <?php
                                            endif; ?>
                                        </div>
                                    <span class="validate-error"></span>
                                    <br>
                                    <div class="controls file_block form-group">
                                        <span class="btn btn-primary btn-file" id="file" <?php
                                        echo $disabled; ?>>Choose File
                                            <input type="file" name="payment_file" id="paymentFileToUpload"
                                                   class="btn-upload" <?php
                                            echo $disabled; ?>>
                                        </span>
                                        <span class="help-inline"></span>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Payment date:</label>
                                        <div>
                                            <input id="estimate_payment_date"
                                                   class="form-control"
                                                   type="datetime-local"
                                                   name="payment_date"
                                            >
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Payment notes:</label>
                                        <div>
                                            <textarea class="form-control" name="payment_note"></textarea>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Crew Notes:</label>
                                        <div>
                                            <?php $options = array(
                                                'name' => 'estimate_crew_notes',
                                                'id' => 'estimate_crew_notes',
                                                'class' => 'form-control',
                                                'Placeholder' => 'Notes to the crew.',
                                                'value' => set_value('estimate_crew_notes')?set_value('estimate_crew_notes'):$estimate_data->estimate_crew_notes
                                            );

                                            echo form_input($options);
                                            ?>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label">Office Notes:</label>
                                        <div>
                                            <?php $options = array(
                                                'name' => 'wo_office_notes',
                                                'id' => 'wo_office_notes',
                                                'rows' => '1',
                                                'class' => 'form-control',
                                                'Placeholder' => 'Scheduling preferences, office notes',
                                                'value' => set_value('wo_office_notes'));

                                            echo form_textarea($options);
                                            ?>
                                            <span class="help-inline"></span>
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <!--Confirem status div ends -->
                        </div>
                        <input type="hidden" value="<?php echo $estimate_data->estimate_id; ?>" name="estimate_id">
                    <div class="text-danger form_error" style="display: none; text-align: left; font-size: 20px; padding-top: 20px;"></div>
                </div>

                <div class="modal-footer">
                    <div class="pull-right ">
                        <button name="save"  type="submit" class="btn btn-success m-right-5">
                            <span class="btntext">Save</span>
                            <img src="<?php echo base_url(); ?>assets/img/ajax-loader.gif"
                                 style="display: none;width: 32px;" class="preloader">
                        </button>
                        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Change Estimate Status Modal End-->
<script>
    var processing = false;
    var estimate_id = "<?php echo $estimate_data->estimate_id; ?>";
    var pre_estimate_status = "<?php echo $estimate_data->status_id; ?>";
    var default_cc = '<?php echo $this->config->item('default_cc')?>';
    var cc_extra_fee = <?php echo (float) config_item('cc_extra_fee') ?>;

    const paymentDate = document.getElementById('estimate_payment_date');
    paymentDate.value = dateTime;
    paymentDate.max = dateTime;

    $(document).on('click', '#add_cr_card', function () {
        if (processing)
            return false;
        var form = $(this).parents('form:first');
        $(form).find('.add_cc_block').slideToggle();
    });
    $(document).on('change', '.edit_payment_method', function () {
        var form = $(this).parents('form:first');
        if ($(this).val() == 'cc') {
			$('#changeEstimateStatus').find('[name="save"]').attr('disabled', 'disabled');
			$(form).find('.credit_card').removeClass('hide');

			$('#appendedPrependedInput_wo_deposit').parent().after('<span style="margin-left: 10px;" class="processingCC"><a href="#" class="pull-right">Processing</a></span>');
		}
		else {
			$('#changeEstimateStatus').find('[name="save"]').removeAttr('disabled');
			$(form).find('.credit_card').addClass('hide');
			$('.processingCC').remove();
		}
	});

    $(document).on('change', '[name="payment_id"]', function () {
		if ($(this).is('.payment')) {
			$('.edit_payment_method').attr('disabled', 'disabled');
			$('#appendedPrependedInput_wo_deposit').attr('disabled', 'disabled');
			$('#changeEstimateStatus .btn.btn-primary.btn-file').attr('disabled', 'disabled');
			$('#changeEstimateStatus #paymentFileToUpload').attr('disabled', 'disabled');
		}
		if ($(this).is('.newpayment')) {
			$('.edit_payment_method').removeAttr('disabled');
			$('#appendedPrependedInput_wo_deposit').removeAttr('disabled');
			$('#changeEstimateStatus .btn.btn-primary.btn-file').removeAttr('disabled');
			$('#changeEstimateStatus #paymentFileToUpload').removeAttr('disabled');
		}
		return false;
	});
	$('#appendedPrependedInput_wo_deposit').on("keypress keyup blur", function (event) {
		if ((event.which < 48 || event.which > 57) && (event.which != 46)) {
			event.preventDefault();
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
    if ($('#appendedPrependedInput_wo_deposit').parents('.form-group').find('#with_fee').length != 0) {
        $('#appendedPrependedInput_wo_deposit').on("keyup", function (event) {
            let amount = parseFloat($(this).val()) || 0;
            let with_fee = amount + (amount * (cc_extra_fee / 100));
            $(this).parents('.form-group').find('#with_fee').text(Common.money(with_fee.toFixed(2)));
        });
    }

    $('form#change_estimate_status').submit(function (event) {
        var $form = $(this);
        $form.find('.form_error').hide();
        $form.find('.btntext').hide();
        $form.find('.preloader').show();
        $form.find('.preloader').parent().attr('disabled', 'disabled');
        var $target = $($form.attr('data-target'));
        var formData = new FormData(this);
        formData.append('estimate_id', estimate_id);
        formData.append('pre_estimate_status', pre_estimate_status);
        $.ajax({
            type: $form.attr('method'),
            url: baseUrl + 'estimates/ajax_change_estimates_status',
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
                                $form.find('#' + key).next('.help-inline').html(val);
                                $form.find('#' + key).next('.help-inline').addClass('text-danger');
                                if($form.find('#' + key).next('.help-inline').length == 0) {
                                    $form.find('#' + key).parent().next('.help-inline').html(val);
                                    $form.find('#' + key).parent().next('.help-inline').addClass('text-danger');
                                }

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
                    $('#changeEstimateStatus').modal('hide');
                    location.reload();
                    return false;
                }
            }
        });
        event.preventDefault();
    });
</script>
