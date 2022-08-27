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
						<?php if($call_note['call_route'] || (!$call_note['call_route'] && (!$user && !$client_data['twilio_worker_id']))) : ?>
							<?php echo element('call_from', $call_note); ?>
						<?php elseif(!$call_note['call_route'] && $user) : ?>
							<?php echo $user->firstname . ' ' . $user->lastname; ?>
						<?php elseif(!$call_note['call_route'] && $client_data['twilio_worker_id']) : ?>
							<?php echo $client_data['firstname'] . ' ' . $client_data['lastname']; ?>
						<?php endif; ?>
					</span>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-sm-4"><strong>Client:</strong></div>
				<div class="col-sm-8">
					<a class="text-ul client-iframe" href="/<?php echo element('client_id', $client_data); ?>"><strong><?php echo element('client_name', $client_data); ?></strong></a>
				</div>
			</div>
			<br>
			<div class="row">
				<div class="col-sm-4"><strong>To:</strong></div>
				<div class="col-sm-8">
					<?php if(!$call_note['call_route'] || ($call_note['call_route'] && (!isset($user) || !$user && !$client_data['twilio_worker_id']))) : ?>
						<?php echo element('call_to', $call_note); ?>
					<?php elseif($call_note['call_route'] && isset($user) && $user) : ?>
						<?php echo $user->firstname . ' ' . $user->lastname; ?>
					<?php elseif($call_note['call_route'] && $client_data['twilio_worker_id']) : ?>
						<?php echo $client_data['firstname'] . ' ' . $client_data['lastname']; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-md-5 text-center">
			<?php if($call_note['call_voice']) : ?>
			<div class="ui360 m-t-3">
				<a href="<?php echo $call_note['call_voice']; ?>.wav" data-title="Play Recording"></a>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
