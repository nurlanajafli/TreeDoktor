<html class="app js no-touch no-android chrome no-firefox no-iemobile no-ie no-ie10 no-ie11 no-ios">
<head>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/animate.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
</head>
<body>
<div id="container"></div>
<video id="webcam" height="300" width="300" style="visibility: hidden;"></video>
<div id="gallery" style="visibility: hidden;"></div>
<canvas id="canvas" style="visibility: hidden;"></canvas>

<script> var _BASE_PATH = '<?php echo base_url(); ?>'; </script>
<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
<script>
	var streaming = false;
	var emp_estimator = '<?php echo $emp_estimator;?>';
	/* to photo need uncomment
	var web_cam_started = false;
	canvas = document.querySelector('#canvas'),
		photo = document.querySelector('#gallery'),
		height = '300';
	var videoElement = document.getElementById('webcam'); */
	var _BASE_URL = '<?php echo base_url(); ?>';
	$("document").ready(function () {
		var emp_login = '<?php echo $emp_login ?>';
		
		if (emp_login == 0) {
			call_login();
		} else {
			show_employee_dashboard();
		}
	});
</script>
<script src="<?php echo base_url(); ?>assets/js/emp_dashboard.js"></script>
</body>
</html>
