<?php $this->load->view('includes/header'); ?>

<!-- Edit Client Details Modal Loader -->
<?php $this->load->view('clients/client_information_update_modal'); ?>
<!-- End of Edit Customer Details Modal-->
<section class="scrollable p-sides-15">
<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
	<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
	<li><a href="<?php echo base_url('tasks'); ?>">Tasks</a></li>
	<li class="active">Edit - Task <?php echo $task['task_id']; ?></li>
</ul>
<script src="<?php echo base_url('assets/vendors/notebook/js/combodate/combodate.js'); ?>"></script>
<script src="<?php echo base_url('assets/vendors/notebook/js/libs/moment.min.js'); ?>"></script>
<!-- Client profile -->
<?php $this->load->view('clients/client_information_display'); ?>
<!-- /Client profile -->

<?php echo form_open('tasks/update_task'); ?>

<!-- Task Date and Status -->
<section class="col-md-12 panel panel-default p-n">
	<header class="panel-heading">Task Details</header>
	<div class="m-t-md col-md-6">
		<table cellpadding="5" cellspacing="0" border="0" width="50%" class="table table-striped b-t b-light">
			<tr>
				<td>
					<?php echo form_hidden(['client_id' => $task['task_client_id'], 'task_id' => $task['task_id']]); ?>
					Date
				</td>
				<td>
					<?php echo getDateTimeWithDate($task['task_date_created'], 'Y-m-d') ?>

                    <?php if(!element('task_lead_id', $task, false)): ?>
					    <label class="checkbox pull-right"><input type="checkbox" name="new_add" id="new_add" value="1"
		                               onchange='showOrHide("new_add", "new_add_tab");'>Another Address</label>
                    <?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>
					<!-- Task Category -->
					<div class="form-inline">
						<label class="pull-left">Task Category</label>
				</td>
				<td>
					<select name="task_category" class="form-control">
						<?php foreach($task_categories as $key=>$val) : ?>
							<?php $checked = ''; ?>
							<?php if($val['category_id'] == $task['task_category']) : ?>
								<?php $checked = 'selected="selected"'; ?>
							<?php endif; ?>
							<option <?php echo $checked; ?> value="<?php echo $val['category_id']?>"><?php echo $val['category_name']; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>
					<!-- Task Status -->
					<div class="form-inline">
						<label class="pull-left">Task Status</label>
				</td>
				<td>
					<select  name="task_status"  class="form-control">
						<option <?php if($task['task_status'] == 'new') : ?>selected="selected"<?php endif; ?> value="new">New</option>
						<option <?php if($task['task_status'] == 'canceled') : ?>selected="selected"<?php endif; ?> value="canceled">Canceled</option>
						<option <?php if($task['task_status'] == 'done') : ?>selected="selected"<?php endif; ?> value="done">Done</option>
					</select>
				</td>
			</tr>
	<!-- Task Status -->
	</td></tr>
			<tr>
				<td>
					<!-- Task Assigned -->

					
						<label class="pull-left">Assigned User</label>
				</td>
				<td>
					
						<select name="task_assigned_user" id="task_assigned_user" class="form-control" style="display:block;">
							<?php foreach($active_users as $key=>$user) : ?>
								<option <?php if($user->id == $task['task_assigned_user']) : ?>selected="selected"<?php endif; ?> value="<?php echo $user->id;?>"><?php echo $user->firstname;?> <?php echo $user->lastname;?> </option>
							<?php endforeach; ?>
						</select>
					
				</td>
			</tr>
			<tr>
				<td>
					<label class="checkbox pull-left">
						<input type="checkbox" name="scheduled" id="new_task_scheduled" value="1" <?php if($task['task_date']) : ?>checked<?php endif; ?> onchange='showOrHide("new_task_scheduled", "event-time");'>Task At Schedule
					</label>
				</td>
				<td>
				<div class="control-group">
					
					<div id="event-time" class="pull-right w-100" <?php if(!$task['task_date']) :?>style="display:none" <?php endif; ?>>

						<input name="from" class="datepicker form-control text-center" type="text" readonly="" value="<?php echo getDateTimeWithDate($task['task_date'], 'Y-m-d') ?? date('Y-m-d'); ?>"><br>
                        <input name="start_time" type="time" style="width:48%" value="<?php echo $task['task_start'] ?>" class="form-control pull-left" >
                        <input name="end_time" type="time" style="width:48%" value="<?php echo $task['task_end'] ?>" class="form-control pull-right" >
					</div>
					<div class="clear"></div>
				</div>
				</td>		
			</tr>
		</table>
	</div>
	<div class="control-group p-left-5 col-md-5 m-b-md">
		<?php 
		$task['task_address'] = ($task['task_lead_id'] && $task["lead_address"])?$task["lead_address"]:element('task_address', $task);
		$task['task_city'] = ($task['task_lead_id'] && $task["lead_city"])?$task["lead_city"]:element('task_city', $task);
		$task['task_state'] = ($task['task_lead_id'] && $task["lead_state"])?$task["lead_state"]:element('task_state', $task);
  		$task['task_country'] = ($task['task_lead_id'] && $task["lead_country"])?$task["lead_country"]:element('task_country', $task);
  		$task['task_zip'] = ($task['task_lead_id'] && $task["lead_zip"])?$task["lead_zip"]:element('task_zip',$task);
  		$task['task_latitude'] = ($task['task_lead_id'] && $task["latitude"])?$task["latitude"]:element('task_latitude',$task);
  		$task['task_longitude'] = ($task['task_lead_id'] && $task["longitude"])?$task["longitude"]:element('task_longitude',$task);
  		?>
		<br>
		<table id="new_add_tab" style="display:none;">



			<tr>
				<td class="w-200">
					<label class="control-label">Address:</label>
					<input type="text" class="form-control" data-autocompleate="true" data-part-address="address" name="new_address"
					       value="<?php if (isset($task['task_address'])) : echo $task['task_address']; endif; ?>" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
				</td>
			</tr>
			<tr>
				<td class="w-200">
					<label class="control-label">City:</label>
					<input type="text" data-part-address="locality" class="form-control" name="new_city"
					       value="<?php if (isset($task['task_city'])) : echo $task['task_city']; endif; ?>" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
				</td>
			</tr>
			<tr>
				<td class="w-200">
					<label class="control-label">State/Province:</label>
					<input type="text" name="new_state" class="form-control"
					       value="<?php if (isset($task['task_state'])) : echo $task['task_state']; endif; ?>" data-part-address="administrative_area_level_1" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
				</td>
			</tr>
			<tr>
				<td class="w-200">
					<label class="control-label">Zip/Postal:</label>
					<input type="text" name="new_zip" class="form-control"
					       value="<?php if (isset($task['task_zip'])) : echo $task['task_zip']; endif; ?>" data-part-address="postal_code" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
					<input type="hidden" class="new_lat" data-part-address="lat" name="new_lat" value="<?php if (isset($task['task_latitude'])) : echo $task['task_latitude']; endif; ?>" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
					<input type="hidden" class="new_lon" data-part-address="lon" name="new_lon" value="<?php if (isset($task['task_longitude'])) : echo $task['task_longitude']; endif; ?>" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
				</td>
			</tr>
			<tr>
				<td class="w-200">
					<label class="control-label">Country:</label>
					<input type="text" name="new_country" class="form-control" value="<?php if (isset($task['task_country'])) : echo $task['task_country']; endif; ?>" data-part-address="country" <?php if(element('task_lead_id', $task, false)): ?>disabled<?php endif; ?>>
				</td>
			</tr>
		</table>
	</div>
