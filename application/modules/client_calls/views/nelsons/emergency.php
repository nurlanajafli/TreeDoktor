<Response>
<?php if(!$emergency) : ?>

	<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" <?php /*maxLength="20"*/ ?> finishOnKey="*" playBeep="true"/>
<?php else : ?>
	
	<Dial timeout="20" action="<?php echo base_url('client_calls/emergency_gather_result/' . $key); ?>">
		<Number><?php echo $emergency->extention_number; ?></Number>
	</Dial>
<?php endif;?>
</Response>
