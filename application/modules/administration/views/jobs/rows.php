<?php
foreach ($current_jobs as $key => $job) : ?>
	<tr data-id="<?php echo $job->job_id; ?>">
        <input type="hidden" value="<?php echo $job->job_id; ?>" id="job_<?php echo $job->job_id; ?>">
		<td class="text-center">
			<?php echo isset($start) ? $start + ($key + 1) : ($key + 1);  ?>
		</td>
		<td>
			<?php echo $job->job_driver; ?>
		</td>
		<td class="text-center job-payload">
            <textarea class="job-payload-hidden hidden"><?= $job->job_payload; ?></textarea>
			<?php echo substr ($job->job_payload, 0, 50); ?>...
		</td>
		<td class="text-center job-attempts">
            <input class="job-attempts-hidden hidden" type="text" value="<?=$job->job_attempts?>">
			<?php echo $job->job_attempts; ?>
		</td>
		<td class="text-center job-is-completed">
            <input class="job-is-completed-hidden hidden" type="text" value="<?=$job->job_is_completed?>">
			<?php echo $job->job_is_completed; ?>
		</td>
		<td class="text-center job-available-at">
			<?php echo date('Y-m-d H:i:s', $job->job_available_at); ?>
		</td>
		<td class="text-center job-reserved-at">
            <input class="job-reserved-at-hidden hidden" type="text" value="<?=$job->job_reserved_at?>">
            <?php echo date('Y-m-d H:i:s', $job->job_reserved_at); ?>
		</td>
		<td class="text-center">
            <?php echo $job->job_created_at; ?>
		</td>
		<td class="text-center job-output">
            <?php if($job->job_output) : ?>
                <span class="badge" data-toggle="tooltip" data-placement="left" title="" data-html="true" data-original-title="<?php echo strip_tags($job->job_output); ?>">
                    <i class="fa fa-info"></i>
                </span>
            <?php else : ?>
                —
            <?php endif; ?>
        </td>
		<td class="text-center job-worker-pid">
            <?php echo $job->job_worker_pid; ?>
		</td>
		<td class="text-center">
			<a class="job-actions action-edit" title="Edit job">
                <i class="fa fa-edit"></i>
            </a>
            <a class="job-actions action-remove" title="Remove job">
                <i class="fa fa-times"></i>
            </a>
            <?php if (!$job->job_is_completed): ?>
                <a class="job-actions action-execute" title="Run job">
                    <i class="fa fa-play"></i>
                </a>
            <?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>
