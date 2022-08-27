<Response>
	<?php $files = bucketScanDir('uploads/sounds'); ?>
	<Gather action="<?php echo base_url('client_calls/redirectToVoice'); ?>" method="POST" numDigits="1">
		<?php foreach($files as $key=>$val) : ?>
			<Play><?php echo base_url('uploads/sounds/' . $val); ?></Play>
			<?php if($key - 1 != !empty($files)) : ?>
				<Say voice="woman">All our operators are still busy. If you like, please press any key to leave a voicemail and we will get back to you shortly.</Say>
			<?php else : ?>
				<Say voice="woman">All our operators are still busy. Please leave a voicemail and we will get back to you shortly.</Say>
			<?php endif; ?>
			<Pause length="1"/>
		<?php endforeach; ?>
	</Gather>
	
	<Redirect method="POST"><?php echo base_url('client_calls/redirectToVoice'); ?></Redirect>
</Response>
