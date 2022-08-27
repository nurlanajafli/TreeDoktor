<Response>
	<Gather action="<?php echo base_url('client_calls/gather'); ?>" method="POST">
		<Say>
			Sorry, your phone call got disconnected, please stay on the line for the next agent
		</Say>
		<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/welcome.mp3'); ?></Play>
	</Gather>
	<Redirect method="POST"><?php echo base_url('/client_calls/gather'); ?></Redirect>
</Response>
