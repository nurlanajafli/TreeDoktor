<!-- Estimate Information Display Options-->
<section class="estimate_profile_data m-b">


	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped b-a bg-white">
		<tr>
			<td class="p-5" width="108">Estimate:</td>
			<td class="p-5"><strong><?php echo $estimate_data->estimate_no; ?></strong></td>
		</tr>

		<tr>
			<td class="p-5">Date:</td>
<!--			<td class="p-5"><strong>--><?php //echo date('Y-m-d', $estimate_data->date_created); ?><!--</strong></td>-->
			<td class="p-5"><strong><?php echo getDateTimeWithTimestamp($estimate_data->date_created); ?></strong></td>
		</tr>
 
		<tr>
			<td class="p-5" style="vertical-align: middle;">Estimator:</td>
			<td class="p-5">
				<?php if($this->session->userdata('user_type') == "admin" || $this->session->userdata('CHNGESTS') == 1) : ?>
					<select name="estimator" class="form-control" id="changeEstimator">
                        <option>-</option>
						<?php foreach ($active_users as $active_user) : ?>
							<option
								value="<?php echo $active_user->id; ?>"<?php if ($estimate_data->user_id == $active_user->id) : ?> selected<?php endif; ?>><?php echo $active_user->firstname . "&nbsp;" . $active_user->lastname; ?></option>
						<?php endforeach; ?>
					</select>
					<script>
						$('#changeEstimator').change(function(){
							if(confirm('Are you sure ?'))
							{
								$.post(baseUrl + 'estimates/ajax_change_estimator', {estimate_id:<?php echo $estimate_data->estimate_id; ?>,estimator_id:$(this).val()}, function(resp){
									if(resp.status == 'ok')
										successMessage('Estimator changed!');
									else
										errorMessage('Error!');
								}, 'json');
							}
							else
							{
								$('#changeEstimator').val($('#changeEstimator').find('option[selected]').val())
							}
						});
					</script>
				<?php else : ?>
					<strong><?php echo $user_data->firstname; ?>
						&nbsp;<?php echo $user_data->lastname; ?></strong>
				<?php endif; ?>
			</td>
		</tr>
		<?php if((isset($lead_data->reffered_client) && $lead_data->reffered_client) || (isset($lead_data->reffered_user) && $lead_data->reffered_user)) : ?>
			<tr>
				<td class="p-5">Reffered By:</td>
				<td class="p-5">
					<?php if($lead_data->reffered_client) : ?>
						<a href="<?php echo base_url($lead_data->lead_reffered_client); ?>"><?php echo $lead_data->reffered_client; ?></a>					
					<?php else : ?>
						<?php echo $lead_data->reffered_user; ?>
					<?php endif; ?>
				</td>
		<?php endif; ?>
		<tr>
			<td class="p-5">Provided By:</td>
			<td class="p-5"><strong><?php echo isset($estimate_data->estimate_provided_by) && $estimate_data->estimate_provided_by  ? ucfirst($estimate_data->estimate_provided_by) : '-'; ?></strong></td>
		</tr>

		<tr>
			<td class="p-5">Status:</td>
			<td class="p-5"><strong><?php echo isset($estimate_data->est_status_name) ? $estimate_data->est_status_name : $estimate_data->status; ?></strong></td>
		</tr>

		<tr>
			<td class="p-5">Last Contact:</td>
			<td class="p-5">
				<strong><?php echo $estimate_data->estimate_last_contact ? getDateTimeWithTimestamp($estimate_data->estimate_last_contact) : '-'; ?></strong>
			</td>
		</tr>

		<tr>
			<td class="p-5">Count Contact:</td>
			<td class="p-5">
				<strong><?php echo $estimate_data->estimate_count_contact ? $estimate_data->estimate_count_contact : '-'; ?></strong>
			</td>
		</tr>
	</table>

	<div class="hidden-sm hidden-xs"><!-- class="hidden-sm hidden-xs"-->
		<!--Estimate Options -->
		
		<?php $this->load->view('brands/partials/estimate_brands_dropdown', ['brand_style'=>'', 'class'=>'input-sm select2 p-n']); ?>		
		<?php $this->load->view('brands/partials/estimate_brands_dropdown_form', ['brand_style'=>'', 'class'=>'input-sm select2 p-n']); ?>

		<a title="Call" href="#addEstimateCall" role="button" class="btn btn btn-primary btn-block" data-toggle="modal"><i
				class="fa fa-headphones"></i>&nbsp;&nbsp;Contact&nbsp;&nbsp;</a>
		<a title="Update Estimate" href="#changeEstimateStatus" role="button" class="btn btn-success btn-block"
		   data-toggle="modal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Update Status&nbsp;&nbsp;</a>
		<?php echo anchor($estimate_data->estimate_no . '/pdf', '<i class="fa fa-file"></i>&nbsp;&nbsp;Download Estimate PDF', 'class="btn btn-dark btn-block" type="button"'); ?>
		
		<?php //if (/*!intval($client_data->client_unsubscribe) && */isset($client_contact['cc_email']) && $client_contact['cc_email'] && filter_var($client_contact['cc_email'], FILTER_VALIDATE_EMAIL)): ?>
			<a title="Email Estimate with PDF" <?php /*href="#sentEstimatePdfToEmail"*/ ?> href="#email-template-modal"
               data-callback="ClientsLetters.estimate_pdf_letter_modal"
               data-email_template_id="<?php echo $estimate_pdf_letter_template_id ?? 0; ?>" role="button"
               class="btn btn-danger btn-block" type="button" data-toggle="modal">
               <i class="fa fa-mail-forward"></i>
                <?php if($estimate_data->est_status_default == 1): ?>
                    &nbsp;&nbsp;Email Estimate with PDF
                <?php else: ?>
                    &nbsp;&nbsp;Re-send Estimate PDF
                <?php endif; ?>
		   </a>
		<?php //endif; ?>

        <?php $this->load->view('includes/messages/menu_button'); // SMS messages ?>

        <?php if ($client_estimates && $client_estimates->num_rows()) : ?>
			<a href="#new_payment" role="button" class="btn btn-success btn-block" data-toggle="modal" style="margin-top: 5px;">
                Add Payment
            </a>
		<?php endif; ?>
		<?php echo anchor('estimates/edit/' . $estimate_data->estimate_id, '<i class="fa fa-edit"></i>&nbsp;&nbsp;Edit Estimate&nbsp;&nbsp;&nbsp;', 'class="btn btn-danger btn-block" type="button"'); ?>
		<?php if (!empty($invoice_data) && $invoice_data && !empty($invoice_data)) : ?>
			<a title="QA" href="#addInvoiceQA" role="button" class="btn btn btn-primary btn-block" data-toggle="modal"
			   data-backdrop="static" data-keyboard="false"><i class="fa fa-thumbs-up"></i>&nbsp;&nbsp;QA&nbsp;&nbsp;</a>
		<?php endif; ?>
		<?php echo anchor('estimates/', 'Close', 'class="btn btn-info btn-block"'); ?>

            <a href="#copyEstimateForm-modal" role="button" class="btn btn-dark btn-block" data-toggle="modal" style="margin-top: 5px;" data-backdrop="static" data-keyboard="false">
                Copy estimate
            </a>
	</div>
