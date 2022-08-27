	<?php if(isset($calls) && !empty($calls)) : ?>
		<?php foreach($calls as $key=>$val) : ?>
			<?php if($val['call_route']) : ?>
				<?php $phone = 'From: ' . $val['call_from']; ?>
			<?php else : ?>
				<?php $phone = 'To: ' . $val['call_to']; ?>
			<?php endif; ?>
			<li class="p-5 list-group-item">
				<div class="row">
				
					<div class="col-md-1 call-route h3">
						<i class="fa fa-sign-<?php if($val['call_route']) : ?>in<?php else : ?>out<?php endif; ?> text-<?php if($val['call_route']) : ?><?php if($val['call_duration']) : ?>success<?php else : ?>danger<?php endif; ?><?php else : ?>info<?php endif; ?>" ></i>
					</div>
					<div class="col-md-6 call-info">
										<span class="call-agent-name">
											<?php if($val['firstname'])  : ?>
												<?php echo strtoupper($val['firstname'][0]) . '. ' . $val['lastname'];?>
											<?php else : ?>
												<?php echo $val['call_to']; ?>
											<?php endif; ?>
										</span><br>
						<strong>
							<?php echo isset($val['client_name']) ? anchor(base_url($val['client_id']), $val['client_name'], ['class' => 'text-ul client-iframe']) : '';?><br>
							<?php echo $phone; ?>
						</strong>
					</div>
					<div class="col-md-4 text-center">
						<small class="text-muted">
							<?php if(date('Y-m-d', strtotime($val['call_date'])) == date('Y-m-d')) : ?>
								Today<br>
							<?php else : ?>
								<?php echo date('M d', strtotime($val['call_date'])); ?><br>
							<?php endif; ?>
							<?php echo date('H:i', strtotime($val['call_date'])); ?><br>
							<?php echo intval($val['call_duration'] / 60)?>m.
							<?php echo intval($val['call_duration'] % 60)?>s.
						</small>
						<?php if($val['call_voice']) : ?>
						<div class="ui360">
							<a href="<?php echo $val['call_voice']; ?>.wav" data-title="Play Recording"></a>
						</div>
						<?php endif; ?>
					</div>
					<a href="#" title="Call To Client" class="btn btn-success call-button h3 clear outgoing-call" data-phone="<?php echo $phone; ?>">
						<br><i class="fa fa-phone"></i>
					</a>
				</div>
			</li>
		<?php endforeach; ?>
		<script type="text/javascript" src="<?php echo base_url('assets/js/soundmanager/script/360player.js'); ?>"></script>
	<?php else :?>
		Not found
	<?php endif; ?>