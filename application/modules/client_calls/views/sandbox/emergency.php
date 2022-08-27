<Response>
<?php if(!$emergency) : ?>

	<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_5.mp3'); ?></Play>

	<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" <?php /*maxLength="20"*/ ?> finishOnKey="*" playBeep="true"/>
<?php else : ?>
    <Say>
        Thank you. Please wait.
    </Say>
	
	<Dial timeout="20" action="<?php echo base_url('client_calls/emergency_gather_result/' . $key); ?>">
		<Number><?php echo $emergency->extention_number; ?></Number>
	</Dial>
<?php endif;?>
</Response>

<?php /*
<Response>
    <Dial timeout="5"><?php echo $emergency->extention_number;?></Dial>
    <Redirect><?php echo base_url('client_calls/emergency_gather_result/1'); ?></Redirect>
</Response>
*/?>
