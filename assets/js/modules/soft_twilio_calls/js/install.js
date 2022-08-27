$(document).on('click', '.view_existing_services', function(e) {
	e.stopPropagation();
	e.preventDefault();
	var currentThis = $(this);
	let data = $("#sms_account_sid, #sms_auth_token").serialize();
	$.ajax({
		url : '/settings/integrations/twilio/install/get_messaging_services',
		data : data,
		type : 'post',
		success : function(response) {
			if (response.error) {
				if (typeof response.error == 'object') {
					let errorText = response.error.sms_twilio_account_sid ? response.error.sms_twilio_account_sid[0] : '';
					errorText += "<br />";
					errorText += response.error.sms_twilio_auth_token_sid ? response.error.sms_twilio_auth_token_sid[0] : '';
					$('.messagingError').html(errorText);
				} else {
					$('.messagingError').html(response.error);
				}
			} else {
				$('.messagingError').html('');
				if (response.data.length == 0) {
					$('.view_existing_services').addClass('hidden');
					$('.get_form_create_services').removeClass('hidden');
					$('#messaging_service_block').html('');
				} else {
					$('#messaging_service_block').html(response.data);
				}
			}

		},
		error : function(XHR, textStatus, errorThrown) {

		}
	});
});

$(document).on('submit', 'form[name="modalMessagingServicesForm"]', function(e) {
	e.stopPropagation();
	e.preventDefault();
	var currentThis = $(this);

	$.ajax({
		url : '/settings/integrations/twilio/messaging-services/create',
		data: currentThis.serialize(),
		type : 'post',
		success : function(response) {
			if (response.data.errors) {
				if (typeof response.data.errors == 'object') {
					Object.keys(response.data.errors).map(( v, i ) => {
						$('.' + v).html(response.data.errors[v][0]);
					});
				}
				return false;
			}
			if (response.data.primaryNumberSid) {
				$('.primarySmsTwilioNumber').val(response.data.primaryNumberSid);
			}
			if (response.data.messagingServiceSid) {
				$('#messaging_service_block').append('<input type="hidden" name="messagingServiceSid" value="' + response.data.messagingServiceSid + '" />');
			}
			$('#messagingServicesModal').modal('hide');
			$('.servicesBlock').addClass('hidden');
		},
		error : function(XHR, textStatus, errorThrown) {

		}
	});
	return false;
});

$(document).on('click', '.get_form_create_services', function(e) {
	e.stopPropagation();
	e.preventDefault();
	var currentThis = $(this);

	let sms_twilio_account_sid = $("#sms_account_sid").val();
	let sms_twilio_auth_token_sid = $('#sms_auth_token').val();
	let queryUrl = '?sms_twilio_account_sid=' + sms_twilio_account_sid + '&sms_twilio_auth_token_sid=' + sms_twilio_auth_token_sid;
	$.ajax({
		url : '/settings/integrations/twilio/messaging-services/create' + queryUrl,
		type : 'get',
		async : false,
		dataType : 'json',
		success : function(response) {
			if (response.error) {
				console.log(response, 'response');
				if (typeof response.error == 'object') {
					let errorText = response.error.sms_twilio_account_sid ? response.error.sms_twilio_account_sid[0] : '';
					errorText += "<br />";
					errorText += response.error.sms_twilio_auth_token_sid ? response.error.sms_twilio_auth_token_sid[0] : '';
					$('.messagingError').html(errorText);
				} else {
					$('.messagingError').html(response.error);
				}
			} else {
				$('#messaging-services-modal').html(response.data);
				$('#messagingServicesModal').modal('show');
			}

		},
		error : function(XHR, textStatus, errorThrown) {

		}
	});
});

$(document).on('click', '.sms_numbers_block_duplicate', function(e) {
	e.stopPropagation();
	e.preventDefault();
	let newItem = $('.sms_numbers_item_block').first();
	let newItemClone = newItem.clone();
	let primary_number_total = $('.primary_number').length + 1;
	let sms_service_twilio_number_total = $('.sms_service_twilio_number').length + 1;

	newItemClone.find('.sms_numbers_block_duplicate')
		.removeClass('sms_numbers_block_duplicate fa-plus-circle')
		.addClass('sms_numbers_block_duplicate_remove fa-minus-circle');


	newItemClone.find('.primary_number').attr({
		'name': 'sms_twilio_primary_number[' + primary_number_total + ']',
		'checked': false
	});
	newItemClone.find('.sms_service_twilio_number_total').attr('name', 'twilioNumber[' + sms_service_twilio_number_total + ']');

	$('.sms_numbers_block').append(newItemClone);
});

$(document).on('click', '.sms_numbers_block_duplicate_remove', function(e) {
	e.stopPropagation();
	e.preventDefault();
	$(this).parents('.sms_numbers_item_block').remove();
});