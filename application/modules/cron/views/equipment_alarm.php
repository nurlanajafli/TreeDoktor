<?php if($resultLeft && !empty($resultLeft)) : ?>
	<div>
		<div>
			<strong>Trucks active on the <?php echo $date; ?>:</strong>
		</div>
		<ul>
			<?php foreach ($resultLeft as $key => $value) : ?>
				<?php $planOnSite = $actualOnSite = 0; ?>
				<li style="margin-bottom: 20px;"> 
					<?php echo $value['name']; ?>
					 - <a href="<?php echo base_url('/cron/equipmentMap/' . $date . '/' . $value['code']); ?>">Route</a>
					<div>
					<?php if(isset($value['items']) && !empty($value['items'])) : ?>
						<u>Team:</u>
						<ol>
							<?php foreach ($value['items'] as $k => $v) : ?>
								<?php if($v['type'] == 'user') : ?>
									<li>
										<?php echo $v['name']; ?> 
										<?php if($v['item_id'] == $v['team_leader_user_id']) : ?>*<?php endif; ?>
										
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
					<?php if(isset($value['events']) && !empty($value['events'])) : ?>
						<u>Jobs:</u>
						<ol>
							<?php foreach ($value['events'] as $k => $v) : ?>
								<li><?php echo $v['lead_address']; ?>, <?php echo $v['lead_zip']; ?> - 
									Planed On Site: <?php echo sprintf('%02d:%02d', (int) round(floatval($v['planned_service_time']), 2), fmod(round(floatval($v['planned_service_time']), 2), 1) * 60); ?>, 
									<?php $planOnSite += round(floatval($v['planned_service_time']), 2); ?>

									<?php if(isset($value['actual_worked_time'][$k])) : ?>

										Actual On Site: <?php echo sprintf('%02d:%02d', (int) round(floatval($value['actual_worked_time'][$k] / 3600), 2), fmod(round(floatval($value['actual_worked_time'][$k] / 3600), 2), 1) * 60); ?>

										<?php $actualOnSite += round(floatval($value['actual_worked_time'][$k] / 3600), 2); ?>

									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
					
					<u>Summary:</u>
					<ul>
						<?php if(isset($value['route_travel_time']) && $value['route_travel_time']) : ?>
							<li>
								Planed Travel Time: 
								<?php echo sprintf('%02d:%02d', (int) $value['planned_travel_time'], fmod($value['planned_travel_time'], 1) * 60); ?>, 
								Actual Travel Time:
								<?php echo sprintf('%02d:%02d', (int) round($value['route_travel_time'] / 3600, 2), fmod(round($value['route_travel_time'] / 3600, 2), 1) * 60); ?>
							</li>
						<?php endif; ?>

						<?php if((isset($value['kms']) && $value['kms']) || (isset($value['route_kms']) && $value['route_kms'])) : ?> 
							<li>  
								<?php if(isset($value['route_kms']) && $value['route_kms']) : ?>
									Planed Distance: <?php echo round($value['route_kms'] / 1000, 2); ?>kms, 
								<?php endif; ?>

								<?php if(isset($value['kms']) && $value['kms']) : ?>
									Actual Distance: <?php echo round($value['kms']/* / 1000*/, 2); ?>kms.
								<?php endif; ?>
							</li>
						<?php endif; ?>

						<?php if($planOnSite || $actualOnSite) : ?>
							<li>
								Planed On Sites: <?php echo sprintf('%02d:%02d', (int) $planOnSite, fmod($planOnSite, 1) * 60); ?>, 
								Actual On Sites: <?php echo sprintf('%02d:%02d', (int) $actualOnSite, fmod($actualOnSite, 1) * 60); ?>
							</li>
						<?php endif; ?>
					</ul>

				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<strong>
		<a href="<?php echo base_url($link); ?>">Routes PDF</a>
	</strong>
<?php endif; ?>
