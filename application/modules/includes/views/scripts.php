<style>
	* {
	   -webkit-overflow-scrolling: touch;
	}
    @media (max-width: 768px) {
        .chat.body {
            right: 0;
            left: 0;
        }
        .smssection .sms {
            position: absolute;
            background: #fff;
            display: none;
            z-index: 1100;
        }
        .sms-to-list {
            left: 10px;
            top: 8px;
            font-size: 25px;
            color: #2095FE!important;
            padding: 0 10px;
        }
        .scrollable.mapper {
            top: 50px!important;
        }
       
    }
    @media (min-width: 992px) {
        .chat.body {
            right: 20px;
        }
        .chat-box {
            max-height: 600px;
        }
    }
    @media (min-width: 768px) {
        .app, .app body {
            width: 100%;
            height: 100%;
            overflow-x: hidden!important;
        }
        .chat.body {
            right: 20px;
        }
    }
	@media (max-width: 992px) {
		.hbox > aside.aside-md.hidden-sm {display: none!important;}
		.hbox > aside.aside-md.hidden-sm.nav-off-screen {
			display: table-cell !important;
		}
		.navbar-header {
			text-align: center;
			float: none;
		}
		.aside-md {
			width: auto;
		}
        .app, .app body {
            width: 100%;
            height: 100%;
            overflow-x: hidden;
        }
        .nav-user.open {
            display: inherit !important;
            padding: 0 15px 15px;
            float: none!important;
        }
        .nav-user .dropdown-menu {
            display: block;
            position: static;
            float: none;
        }
        .nav-user .dropdown > a {
            display: block;
            text-align: center;
            font-size: 18px;
            padding-bottom: 10px;
        }
        
        .dropdown-menu > li:last-child{
            border-bottom: none; 
        }
        .dropdown-menu > li{ 
            border-bottom: 1px solid #eee; 
        }

        .dropdown-menu > li > a{
            padding: 7px 15px;
            color: #505050;
        }

        .nav-user .avatar {
            width: 160px !important;
            float: none !important;
            display: block;
            margin: 20px auto;
            padding: 5px;
            background-color: rgba(255,255,255,0.1);
            position: relative;
        }
        a.open-messenger {
            padding-top: 10px!important;
        }
        .dropdown>a.open-messenger>.messenger-counter {
            position: absolute!important;
            top: 3px!important;
            right: 3px!important;
        }
        .navbar-nav.open .dropdown-menu.chat-box {
            display: none;
        }
        .navbar-nav.open>li>div.open>.dropdown-menu.chat-box {
            display: block!important;
        }
        .dropdown-menu.chat-box {
            position: absolute!important;
            top: 43px;
            right: 100%;
            left: auto;
            z-index: 1000;
            display: none;
            float: left;
            min-width: 160px;
            padding: 5px 0;
            margin: 2px 0 0;
            font-size: 14px;
            list-style: none;
            background-color: #fff!important;
            background-clip: padding-box;
            border: 1px solid #ccc!important;
            border: 1px solid rgba(0, 0, 0, .15)!important;
            border-radius: 4px;
            -webkit-box-shadow: 0 6px 12px rgba(0, 0, 0, .175)!important;
            box-shadow: 0 6px 12px rgba(0, 0, 0, .175)!important;
        }
        .chat-box-button>a {
            background-color: #74ad46!important;
        }
        .chat-box-button {
            padding: 11px 15px;
            position: absolute;
            right: 0;
        }
        .chat-box-block {
            position: absolute!important;
            right: 0;
            top: 0;
        }
        .mobile-menu {
            text-align: center;
            font-size: 18px;
            padding-bottom: 10px;
            padding-top: 40px;
        }
        .navbar-nav > li {
            float: none!important;
        }
        .navbar-fixed-top-xs {
            position: fixed;
            left: 0;
            width: 100%;
            z-index: 1100;
        }
        .chat-box {
            max-height: 300px;
        }
        .ls_container {
            position: absolute;
            left: 60px;
            right: 60px;
            top: -5px;
            margin: 0 auto;
            max-width: 300px;
            z-index: 1100;
        }
        input#gSearch {
            width: 100%!important;
        }
        .ls_result_div {
            left: -20px!important;
            right: -20px!important;
            width: 100%!important;
            max-height: 350px;
        }
        .gSearchItem {
            margin-right: 0!important;
        }
        .scrollable.mapper {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
        }
        #note-list {
            display:none;
            background: #fff;
            border: 1px solid #ccc;
        }
        .open #note-list {
            display:block!important;
        }
	}
	@media (min-width:500px) and (max-width: 992px) {
		
		.nav-off-screen {
			display: block !important;
			position: absolute;
			left: 0;
			top: 0;
			bottom: 0;
			width: 50%;
			visibility: visible;
			overflow-x: hidden;
			overflow-y: auto;
			-webkit-overflow-scrolling: touch;
		}
		.nav-off-screen + * {
			background-color: #f7f7f7;
			-webkit-transition: -webkit-transform 0.2s ease-in-out;
			-moz-transition: -moz-transform 0.2s ease-in-out;
			-o-transition: -o-transform 0.2s ease-in-out;
			transition: transform 0.2s ease-in-out;
			-webkit-transition-delay: 0s;
			transition-delay: 0s;
			-webkit-transform: translate3d(0px, 0px, 0px);
			transform: translate3d(0px, 0px, 0px);
			-webkit-backface-visibility: hidden;
			-moz-backface-visibility: hidden;
			backface-visibility: hidden;
			-webkit-transform: translate3d(50%, 0px, 0px);
			transform: translate3d(50%, 0px, 0px);
			overflow: hidden;
			position: absolute;
			width: 100%;
			top: 0px;
			bottom: 0;
			left: 0;
			right: 0;
			z-index: 2;
		}
	}
	@media (max-width:499px) {
		.nav-off-screen {
			display: block !important;
			position: absolute;
			left: 0;
			top: 0;
			bottom: 0;
			width: 75% !important;
			visibility: visible;
			overflow-x: hidden;
			-webkit-overflow-scrolling: touch;
		}
		.nav-off-screen + * {
			background-color: #f7f7f7;
			-webkit-transition: -webkit-transform 0.2s ease-in-out;
			-moz-transition: -moz-transform 0.2s ease-in-out;
			-o-transition: -o-transform 0.2s ease-in-out;
			transition: transform 0.2s ease-in-out;
			-webkit-transition-delay: 0s;
			transition-delay: 0s;
			-webkit-transform: translate3d(0px, 0px, 0px);
			transform: translate3d(0px, 0px, 0px);
			-webkit-backface-visibility: hidden;
			-moz-backface-visibility: hidden;
			backface-visibility: hidden;
			-webkit-transform: translate3d(75%, 0px, 0px) !important;
			transform: translate3d(75%, 0px, 0px) !important;
			overflow: hidden;
			position: absolute;
			width: 100%;
			top: 0px;
			bottom: 0;
			left: 0;
			right: 0;
			z-index: 2;
		}
	}
    @media (max-width:430px) {
        .ls_result_div {
            left: -60px!important;
            right: -60px!important;
            width: 290px!important;
        }
    }
    @media (max-width:1500px) {
        .messenger-title {
            display: none;
        }
    }
    @media (max-width:920px) {
        .sms-history .nav-tabs.mode-nav {
            font-size: 10px!important;
            margin-left: 0!important;
        }
        .change-notifications {
            width: 18px!important;
        }
        #messenger .sms-history .h3 {
            font-size: 20px!important;
        }
    }
    .client-profile-map {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 20px;
        padding-right: 15px;
        overflow: hidden;
    }

    .avatar, .avatar img {
        border-radius: 0;
        border: 1px solid rgba(255,255,255,0.35);
        display: block;
        border-radius: 500px;
        white-space: nowrap;
        width: 148px;
    }
    .chat-box {
        overflow-y: auto;
    }
    #chatboxes_history_containers, #chat_box_history_container, #chat_box_history_container .tab-pane {
        height: 100%;
    }
    .chat-header .chat_box_name_separator {
        margin-left: -4px;
        font-weight: bold;
    }
    .btn-arrow-right,
    .btn-arrow-left {
        position: relative;
        padding-left: 18px;
        padding-right: 18px;
        border-radius: 0 !important;
        margin-right: 1px; }
    .btn-arrow-right[disabled],
    .btn-arrow-left[disabled] {
        opacity: 1.00; }
    .btn-arrow-right:before, .btn-arrow-right:after,
    .btn-arrow-left:before,
    .btn-arrow-left:after {
        content: "";
        position: absolute;
        top: 4px;
        /* move it down because of rounded corners */
        height: 24px;
        /* button_inner_height / sqrt(2) */
        width: 24px;
        /* same as height */
        background: inherit;
        /* use parent background */
        border: inherit;
        /* use parent border */
        border-left-color: transparent;
        /* hide left border */
        border-bottom-color: transparent;
        /* hide bottom border */
        border-radius: 0 !important; }
    .btn-arrow-right:before,
    .btn-arrow-left:before {
        left: -13px; }
    .btn-arrow-right:after,
    .btn-arrow-left:after {
        right: -13px; }
    .btn-arrow-right.btn-arrow-left,
    .btn-arrow-left.btn-arrow-left {
        padding-right: 36px; }
    .btn-arrow-right.btn-arrow-left:before, .btn-arrow-right.btn-arrow-left:after,
    .btn-arrow-left.btn-arrow-left:before,
    .btn-arrow-left.btn-arrow-left:after {
        -webkit-transform: rotate(225deg);
        -ms-transform: rotate(225deg);
        transform: rotate(225deg);
        /* rotate right arrow squares 45 deg to point right */ }
    .btn-arrow-right.btn-arrow-right,
    .btn-arrow-left.btn-arrow-right {
        padding-left: 36px; }
    .btn-arrow-right.btn-arrow-right:before, .btn-arrow-right.btn-arrow-right:after,
    .btn-arrow-left.btn-arrow-right:before,
    .btn-arrow-left.btn-arrow-right:after {
        -webkit-transform: rotate(45deg);
        -ms-transform: rotate(45deg);
        transform: rotate(45deg);
        /* rotate right arrow squares 45 deg to point right */ }

    .btn-arrow-right:after,
    .btn-arrow-left:before {
        /* bring arrow pointers to front */
        z-index: 3; }

    .btn-arrow-right:before,
    .btn-arrow-left:after {
        /* hide arrow tails background */
        background-color: white; }

    /* Large */
    .btn-lg.btn-arrow-right,
    .btn-lg.btn-arrow-left,
    .btn-group-lg > .btn-arrow-left,
    .btn-group-lg > .btn-arrow-right {
        padding-left: 22px;
        padding-right: 22px;
        margin-right: 0px; }
    .btn-lg.btn-arrow-right:before, .btn-lg.btn-arrow-right:after,
    .btn-lg.btn-arrow-left:before,
    .btn-lg.btn-arrow-left:after,
    .btn-group-lg > .btn-arrow-left:before,
    .btn-group-lg > .btn-arrow-left:after,
    .btn-group-lg > .btn-arrow-right:before,
    .btn-group-lg > .btn-arrow-right:after {
        top: 6px;
        /* move it down because of rounded corners */
        height: 32px;
        /* button_inner_height / sqrt(2) */
        width: 32px;
        /* same as height */ }
    .btn-lg.btn-arrow-right:before,
    .btn-lg.btn-arrow-left:before,
    .btn-group-lg > .btn-arrow-left:before,
    .btn-group-lg > .btn-arrow-right:before {
        left: -16px; }
    .btn-lg.btn-arrow-right:after,
    .btn-lg.btn-arrow-left:after,
    .btn-group-lg > .btn-arrow-left:after,
    .btn-group-lg > .btn-arrow-right:after {
        right: -16px; }
    .btn-lg.btn-arrow-right.btn-arrow-left,
    .btn-lg.btn-arrow-left.btn-arrow-left,
    .btn-group-lg > .btn-arrow-left.btn-arrow-left,
    .btn-group-lg > .btn-arrow-right.btn-arrow-left {
        padding-right: 44px; }
    .btn-lg.btn-arrow-right.btn-arrow-right,
    .btn-lg.btn-arrow-left.btn-arrow-right,
    .btn-group-lg > .btn-arrow-left.btn-arrow-right,
    .btn-group-lg > .btn-arrow-right.btn-arrow-right {
        padding-left: 44px; }

    /* Small */
    .btn-sm.btn-arrow-right,
    .btn-sm.btn-arrow-left,
    .btn-group-sm > .btn-arrow-left,
    .btn-group-sm > .btn-arrow-right {
        padding-left: 14px;
        padding-right: 14px;
        margin-right: -1px; }
    .btn-sm.btn-arrow-right:before, .btn-sm.btn-arrow-right:after,
    .btn-sm.btn-arrow-left:before,
    .btn-sm.btn-arrow-left:after,
    .btn-group-sm > .btn-arrow-left:before,
    .btn-group-sm > .btn-arrow-left:after,
    .btn-group-sm > .btn-arrow-right:before,
    .btn-group-sm > .btn-arrow-right:after {
        top: 4px;
        /* move it down because of rounded corners */
        height: 20px;
        /* button_inner_height / sqrt(2) */
        width: 20px;
        /* same as height */ }
    .btn-sm.btn-arrow-right:before,
    .btn-sm.btn-arrow-left:before,
    .btn-group-sm > .btn-arrow-left:before,
    .btn-group-sm > .btn-arrow-right:before {
        left: -10px; }
    .btn-sm.btn-arrow-right:after,
    .btn-sm.btn-arrow-left:after,
    .btn-group-sm > .btn-arrow-left:after,
    .btn-group-sm > .btn-arrow-right:after {
        right: -10px; }
    .btn-sm.btn-arrow-right.btn-arrow-left,
    .btn-sm.btn-arrow-left.btn-arrow-left,
    .btn-group-sm > .btn-arrow-left.btn-arrow-left,
    .btn-group-sm > .btn-arrow-right.btn-arrow-left {
        padding-right: 28px; }
    .btn-sm.btn-arrow-right.btn-arrow-right,
    .btn-sm.btn-arrow-left.btn-arrow-right,
    .btn-group-sm > .btn-arrow-left.btn-arrow-right,
    .btn-group-sm > .btn-arrow-right.btn-arrow-right {
        padding-left: 28px; }

    /* Extra Small */
    .btn-xs.btn-arrow-right,
    .btn-xs.btn-arrow-left,
    .btn-group-xs > .btn-arrow-left,
    .btn-group-xs > .btn-arrow-right {
        padding-left: 10px;
        padding-right: 10px;
        margin-right: -1px; }
    .btn-xs.btn-arrow-right:before, .btn-xs.btn-arrow-right:after,
    .btn-xs.btn-arrow-left:before,
    .btn-xs.btn-arrow-left:after,
    .btn-group-xs > .btn-arrow-left:before,
    .btn-group-xs > .btn-arrow-left:after,
    .btn-group-xs > .btn-arrow-right:before,
    .btn-group-xs > .btn-arrow-right:after {
        top: 3px;
        /* move it down because of rounded corners */
        height: 14px;
        /* button_inner_height / sqrt(2) */
        width: 14px;
        /* same as height */ }
    .btn-xs.btn-arrow-right:before,
    .btn-xs.btn-arrow-left:before,
    .btn-group-xs > .btn-arrow-left:before,
    .btn-group-xs > .btn-arrow-right:before {
        left: -8px; }
    .btn-xs.btn-arrow-right:after,
    .btn-xs.btn-arrow-left:after,
    .btn-group-xs > .btn-arrow-left:after,
    .btn-group-xs > .btn-arrow-right:after {
        right: -7px; }
    .btn-xs.btn-arrow-right.btn-arrow-left,
    .btn-xs.btn-arrow-left.btn-arrow-left,
    .btn-group-xs > .btn-arrow-left.btn-arrow-left,
    .btn-group-xs > .btn-arrow-right.btn-arrow-left {
        padding-right: 20px; }
    .btn-xs.btn-arrow-right.btn-arrow-right,
    .btn-xs.btn-arrow-left.btn-arrow-right,
    .btn-group-xs > .btn-arrow-left.btn-arrow-right,
    .btn-group-xs > .btn-arrow-right.btn-arrow-right {
        padding-left: 20px; }

    /* Button Groups */
    .btn-group > .btn-arrow-left:hover, .btn-group > .btn-arrow-left:focus,
    .btn-group > .btn-arrow-right:hover,
    .btn-group > .btn-arrow-right:focus {
        z-index: initial; }

    .btn-group > .btn-arrow-right + .btn-arrow-right,
    .btn-group > .btn-arrow-left + .btn-arrow-left {
        margin-left: 0px; }

    .btn-group > .btn:not(.btn-arrow-right):not(.btn-arrow-left) {
        z-index: 1; }
    .mycolorpicker {cursor: cell!important;}
