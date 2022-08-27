<ul class="dropdown-menu animated fadeInTop">
	<?php foreach($emailTpls as $k=>$v) : ?>
    <?php if(!isset($v->email_template_title)) continue; ?>
    	<li>
			<a href="#email-template-modal"
               data-callback="ClientsLetters.crews_schedule_letters_modal"
               data-email_template_id="<?php echo $v->email_template_id; ?>"
               data-event_id="0"
               data-sms_id="<?php echo $v->sms;?>"

               data-toggle="modal"
               data-backdrop="static" data-keyboard="false">

                <?php /*
                    class="sendLetter"
				data-email_tpl_id="<?php echo $v->email_template_id; ?>"
				data-sms_check="<?php echo $v->sms;?>">
                */ ?>
			<?php echo $v->email_template_title; ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
