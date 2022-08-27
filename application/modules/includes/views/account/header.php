<html lang="en"
      class="app js no-touch no-android chrome no-firefox no-iemobile no-ie no-ie10 no-ie11 no-ios js-focus-visible"
      data-js-focus-visible="">
<head>
    <meta charset="utf-8">
    <title><?php echo isset($title) ? $title . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description"
          content="app, web app, responsive, admin dashboard, admin, flat, flat ui, ui kit, off screen nav">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/bootstrap.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/animate.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font-awesome.min.css'); ?>"
          type="text/css">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/font.css'); ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendors/notebook/css/app.css?v='.config_item('app.css')); ?>" type="text/css">
    <script src="<?php echo base_url('assets/js/jquery.js'); ?>"></script>
    <!--[if lt IE 9]>
    <script src="<?php echo base_url('assets/vendors/notebook/js/ie/html5shiv.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendors/notebook/js/ie/respond.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendors/notebook/js/ie/excanvas.js'); ?>"></script>
    <![endif]-->
    <script>
        var baseUrl = '<?php echo base_url(); ?>';
    </script>
</head>
<body class="" style="">
<section class="vbox">
    <header class="bg-primary dk header navbar navbar-fixed-top-xs">
        <div class="text-center">
            <a href="<?php echo base_url("dashboard"); ?>" class="navbar-brand">
                <img src="<?php echo base_url($this->config->item('company_header_logo')); ?>" style="<?php echo $this->config->item('company_header_logo_styles'); ?>" class="m-r-sm">
                <?php echo $this->config->item('company_header_logo_string'); ?>
            </a>
        </div>
    </header>
