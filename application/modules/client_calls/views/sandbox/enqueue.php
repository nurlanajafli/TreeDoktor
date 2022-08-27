<Response>
    <Say>
        Thank you. Please wait.
    </Say>
	<Enqueue workflowSid="<?php echo $workflowSid; ?>" waitUrl="<?php echo base_url('client_calls/play'); ?>">
		<Task><?php echo json_encode($taskAttributes); ?></Task>
	</Enqueue>
</Response>

