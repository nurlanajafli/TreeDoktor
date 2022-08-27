<Response>
	<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/ArboStar_LongHold.mp3'); ?></Play>
	<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" <?php /*maxLength="20"*/ ?> finishOnKey="*" playBeep="true"/>
</Response>
