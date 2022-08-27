<!DOCTYPE html>
<html class="app js no-touch no-android chrome no-firefox no-iemobile no-ie no-ie10 no-ie11 no-ios">
<head>
	<meta charset="utf-8">
	<title>unsubscribe - <?php echo $this->config->item('company_name_short'); ?></title>
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
	<script src="<?php echo base_url('assets/js/jquery.js'); ?>"></script>
	<script src="<?php echo base_url('assets/vendors/notebook/js/bootstrap.js'); ?>"></script>
	<script src="<?php echo base_url('assets/js/common.js?v='.config_item('js_common')); ?>"></script>
</head>
<body class="" style="overflow: auto;">
<section id="content">
	<div class="row m-n">
		<div class="col-sm-8 col-sm-offset-2">
			<div class="text-center m-b-lg p-lg" style="height: 300px; padding-top: 70px;">
				<h1 class="text-badge animated fadeInDownBig">
					Email notifications have been successfully disabled
				</h1>
			</div>

			<div class="list-group p-n bg-white m-b-lg col-sm-4 col-sm-offset-4">
				<a href="<?php echo $this->config->item('company_site'); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<i class="fa fa-fw fa-home icon-muted"></i> Goto <?php echo $this->config->item('company_site_name'); ?>
				</a>
				<a href="mailto:<?php echo $this->config->item('account_email_address'); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<i class="fa fa-fw fa-question icon-muted"></i> Contact Us
				</a>
				<a href="tel:<?php echo $this->config->item('office_phone'); ?>" class="list-group-item" style="text-decoration: none;">
					<i class="fa fa-chevron-right icon-muted"></i>
					<span class="badge"><?php echo $this->config->item('office_phone_mask'); ?></span>
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
			<small><?php echo $this->config->item('company_name_long'); ?>, All Rights Reserved.<br>© <?php echo date('Y'); ?></small>
		</p>
	</div>
</footer>
<script>
	$(document).ready(function(){
		setTimeout(function(){
			location.href = '<?php echo $this->config->item('company_site'); ?>';
		}, 5000);
	});
</script>
</body></html>
