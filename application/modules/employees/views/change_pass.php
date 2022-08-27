<?php $this->load->view('includes/header'); ?>
<section class="scrollable p-sides-15">
	<ul class="breadcrumb no-border no-radius b-b b-light pull-in">
		<li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
		<li><a href="<?php echo base_url('employees'); ?>">Employees</a></li>
		<li class="active"><?php if (isset($employee_row->emp_name)) : ?>Employee - <?php echo $employee_row->emp_name; ?><?php else : ?>New Employee<?php endif; ?></li>
	</ul>
	<section class="col-md-12 panel panel-default p-n">
		<header class="panel-heading"><?php echo ucwords($employee_row->emp_name) ?>: Change Password</header>

		<?php
		$attri = array(
			'name' => 'employees_form',
			'id' => 'employees_form');

		echo form_open_multipart('employees/employee_change_pass/' . $employee_row->employee_id, $attri);
		?>
		<div class="col-md-12">
			<div class="col-md-6">
				<div class="p-sides-30 p-top-10">
					<table class="table">
						<tr>
							<?php  if (form_error('txtPassword')) {
								$emp_pass_error = "inputError";
								$emp_pass_holder = "control-group error";
							} else {
								$emp_pass_error = "inputSuccess";
								$emp_pass_holder = "control-group success";
							}; ?>
							<td><label class="control-label">Password:</label></td>
							<td class="p-left-30">
								<?php
								$emp_pass_attributes = array(
									'type' => 'password',
									'class' => 'form-control',
									'name' => 'txtPassword',
									'id' => 'txtPassword',
									'value' => set_value('txtPassword', ''),
									'maxlength' => '20'
								);
								?>
								<span class="<?php echo $emp_pass_holder ?>">
	                <?php echo form_input($emp_pass_attributes) ?>
				</span>
								<?php echo form_error('txtPassword', '<div class="$emp_pass_holder">', '</div>'); ?>
							</td>
						</tr>
						<tr>
							<td><label class="control-label">Confirm Password:</label></td>
							<td class="p-left-30">
								<?php
								$emp_conf_pass_attributes = array(
									'type' => 'password',
									'name' => 'txtConfPassword',
									'class' => 'form-control',
									'id' => 'txtConfPassword',
									'value' => set_value('txtConfPassword', ''),
									'maxlength' => '20'
								);
								?>
								<?php echo form_input($emp_conf_pass_attributes) ?>
							</td>
						</tr>

					</table>
					<div class="pull-right p-sides-30 p-bottom-10">
						<?php
						$value = 'submit';
						$attr = array(
							'class' => 'btn btn-info',
							'id' => 'submit',
							'name' => 'submit',
							'value' => $value
						);
						echo form_submit($attr); ?>

						<?php
						$js = 'class="btn" onClick="javascript: window.location.href=\'' . base_url() . 'employees\';"';

						echo form_button('mybutton', 'Cancel', $js);
						?>
					</div>
					<?php echo form_close() ?>
				</div>
				<!-- /span12 -->
			</div>

			<?php echo form_close() ?>
			<section>
				<section>
					<?php $this->load->view('includes/footer'); ?>
