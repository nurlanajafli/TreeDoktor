<title><?php echo $title; ?></title>
<!-- 
<span style="color:red;"><?php echo validation_errors(); ?></span> -->
<div style="margin:0px 10px;"> <?php echo $this->session->flashdata('user_message'); ?></div>
<section class="vbox flex">
	<section class="m-t-lg wrapper-md animated fadeInUp bg-primary">
		<div class="container aside-xxl">
			<a class="navbar-brand block" style="line-height: initial; padding-top: 10px;">Tree Doctors<br><span style="font-size: 13px;">Employee Login</span></a>
			<section class="panel panel-default m-t-lg bg-white">
				<header class="panel-heading text-center">
					<strong>Sign in</strong>
				</header>
				<?php
				$attri = array(
					'name' => 'employees_form',
					'class' => 'panel-body wrapper-lg',
					'id' => 'employeesform');

				echo form_open_multipart('employee_login', $attri);
				?>

				<?php  if (form_error('txtUsername')) {
					$emp_pass_error = "inputError";
					$emp_pass_holder = "control-group error";
				} else {
					$emp_pass_error = "inputSuccess";
					$emp_pass_holder = "control-group success";
				}; ?>
				<div class="form-group">
					<label class="control-label">Username</label>
					<?php
					$emp_username_attributes = array(
						'name' => 'txtUsername',
						'id' => 'txtUsername',
						'value' => set_value('txtUsername', ''),
						'maxlength' => '100',
						'class' => 'form-control input-lg'
					);
					?>
					<?php echo form_input($emp_username_attributes) ?>
					<?php echo form_error('txtUsername', '<div class="$emp_pass_holder">', '</div>'); ?>
				</div>
				<div class="form-group">
					<label class="control-label">Password</label>
					<?php  if (form_error('txtPassword')) {
						$emp_pass_error = "inputError";
						$emp_pass_holder = "control-group error";
					} else {
						$emp_pass_error = "inputSuccess";
						$emp_pass_holder = "control-group success";
					}; ?>
					<?php
					$emp_conf_pass_attributes = array(
						'type' => 'password',
						'name' => 'txtPassword',
						'id' => 'txtPassword',
						'class' => 'form-control input-lg',
						'value' => set_value('txtPassword', ''),
						'maxlength' => '20',
					);
					?>
					<?php echo form_input($emp_conf_pass_attributes) ?>
					<?php echo form_error('txtPassword', '<div class="$emp_pass_holder">', '</div>'); ?>
				</div>
				<?php
				$value = 'Login';
				$attr = array(
					'class' => 'btn btn-success',
					'id' => 'btnlogin',
					'name' => 'submit1',
					'value' => $value
				);
				echo form_button($attr, "Login"); ?>
				<?php echo anchor('login/', '<small>Office Login</small>', 'class="pull-right m-t-sm"'); ?>
				<?php echo form_close() ?>
			</section>
		</div>
	</section>
	<footer class="footer animated fadeInUp bg-primary dker text-center">
		<p>
			<small>©&nbsp;Tree Doctors&nbsp;<?php echo date('Y'); ?>.</small>
		</p>
	</footer>
</section>

<script> var _BASE_PATH = '<?php echo base_url(); ?>'; </script>
<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
<script>
	$("document").ready(function () {
		$("#btnlogin").on("click", function () {
			dologin();
		});
	});
</script>
<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
