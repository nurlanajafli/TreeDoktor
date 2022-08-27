<Response>
    <?php  if(is_weekend() || !is_worked_time()) : ?>
        <Play><?php echo base_url('assets/' . $this->config->item('company_dir') .  '/audio/not_business_hours.mp3'); ?></Play>
        <Record action="<?php echo base_url('client_calls/recording'); ?>" method="POST" finishOnKey="*" playBeep="true"/>
    <?php  else :  ?>
        <Dial>
            <Number><?php echo $extention->extention_number; ?></Number>
        </Dial>
    <?php endif; ?>
</Response>
