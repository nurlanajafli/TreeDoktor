<!DOCTYPE html>

<head>
	<meta charset="utf-8">
	<title>Tree Doctors</title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/<?php echo $this->config->item('company_dir');?>/css/style.css?v=<?php echo config_item('style.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/bootstrap.css">

</head>
<body>
<!-- 
<span style="color:red;"><?php echo validation_errors(); ?></span> -->
<div style="margin:0px 10px;"> <?php echo $this->session->flashdata('user_message'); ?></div>
<?php
$attri = array(
	'name' => 'employees_form',
	'id' => 'employees_form');

echo form_open_multipart('employees/login', $attri);
?>

<div id="horizon">

	<div id="login" class="rounded filled_white shadow">
		<div id="logo" class="wrapper"></div>
		<input type="text" name="imagedata" id="imagedata"/>
		<?php  if (form_error('txtUsername')) {
			$emp_pass_error = "inputError";
			$emp_pass_holder = "control-group error";
		} else {
			$emp_pass_error = "inputSuccess";
			$emp_pass_holder = "control-group success";
		}; ?>
		<p>
			<label for="txtUsername">Employee ID:</label>
			<?php
			$emp_username_attributes = array(
				'name' => 'txtUsername',
				'id' => 'txtUsername',
				'value' => set_value('txtUsername', ''),
				'maxlength' => '100'
			);
			?>
			<span class="<?php echo $emp_pass_holder ?>">
			<?php echo form_input($emp_username_attributes) ?>
		</span>
			<?php echo form_error('txtUsername', '<div class="$emp_pass_holder">', '</div>'); ?>
		</p>

		<p>
			<?php  if (form_error('txtPassword')) {
				$emp_pass_error = "inputError";
				$emp_pass_holder = "control-group error";
			} else {
				$emp_pass_error = "inputSuccess";
				$emp_pass_holder = "control-group success";
			}; ?>
			<label for="txtPassword">Password:</label>
			<?php
			$emp_conf_pass_attributes = array(
				'type' => 'password',
				'name' => 'txtPassword',
				'id' => 'txtPassword',
				'value' => set_value('txtPassword', ''),
				'maxlength' => '20'
			);
			?>
			<span class="<?php echo $emp_pass_holder ?>">
	                <?php echo form_input($emp_conf_pass_attributes) ?>
				</span>
			<?php echo form_error('txtPassword', '<div class="$emp_pass_holder">', '</div>'); ?>
		</p>

		<div class="pull-right p-sides-30 p-bottom-10">
			<?php
			$value = 'Login';
			$attr = array(
				'class' => 'btn btn-info',
				'id' => 'btnSubmit',
				'name' => 'submit',
				'value' => $value
			);
			echo form_button($attr, "Login"); ?>

		</div>
		<?php echo form_close() ?>
	</div>
	<!-- /span12 -->
</div>

<div style="width: 800px;">
	<div id="example" style="height: 300px; visibility: hidden;"></div>
	<div id="gallery" style="height: 300px; visibility: hidden;"></div>
</div>
<script> var _BASE_PATH = '<?php echo base_url(); ?>'; </script>
<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
<script src="<?php echo base_url(); ?>assets/js/photobooth.js"></script>
<script>
	$("document").ready(function () {
		$("#btnSubmit").attr("disabled", true);
		container = document.getElementById("example");
		gallery = document.getElementById("gallery");
		myPhotobooth = new Photobooth(container);
		//$(".photobooth").css("float","left");
		$("#btnSubmit").click(function () {
			$("#txtUsername").parent().addClass("control-group success");
			$("#txtPassword").parent().addClass("control-group success");
			if ($("#txtUsername").val() == "") {
				$("#txtUsername").parent().removeClass();
				$("#txtUsername").parent().addClass("control-group error");
				$("#txtUsername").focus();
				return false;
			}

			if ($("#txtPassword").val() == "") {
				$("#txtPassword").parent().removeClass();
				$("#txtPassword").parent().addClass("control-group error");
				$("#txtPassword").focus();
				return false;
			}

			$(".trigger").click();
		});
		myPhotobooth.onImage = function (dataUrl) {
			if (dataUrl != "") {
				//$("#gallery").append('<img src="' + dataUrl + '" >');
				uploadImage(dataUrl);
			}
		};
	});

	function uploadImage(file) {
		$.ajax({
			url: "<?php echo base_url() ?>employees/recognize",
			type: "POST",
			data: {image: file},
			success: function (rd) {
				if (rd == 0) {
					//	myPhotobooth.destroy();
				} else {
					$("#imagedata").val(rd);
				}
			}
		});
	}
</script>
<!-- Le javascript
	================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>

</body>
</html>