</style>

<script type="text/javascript">
	var activityTimeout = <?php echo (ACTIVITY_TIMEOUT * 1000); ?>;
	var serverLastActivity = <?php echo $this->session->userdata('lastActivity') ? $this->session->userdata('lastActivity') : 0; ?>;
	var clientLastActivity = new Date().getTime();
	var diffLastActivity = clientLastActivity - serverLastActivity;

	var baseUrl = '<?php echo base_url();?>';
	var click = false;
	var haveResult = false;
	var isSupport = '<?php echo $this->session->userdata('twilio_support'); ?>';
	var sms_notifications = $.cookie('sms_notifications') ? $.cookie('sms_notifications') : isSupport;
	var MAP_CENTER_LAT = '<?php echo config_item('map_lat') ? config_item('map_lat') : '0'; ?>';
	var MAP_CENTER_LON = '<?php echo config_item('map_lon') ? config_item('map_lon') : '0'; ?>';
    var OFFICE_LAT = '<?php echo config_item('office_lat') ? config_item('office_lat') : '0'; ?>';
    var OFFICE_LON = '<?php echo config_item('office_lon') ? config_item('office_lon') : '0'; ?>';
    var OFFICE_ADDRESS = '<?php echo addslashes(config_item('office_address')); ?>';
    var OFFICE_CITY = '<?php echo addslashes(config_item('office_city')); ?>';
    var OFFICE_STATE = '<?php echo addslashes(config_item('office_state')); ?>';
    var OFFICE_COUNTRY = '<?php echo addslashes(config_item('office_country')); ?>';
    var AUTOCOMPLETE_RESTRICTION = '<?php echo config_item('autocomplete_restriction') ? config_item('autocomplete_restriction') : ''; ?>';
    var MOMENT_DATE_FORMAT = '<?php echo getMomentJSDateFormat(); ?>';
    var DATEPICKER_DATE_FORMAT = '<?php echo getJSDateFormat(); ?>';

    var INT_TIME_FROMAT = '<?php echo getIntTimeFormat(); ?>';
    var currency_symbol = '<?php echo config_item('currency_symbol'); ?>';
    var currency_symbol_position = '<?php echo config_item('currency_symbol_position'); ?>';

    var DISTANCE_MEASUREMENT = "<?php echo DISTANCE_MEASUREMENT; ?>";
    var PHONE_NUMBER_MASK = "<?php echo config_item('phone_inputmask'); ?>";
    var default_brand_id = '<?php echo default_brand(); ?>';
    var default_brand_name = '<?php echo brand_name(default_brand()); ?>';
