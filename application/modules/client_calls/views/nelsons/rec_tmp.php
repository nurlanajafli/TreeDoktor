<Response>
	<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/busy_or_no_answer.mp3'); ?></Play>
	<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" <?php /*maxLength="20"*/ ?> finishOnKey="*" playBeep="true"/>
</Response>
