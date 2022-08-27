<div class="row pull-out event_buttons" style="display: flex;justify-content: space-between;">
	<?php if(!empty($started_events) && isset($started_events['ev_end_work']) && $started_events['ev_end_work']): ?>

		<div class="bg-info" style="flex-grow: 1;">
			<div class="padder-v p-top-5 p-bottom-5">
			<span class="m-b-xs h3 block text-white">
				<button class="btn btn-info"><i class="glyphicon glyphicon-ok h3"></i></button>
			</span>
			<small><strong>Event completed</strong></small>
			</div>
		</div>	

	<?php else: ?>
		<div id="btn-get-geo" style="flex-grow: 1;" data-ev_event_id="<?php echo $event['id']; ?>" data-ev_team_id="<?php echo $event['team_id']; ?>" data-ev_estimate_id="<?php echo $estimate_data->estimate_id; ?>">
			<div class="padder-v p-top-5 p-bottom-5">
			<span class="m-b-xs h3 block text-white"><button class="btn btn-info" href="#" type="button"><i class="fa fa-truck h3"></i></button></span>
			<small><strong>Ride</strong></small>
			</div>
		</div>
		<?php if(empty($started_events) || (isset($started_events['ev_start_work']) && !$started_events['ev_start_work'])): ?>
		<div class="bg-success start-work-btn" href="#" data-toggle="modal" data-target="#safety-meeting-form-modal"  style="flex-grow: 1;">
			<div class="padder-v p-top-5 p-bottom-5">
			<span class="m-b-xs h3 block text-white"><button class="btn btn-success" href="#" type="button" <?php /*data-toggle="modal" data-target="#start-work-modal" */ ?> ><i class="fa fa-play h3"></i></button></span>
				<small><strong>Start Work</strong></small>
			</div>
		</div>
		<?php else: ?>
			<div class="bg-warning stop-work-btn"  href="#" data-toggle="modal" data-target="#report-form-modal" style="flex-grow: 1;">
				<div class="padder-v p-top-5 p-bottom-5">
				<span class="m-b-xs h3 block text-white">
					<button class="btn btn-warning"><i class="fa fa-stop h3"></i></button>
				</span>
				<small><strong>Stop Work</strong></small>
				</div>
			</div>	
		<?php endif; ?>

	<?php endif; ?>
</div>
