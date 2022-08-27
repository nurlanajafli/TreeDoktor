<?php $i = 0; $lastCrew = NULL; ?>
<?php if(isset($ajax_update) && $ajax_update) echo $map1['js']; ?>
<?php foreach ($workorders as $status => $works) : ?>
	<div class="tab-content">
		<div class="tab-pane panel <?php if (!$i) : ?> active<?php endif; ?>" id="tab<?php echo ++$i; ?>">
			<div data-toggle="buttons">
				<?php if (!empty($works)) : ?>
					<?php $count = 0; ?>
					<?php //foreach($crews_types as $crew) : ?>
						<?php foreach ($works as $key => $workorder) : ?>
							<?php //if($workorder['crew_id'] == $crew->crew_id) : ?>
								<?php /*if($count && $crew->crew_id != $lastCrew) : ?><div>&nbsp;</div><?php endif;*/ ?>
								<?php //var_dump($workorder['equipment']);die; ?>
								<label class="btn btn-dark no-shadow m-b-xs m-l-xs label-wo"
								       data-label-wo-id="<?php echo $workorder['id']; ?>"
								       data-label-wo-no="<?php echo $workorder['workorder_no']; ?>"
								       style="width:98%;overflow: hidden;text-overflow: ellipsis;  position: relative;"
								       data-toggle="popover" data-html="true" data-placement="bottom"
								       data-address="<?php echo $workorder['client_address']; ?>"
								       data-estimator="<?php echo $workorder['estimator_id']; ?>"
								       <?php foreach ($workorder['equipment'] as $key => $value) : ?>
								       	<?php $opts = $value['equipment_attach_option'] ? json_decode($value['equipment_attach_option']) : []; ?>
								       	<?php if(isset($opts) && $opts && is_array($opts)) : ?>
								       		<?php foreach ($opts as $k => $v) : ?>
								       			data-equipment-<?php echo $value['equipment_attach_id']; ?>="<?php echo $v; ?>"
								       		<?php endforeach; ?>
								       	<?php else : ?>
								       		data-equipment-<?php echo $value['equipment_attach_id']; ?>=""
								       	<?php endif; ?>
								   	   <?php endforeach; ?>
								       data-price="<?php echo money($workorder['sum_without_tax']); ?>"
								       data-time="<?php echo $workorder['total_time']; ?>"
										<?php foreach ($workorder['crewsTypes'] as $type => $k) {
											echo ' data-' . $type . '="' . $k . '"';
										} ?>
								       >
									<input type="radio" name="options" id="option1" autocomplete="off">
									<?php $workorder['total_time'] = $workorder['total_time'] ? $workorder['total_time'] : 0; ?>
                                    <?php echo $workorder['crews'] ? $workorder['crews'] . ' - ' : ''; ?>

									<?php echo round($workorder['total_time'], 2) . ' hrs. - ' . money($workorder['sum_without_tax']) . ' - ' . $workorder['client_name'] . ', ' . $workorder['client_address']; ?>

                                    <?php if(isset($workorder['change_date'])) : ?>
										<?php $now = date_create(date('Y-m-d'));?>
										<?php $wo = date_create(date('Y-m-d', $workorder['change_date']));?>
										<?php $interval = date_diff($now, $wo); ?>
										<span class="badge badge-xs bg-danger count" style="position:absolute;top:0;right: 0px;"><?php echo $interval->format('%a'); ?></span>
									<?php endif; ?>
								</label>
								<?php $count++; //$lastCrew = $crew->crew_id; ?>
								<?php //unset($works[$key]); ?>
							<?php //endif; ?>
						<?php endforeach; ?>
					<?php //endforeach; ?>
				<?php else : ?>
					<div class="alert alert-danger" style="font-size: 13px;">
						<i class="fa fa-ban-circle"></i>No record found.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endforeach; ?>
