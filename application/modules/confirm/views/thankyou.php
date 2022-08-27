<!DOCTYPE html>
<html class="app js no-touch no-android chrome no-firefox no-iemobile no-ie no-ie10 no-ie11 no-ios">
<head>
	<meta charset="utf-8">
	<title>Thank You - <?php echo brand_name(isset($brand_id)?$brand_id:0); ?></title>
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
</head>
<body class="">
<section id="content">
	<div class="row m-n">
		<div class="col-sm-12">
			<div class="text-center m-b-lg">
				<h1 class="h text-badge animated fadeInDownBig">Thank You</h1>
			</div>
			<div class="list-group p-n bg-white m-b-lg col-sm-4 col-sm-offset-4">
				<a href="<?php echo brand_site(isset($brand_id)?$brand_id:0); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<i class="fa fa-fw fa-home icon-muted"></i> Goto <?php echo brand_name(isset($brand_id)?$brand_id:0); ?>
				</a>
				<a href="mailto:<?php echo brand_email(isset($brand_id)?$brand_id:0);?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<i class="fa fa-fw fa-question icon-muted"></i> Contact Us
				</a>
				<a href="tel:<?php echo brand_phone(isset($brand_id)?$brand_id:0, true); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<span class="badge"><?php echo brand_phone(isset($brand_id)?$brand_id:0); ?></span>
					<i class="fa fa-fw fa-phone icon-muted"></i> Call Us
				</a>
			</div>
		</div>
	</div>
</section>
<!-- footer -->
<footer id="footer">
	<div class="text-center padder clearfix">
		<p>
			<small><?php echo brand_name(isset($brand_id)?$brand_id:0, true); ?>, All Rights Reserved.<br>© <?php echo date('Y'); ?></small>
		</p>
	</div>
</footer>
</body></html>
