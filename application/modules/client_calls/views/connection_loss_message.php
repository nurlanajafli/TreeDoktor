<Response>
	<Gather action="<?php echo base_url('client_calls/gather'); ?>" method="POST">
		<Say>
			Sorry, your phone call got disconnected, please stay on the line for the next agent
		</Say>
		<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_1.mp3'); ?></Play>
	</Gather>
	<Redirect method="POST">https://td.onlineoffice.io/client_calls/gather</Redirect>
</Response>
