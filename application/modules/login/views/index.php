<!DOCTYPE html>
<html class="app js no-touch no-android chrome no-firefox no-iemobile no-ie no-ie10 no-ie11 no-ios">
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/animate.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
    <?php echo config_item('gtm_head_code') ? config_item('gtm_head_code') : ""; ?>
</head>
<body>
<?php echo config_item('gtm_body_code') ? config_item('gtm_body_code') : ""; ?>
<div style="margin:0px 10px;"> <?php echo $this->session->flashdata('user_message'); ?></div>
<section class="vbox flex">
	<section class="m-t-lg wrapper-md animated fadeInUp bg-info dk">
		<div class="container aside-xxl">
			<a class="navbar-brand block" style="line-height: initial; padding-top: 10px;"><?php echo $this->config->item('company_name_short'); ?><br><span style="font-size: 13px;">Office Login</span></a>
			<section class="panel panel-default bg-white m-t-lg">
				<header class="panel-heading text-center">
					<strong>Sign in</strong>
				</header>
				<?php
				$form_attr = array('class' => 'panel-body wrapper-lg', 'id' => 'login_form');
				echo form_open_multipart('login/authenticate', $form_attr);
				?>
				<div class="form-group">
					<label class="control-label">Username</label>
					<?php
					$data = array(
						'name' => 'log_email',
						'id' => 'log_email',
						'class' => 'form-control input-lg',
						'value' => '',
						'maxlength' => '100');
					echo form_input($data);
					?>
				</div>

				<div class="form-group">
					<label class="control-label">Password</label>
					<?php
					$data = array(
						'name' => 'log_password',
						'id' => 'log_password',
						'class' => 'form-control input-lg',
						'value' => '',
						'maxlength' => '50');
					echo form_password($data); ?>
				</div>

				<?php $data = array(
					'name' => 'submit',
					'id' => 'submit',
					'value' => 'Login',
					'class' => 'btn btn-success');
				echo form_submit($data);
				//echo anchor('employee/', '<small>Employee Login</small>', 'class="pull-right m-t-sm"');
				echo form_close();
				?>
			</section>
		</div>
	</section>
	<footer class="footer animated fadeInUp bg-info dker text-center">
		<p>
			<small>©&nbsp;<?php echo $this->config->item('company_name_short'); ?>&nbsp;<?php echo date('Y'); ?>.</small>
		</p>
	</footer>
</section>

<!-- Le javascript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/bootstrap.js"></script>
<script>
	
	// This is the "Offline page" service worker

// Add this below content to your HTML page, or add the js file to your page at the very top to register service worker

// Check compatibility for the browser we're running this in
    /*if ("serviceWorker" in navigator) {
        if (navigator.serviceWorker.controller) {
            console.log("[PWA Builder] active service worker found, no need to register");
        } else {
            // Register the service worker
            navigator.serviceWorker
                .register("/pwabuilder-sw.js?v.1.0.3", {
                    scope: "./"
                })
                .then(function (reg) {
                    console.log("[PWA Builder] Service worker has been registered for scope: " + reg.scope);
                });
        }
    }*/

	
	function setCookie(name, value, props) {
		props = props || {}
		var exp = props.expires
		if (typeof exp == "number" && exp) {
			var d = new Date()
			d.setTime(d.getTime() + exp*1000)
			exp = props.expires = d
		}
		if(exp && exp.toUTCString) { props.expires = exp.toUTCString() }

		value = encodeURIComponent(value)
		var updatedCookie = name + "=" + value
		for(var propName in props){
			updatedCookie += "; " + propName
			var propValue = props[propName]
			if(propValue !== true){ updatedCookie += "=" + propValue }
		}
		document.cookie = updatedCookie

	}
	$(document).ready(function() {
		var currentTime = new Date().getTime();
		setCookie('lastActivity', currentTime);
		$(document).on('submit', '#login_form', function(){
			var currentTime = new Date().getTime();
			setCookie('lastActivity', currentTime);
		});
	});
</script>
</body>
</html>
