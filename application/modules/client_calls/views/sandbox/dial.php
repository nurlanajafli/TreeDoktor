<Response>
	<Dial callerId="<?php echo $caller; ?>" record="record-from-answer" action="<?php echo base_url('client_calls/recording/') ; ?>">
		<Number><?php echo $phone_number; ?></Number>
	</Dial>
</Response>
