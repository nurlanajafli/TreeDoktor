<Response>
    <?php  if(is_weekend() || !is_worked_time()) : ?>
        <Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/not_business_hours.mp3'); ?></Play>
        <Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" finishOnKey="*" playBeep="true"/>
    <?php  else :  ?>
        <Gather numDigits="3"  timeout="5" action="<?php echo base_url('client_calls/gather'); ?>" method="POST">

            <Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/welcome.mp3'); ?></Play>

        </Gather>

        <Redirect method="POST"><?php echo base_url('client_calls/gather'); ?></Redirect>

    <?php  endif; ?>
</Response>
