<Response>
	<Say>
		Please leave a voicemail after a tone.
	</Say>
	<Record action="<?php echo base_url('client_calls/recording/' . $extention->extention_user_id); ?>" method="POST" finishOnKey="*" playBeep="true"/>
</Response>