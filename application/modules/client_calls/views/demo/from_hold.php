<Response>
	<Dial record="record-from-answer" action="<?php echo base_url('client_calls/recording') ; ?>">
		<Client statusCallbackEvent='initiated ringing answered completed'><?php echo $contact_uri; ?></Client>
	</Dial>
</Response>
