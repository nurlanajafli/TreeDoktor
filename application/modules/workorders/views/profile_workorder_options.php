<!-- Profile Workorder Options -->
<section class="estimate_profile_data" >

	<!-- Estimate data -->
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="table table-striped b-a bg-white">
		<tr>
			<td class="p-5">Workorder:</td>
			<td class="p-5"><strong><?php echo $workorder_data->workorder_no; ?></strong></td>
		</tr>

		<tr>
			<td class="p-5">Date:</td>
<!--			<td class="p-5"><strong>--><?php //echo $workorder_data->date_created; ?><!--</strong></td>-->
			<td class="p-5"><strong><?php echo getDateTimeWithDate($workorder_data->date_created, 'Y-m-d')  ?></strong></td>
		</tr>
		<?php if(!isset($schedule_event)) : ?>
			<tr>
				<td class="p-5">Priority:</td>
				<td class="p-5">
					<strong>
						<a data-toggle="collapse" href="#changePriority" title="Edit task">
							<?php if ($workorder_data->wo_priority == "") {
								echo "Undefined";
							} else {
								echo $workorder_data->wo_priority;
							} ?></a>
					</strong>
				</td>
			</tr>
			<tr id="changePriority" class="collapse">
				<td colspan="2">
					<div>
						<form name="update_workorder_priority_form" action="">

							<input type="hidden" id="for_client_id" value="<?php echo $client_data->client_id; ?>">
							<input type="hidden" id="workorder_id" value="<?php echo $workorder_data->id; ?>">
							<input type="hidden" id="workorder_number" value="<?php echo $workorder_data->workorder_no; ?>">
							<input type="hidden" id="old_workorder_priority"
								   value="<?php echo $workorder_data->wo_priority; ?>">

							<select id="new_workorder_priority" class="form-control col-md-3" style="width: 70%;">
								<option value="Regular" <?php if ($workorder_data->wo_priority == 'Regular') {
									echo "selected";
								} ?> >Regular
								</option>
								<option value="Priority" <?php if ($workorder_data->wo_priority == 'Priority') {
									echo "selected";
								} ?> >Priority
								</option>
								<option value="Emergency" <?php if ($workorder_data->wo_priority == 'Emergency') {
									echo "selected";
								} ?>>Emergency
								</option>

							</select>
							<button type="submit" name="save" id="workorder_priority"
									class="btn col-md-3 btn-info pull-right">Save
							</button>
						</form>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td class="p-5">Status:</td>
			<td class="p-5"><strong><?php echo $workorder_data->wo_status_name; ?></strong></td>
		</tr>
		<tr>
			<td class="p-5">Estimator:</td>
			<td class="p-5"><strong><?php echo $user_data->firstname ?? null; ?>
					&nbsp;<?php echo $user_data->lastname ?? null; ?></strong></td>
		</tr>
	</table>
	<div class="hidden-sm hidden-xs">
		<!-- Links -->
			<a title="Edit Status" href="#changeWorkorderStatus" role="button" class="btn btn-success btn-block"
				data-toggle="modal"><i class="fa fa-pencil"></i>&nbsp;&nbsp;Update Status&nbsp;&nbsp;</a>
		<?php if(!isset($schedule_event)) : ?>
			<?php echo anchor($workorder_data->workorder_no . '/pdf', '<i class="fa fa-file"></i>&nbsp;&nbsp;Download PDF', 'class="btn btn-dark btn-block" type="button"'); ?>
			<?php echo anchor('workorders/partial_invoice_pdf/' . $workorder_data->id, '<i class="fa fa-file"></i>&nbsp;&nbsp;Partial Invoice PDF', 'class="btn btn-dark btn-block" type="button"'); ?>
		<?php else : ?>
			<?php echo anchor($workorder_data->workorder_no . '/pdf/'. $schedule_event['id'], '<i class="fa fa-file"></i>&nbsp;&nbsp;Download PDF', 'class="btn btn-dark btn-block" type="button"'); ?>
			<?php echo anchor('workorders/partial_invoice_pdf/' . $workorder_data->id, '<i class="fa fa-file"></i>&nbsp;&nbsp;Partial Invoice PDF', 'class="btn btn-dark btn-block" type="button"'); ?>
		<?php endif; ?>
        <a title="Sent Partial Invoice to Email" href="#email-template-modal" data-callback="ClientsLetters.partial_invoice_modal"  role="button" class="btn btn-primary btn-block" type="button"
           data-toggle="modal" data-email_template_id="<?php echo isset($partial_invoice_letter_template_id)?$partial_invoice_letter_template_id:0; ?>">
            <i class="fa fa-inbox"></i>
            &nbsp;Send Partial Invoice&nbsp;&nbsp;
        </a>
		<?php //if ($client_estimates && $client_estimates->num_rows()) : ?>
			<a href="#new_payment" role="button" class="btn btn-success btn-block" data-toggle="modal"
			   style="margin-top: 10px">Add Payment</a>
        <?php // endif; ?>
			<?php echo anchor('workorders/', 'Close', 'class="btn btn-info btn-block"'); ?>
	</div>
</section>
<!--/Profile Workorder Options -->
