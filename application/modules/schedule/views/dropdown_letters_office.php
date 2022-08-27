<ul class="dropdown-menu animated fadeInTop">
	<?php foreach($emailTpls as $k=>$v) : ?>
		<li>
            <a href="#email-template-modal"
               data-task_id=""  data-email_template_id="<?php echo $v->email_template_id?>" data-callback="ClientsLetters.schedule_appointment_email_modal" data-toggle="modal" data-backdrop="static" data-keyboard="false">
                <?php echo $v->email_template_title; ?>
            </a>
		</li>
	<?php endforeach; ?>
</ul>