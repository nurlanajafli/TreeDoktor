<Response>
	<Dial record="record-from-answer" action="<?php echo base_url('client_calls/extensionToWorker/' . $ext . '/1/'); ?>" timeout="10">
		<Client statusCallbackEvent='initiated ringing answered completed'><?php echo $uri; ?></Client>
	</Dial>
</Response>
