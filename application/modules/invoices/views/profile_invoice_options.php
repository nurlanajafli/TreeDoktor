<!-- Profile Invoice Options -->
<section class="estimate_profile_data">

	<!-- Estimate data -->
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped b-a bg-white">
		<tr>
			<td class="p-5">Invoice:</td>
			<td class="p-5"><strong><?php echo $invoice_data->invoice_no; ?></strong></td>
		</tr>
        <?php if(!empty($invoice_data->qb_invoice_no)) : ?>
        <tr>
            <td class="p-5" style="min-width: 90px">QB Invoice:</td>
            <td class="p-5"><strong><?php echo $invoice_data->qb_invoice_no; ?></strong></td>
        </tr>
        <?php endif; ?>
		<tr>
			<td class="p-5" style="vertical-align: middle;">Date:</td>
<!--			<td class="p-5"><strong>--><?php //echo $invoice_data->date_created; ?><!--</strong></td>-->
			<td class="p-5">
                <?php if(isAdmin()) : ?>
                <div class="row">
                    <div class="input-group col-xs-11">
                        <input type="text" class="form-control created-date-input" value="<?php echo getDateTimeWithDate($invoice_data->date_created, 'Y-m-d'); ?>" disabled>
                        <input type="hidden" class="form-control created-prev-date-input" value="<?php echo getDateTimeWithDate($invoice_data->date_created, 'Y-m-d'); ?>">
                        <div class="input-group-btn">
                            <button class="btn btn-outline-secondary edit-created-date" type="button"><i class="fa fa-pencil"></i></button>
                            <button class="btn btn-outline-secondary confirm-created-date" type="button" style="display:none;"><i class="fa fa-check"></i></button>
                        </div>
                    </div>
                </div>
                <?php else : ?>
                <strong class="inv-created-date"><?php echo getDateTimeWithDate($invoice_data->date_created, 'Y-m-d'); ?></strong>
                <?php endif; ?>
            </td>
		</tr>
		
		<tr>
			<td class="p-5" style="vertical-align: middle;">Due:</td>
			<td class="p-5">
				<div class="row">
					<div class="input-group col-xs-11">
						<input type="text" class="form-control due-date-input" value="<?php echo getDateTimeWithDate($invoice_data->overdue_date, 'Y-m-d'); ?>" disabled>
						<input type="hidden" class="form-control prev-due" value="<?php echo getDateTimeWithDate($invoice_data->overdue_date, 'Y-m-d'); ?>">
						<div class="input-group-btn">
							<button class="btn btn-outline-secondary edit-due-date" type="button"><i class="fa fa-pencil"></i></button>
							<button class="btn btn-outline-secondary confirm-due-date" type="button" style="display:none;"><i class="fa fa-check"></i></button>
						</div>
					</div>
				</div>
			</td>
		</tr>

		<tr>
			<td class="p-5">Status:</td>
			<td class="p-5"><strong><?php echo $invoice_data->invoice_status_name; ?></strong></td>
		</tr>
		<?php if (!empty($payment_files)) { ?>
			<tr>
				<td class="p-5">Payment Files:</td>
				<td class="p-5">
					<a class="btn btn-success btn-mini pull-left" type="button" data-toggle="collapse"
					   href="#payment_files" style="margin-top: -1px;" href="#">
						<i class="icon-envelope icon-white"></i>
					</a>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<div id="payment_files" class="collapse">
						<div class="list-group">
							<?php foreach ($payment_files as $pp => $kk) { ?>
								<a href="<?php echo base_url() . PAYMENT_FILES_PATH . $kk["payment_file"]; ?>"
								   class="list-group-item" target="_new"
								   style="width: 150px; float: left; margin: 3px;">
									<?php echo substr($kk["payment_file"], 0, -4) ?>
								</a>
								<?php if (isAdmin()) { ?>
									&nbsp;<img src="<?php echo base_url(); ?>assets/img/close.png"
									           style="float: left; margin-top: 6px;" id="<?php echo $kk["id"] ?>"
									           class="del_payment_file"/>
									<div style="clear: both;"></div>
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</td>
			</tr>
		<?php } ?>
	</table>
	<div class="hidden-sm hidden-xs">
		<!-- Links -->
		<a title="Edit Status" href="#changeInvoiceStatus" role="button" class="btn btn-success btn-block"
		   data-toggle="modal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Update Status&nbsp;&nbsp;</a>
		<?php echo anchor($invoice_data->invoice_no . '/pdf', '<i class="fa fa-file"></i>&nbsp;&nbsp;Download PDF', 'class="btn btn-dark btn-block" type="button"'); ?>
		
		<a href="#new_payment" role="button" class="btn btn-success btn-block" data-toggle="modal">
            Add Payment
        </a>
		
		<?php //if (isset($client_contact['cc_email']) && $client_contact['cc_email'] && filter_var($client_contact['cc_email'], FILTER_VALIDATE_EMAIL)): ?>
        <?php if(isset($paid_invoice_template_id) && !empty($paid_invoice_template_id)): ?>
            <a title="Send Paid Invoice" id="sendThanks" href="#email-template-modal" data-callback="ClientsLetters.invoice_email_modal"
               data-email_template_id="<?php echo $paid_invoice_template_id; ?>" role="button" class="btn btn-danger btn-block"
               type="button" data-toggle="modal">
                <i class="fa fa-inbox"></i>
                &nbsp;&nbsp;  Send Paid Invoice&nbsp;&nbsp;
            </a>
        <?php else: $sent = (int)$invoice_data->default == 1 ? 'E-mail' : 'Re-send'; ?>
			<a title="Sent PDF to Email" id="sendLink" href="#email-template-modal" data-callback="ClientsLetters.invoice_email_modal"
               data-email_template_id="<?php echo $invoice_letter_template_id ?? 0; ?>" role="button" class="btn btn-danger btn-block"
               type="button" data-toggle="modal">
               <i class="fa fa-inbox"></i>&nbsp;&nbsp;<?php echo $sent; ?> Invoice&nbsp;&nbsp;
		   </a>
		<?php endif; ?>

        <?php $this->load->view('includes/messages/menu_button'); // SMS messages ?>

		<!--<a title="QA" href="#addInvoiceQA" role="button" class="btn btn btn-primary btn-block" data-toggle="modal"
           style="margin-top: 5px;"
		   data-backdrop="static" data-keyboard="false"><i class="fa fa-thumbs-up"></i>&nbsp;&nbsp;QA&nbsp;&nbsp;</a>-->
		<?php echo anchor('invoices/', '<i class="fa fa-times"></i>&nbsp;&nbsp;Close&nbsp;&nbsp;', 'class="btn btn-info btn-block"'); ?>
	</div>
</section>
<!--/Profile Invoice Options -->
<script>			
	$("document").ready(function () {
		
		$('.due-date-input').datepicker({format:'<?php echo getJSDateFormat(); ?>'});
		$('.created-date-input').datepicker({format:'<?php echo getJSDateFormat(); ?>'});

		$(".del_payment_file").each(function () {
			$(this).click(function () {
				var id = $(this).attr("id");
				var cofn = window.confirm("Do you really want to delete this file?");
				if (cofn == true) {
					$.ajax({
						url: '<?php echo base_url(); ?>invoices/del_payment_file',
						type: "POST",
						data: "id=" + id,
						dataType: "text",
						success: function (s) {
							if (s == "_DELETED") {
								$("#" + id).prev().remove();
								$("#" + id).parent().append('<span style="color: green; font-weight: bold;">File deleted!!!</span>').fadeIn(1000).delay(1000).fadeOut(1000);
								$("#" + id).remove();
								location.reload();
							} else if (s == "_NOT_DELETED") {
								$("#" + id).prev().css("border", "1px solid red");
								$("#" + id).parent().append('<span style="color: red; font-weight: bold;">File deleted!!!</span>').fadeIn(1000).delay(1000).fadeOut(1000);
							} else if (s == "_NO_ID") {
								$("#" + id).prev().css("border", "1px solid red");
								$("#" + id).parent().append('<span style="color: red; font-weight: bold;">Please select the file you want to delete!!!</span>').fadeIn(1000).delay(1000).fadeOut(1000);
							} else if (s == "_NOT_ADMIN") {
								$("#" + id).prev().css("border", "1px solid red");
								$("#" + id).parent().append('<span style="color: red; font-weight: bold;">Unauthorized Access!!!</span>').fadeIn(1000).delay(1000).fadeOut(1000);
							}
						},
						error: function (e) {
							$("#" + id).prev().css("border", "1px solid red");
							$("#" + id).parent().append('<span style="color: red; font-weight: bold;">Error in deleting file: ' + e.responseText + '</span>').fadeIn(1000).delay(1000).fadeOut(1000);
						}
					});
				} else {
					return false;
				}
			});
		});
		
		$('.edit-due-date').click(function(e){
			e.preventDefault();
			$('.due-date-input').removeAttr('disabled');
			$(this).hide();
			$('.confirm-due-date').show();
		});

        $('.edit-created-date').click(function(e){
            e.preventDefault();
            $('.created-date-input').removeAttr('disabled');
            $(this).hide();
            $('.confirm-created-date').show();
        });
	
		$('.confirm-due-date, .confirm-created-date').click(function(e){
			e.preventDefault();
			var new_due = $.trim($('.due-date-input').val());
			var old_due = $.trim($('.prev-due').val());
			var newDate = $.trim($('.created-date-input').val());
			var oldDate = $.trim($('.created-prev-date-input').val());

			if(new_due != old_due || newDate != oldDate) {
                if(newDate && moment(newDate, MOMENT_DATE_FORMAT) > moment(new_due, MOMENT_DATE_FORMAT)) {
                    new_due = newDate;
                    $('.due-date-input').val(newDate);
                }
				$.post(baseUrl + 'invoices/save_due_date', {id: $('#invoice_id').val(), overdue_date:new_due, date_created:newDate}, function (resp) {
						if(resp.status == true){
							successMessage(resp.message);
							$('.due-date-input').attr('disabled', 'disabled');
							$('.confirm-due-date').hide();
							$('.edit-due-date').show();
							$('.prev-due').val(new_due);
                            $('.created-date-input').attr('disabled', 'disabled');
                            $('.confirm-created-date').hide();
                            $('.edit-created-date').show();
                            $('.created-prev-date-input').val(new_due);
						} else {
							errorMessage(resp.message);
						}				
				}, 'json');
			} else {
				$('.due-date-input').attr('disabled', 'disabled');
				$('.confirm-due-date').hide();
				$('.edit-due-date').show();
				$('.due-date-input').val(new_due);
			}
		});
	});
</script>