</script>


<?php if (!isset ($map['js']) && !isset($map1['js'])) : ?>
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=<?php echo $this->config->item('gmaps_key'); ?>&libraries=places,drawing,geometry&language=en"></script>
<?php endif; ?>

<?php if($this->router->fetch_class() == 'estimates') : ?>
	<script src="<?php echo base_url('assets/vendors/notebook/js/slider/bootstrap-slider.js'); ?>"></script>
<?php endif; ?>

<script type="text/javascript">
	var last_message_id = <?php echo get_last_message_id_for_page(); ?>;
	var user_id = <?php echo get_user_id(); ?>;
	$(document).ajaxStart(
		function(e){
          let currentTarget = $(e.currentTarget.activeElement);

          if(currentTarget.attr('data-show-processing') !== 'false')
				$('#processing-modal').modal();
		}
	);
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

    $(document).ajaxStop(
		function(e){
			setTimeout(function(){
				$('#processing-modal').modal('hide');
				if($('.modal-backdrop.fade.in:visible').length && !$('.modal.fade.in').length)
					$('.modal-backdrop.fade.in:visible').remove();
				$('.modal-backdrop.fade.in').remove();
				$('.modal-backdrop.fade').remove();
			}, 200);
		}
	);
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
        $.notify({
            icon: 'glyphicon glyphicon-exclamation-sign',
            title: '<strong>Error:</strong>',
            message:msg
        }, {
            type: "danger",
            allow_dismiss: true,
            delay: 500,
            timer: 3000,
            z_index:99999,
            placement: {
                from: "top",
                align: "center"
            },
        });
	}
	function successMessage(msg) {
        $.notify({
            icon: 'glyphicon glyphicon-ok-sign',
            title: '<strong>Success</strong>',
            message:msg
        }, {
            /*position: 'center',*/
            type: "success",
            allow_dismiss: true,
            delay: 500,
            timer: 3000,
            z_index:99999,
            placement: {
                from: "top",
                align: "center"
            },
        });
	}
	
	
	
	$(document).ready(function () {
		$.cookie('sms_notifications', sms_notifications, {path:'/'});
		$.ajaxSetup({ cache: false });
		if(typeof(soundManager) != "undefined") {
			soundManager.setup({
				// path to directory containing SM2 SWF
				url: '<?php echo base_url('assets/js/soundmanager/swf/'); ?>'
			});
		}
		
		$('.btn-upload').change(function () {
			var obj = $(this).parent().next();
			$(obj).html('');
			$.each($(this)[0].files, function (key, val) {
				if (val.name)
					$(obj).html($(obj).html() + "<span class='label label-info' id='upload-file-info'><div style='padding: 2px;font-size:13px;'>" + val.name + "</div></span>");
			});
		});
		$(document).on('click', '.showPhone', function(){
			if(!$('.site-iframe').attr('src'))
				$('.site-iframe').attr('src', baseUrl + 'iframe' + location.pathname)
			$('.phone-overlay').show();

			return false;
		});
		$(document).on('click', '.closePhone', function(){
			$('.phone-overlay').hide();
			return false;
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
				//history.pushState("", document.title, window.location.pathname);
				//location.href = location.href + $(this).attr('href');
				$('[data-type="' + type + '"] [data-toggle="tab"][href="' + $(this).attr('href') + '"]').parent().addClass('active');
				$($(this).attr('href')).show();
				if (action == 'stop')
					return false;
				if ($('.dhx_cal_light.dhx_cal_light_wide').length) {
					$('[data-type="' + type + '"]').prev('button').html($('[data-type="' + type + '"] [data-toggle="tab"][href="' + $(this).attr('href') + '"]').text() + '<span class="caret" style="margin-left:5px;"></span>');
					$('.dhx_cal_light.dhx_cal_light_wide').scrollTop(0);
					$('[data-type="' + type + '"]').parent().removeClass('open');
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

        $(document).click(function (e) {
            if($('.nav-user').hasClass('open') && !$(e.target).is('#messenger')  && !$(e.target).parents('#messenger').length && !$(e.target).is('.chat.body')  && !$(e.target).parents('.chat.body').length && !$(e.target).parents('.nav-user').length && !$(e.target).is('.mobile-gear') && !$(e.target).parent().is('.mobile-gear')) {
                $('.nav-user').removeClass('open');
            }
            return true;
        });

        $(".mobile-gear").click(function(event) {
            if ($('.nav-user').hasClass('open'))
                $('.nav-user').removeClass('open');
            else
                $('.nav-user').addClass('open');
        });

		$("#gSearch").ajaxlivesearch({
			enter: false,
			loaded_at: <?php echo time(); ?>,
			token: 'test',
			max_input: 100,
			url: baseUrl + 'dashboard/ajax_gsearch',
			footer_class: 'hide',
			onResultClick: function(e, data) {
				// get the index 1 (second column) value
				var selectedOne = jQuery(data.selected).find('td').eq('1').text();
				
				// set the input value
				jQuery('#ls_query').val(selectedOne);

				// hide the result
				jQuery("#ls_query").trigger('ajaxlivesearch:hide_result');
			},
			
			onAjaxComplete: function(e, data) {
			}
		});
		$('#gSearch').keyup(function(event){
			setTimeout(function () {
				var obj = $('.list-group-item:first');
				var href = $(obj).find('.searchLink').attr('href');
				
				if(event.keyCode == 13 && $(obj).hasClass('gsearch_result') && $('#gSearch').val() != '')
					window.open(href, '_blank');
				
			}, 800);
			return false;
		});
	});



    function initQbLogPopover(){
        $('body').on('click', function (e) {
            if ($(e.target).data('toggle') !== 'popover'
                && $(e.target).parents('.popover.in').length === 0) {
                $('[data-toggle="popover"]').data('show', 0).popover('hide');
            }
        });
        $('[data-toggle="popover"]').popover({
            html: true,
            content: function () {
                return $('#popover-content-' + $(this).data('id')).html();
            }
        }).on("shown.bs.popover", function () {
        }).on("click", function (e) {
            console.log($(this).data('show'));
            if($(this).data('show') == 1) {
                $(this).data('show', 0).popover('hide');
            } else {
                $.ajax({
                    type: "GET",
                    url: baseUrl + "qb/getLogData",
                    dataType: 'json',
                    data: {id: $(this).data('id'), module: $(this).data('module')}
                }).done(function (msg) {
                    let qbLogs = msg['qb_logs'];
                    let result = '<div class="col-md-12 text-center m-top-5 m-bottom-5">Empty Logs</div>';
                    if (qbLogs.length > 0) {
                        result = '<ul class="list-group custom-popover" style="max-height: 340px; overflow: auto; max-width: 380px" title="Logs">';
                        $.each(qbLogs, function (key, val) {
                            if (val['log_result'] == 1) {
                                result += '<li class="list-group-item list-group-item-success">';
                                result += '<div class="row">';
                                result += '<div class="col-md-2">';
                                if (val["log_route"] == 1) {
                                    result += '<i class="fa fa-sign-out fa-3x" aria-hidden="true"  title="Route" > </i>';
                                } else {
                                    result += '<i class="fa fa-sign-in fa-3x" aria-hidden="true"  title="Route" > </i>';
                                }
                                result += '</div>';
                                result += '<div>';
                                result += '<div class="col-md-10">';
                                result += '<div class="col-md-12"> <strong>Date:</strong> ' + val['log_created_at_js'] + ' </div>';
                                result += '<div class="col-md-12"> <strong>Status:</strong> Success </div>';
                                result += '</div>';
                                result += '</div>';
                                result += '</div>';
                                result += '</li>';
                            } else if (val['log_result'] == 0) {
                                result += '<li class="list-group-item list-group-item-danger">';
                                result += '<div class="row">';
                                result += '<div class="col-md-2">';
                                if (val["log_route"] == 1) {
                                    result += '<i class="fa fa-sign-out fa-3x" aria-hidden="true"  title="Route" > </i>';
                                } else {
                                    result += '<i class="fa fa-sign-in fa-3x" aria-hidden="true"  title="Route" > </i>';
                                }
                                result += '</div>';
                                result += '<div>';
                                result += '<div class="col-md-10">';
                                result += '<div class="col-md-12"><strong>Date: </strong>' + val['log_created_at_js'] + '</div>';
                                result += '<div class="col-md-12"><strong>Message: </strong>' + val['log_message'] + '</div>';
                                result += '</div>';
                                result += '</div>';
                                result += '</div>';
                                result += '</li>';
                            }
                        });
                        result += '</ul>';
                    }
                    $('#popover-content-' + msg['id']).find('.logs').html(result);
                    $('#popover-' + msg['id']).data("bs.popover").tip().css({"max-width": "350px", "max-height": "400px", "min-width": "250px"});
                    $('#popover-' + msg['id']).popover('show');
                    $('#popover-' + msg['id']).data('show', 1);
                    console.log(msg['id']);
                });
            }
        });
    }

    function sync(entity) {
        let theEntity = $(entity);
        let route = theEntity.hasClass('inQB');
        if(route === true)
            route = 'push';
        else
            route = 'pull';
        $.ajax({
            type: "POST",
            url: baseUrl + "qb/manualSync",
            dataType: 'json',
            data: { id : theEntity.closest('div').data('id'), module: theEntity.closest('div').data('module'), route: route}
        }).done(function (msg) {
            console.log(msg);
        });
    }

    window.update_members_list = function(data){
        $('.reportInfoModal '+data.container).html(data.html);
        init_report_editable();
    }

    $(document).delegate('.lunch', 'change', function(){

        date = $(this).attr('data-date');
        var worked_id = $(this).attr('data-worked-id');
        var lunch = +$(this).is(':checked');

        if(!lunch)
            lunch = 0.5;
        else
            lunch = 0;

        $.post(baseUrl + 'payroll/ajax_change_lunch', {worked_id : worked_id, lunch : lunch}, function (resp) {
            if(resp.status == 'ok')
            {
                successMessage('Success!');
                if(lunch == 0)
                    $('input[data-worked-id="'+ worked_id +'"]').prop('checked', true);
                else
                    $('input[data-worked-id="'+ worked_id +'"]').prop('checked', false);
            }
            else
                alert(resp.msg);

        }, 'json');
        return false;
    });
</script>

<script>

    $(document).on('click', '.reportInfoModal .save_time', function(){
        var inpid = $(this).data('id');

        $('.save_login_time').remove();
        $('.cancel_save_time').remove();
        $.each($(this).parent().parent().parent().find('input[type="time"]'), function(key, val){
            $(val).replaceWith('<a href="#" class="save_time " name="'+ $(val).attr('name') +'" data-id="'+ inpid +'">'+ $(val).val() +'</a>');
        });
        $.each($(this).parent().find('a'), function(key, val){
            var style = '';
            if(!key)
                style=' style="margin-left: 9px;"';
            $(val).replaceWith('<input type="time" data-default="' + $(val).text() + '" name="'+ $(val).attr('name') +'" id="inp-'+ $(val).attr('name') +'-time-'+ inpid +'" value="'+ $(val).text() +'"'+style+'>');
        });
        $('#inp-logout-time-'+ inpid +'').parent().append('<a href="#" onclick="editTime('+ inpid +')" class="btn btn-xs btn-success save_login_time" title="Save" style="margin-top: -30px;"><i class="fa fa-edit"></i></a>');
        $('#inp-logout-time-'+ inpid +'').parent().append('<a href="#" data-id="'+ inpid +'" class="btn btn-xs cancel_save_time btn-default m-l-xs" title="Cancel" style="margin-top: -30px;"><i class="fa fa-times"></i></a>');
    });
    $(document).on('click', '.reportInfoModal .cancel_save_time', function(){
        var inpid = $(this).data('id');
        $.each($(this).parent().parent().parent().find('input[type="time"]'), function(key, val){
            $(val).replaceWith('<a href="#" class="save_time " name="'+ $(val).attr('name') +'" data-id="'+ inpid +'">'+ $(val).attr('data-default') +'</a>');
        });
        $('.save_login_time').remove();
        $('.cancel_save_time').remove();
    });

    function editTime(login_id) {

        var login = $("#inp-login-time-" + login_id).val();
        var logout = $("#inp-logout-time-" + login_id).val();

        // edit
        var url = 'ajax_save_time';
        var text1 = 'updated';
        var text2 = 'updating';

        $.ajax({
            url: baseUrl + 'payroll/' + url,
            data: 'login=' + login + "&logout=" + logout + "&login_id=" + login_id,
            type: 'post',
            dataType: 'json',
            success: function (res) {
                if (res.worked_total > 0) {
                    successMessage("Time details " + text1 + " successfully.");

                    $.each($('.log_time_' + login_id).find('input[type="time"]'), function(key, val){
                        $(val).replaceWith('<a href="#" class="save_time " name="'+ $(val).attr('name') +'" data-id="'+ login_id +'">'+ $(val).val() +'</a>');
                    });
                    $('.log_time_' + login_id + ' .save_login_time').remove();
                    $('.log_time_' + login_id + ' .cancel_save_time').remove();
                }
                else {
                    alert("Error in " + text2 + " time details. Please try again!!");
                }
            },
            error: function (err) {
                alert("Error in " + text2 + " time details: " + err.responseText);

            }
        });
    }
</script>
<!-- Google Map API loader -->
<?php if (isset ($map['js'])) {
	echo $map['js'];
}?>

