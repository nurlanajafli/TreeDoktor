<!-- Modal -->
<div id="changeWorkorderStatus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content panel panel-default p-n">
			

			<header class="panel-heading">Edit Workorder Status</header>
			
			<div id="status_div" class="p-10">
				<form id="change_workorder_status_form" name="change_workorder_status_form" method="post">
					<input type="hidden" name="workorder_id" value="<?php echo $workorder_data->id; ?>">
					<input type="hidden" name="pre_workorder_status" value="<?php echo $workorder_data->wo_status; ?>">

					
					<div id="change_status_error" class="alert alert-danger alert-block" style="display: none;"></div>
					

					Workorder Status:<br>
					<select name="workorder_status" id="workorder_status" onchange="checkStatus(this.value);" class="form-control">
						<?php foreach($statuses as $key=>$status) : ?>
							<option <?php if($workorder_data->wo_status_id == $status['wo_status_id']) : ?>selected="selected"<?php endif; ?>value="<?php echo $status['wo_status_id']; ?>"><?php echo $status['wo_status_name']; ?></option>
						<?php endforeach; ?>
					</select>
					
					<br>

					<!--Finished status div, show if the Finished status is being selected -->
					<div id="confirm_div">
						<span>
						Time Left the office/previous job:<br>
							<?php 
							$options = [
								'name' => 'in_time_left_office',
								'id' => 'in_time_left_office',
								'class' => 'form-control',
								'type' => 'time',
								'Placeholder' => 'Time left the office/previous job.',
								'value' => set_value('in_time_left_office')
							];
							echo form_input($options);
							?>

							Time Arrived at the job site:<br>
							<?php 
							$options = [
								'name' => 'in_time_arrived_site',
								'id' => 'in_time_arrived_site',
								'class' => 'form-control',
								'type' => 'time',
								'Placeholder' => 'Time Arrived at the job site.',
								'value' => set_value('in_time_arrived_site')
							];
							echo form_input($options);
							?>

							Time left the job site:<br>
							<?php 
							$options = [
								'name' => 'in_time_left_site',
								'id' => 'in_time_left_site',
								'class' => 'form-control',
								'type' => 'time',
								'Placeholder' => 'Time left the job site.',
								'value' => set_value('in_time_left_site')
							];
							echo form_input($options);
							?>

							Time Arrived at the office:<br>
							<?php 
							$options = [
								'name' => 'in_time_arrived_office',
								'id' => 'in_time_arrived_office',
								'class' => 'form-control',
								'type' => 'time',
								'Placeholder' => 'Time Arrived at the office.',
								'value' => set_value('in_time_arrived_office')
							];
							echo form_input($options);
							?>

							<br>
						Was the job completed in full: &nbsp;&nbsp;
							<?php echo form_radio('in_job_completed', 'Yes', FALSE); ?>&nbsp;Yes &nbsp;&nbsp;
							<?php echo form_radio('in_job_completed', 'No', TRUE); ?>&nbsp;No
						<br>
						Was the payment received: 
						&nbsp;&nbsp; &nbsp;&nbsp;
							<?php echo form_radio('in_payment_received', 'Yes', FALSE); ?>&nbsp;Yes &nbsp;&nbsp;
							<?php echo form_radio('in_payment_received', 'No', TRUE); ?>&nbsp;No
						
						<br style="margin-top:5px;">
						What is there left to do:<br style="margin-top: 5px;">
							<?php $options = array(
								'name' => 'in_left_todo',
								'id' => 'in_left_todo',
								'rows' => '1',
								'class' => 'form-control',
								'Placeholder' => 'What is there left to do',
								'value' => set_value('in_left_todo'));

							echo form_textarea($options);
							?>

							Any damage to the property:<br>
							<?php $options = array(
								'name' => 'in_any_damage',
								'id' => 'in_any_damage',
								'rows' => '1',
								'class' => 'form-control',
								'Placeholder' => 'Any damage to the property',
								'value' => set_value('in_any_damage'));

							echo form_textarea($options);
							?>

							Equipment malfunction:<br>
							<?php $options = array(
								'name' => 'in_eq_malfuntion',
								'id' => 'in_eq_malfuntion',
								'rows' => '1',
								'class' => 'form-control',
								'Placeholder' => 'Equipment malfunction',
								'value' => set_value('in_eq_malfuntion'));

							echo form_textarea($options);
							?>

							Notes on completion:<br>
							<?php $options = array(
								'name' => 'in_note_completion',
								'id' => 'in_note_completion',
								'rows' => '1',
								'class' => 'form-control',
								'Placeholder' => 'Notes on completion',
								'value' => set_value('in_note_completion'));

							echo form_textarea($options);
							?>
						 
						</span>
					</div>
					<!--Finished status div ends -->

					<input type="submit" id="save-workorder-status" name="save" value="Save" class="btn btn-success pull-right m-right-10"/>

				</form>
				<br><br>
			</div>


			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		</div>
	</div>

</div>
<!-- Modal -->
