<?php if($service_data->service_time || $service_data->service_travel_time) : ?>
    (
<?php endif; ?>
<?php if($service_data->service_time || $service_data->service_travel_time || $service_data->service_disposal_time) : ?>
    <?php $service_data->count_members = isset($service_data->count_members) ? $service_data->count_members : count($service_data->crew); ?>
    <?php $service_data->count_members = $service_data->count_members ? $service_data->count_members : 1; ?>
    <strong>Estimated Time (with travel): <?php echo round(($service_data->service_time + $service_data->service_disposal_time + $service_data->service_travel_time) * $service_data->count_members, 2); ?>mhr.
        <?php if(isset($schedule_event['count_members']) && $schedule_event['count_members']) : ?>
            (<?php echo round((($service_data->service_time + $service_data->service_disposal_time + $service_data->service_travel_time) * $service_data->count_members) / $service_data->count_members, 2); ?> hrs.)
        <?php endif; ?>
    </strong>
<?php endif; ?>
<?php if($service_data->service_time || $service_data->service_travel_time) : ?>
    )
<?php endif; ?>
