<Response>
	<?php $file = bucketScanDir('uploads/sounds'); ?>
	<Gather action="<?php echo base_url('client_calls/redirectToVoice'); ?>" method="POST" numDigits="1">
		<?php foreach($file as $key=>$val) : ?>
			<Play><?php echo base_url('uploads/sounds/' . $val); ?></Play>
			<Say>All our operators are still busy. If you like, please press any key to leave a voicemail and we will get back to you shortly.</Say>
			<Pause length="1"/>
		<?php endforeach; ?>
	</Gather>
	
	<Redirect method="POST"><?php echo base_url('client_calls/redirectToVoice'); ?></Redirect>
</Response>
<?php /*http://www.jetcityorange.com/silent-ringtone/silence-10sec.mp3*/ ?>
