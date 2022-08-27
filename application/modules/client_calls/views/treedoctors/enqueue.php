<Response>
 	<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_3.mp3'); ?></Play>
	<Enqueue workflowSid="<?php echo $workflowSid; ?>" waitUrl="<?php echo base_url('client_calls/play'); ?>">
		<Task><?php echo json_encode($taskAttributes); ?></Task>
	</Enqueue>
</Response>

