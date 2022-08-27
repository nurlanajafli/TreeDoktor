<?php if(isset($schedule_event) && $schedule_event->schedule_event_service): ?>
<?php foreach ($schedule_event->schedule_event_service as $service): ?>
    <li><strong><?php echo $service->service->service_name; ?></strong><br><?php echo $service->service_description; ?></li>
<?php endforeach; ?>
<?php endif; ?>
