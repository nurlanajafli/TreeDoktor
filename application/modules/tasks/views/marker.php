<div id="<?php echo $task_id; ?>" class="marker taskMarker" data-task="1">
<strong><a href='<?php echo $marker_link; ?>' target='_blank'><?php echo $name ?></a></strong><br/>
			<br>Created by:&nbsp;<strong><?php echo $task_name_creator; ?></strong>
			<br>Assigned to:&nbsp;<strong><?php echo $task_assigned; ?></strong><br>Date:&nbsp;<?php echo $task_date ?><br>
			<?php if(isset($task_schedule_date) && $task_schedule_date) : ?>
<!--				<br>Schedule Date:&nbsp;--><?php //echo substr($task_schedule_start, 0, -3);?><!-- - --><?php //echo substr($task_schedule_end, 0, -3); ?><!-- on --><?php //echo $task_schedule_date; ?><!--<br>-->
				<br>Schedule Date:&nbsp;<?php echo $task_schedule_start;?> - <?php echo $task_schedule_end; ?> on <?php echo $task_schedule_date; ?><br>
			<?php endif;?>
			<abbr title='Phone'>Phone:&nbsp;</abbr><?php echo $phone; ?><br>Category:&nbsp;<?php echo $category_name; ?><br>
				<form action='#'>
					<span class='task_status'>Status:&nbsp; 
					<select name='task_status_change' class='task_status_change m-t-xs m-b-xs'>
						<option value='new' <?php if($status == 'new') : ?>selected='selected'<?php endif; ?>>
							New
						</option>
						<option value='canceled' <?php if($status == 'canceled') : ?>selected='selected'<?php endif; ?>>
							Canceled
						</option>
						<option value='done' <?php if($status == 'done') : ?>selected='selected'<?php endif; ?>>
							Done
						</option>
					</select>
					</span>
					<textarea placeholder='This field is required...' name='new_status_desc' class='new_status_desc form-control m-t-sm m-b-sm' style='display:none;'></textarea>
					<button class='pull-right btn btn-success submit' style='display:none; padding: 6px 12px;'>
						<span class='btntext'>Save</span>
						<img src='<?php echo base_url("assets/img/ajax-loader.gif"); ?>' style='display: none;width: 32px;' class='preloader'>
					</button>
					<button class='pull-right btn submit' style='display:none; padding: 6px 12px; margin-right:10px;'>Close</button>					
				</form>
			<span>Address:&nbsp;<?php echo $address; ?><br><?php echo ucfirst($task_body); ?></span><br>
</div>
