<script type="text/javascript">
	$(document).ready(function(){
		$('#sel_estimate_status').change(function(){
			var status_id = $(this).val();
			$('.statusReasons').fadeOut();
			$('.statusReasons').find('select').attr('disabled', 'disabled');
			$('.statusReasons[data-status_id="'+status_id+'"]').fadeIn();
			$('.statusReasons[data-status_id="'+status_id+'"]').find('select').removeAttr('disabled');
			if($('#sel_estimate_status option[value="'+status_id+'"]').data('confirmed'))
				$('#confirm_div').fadeIn();
			else
				$('#confirm_div').fadeOut();
			return false;
		});
	});
	function checkStatus(val) {

		$('.input-block-level').val("");

		if (val == 'Confirmed') {
			$('.modal.fade.in').css('top', '0px');
			$('#confirm_div').show();
		} else {
			$('.modal.fade.in').css('top', '10%');
			$('#confirm_div').hide();
		}
	}

	// function change_estimaate_status(estimate_id, pre_estimate_status) {
	// 	$('#changeEstimateStatus #fileToUpload').parent().next().find('.fileError').remove();
	// 	$('#changeEstimateStatus #appendedPrependedInput_wo_deposit').parent().next().find('.depositError').remove();
	// 	$('.modal-footer .btntext').hide();
	// 	$('.modal-footer .preloader').show();
	// 	$('.modal-footer .preloader').parent().attr('disabled', 'disabled');
	// 	var $form = $('#change_estimate_status');
	// 	var formData = new FormData(document.getElementById('change_estimate_status'));
	// 	formData.append('estimate_id', estimate_id);
	// 	formData.append('pre_estimate_status', pre_estimate_status);
	// 	$.ajax({
	// 		type: $form.attr('method'),
	// 		url: baseUrl + 'estimates/ajax_change_estimates_status',
	// 		data: formData,
	// 		mimeType: "multipart/form-data",
	// 		contentType: false,
	// 		dataType: 'json',
	// 		cache: false,
	// 		processData: false,
	// 		method: 'post',
	// 		success: function (data, status) {
	// 			if (data.status == 'success') {
	// 				$("#changeEstimateStatus").modal('hide');
	// 				if(data.confirmed)
	// 				{
	// 					if(confirm("Estimate status Confirmed. Do you want to send email?"))
	// 					{
	// 						$('.modal-footer .btntext').show();
	// 						$('.modal-footer .preloader').hide();
	// 						$('.modal-footer .preloader').parent().removeAttr('disabled');
	// 						$('#sentConfirmedPdfToEmail').modal();
	// 					}
	// 				}
	// 				else
	// 					location.reload();
	// 			} else {
	// 				if(data.msg)
	// 				{
	// 					if(data.status == 'deposit_error')
	// 					{
	// 						$('#changeEstimateStatus #appendedPrependedInput_wo_deposit').parent().next().html('<span class="text-danger depositError">' + data.msg + '</span>');
	// 					}
	// 					else{
	// 						$('#changeEstimateStatus #fileToUpload').parent().next().html('<span class="text-danger fileError">' + data.msg + '</span>');
	// 					}
	// 				}
	// 				else
	// 				{
	// 					$('#change_status_error').html("Sorry! there is some problem. Please try later.").show();
	// 				}
	// 				$('.modal-footer .btntext').show();
	// 				$('.modal-footer .preloader').hide();
	// 				$('.modal-footer .preloader').parent().removeAttr('disabled');
	// 			}
	// 		}
	// 	});
	// 	event.preventDefault();
	// }

</script>
