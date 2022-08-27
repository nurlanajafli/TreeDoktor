<!DOCTYPE html>
<html lang="en" class="app">

<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="app, web app, responsive, admin dashboard, admin, flat, flat ui, ui kit, off screen nav" />
	<title><?php echo $title; ?></title>
	<meta name="description" content="">
	<meta name="author" content="Arbostar">
	<!-- CSS -->
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/animate.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css?v='.config_item('font-awesome.min.css')); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/fuelux/fuelux.css'); ?>"
	      type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo base_url('assets/css/jquery.datetimepicker.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/datepicker/datepicker.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/css/tag.css?v1.1'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/css/arbostar-theme.css?v='.config_item('arbostar-theme.css')); ?>">
    <?php if($this->router->fetch_class() == 'estimates') : ?>
		<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/slider/slider.css'); ?>">
	<?php endif; ?>

	<link rel="stylesheet" href="<?php echo base_url('assets/css/lightbox.css'); ?>">
	<link rel="shortcut icon" href="<?php echo base_url('favicon.ico'); ?>">

	<link type="text/css" rel="stylesheet" href="<?php echo base_url('assets/css/360player.css'); ?>"/>
	<?php /*<link type="text/css" rel="stylesheet" href="//static1.twilio.com/console/bundles/soundmanager.css?3069d11240df02ceacfa5658d36cb3de"/>*/ ?>

    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">

	<!--[if lt IE 9]>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ie/html5shiv.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ie/respond.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ie/excanvas.js'); ?>"></script>
	<![endif]-->
	<script src="<?php echo base_url(); ?>assets/js/jquery.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/libs/currency.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/common.js?v=<?php echo config_item('js_common'); ?>"></script>
    <script src="<?php echo base_url(); ?>assets/vendors/notebook/js/libs/moment.min.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/jquery.datetimepicker.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/chart.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/lightbox.js"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/ajax-live-search-master/js/ajaxlivesearch.min.js'); ?>"></script>
	<link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/js/ajax-live-search-master/css/ajaxlivesearch.min.css?v1.01'); ?>">
	<script src="<?php echo base_url(); ?>assets/js/ajaxfileupload.js"></script>
	<script src="<?php echo base_url(); ?>assets/js/jquery-ui.min.js"></script>
    <script src="<?php echo base_url(); ?>assets/js/libs/bootstrap-notify/bootstrap-notify.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap.min.css">
    <script src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/fixedheader/3.1.9/js/dataTables.fixedHeader.min.js"></script>



	<?php $this->load->view('scripts'); ?>

	<link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url(); ?>assets/css/chat/chat.css?v=1.01" />
	<link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url(); ?>assets/css/chat/wschat.css?v=1.06" />
	<link type="text/css" rel="stylesheet" media="all" href="<?php echo base_url(); ?>assets/css/chat/screen.css" />
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/socket.io-1.4.5.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/autosize.min.js"></script>
	<style>

		* {
   -webkit-overflow-scrolling: touch;
}
	</style>
	<?php if($this->session->userdata('STP') != "3") : ?>
        <script>var timeFormat = "<?= getIntTimeFormat() ?>"; var dateFormatJS = "<?= getJSDateFormat() ?>";</script>
        <script>const EXTERNAL_WS_PORT = "<?php echo config_item('externalWsPort') ?? '8895'; ?>";</script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/chat/wschat.js?v=1.191"></script>

	<?php endif; ?>
    <script type="text/javascript" src="<?php echo base_url('assets/js/libs/jsrender.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendors/notebook/js/select2/select2.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ion.sound.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/vendors/notebook/js/select2/select2.css'); ?>">
    <?php echo isset($renderer) ? $renderer->renderHead() : ""; ?>
    <?php echo config_item('gtm_head_code') ? config_item('gtm_head_code') : ""; ?>
    <script>
        jQuery.browser={};
        (function(){
            jQuery.browser.msie=false;
            jQuery.browser.version=0;
            if(navigator.userAgent.match(/MSIE ([0-9]+)./)){
                jQuery.browser.msie=true;
                jQuery.browser.version=RegExp.$1;
            }
        })();
    </script>
</head>
<body ng-app="myapp" class="ng-scope">
<section class="vbox">
    <?php echo config_item('gtm_body_code') ? config_item('gtm_body_code') : ""; ?>
	<?php $this->load->view('includes/partials/preloader_modal'); ?>
	<?php $this->load->view('partials/autologout_modal'); ?>

	<?php if (config_item('messenger') && ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner())) : ?>
        <?php $this->load->view('messaging/messenger'); ?>
    <?php endif; ?>

	<header class="bg-success dk header navbar navbar-fixed-top-sm navbar-fixed-top-xs <?php echo config_item('custom_header_classes') ?>" style="/*height: 50px;*/">
		<div class="navbar-header aside-md">
			<a href="#nav" class="btn btn-link visible-sm visible-xs" data-toggle="class:nav-off-screen,open">
				<i class="fa fa-bars"></i>
			</a>

			<a href="<?php echo base_url("dashboard"); ?>" class="navbar-brand" style="white-space: nowrap">
                <img src="<?php echo base_url($this->config->item('company_header_logo')); ?>" style="<?php echo $this->config->item('company_header_logo_styles'); ?>" class="m-r-sm">
                <?php //echo $this->config->item('company_header_logo_string'); ?>
            </a>

			<a class="btn btn-link visible-sm visible-xs mobile-gear">
				<i class="fa fa-cog"></i>
			</a>
		</div>
		<ul class="nav navbar-nav navbar-right m-n hidden-sm hidden-xs nav-user">

			<?php if ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner()) : ?>

				<li class="m-r-sm gSearchItem">
					<div class="form-group">
						<input type="text" class="form-control rounded gSearchItem-input" id="gSearch" placeholder="Live Search..." data-show-processing="false">
                    </div>
				</li>

			<?php endif; ?>
			<?php if (isUserLoggedIn()) : ?>

				<?php if (config_item('messenger') && ($this->session->userdata('user_type') == "admin" || is_cl_permission_all() || is_cl_permission_owner())) : ?>
                    <li class="hidden-xs hidden-sm text-center messenger-item" style="width: 50px; font-size: 20px;">
                        <a href="#messenger" class="dker open-messenger" data-toggle="modal" data-backdrop="true" data-keyboard="true" style="max-height:50px;">
                            <i class="fa fa-mobile-phone"></i>
                            <span class="badge badge-sm up bg-danger m-l-n-sm text-xs pos-abt messenger-counter"></span>
                        </a>
                        <?php echo show_sms_user_limit(); ?>
                    </li>
				<?php endif; ?>
				
				<?php if($this->session->userdata('STP') != "3") : ?>
				<li class="hidden-xs hidden-sm chat-box-item">
					<a href="#" class="dropdown-toggle dk" data-toggle="dropdown">
						<i class="fa fa-comment-o"></i>
						<span class="badge badge-sm up bg-danger m-l-n-sm countUnread"></span>
					</a>
					<?php $this->load->view('chat_box'); ?>
				</li>
				
				<?php endif; ?>
			<?php endif; ?>
			<li class="dropdown"> <!--Mobile user dropdown-->
				<a href="#" class="dropdown-toggle hidden-sm hidden-xs" data-toggle="dropdown">
                    <span class="thumb-sm avatar pull-left">
                        <img src="<?php echo ($this->session->userdata('user_pic')/* && is_file(PICTURE_PATH . $this->session->userdata('user_pic'))*/) ? base_url(PICTURE_PATH) . $this->session->userdata('user_pic') : base_url('assets/pictures/avatar_default.jpg'); ?>">
                    </span>

					<?php if (isUserLoggedIn()) : ?>
						<?php echo($this->session->userdata('firstname')); ?>&nbsp;<?php echo($this->session->userdata('lastname')); ?>
					<?php endif; ?>

					<?php if (isEmployee()) : ?>
						<?php echo($this->session->userdata('emp_name')); ?>
					<?php endif; ?>

					<b class="caret"></b>
				</a>
                <div class="hidden-lg hidden-md mobile-menu">
                    <span class="thumb-sm avatar pull-left hidden-md hidden-lg">
                        <img src="<?php echo ($this->session->userdata('user_pic')/* && is_file(PICTURE_PATH . $this->session->userdata('user_pic'))*/) ? base_url(PICTURE_PATH) . $this->session->userdata('user_pic') : base_url('assets/pictures/avatar_default.jpg'); ?>">
                    </span>
                    <?php if (isUserLoggedIn()) : ?>
                        <?php echo($this->session->userdata('firstname')); ?>&nbsp;<?php echo($this->session->userdata('lastname')); ?>
                    <?php endif; ?>

                    <?php if (isEmployee()) : ?>
                        <?php echo($this->session->userdata('emp_name')); ?>
                    <?php endif; ?>

                    <b class="caret"></b>
                </div>
                <?php if($this->session->userdata('STP') != "3" && ($this->session->userdata('user_type') == "admin" || $this->session->userdata('CHAT') == 1)) : ?>
                    <a href="#messenger" class="dker open-messenger hidden-md hidden-lg" data-toggle="modal" data-backdrop="true" data-keyboard="true" style="position: absolute;top: 0px; left: 0px;">
                        <i class="fa fa-mobile-phone"></i>
                        <span class="badge badge-sm up bg-danger m-l-n-sm text-xs pos-abt messenger-counter"></span>
                    </a>
                    <?php echo show_sms_user_limit('mobile'); ?>
                
                     <div class="chat-box-block hidden-lg hidden-md">
                        <a href="#" class="dropdown-toggle dker chat-box-button" data-toggle="dropdown">
                            <i class="fa fa-comment-o"></i>
                            <span class="badge badge-sm up bg-danger m-l-n-sm countUnread"></span>
                        </a>
                        <?php $this->load->view('chat_box'); ?>
                     </div>
                <?php endif; ?>
				<ul class="dropdown-menu animated fadeInRight">
					<span class="arrow top"></span>

					<?php if (isUserLoggedIn()) : ?>
						<?php $divider = FALSE; ?>
						<?php if ($this->session->userdata('user_type') == "admin" || $this->session->userdata('UM') == 1 ) : ?>
							<li><?php echo anchor('user/active', '<i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;User Management'); ?></li>
						<?php endif; ?>
                        <?php if(isSystemUser() && config_item('messenger')) : ?>
                            <li><?php echo anchor('billing', '<i class="fa fa-credit-card"></i>&nbsp;&nbsp;Billing Management'); ?></li>
                        <?php endif; ?>
                        <?php if(isAdmin()) : ?>
                            <li><?php echo anchor('settings', '<i class="fa fa-cogs"></i>&nbsp;&nbsp;Company Management'); ?></li>

                            <li><?php echo anchor('brands', '<i class="fa fa-bookmark"></i>&nbsp;&nbsp;Brands'); ?></li>
                        <?php endif; ?>

						<?php /* if ($this->session->userdata('user_type') == "admin") : ?>
							<li><?php echo anchor('administration/backups', 'Database Backups'); ?></li>
							<li class="divider"></li>
						<?php endif; */?>


						<?php if($divider) : ?>
							<?php $divider = FALSE; ?>
							<li class="divider"></li>
						<?php endif; ?>

                        <li><a href="https://help.arbostar.com/" target="_blank"> <i class="fa fa-book"></i>&nbsp;&nbsp;Knowledge Base</a></li>
						<li><?php echo anchor('login/logout', '<i class="glyphicon glyphicon-log-out"></i>&nbsp;&nbsp;Logout') ?></li>

					<?php endif; ?>

					<?php if (isEmployee()) : ?>
						<li><a href="#" id="emp_logout">Logout</a></li>
					<?php endif; ?>

				</ul>
			</li>
		</ul>
	</header>
	<section>
		<section class="hbox stretch">
            <?php if(isUserLoggedIn()): ?>
			    <?php $this->load->view('side_nav'); ?>
            <?php endif; ?>
			<section id="content" class="bg-white bg-light dk" style="width: 100%;">
				<section class="vbox">
					<?php echo !empty($msg) ? $msg : $this->session->flashdata('user_message'); ?>

