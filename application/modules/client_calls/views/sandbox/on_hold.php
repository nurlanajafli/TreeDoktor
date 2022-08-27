<Response>
	<?php for($i = 0; $i < 10; $i++) : ?>
		<?php foreach($file as $key=>$val) : ?>
			<Play><?php echo base_url('uploads/sounds/' . $val); ?></Play>
			<Say>Please wait for the connection with operator</Say>
			<Pause length="1"/>
		<?php endforeach; ?>
	<?php endfor; ?>
</Response>
