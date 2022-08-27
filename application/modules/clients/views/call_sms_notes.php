<?php foreach($client_notes as $k=>$call_note) : ?>
<div class="media m-t-sm">
	<div class="pull-left m-l">
		<span class="thumb-md">
			<?php if (isset($call_note['picture']) && is_file(PICTURE_PATH . $call_note['picture'])) : ?>
				<img src="<?php echo base_url(PICTURE_PATH) . $call_note['picture']; ?>" class="img-circle">
			<?php else : ?>
				<img src="<?php echo base_url("assets/pictures/avatar_default.jpg"); ?>" class="img-circle">
			<?php endif; ?>
		</span>
	</div>
	<div class="h6 media-body p-10 client-note">
		<div class="m-b-sm">
			<div class="p-10">
			<div class="row">
				<div class="col-md-1 call-route h3 m-t-3">
					<i class="fa fa-sign-<?php if($call_note['call_route']) : ?>in<?php else : ?>out<?php endif; ?> text-<?php if($call_note['call_route']) : ?><?php if($call_note['call_duration']) : ?>success<?php else : ?>danger<?php endif; ?><?php else : ?>info<?php endif; ?>"></i>
				</div>
				<div class="col-md-6 call-info">
					<div class="row">
						<div class="col-sm-4"><strong>From:</strong></div>
						<div class="col-sm-8">
							<span class="call-agent-name">
								<?php if($call_note['call_route'] || (!$call_note['call_route'] && !$call_note['twilio_worker_id'])) : ?>
									<?php echo element('call_from', $call_note); ?>
								<?php elseif(!$call_note['call_route'] && $call_note['twilio_worker_id']) : ?>
									<?php echo $call_note['firstname'] . ' ' . $call_note['lastname']; ?>
								<?php endif; ?>
							</span>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-4"><strong>Client:</strong></div>
						<div class="col-sm-8">
							<a class="text-ul client-iframe" href="<?php echo element('client_id', $client_info); ?>"><strong><?php echo element('client_name', $client_info); ?></strong></a>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-sm-4"><strong>To:</strong></div>
						<div class="col-sm-8">
							<?php //echo '<pre>'; var_dump($client_info); die; ?>
							<?php if(!$call_note['call_route'] || ($call_note['call_route'] && !$call_note['twilio_worker_id'])) : ?>
								<?php echo element('call_to', $call_note); ?>
							<?php elseif($call_note['call_route'] && $call_note['twilio_worker_id']) : ?>
								<?php echo $call_note['firstname'] . ' ' . $call_note['lastname']; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="col-md-5 text-center">
					<?php if($call_note['call_voice']) : ?>
					<div class="ui360 m-t-3">
						<a href="<?php echo $call_note['call_voice']; ?>.mp3" data-title="Play Recording"></a>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="note-author border-top filled_dark_grey">

		Created on:&nbsp;<?php echo $call_note['call_date']; ?>
			
			
	</div>
</div>
<?php endforeach;?>
<script>
	$(document).ready(function () {
		soundManager.reboot();
	});
</script>
