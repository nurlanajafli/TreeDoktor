<Response>
	<Dial timeout="10" record="record-from-answer" action="<?php echo base_url('client_calls/extensionToNumber/' . $ext . '/1'); ?>">
		<Number><?php echo $number; ?></Number>
	</Dial>
</Response>