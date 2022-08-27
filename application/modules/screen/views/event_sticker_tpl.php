<div data-event_wo_id="<?php echo $event['wo_id'];?>" data-event_wo_color="<?php echo $event['team_color'];?>">
	<?php echo $event['lead_address']; ?>
	<?php $event['count_members'] = $event['count_members'] ? $event['count_members'] : 1; ?>
	<div><?php echo round($event["total_service_time"], 1); ?>mhr. (<?php echo round($event['total_service_time'] / $event['count_members'], 2); ?>hr.)</div>
	<?php if($event['event_note']) : ?>
		<br><i><?php echo $event['event_note']; ?></i>
	<?php endif;?>
</div>
