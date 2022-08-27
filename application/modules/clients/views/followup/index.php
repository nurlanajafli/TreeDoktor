<?php $this->load->view('includes/header'); ?>
<link href="<?php echo base_url('assets/vendors/notebook/js/bootstrap-editable/css/bootstrap-editable.css'); ?>" rel="stylesheet">
<link href="<?php echo base_url('assets/vendors/notebook/js/datepicker/datepicker.css'); ?>" rel="stylesheet">
<style type="text/css">
	.popover {
		max-width: 300px!important;
		background-color: #fff;
    	color: #717171;
	}
	.notes .tooltip-inner {
	    white-space: pre-wrap;
	}
	.editable-buttons {
		margin-bottom: 15px;
	}
	@media (min-width: 768px) {
		.form-inline .form-control {
		    width: -webkit-fill-available!important;
		}
		.form-inline .form-group {
		    display: block;
		}
	}
	.tooltip .tooltip-inner {
		max-height: 300px;
		overflow: hidden;
	}
</style>
<script type="text/javascript">var ccTpls = {};</script>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('/clients'); ?>">Clients</a></li>
		<li class="active">Follow Up List</li>
	</ul>
	<section class="panel panel-default">
		<header class="panel-heading panel-heading__followup-container">
            <div class="panel-heading__count-list">
			    List (<?php echo $count; ?>)
            </div>
            <div class="panel-heading__filter-container">
                <div class="pull-right">
                    <select class="form-control changeType">
                        <?php foreach ($types as $key => $itemType) : ?>
                            <option value="<?php echo $key; ?>"<?php if($key == $type) echo ' selected'; ?>><?php echo $itemType; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pull-right m-r-sm">
                    <select class="form-control changeStatus">
                        <?php foreach ($statuses as $item) : ?>
                            <option value="<?php echo $item; ?>"<?php if($item == $status) echo ' selected'; ?>><?php echo ucfirst($item); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="pull-right m-r-sm">
                    <select class="form-control changeModule">
                        <?php foreach ($modules as $moduleName) : ?>
                            <option value="<?php echo $moduleName; ?>"<?php if($moduleName == $module) echo ' selected'; ?>><?php echo $moduleName == 'all' ? 'Select Module' : ucwords(str_replace('_', ' ', $moduleName)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
		</header>

		<div class="m-bottom-10 p-sides-10">
			<table class="table tsble-striped m-n" id="followupTable" data-firstnumber="<?php echo $start + 1; ?>">
				<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center" width="150px">Client</th>
					<th class="text-center">Phone</th>
					<th class="text-center">Estimator</th>
					<th class="text-center">Profile</th>
					<th class="text-center">Due</th>
					<th class="text-center">Item Status</th>
					<th class="text-center">Type</th>
					<th class="text-center">Status</th>
					<th class="text-center">Date</th>
					<th class="text-center">Period</th>
					<th class="text-center">TPL</th>
					<th class="text-center">User</th>
					<th class="text-center">Notes</th>
					<th class="text-center">V</th>
				</tr>
				</thead>
				<tbody>
				<?php if ($fu_list) : ?>
					<?php $this->load->view('followup/rows'); ?>
				<?php else : ?>
					<tr>
						<td colspan="7">
							<?php echo "No records found"; ?>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<footer class="panel-footer">
			<div class="row">
				<div class="col-sm-5 text-right text-center-xs pull-right">
					<?php echo isset($links) ? $links : ''; ?>
				</div>
			</div>
		</footer>
	</section>
</section>
<script src="<?php echo base_url(); ?>assets/js/card-js.min.js?v=1.01"></script>
<script type="text/javascript">
	var processing = <?php echo config_item('processing') ? 'true': 'false'; ?>;
	var default_cc = <?php echo config_item('default_cc'); ?>;
	var payment_methods = JSON.parse('<?php echo addslashes(json_encode(config_item('payment_methods'))); ?>');
	function initFollowUpEditable(selector) {
		if(!selector)
			selector = '';
		$(selector + '.fu_status').editable({
			success: function(response, newValue) {
				editableCallback(response);
			},
			validate: function(value){
				if(value.fu_status == "postponed" && !value.fu_date)
				{
					return '"Postpone Date" field is required';
				}
				if(!value.fu_comment)
				{
					return '"Notes" field is required';
				}
			}
		});

		$(selector + '.fu_item_status_leads').editable({
			success: function(response, newValue) {
				editableCallback(response);
			},
			validate: function(value){
				 console.log(value); return false;
				if(value.new_estimate_status == 4 && !value.reason) {
					return 'Reason Is Required';
				}
			}
		});

		$(selector + '.fu_item_status_estimates').editable({
			success: function(response, newValue) {
				editableCallback(response);
			},
            error: function(response, newValue) {
			    var resp = response.responseJSON;
			    if(!resp)
			        return "Unknown error!";
			    if(resp.error)
			        return resp.error;
			    if(resp.errors)
			        self = this;
                    $.each(resp.errors, function (key, val) {
                        if (val) {
                            self.$form.find('#' + key).parent().parent().addClass('error').addClass('has-error');
                            self.$form.find('#' + key).next('.help-inline').html(val);
                            self.$form.find('#' + key).next('.help-inline').addClass('text-danger');
                            if(self.$form.find('#' + key).next('.help-inline').length == 0) {
                                self.$form.find('#' + key).parent().next('.help-inline').html(val);
                                self.$form.find('#' + key).parent().next('.help-inline').addClass('text-danger');
                            }

                        }
                    });
            },
			validate: function(value){
				if(value.new_estimate_status == value.pre_estimate_status) {
					$(this).editable('toggle');
					return 'no changes';
				}
				// if(value.new_estimate_status == 6) {
				// 	var fileInput = $(document).find('.confirmed_block input[type=file]');
				//
				// 	if(!$(fileInput)[0].files.length && !$('#ccReceipt').val())
				// 		return 'File Is Required';
				//
				// 	var fileTypes = ["image/png", "image/jpeg", "image/jpg", "image/gif", "application/pdf"];
                //
				// 	if(($(fileInput).length && $(fileInput)[0].files.length && !(fileTypes.indexOf($(fileInput)[0].files[0].type) + 1)) && !$('#ccReceipt').val())
				// 		return 'File must be Image or PDF';
				// }
				// if(value.new_estimate_status == 4 && !value.reason) {
				// 	return 'Reason Is Required';
				// }
			}
		});

		$(selector + '.fu_item_status_invoices').editable({
			success: function(response, newValue) {
				if(response.status == 'ok')
					editableCallback(response);
				else {
					return false;
				}
			},
			error: function(response, newValue) {
				return response.responseJSON.msg;
			},
			validate: function(value) {

				// $(this).editable('option', 'url', baseUrl + 'clients/ajax_update_followup_item_status');
				// if(value.new_invoice_status == 4) {
                //
				// 	$(this).editable('option', 'url', $(this).data('editable').options.url + '_paid_invoice');
				//
				// 	var fileTypes = ["image/png", "image/jpeg", "image/jpg", "image/gif", "application/pdf"];
                //
				// 	if(!value.payment_amount) {
				// 		return 'Payment Amount Is Required';
				// 	}
                //
				// 	var fileInput = $(document).find('.paid_block input[type=file]');
				//
				// 	if(!$(fileInput)[0].files.length && !$('#ccReceipt').val())
				// 		return 'File Is Required';
                //
				// 	if(($(fileInput).length && $(fileInput)[0].files.length && !(fileTypes.indexOf($(fileInput)[0].files[0].type) + 1)) && !$('#ccReceipt').val()) {
				// 		return 'File must be Image or PDF';
				// 	}
                //
				// }
			}
		}).on('shown', function(e, editable) {
    		editable.input.$input.val('overwriting value of input..');
		});

		$(selector + '.fu_comment').editable({
			display: function(value) {
				if(!value)
					$(this).text('—');
				else
					$(this).text(value);
			},
			success: function(response, newValue) {
				editableCallback(response);
			},
		}).on('shown', function(e, editable) {
			$(this).parents('td:first').find('.tooltip').addClass('hide');
		}).on('hidden', function(e, editable) {
			$(this).parents('td:first').find('.tooltip').css('display','none');
			$(this).parents('td:first').find('.tooltip').removeClass('hide');
		});
	}

	function editableCallback(response) {
		if(typeof(response) != 'object')
			response = $.parseJSON(response);
		if(response.status == 'ok') {
			var tableRowSelector = 'tr[data-id="' + response.id + '"]';

			if(response.html != undefined) {
				$(tableRowSelector).replaceWith(response.html);
				initFollowUpEditable(tableRowSelector + ' ');
				$(tableRowSelector).find('[data-toggle="tooltip"]').tooltip();
			}
			else {
				$(tableRowSelector).remove();
			}
			var firstNumber = $('#followupTable').data('firstnumber');
			$.each($('#followupTable tbody tr'), function(key, val) {
				$(this).find('td:first').text((firstNumber + key));
			});
		}
	}

	$(document).ready(function() {
		initFollowUpEditable();
		$('.changeStatus').change(function() {
			location.href = baseUrl + 'clients/followup/' + $(this).val() + '/' + $('.changeModule').val() + '/' + $('.changeType').val();
		});
		$('.changeModule').change(function() {
			location.href = baseUrl + 'clients/followup/' + $('.changeStatus').val() + '/' + $(this).val() + '/' + $('.changeType').val();
		});
		$('.changeType').change(function() {
			location.href = baseUrl + 'clients/followup/' + $('.changeStatus').val() + '/' + $('.changeModule').val() + '/' + $(this).val();
		});

		$(document).on('click', '.add_cr_card', function() {
			if($(this).attr('disabled') == 'disabled')
				return false;
			var obj = $(this).parents('.confirmed_block:first').length ? $(this).parents('.confirmed_block:first') : $(this).parents('.paid_block:first');
            if(!$(obj).find('.fu_cc_block').is(':visible'))
                $(obj).find('.fu_cc_block').show(10, function() {Popup.setPosition();});
            else
                $(obj).find('.fu_cc_block').hide(10, function() {Popup.setPosition();});
        });

        // $(document).on('change', '.fu_pm', function() {
        // 	var obj = $(this).parents('form:first');
        // 	if($(this).val() == 'cc') {
        // 		$(obj).find('#processingCC').show();
        // 	}
        // 	else {
        // 		$(obj).find('#processingCC').hide();
        // 	}
        // });

        $(document).on('click', '#processingCC', function () {
			if (processing)
				return false;
			var obj = $(this);
			var form = $(this).parents('form:first');
			$(form).find('.editable-error-block.help-block').html('').hide();
			$(form).find('.form-group:first').removeClass('has-error');
			var payment_type = $(form).find('.payment_type').val();
			var estimate_id = $(form).find('.estimate_id').val();
			var amount = $(form).find('.payment_amount').val();
			var validation = $(this).data('price_validation');
			if (amount > 2000)
				if (!confirm('Confirm payment processing > '+Common.money(2000)))
					return false;
			if(validation) {
				valid = amountValidation(estimate_id, amount);
				if(valid.status != 'ok') {
					$(form).find('.editable-error-block.help-block').html(valid.msg.replace("\r\n", '<br>')).show();
					$(form).find('.form-group:first').addClass('has-error');
					return false;
				}
			}


			processing = true;
			$(this).replaceWith('<img width="25px" id="processingCCWait" style="margin-left: 10px;position: absolute; right: -20px; top: 5px;" src="' + baseUrl + 'assets/img/wait.gif' + '">');
			$(form).find('.btn-file').attr('disabled', 'disabled');
			$(form).find('input.payment_amount').attr('disabled', 'disabled');
			$(form).find('select[name="payment_method_int"]').attr('disabled', 'disabled');
			$(form).find('#processingCC a').attr('disabled', 'disabled');
			$(form).find('a.add_cr_card').attr('disabled', 'disabled');
			$.post(baseUrl + 'payments/ajax_payment', {type: payment_type, estimate_id: estimate_id, amount: amount}, function (resp) {
				if (resp.status == 'error') {
					$(form).find('.btn-file').removeAttr('disabled');
					$(form).find('input[name="wo_deposit"]').removeAttr('disabled');
					$(form).find('select[name="payment_method_int"]').removeAttr('disabled');
					$(form).find('#processingCC a').removeAttr('disabled');
					$('#processingCCWait').replaceWith('<span style="margin-left: 10px;" id="processingCC"><a href="#" class="pull-right">Processing</a></span>');
					alert(resp.msg);
					processing = false;
				}
				else {
					var arr = resp.file.split('/');
					$(form).find('#ccReceipt').val(resp.file);
					$(form).find('#processingCCWait').replaceWith('<i class="fa fa-check"></i><a target="_blank" class="pull-right" href="' + resp.file + '">' + arr[arr.length - 1] + '</a>');
					processing = false;
					$.post(baseUrl + 'clients/ajax_followup_html', {pk: $(form).find('.fuPk').val()}, function (resp) {
						editableCallback(resp);
					});
				}
			}, 'json');
		});

		$(document).on('change', '.toComplete', function(){
			var obj = $(this);
			var pk = $(this).data('pk');
			var name = 'fu_status';
			var value = 'completed';
			$.post(baseUrl + 'clients/ajax_update_followup_field', {pk:pk, name:name, value:value}, function(response) {
				if(response.status == 'ok') {
					var tableRowSelector = 'tr[data-id="' + response.id + '"]';

					if(response.html != undefined) {
						console.log(tableRowSelector);
						$(tableRowSelector).replaceWith(response.html);
						initFollowUpEditable(tableRowSelector + ' ');
						$(tableRowSelector).find('[data-toggle="tooltip"]').tooltip();
					}
					else {
						$(tableRowSelector).remove();
					}
					var firstNumber = $('#followupTable').data('firstnumber');
					$.each($('#followupTable tbody tr'), function(key, val) {
						$(this).find('td:first').text((firstNumber + key));
					});
				}
				else {
					$(obj).prop('checked', false);
				}
			}, 'json');
		});
	});
	function amountValidation(estimate_id, amount) {
		var result = false;
		$.ajax({
			method:'POST',
			url: baseUrl + 'clients/ajax_amount_validator', 
			data: {estimate_id:estimate_id, amount:amount}, 
			success: function(resp){
				result = resp;
				return result;
			},
			async: false,
			//global: false,
			dataType:'json'
		});
		
		return result;
	}
</script>
<?php $this->load->view('includes/footer'); ?>