</section>
<script>
	var cl_id = <?php echo isset($client_data->client_id) ? $client_data->client_id : 0;?>;
	
	$(document).ready(function () {
        /*
		$('#sent-pdf-to-email').click(function () {
			if ($(this).attr('disabled') == 'disabled')
				return false;
			//$('#text').val(tinyMCE.editors[0].getContent());
			$('#template_text_' + cl_id).val(tinyMCE.editors[0].getContent());
			var sms = $('#sent_sms').is(':checked');
			$(this).attr('disabled', 'disabled');
			$.post(baseUrl + 'estimates/send_pdf_to_email', $('#send_pdf_to_email').serialize(), function (resp) {
				response = $.parseJSON($.trim(resp));
				alert(response['message']);
				$('#sent-pdf-to-email').removeAttr('disabled');
				if(sms)
				{
					$('#email-template-modal').modal().hide();
					$('#sms-2').modal().show();
				}
				else
					document.location.href = document.location.href;
			});
			return false;
		});
        */

		$('#addContact').click(function () {
			$('#contact_info').removeAttr('style');
			var estimate_id = $(this).data('estimate-id');
			var message = $('#contact_info').val();
			if (!message) {
				$('#contact_info').css('border', '2px solid red');
				return false;
			}
			$.post(baseUrl + 'estimates/ajax_add_contact', {estimate_id: estimate_id, message: message}, function (resp) {
				if (resp.status == 'ok') {
					$('#addEstimateCall').modal('hide');
					$('#contact_info').val('');
					location.href = location.href;
				}
				else {
					alert('Error');
				}
				return false;
			}, 'json');
			return false;
		});
	});
</script>
<!-- /Estimate Information Display Options End-->
