<Response>
	<Gather numDigits="3"  timeout="3" action="<?php echo base_url('client_calls/gather'); ?>" method="POST">
		<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_4.mp3'); ?></Play>
	</Gather>

	<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" finishOnKey="*" playBeep="true"/>
</Response>
