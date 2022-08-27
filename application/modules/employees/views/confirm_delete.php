<?php $this->load->view('includes/header'); ?>
	<!--
<span style="color:red;"><?php echo validation_errors(); ?></span> -->

<?php
$attri = array(
	'name' => 'employees_form',
	'id' => 'employees_form');

echo form_open_multipart('employees/employee_delete/' . $employee_row->employee_id, $attri);
?>
	<input type="hidden" name="confirm" value="1"/>

	<div class="row">
		<div class="grid_12 filled_white rounded shadow overflow">

			<!-- Client header -->

			<div class="module-header">
				<div class="module-title"><?php echo ucwords($employee_row->emp_name) ?>: Delete Employee Details</div>
			</div>
			<div class="alert alert-dismissable alert-danger">
				<h3>Are you sure you want to delete <?php echo ucwords($employee_row->emp_name) ?>'s details?</h3>
				<button type="submit" name="btnsubmit" class="btn">Submit</button>
				&nbsp;
				<button type="button" name="btncancel" class="btn"
				        onclick="javascript: window.location.href='<?php echo base_url(); ?>employees';">Cancel
				</button>
			</div>
			<?php echo form_close() ?>
		</div>
		<!-- /span12 -->
	</div>

<?php echo form_close() ?>

<?php $this->load->view('includes/footer'); ?>