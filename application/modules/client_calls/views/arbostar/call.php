<Response>
	<Gather numDigits="3"  timeout="3" action="<?php echo base_url('client_calls/gather'); ?>" method="POST">
	
		<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/ArboStar_Greeting.mp3'); ?></Play>

	</Gather>

	<Redirect method="POST"><?php echo base_url('client_calls/gather'); ?></Redirect>
</Response>
