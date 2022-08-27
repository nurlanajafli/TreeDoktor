<Response>
	<?php $forwarder = isset($forwarder) ? $forwarder : NULL ?>
	<Dial record="record-from-answer" action="<?php echo isset($forward) ? base_url('client_calls/resultForwardAgent/' . $forward . '/1/' . $forwarder) : base_url('client_calls/recording') ; ?>" timeout="<?php if(isset($forward)) : ?>20<?php else : ?>20<?php endif; ?>">
		<Client statusCallbackEvent='initiated ringing answered completed'><?php echo $contact_uri; ?></Client>
	</Dial>
</Response>
