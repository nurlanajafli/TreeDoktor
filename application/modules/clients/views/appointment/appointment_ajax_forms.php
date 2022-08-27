<form id="select-schedule-date" data-type="ajax" data-global="false" data-url="<?php echo site_url('clients/get_schedule_intervals'); ?>" data-callback="Clients.set_free_schedule_times">
	<input type="hidden" name="task_author_id" value="<?php echo (isset($scheduled_user_id))?$scheduled_user_id: ''; ?>">
	<input type="hidden" name="task_date" value="<?php echo (isset($scheduled_date) && $scheduled_date && \DateTime::createFromFormat(getDateFormat(), $scheduled_date))?(\DateTime::createFromFormat(getDateFormat(), $scheduled_date)->format(getDateFormat())) : ''; ?>">

	<input type="hidden" name="appointment_address" value="">
	<input type="hidden" name="appointment_lat" value="">
	<input type="hidden" name="appointment_lon" value="">

</form>

<form id="get-appointment-modal" data-type="ajax" data-url="<?php echo site_url('clients/get_appointment_modal'); ?>" data-callback="Clients.init_appointment_modal">
	<input type="hidden" name="appointment_address" value="">
	<input type="hidden" name="appointment_lat" value="">
	<input type="hidden" name="appointment_lon" value="">
	<input type="hidden" name="schedule_lead_priority" value="">
	<input type="hidden" name="clients_ids" value="">
	<input type="hidden" name="id_client" value="">
	<input type="hidden" name="lead_preliminary_estimate" value="">
    <input type="hidden" name="lead_id" value="">
</form>