</section>


<!-- Task display -->
<section class="col-md-12 panel panel-default p-n">

	<!-- Client header -->
	<header class="panel-heading">Task Description</header>

	<!-- Data display -->
	<div class="m-10" id="borderless_form">
		<?php  $options = array(
			'name' => 'task_desc',
			'rows' => '5',
			'class' => 'input-block-level',
			'value' => set_value('task_desc', $task['task_desc']));

		echo form_textarea($options); ?>
	</div>

</section>
<!-- /Task display -->


<!-- Task Requirements -->



<!-- Submit Values -->
<section class="col-md-12 m-top-10 m-bottom-20">
	<div class="m-10 pull-right">
		<?php if($task['task_client_id']): ?>
			<?php echo anchor('client/' . $task['task_client_id'], 'Close', 'class="btn btn-info"'); ?>
		<?php else: ?>
			<?php echo anchor('tasks', 'Close', 'class="btn btn-info"'); ?>
		<?php endif; ?>
		<?php echo form_submit('submit', 'Update', "class='btn btn-success'"); ?>
	</div>
</section>
<!-- /Submit Values -->

    <input type="hidden" id="php-variable" value="<?php echo getJSDateFormat()?>" />
<?php echo form_close() ?>

<?php $this->load->view('includes/footer'); ?>
<script>
    $('.datepicker').datepicker({format: $('#php-variable').val()});
</script>
</section>
