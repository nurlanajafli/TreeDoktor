<div class="sticker" data-crew_id="<?php echo isset($event['task_assigned_user']) ? $event['task_assigned_user'] : '';?>">
	<p>
		<b class="category-name"><?php echo isset($event['category_name']) ? $event['category_name'] : 'Day Off'; ?></b>
		<br>
		<span class="client">
			<?php
				if(isset($event['client_id'])) 
					echo '#' . $event['client_id'] . ', ' . $event['client_name'] . ' - ' . $event['task_address'] . ', ' . $event['task_city'] . ', ' . $event['task_state'];
				elseif(isset($event['task_address']) && $event['task_address'] == config_item('office_address'))
					echo '# Office - ' . $event['task_address'] . ', ' . $event['task_city'] . ', ' . $event['task_state'];
				else
					echo ''; ?>
		</span>
        <br>
        <span class="lead-no">
            <?php
            if(isset($event['lead_id']))
                echo $event['lead_id'] . '-L';
            else
                echo ''; ?>
        </span>
	</p>
	<p class="assigned-to">
		<?php echo isset($event['ass_firstname']) ? $event['ass_firstname'] . ' ' . $event['ass_lastname'] : ''; ?>
	</p>
	<i class="details">
		<?php if(isset($event['task_desc'])) : ?>
			<?php echo $event['task_desc']; ?>
		<?php endif;?>
	</i>
</div>
