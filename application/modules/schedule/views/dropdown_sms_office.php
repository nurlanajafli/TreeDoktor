<ul class="dropdown-menu animated fadeInTop">
	<?php foreach($smsTpls as $k=>$v) : ?>
		<li>
            <a href="#appointment-sms-modal"
               data-id=""  data-sms-id="<?php echo $v->sms_id?>"  data-toggle="modal" data-backdrop="static" data-keyboard="false">
                <?php echo $v->sms_name; ?>
            </a>
		</li>
	<?php endforeach; ?>
</ul>