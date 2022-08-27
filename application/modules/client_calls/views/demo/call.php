<Response>
<?php  /*if((is_weekend() || !is_worked_time()) && !special_day()) : ?>
		<?php /*<Gather action="<?php echo base_url('client_calls/emergency_gather'); ?>" method="POST">?>
			<Say><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/tree_doctors_2.mp3'); ?></Say>

		<?php /*</Gather>?>
		<Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" finishOnKey="*" playBeep="true"/>
<?php  else : */ ?>
	<Gather numDigits="3"  timeout="3" action="<?php echo base_url('client_calls/gather'); ?>" method="POST">
	
		<Say>
            Hello and welcome to demo Arbostar. We are glad you called.
            If you know your parties extension, please dial it now. Otherwise just stay on the line and the customer service representative will be with you shortly
        </Say>

	</Gather>

	<Redirect method="POST"><?php echo base_url('client_calls/gather'); ?></Redirect>

<?php  //endif; ?>
</Response>
