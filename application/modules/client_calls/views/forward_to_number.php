<Response>
	<Dial<?php if($number != config_item('office_phone')) : ?> timeout="<?php if(isset($forward)) : ?>12<?php elseif(!isset($then) || !$then) : ?>20<?php endif; ?>"<?php endif; ?> record="record-from-answer" action="<?php if(isset($forwarder) && $forwarder) : ?><?php echo base_url('client_calls/forwardToAgent/' . $forwarder); ?><?php elseif(!isset($then) || !$then) : ?><?php echo base_url('client_calls/call_status/'); ?><?php else : ?><?php echo base_url('client_calls/forwardToNumber/' . $then); ?><?php endif; ?>">
		<Number><?php echo $number; ?></Number>
	</Dial>
</Response>