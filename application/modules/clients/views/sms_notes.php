<?php $lastDate = date('Y-m-d'); ?>
<?php foreach($client_notes as $k=>$call_note) : ?>
	<?php if($lastDate != date('Y-m-d', strtotime($call_note['sms_date']))) : ?>
		<div class="chat-date p-top-10"><?php echo date('D, d M Y', strtotime($call_note['sms_date'])); ?></div>
		<?php $lastDate = date('Y-m-d', strtotime($call_note['sms_date'])); ?>
    <?php endif; ?>
	<div class="message-row" id="sms-<?php echo $call_note['sms_id']?>">
		<?php if ($call_note['sms_incoming']) : ?>
		  <div class="message-block">
			<div class="message from <?php if ($call_note['sms_support']) : ?> support<?php endif; ?>"><?php echo $call_note['sms_body']; ?></div>
		  </div>
		  <div class="message-time from"><?php echo date('H:i', strtotime($call_note['sms_date'])); ?></div>
		<?php else : ?>
		  <div class="message-time to">
			<?php echo date('H:i', strtotime($call_note['sms_date'])); ?>
			
		  </div>
		  <div class="message-block">
			<div class="message to"><?php echo $call_note['sms_body']; ?></div>
		  </div>
		<?php endif; ?>
	</div>
<?php endforeach;?>
