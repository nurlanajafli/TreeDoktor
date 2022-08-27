<script type="text/javascript">
	function checkStatus(val) {

		$('.input-block-level').val("");

		if (val == 'Finished') {
			//$('.modal.fade.in').css('top', '0px');
			$('#confirm_div').show();
			$('#status_div').css('overflow-y', 'scroll');
			$('#status_div').css('height', '310px');
		} else {
			//$('.modal.fade.in').css('top', '10%');
			$('#confirm_div').hide();
			$('#status_div').css('overflow-y', 'none');
			$('#status_div').css('height', 'auto');
		}
	}

	$('#change_workorder_status_form').submit(function () {
		
		var wo_id = parseInt($(this).find('[name=workorder_id]').val());
		var wo_status = parseInt($(this).find('#workorder_status').val());
		
		$.ajax({
			url: '<?php echo base_url();?>workorders/ajax_change_workorder_status/',
			type: "POST",
			dataType: 'json',
			data: $(this).serialize(),
			success: function (data) {
				
				if (data['status'] == 'success') {
					$("#changeWorkorderStatus").modal('hide');
					
					if(parseInt(data.workorder_data.is_finished)==0)
						location.reload();
					
					if(parseInt(data.workorder_data.is_finished)==1){
						DamagesModal.init(wo_id);
					}
				}
				else {
					message = "Sorry! there is some problem. Please try later.";
					if(data['message']!=undefined && data['message'].length)
						message = data['message']; 

					$('#change_status_error').html(message).show();
				}
			}
		});

		return false;
	});

	$(function () {

		$("#workorder_priority").click(function () {

			var for_client_id = $("input#for_client_id").val();
			var workorder_id = $("input#workorder_id").val();
			var workorder_number = $("input#workorder_number").val();
			var new_workorder_priority = $("select#new_workorder_priority").val();
			var old_workorder_priority = $("input#old_workorder_priority").val();
			//var pre_workorder_priority = $("input#pre_workorder_priority").val();

			var dataString = 'for_client_id=' + for_client_id + '&workorder_id=' + workorder_id + '&workorder_number=' + workorder_number + '&old_workorder_priority=' + old_workorder_priority + '&new_workorder_priority=' + new_workorder_priority;
			//alert (dataString);return false;

			$.ajax({
				type: "POST",
				url: '<?php echo base_url();?>workorders/ajax_update_workorder_priority/',
				data: dataString,
				success: function () {
					location.reload();
				}
			});
			return false;

		});

		<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('SCHD_RPR') == 1) : ?>
		$(document).delegate('.confirmReport', 'click', function() {
            var obj = $(this);
			var event_id = $(this).data('event_id');
            var report_id = $(this).data('er_id');
			$.post(baseUrl + 'schedule/ajax_confirm_report', {report_id:report_id}, function(resp){
				if(resp.status == 'ok')
				{
					if($('.eventsList').length)
					{
						$(obj).parents('.reportInfoModal').modal('hide');
						setTimeout(function(){
							$('.eventsList').find('li[data-event_id="'+event_id+'"]').css('background', '#C9E2B6').fadeOut('slow', function(){
								$('.eventsList').find('li[data-event_id="'+event_id+'"]').remove();
								$('#reportsCounter').text(parseInt($('#reportsCounter').text()) - 1);
							});
						}, 500);
					}
					else
					{
						location.reload();
					}
				}
				else
				{
					alert('Ooops! Error.');
				}
			}, 'json');
			return false;
		});
		<?php endif; ?>
	});

</script>
