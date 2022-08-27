<!DOCTYPE html>
<html lang="en" class="app">

<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Main Prospect">

	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/animate.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/fuelux/fuelux.css'); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/jquery.datetimepicker.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/datepicker/datepicker.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/lightbox.css'); ?>">
	
	<!--<link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="/icons/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">-->

	<!--[if lt IE 9]>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ie/html5shiv.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ie/respond.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ie/excanvas.js'); ?>"></script>
	<![endif]-->
	<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
	<script src="<?php echo base_url(); ?>assets/js/jquery.datetimepicker.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/chart.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/lightbox.js"></script>
	<script>
		var baseUrl = '<?php echo base_url();?>';
		var click = false;
	</script>
	<script src="<?php echo base_url(); ?>assets/js/ajaxfileupload.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/jquery-ui.custom.min.js"></script>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places"></script>
	<script type="text/javascript">
		
			// This is the "Offline page" service worker

// Add this below content to your HTML page, or add the js file to your page at the very top to register service worker

// Check compatibility for the browser we're running this in
/*if ("serviceWorker" in navigator) {
  if (navigator.serviceWorker.controller) {
    console.log("[PWA Builder] active service worker found, no need to register");
  } else {
    // Register the service worker
    navigator.serviceWorker
      .register("/pwabuilder-sw.js", {
        scope: "./"
      })
      .then(function (reg) {
        console.log("[PWA Builder] Service worker has been registered for scope: " + reg.scope);
      });
  }
}*/
		
		function setDefault(msg) {
			setTimeout(function () {
				$('.b-karma_controls-container.js-karma_controls.left').animate({left: '19px'}, 200);
				$('.b-karma_controls-container.js-karma_controls.right').animate({left: '-19px'}, 200);
				click = false;
			}, 200);
		}
		function sendKarma(like, user_id) {
			setDefault();
			if (!click) {
				return false;
			}
			$.post(baseUrl + 'dashboard/ajax_user_like', {user_id: user_id, like: like}, function (resp) {
				if (resp.status == 'ok') {
					if (like)
						$('.userRate-' + user_id + ' .b-karma_value').text(parseInt($('.userRate-' + user_id + ' .b-karma_value:first').text()) + 1);
					else
						$('.userRate-' + user_id + ' .b-karma_value').text(parseInt($('.userRate-' + user_id + ' .b-karma_value:first').text()) - 1);
				}
				else {
					if (resp.msg != undefined)
						errorMessage(resp.msg);
					else
						errorMessage('Ooops! Error!');
				}
				return false;
			}, 'json');
		}
		function errorMessage(msg) {
			$('body').append('<div class="alert alert-danger alert-message" id="errorMessage" style="display:none;"><button type="button" class="close m-l-sm" data-dismiss="alert">×</button><strong>' + msg + '</strong></div>');
			$('#errorMessage').fadeIn();
			setTimeout(function () {
				$('#errorMessage').fadeOut(function () {
					$('#errorMessage').remove();
				});
			}, 10000);
		}
		$(document).ready(function () {
			$('.btn-upload').change(function () {
				var obj = $(this).parent().next();
				$(obj).html('');
				$.each($(this)[0].files, function (key, val) {
					if (val.name)
						$(obj).html($(obj).html() + "<span class='label label-info' id='upload-file-info'><div style='padding: 2px;font-size:13px;'>" + val.name + "</div></span>");
				});
			});
			$(document).on('click', '[data-toggle="tab"]', function () {
				var type = $(this).parent().parent().data('type');
				var action = $(this).parent().parent().data('action');
				if (type) {
					var old_hash = location.hash;
					if (!old_hash)
						old_hash = '#tab1';
					$('[data-type="' + type + '"] [data-toggle="tab"][href="' + old_hash + '"]').parent().removeClass('active');
					$('[data-type="' + type + '"] [data-toggle="tab"][href="#tab1"]').parent().removeClass('active');
					$(old_hash).hide();
					$('#tab1').hide();
					location.hash = '';
					history.pushState("", document.title, window.location.pathname);
					location.href = location.href + $(this).attr('href');
					$('[data-type="' + type + '"] [data-toggle="tab"][href="' + $(this).attr('href') + '"]').parent().addClass('active');
					$($(this).attr('href')).show();
					if (action == 'stop')
						return false;
					if ($('.dhx_cal_light.dhx_cal_light_wide').length) {
						$('[data-type="' + type + '"]').prev('button').html($('[data-type="' + type + '"] [data-toggle="tab"][href="' + $(this).attr('href') + '"]').text() + '<span class="caret" style="margin-left:5px;"></span>');
						$('.dhx_cal_light.dhx_cal_light_wide').scrollTop(0);
					}
				}
			});
			$('.b-karma_controls-container.js-karma_controls.left').click(function () {
				if (!click) {
					click = true;
					var user_id = $(this).data('user_id');
					$('.userRate-' + user_id + ' .b-karma_controls-container.js-karma_controls.right').animate({left: '-25px'}, 200);
					$('.userRate-' + user_id + ' .b-karma_controls-container.js-karma_controls.left').animate({left: '14px'}, 200);
					sendKarma(0, user_id);
				}
				return false;
			});
			$('.b-karma_controls-container.js-karma_controls.right').click(function () {
				if (!click) {
					click = true;
					var user_id = $(this).data('user_id');
					$('.userRate-' + user_id + ' .b-karma_controls-container.js-karma_controls.right').animate({left: '-14px'}, 200);
					$('.userRate-' + user_id + ' .b-karma_controls-container.js-karma_controls.left').animate({left: '25px'}, 200);
					sendKarma(1, user_id);
				}
				return false;
			});
			$('#showMsg').fadeIn();
			setTimeout(function () {
				$('#showMsg').fadeOut(function () {
					$('#showMsg').remove();
				});
			}, 10000);
			setTimeout(function () {
				if (location.hash && $('[href="' + location.hash + '"]').length) {
					$('[href="#tab1"]').parent().removeClass('active');
					$('[href="' + location.hash + '"]').click();
				}
			}, 100);
			$('[data-toggle="class:nav-xs"]').click(function () {
				if ($(this).is('.active')) {
					console.log('active');
				}
				else {
					console.log('not active');
				}
			});
			$('.dropdown-toggle.dker').click(function () {
				setTimeout(function () {
					$('[name="search_keyword"][placeholder="Search"]').focus()
				}, 50);
			});
		});
	</script>

	<!-- Google Map API loader -->
	<?php if (isset ($map['js'])) {
		echo $map['js'];
	}?>

</head>
<body ng-app="myapp" class="ng-scope">
<section class="vbox">
    <?php $this->load->view('includes/partials/preloader_modal'); ?>
