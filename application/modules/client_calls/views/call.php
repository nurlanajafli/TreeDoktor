<Response>
<?php  if(is_weekend() || !is_worked_time()) : ?>
		<?php /*<Gather action="<?php echo base_url('client_calls/emergency_gather'); ?>" method="POST">*/?>
			<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_2.mp3'); ?></Play>

		<?php /*</Gather>*/?>
		<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" finishOnKey="*" playBeep="true"/>
<?php  else :  ?>
	<Gather numDigits="3"  timeout="3" action="<?php echo base_url('client_calls/gather'); ?>" method="POST">
	
		<Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_1.mp3'); ?></Play>

	</Gather>

	<Redirect method="POST"><?php echo base_url('client_calls/gather'); ?></Redirect>

<?php  endif; ?>
</Response>
