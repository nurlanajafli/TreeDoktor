var InvoiceStatus = function(){
	var config = {
		
		ui:{
			//invoice_status_select: 'sel_invoice_status'
			payment_form_container: '#payment-method-container',
			file_container: '#upload_file',
			payment_amount: '#payment_amount'
		},
		
		events:{
			invoice_status_select: '#new_invoice_status',
			btn_change_status: '#changeStatus'
		},

		route:{
			
		},

		templates:{
		
		}
	}
	
	var _private = {
		init:function(){

		},

		change_status_select: function(){
			var data = $(this).children("option:selected").data();
			if (data.completed==1) {
				$(config.ui.payment_amount).val(window.estimate_total_due);
				$(config.ui.payment_form_container).show();
				$(config.ui.file_container).show();
			} else {
				$(config.ui.payment_form_container).hide();
				$(config.ui.file_container).hide();
			}
			_private.reset_file_upload();

		},

		reset_file_upload: function(){
			$(config.ui.file_container+' input').val('').trigger('change');
		},

		change_invoice_status(){
			var data = $(this).data();
			var invoice_id = data.invoice_id;
			var pre_invoice_status = data.in_status;
			var payment = data.payment;
			
			var select_data = $(config.events.invoice_status_select).children("option:selected").data();
						
			$('#changeStatus .btntext').hide();
			$('#changeStatus .preloader').show();
			$('#changeStatus .preloader').parent().attr('disabled', 'disabled');
			$('.payment_amount').parent().removeClass('error').removeClass('has-error');
			$('.payment_amount').next().text('');
			$('.payment_method1').parent().removeClass('error').removeClass('has-error');
			$('.payment_method1').next().text('');
			var invoice_status = $(config.events.invoice_status_select).val();
			var payment = $('#payment').val();
			
			if (select_data.completed == 1) {

				$('#fileupload').attr('id', 'add_client_payment');
				$.each($('.estimate_statuses [name="service_status"]'), function(key, val){
					if($(val).val() == '0')
						alert("The service can't have the status 'New'");
				});
				
				var sum = parseFloat($.trim($('#totalSum').text().replace(Common.get_currency(), '').replace(',', '')));

				errors = false;
				
				if(!$('.payment_method1').val()) {    
					$('.payment_method1').parent().addClass('error').addClass('has-error');
					$('.payment_method1').next().text('Payment Method Is Required');
					$('.payment_method1').next().addClass('text-danger');
					$('#changeStatus .btntext').show();
					$('#changeStatus .preloader').hide();
					$('#changeStatus .preloader').parent().removeAttr('disabled');
					errors = true;
				}

				if ($('.payment_amount').val() < sum) {
					$('.payment_amount').parent().addClass('error').addClass('has-error');
					$('.payment_amount').next().text('Incorrect Amount');
					$('.payment_amount').next().addClass('text-danger');
					$('#changeStatus .btntext').show();
					$('#changeStatus .preloader').hide();
					$('#changeStatus .preloader').parent().removeAttr('disabled');
					errors = true;
				}

				if(!errors)
					$('#add_client_payment').submit();

				return false;

			} else {
				$('#add_client_payment').attr('id', 'fileupload');
				showLoader("sel_invoice_status");
				$.post(window.base_url+'invoices/ajax_change_invoice_status/', {'invoice_id': invoice_id, 'payment': payment, 'pre_invoice_status': pre_invoice_status, 'new_invoice_status': invoice_status},
					function (response) {
						hideLoader();
						$('#changeStatus .btntext').show();
						$('#changeStatus .preloader').hide();
						$('#changeStatus .preloader').parent().removeAttr('disabled');
						if (response.status == 'success') {
							$("#changeInvoiceStatus").modal('hide');
							location.reload();
						} else if (response.status == 'success-mail-fail') {
							alert("Invoice status updated to Sent successfully.");
							$("#changeInvoiceStatus").modal('hide');
							location.reload();
						} else {
							$('#change_status_error').html("Sorry! there is some problem. Please try later.").show();
						}
					}, "json");
			}

			return false;
		}
	}

	var public = {

		init:function(){
			$(document).ready(function(){
			  	_private.init();
			  	public.events();
			});
		},
		
		events:function(){
			$(config.events.invoice_status_select).change(_private.change_status_select);
			/*$(config.events.btn_change_status).click(_private.change_invoice_status);*/
		}
	}

	public.init();
	return public;
}();
